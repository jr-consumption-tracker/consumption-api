# Database Migrations

This document focuses exclusively on managing database schema changes using Doctrine Migrations.

## Kde se dají migrace spouštět

| Prostředí | Jak | Poznámka |
|---|---|---|
| Lokálně (Docker Compose) | `docker compose exec app ...` | appka běží ze zdrojáků, filesystem je zapisovatelný |
| k3s dev/prod | `kubectl exec ...` | appka běží z Docker image, **`readOnlyRootFilesystem: true`** — příkazy, co jen čtou/mění databázi, fungují; příkazy, co **zapisují nové soubory** (`migrations:diff`, `migrations:generate`), tam nejdou |

**Nové migrace se vždy vytváří jen lokálně** (`migrations:diff`/`migrations:generate`), commitnou do gitu a nasadí se normálně přes Docker image/CI — ne přímo na serveru. Na serveru se migrace jen **spouští** (`migrations:migrate` apod.), nikdy negeneruje.

---

## Lokálně (Docker Compose)

Tyto příkazy spouštěj z hlavního adresáře, kde běží `docker compose`.

### Migrate to version (Up/Down)

By default, this runs all available migrations to reach the latest version.

```bash
sudo docker compose exec app php tracker migrations:migrate
```

**Parameters:**

- `[version]`: Target version (e.g., `latest`, `first`, or a specific timestamp like `20260126203937`).
- `--dry-run`: Only display the SQL queries without executing them.
- `--write-sql=[path]`: Saves the SQL queries to a file instead of executing them.
- `--allow-no-migration`: Do not throw an error if no migrations are available to run.
- `--all-or-nothing`: Wrap the entire migration in a single transaction (if supported by DB).
- `--query-time`: Show the time spent on each query.
- `--no-interaction` (`-n`): Execute without asking for confirmation (Safe for CI/CD).

### Execute specific migration

Manually run a single migration version up or down.

```bash
sudo docker compose exec app php tracker migrations:execute Version20260126203937 --down
```

**Parameters:**

- `--up`: Run the "up" method of the migration.
- `--down`: Run the "down" method of the migration.
- `--dry-run`: Display SQL without executing.
- `--write-sql=[path]`: Save SQL to a file.

### Check Status

```bash
sudo docker compose exec app php tracker migrations:status
```

### Generate Diff

Automatically generates a new migration file by comparing your current database entities with the database schema.

```bash
sudo docker compose exec app php tracker migrations:diff
```

**Parameters:**

- `--formatted`: Format the generated SQL.
- `--line-length`: Max line length for generated SQL.
- `--check-database-platform`: Check if the database platform is correct.
- `--allow-empty-diff`: Generate a migration even if no changes are detected.
- `--filter-expression`: Regex to filter which tables should be included in the diff.

### Generate Blank Migration

```bash
sudo docker compose exec app php tracker migrations:generate
```

### Current Version

```bash
sudo docker compose exec app php tracker migrations:current
```

### List All Migrations

```bash
sudo docker compose exec app php tracker migrations:list
```

### Sync Metadata

```bash
sudo docker compose exec app php tracker migrations:sync-metadata-storage
```

---

## Na serveru (k3s dev/prod)

Stejné příkazy, jen `docker compose exec app` nahradíš `kubectl exec` do běžícího Podu.
Namespace je `consumption-dev` (nebo `consumption-prod`, až bude existovat), kontejner
uvnitř Podu se jmenuje `app`.

```bash
kubectl exec -n consumption-dev -it deploy/api -c app -- php tracker migrations:migrate
kubectl exec -n consumption-dev -it deploy/api -c app -- php tracker migrations:status
kubectl exec -n consumption-dev -it deploy/api -c app -- php tracker migrations:current
kubectl exec -n consumption-dev -it deploy/api -c app -- php tracker migrations:execute Version20260126203937 --down
```

> ⚠️ **`migrations:diff` a `migrations:generate` na serveru nepůjdou** —
> `readOnlyRootFilesystem: true` na `app` kontejneru zabraňuje zápisu nového souboru
> migrace kamkoliv do image. To je záměr, ne chyba: nová migrace vzniká lokálně, jde
> do gitu jako součást appky, a na server se dostane přes normální nasazení (build →
> image → deploy), ne ručním zásahem na produkčním kontejneru.

### Doporučený postup pro novou migraci

1. Lokálně: `docker compose exec app php tracker migrations:diff`, zkontroluj vygenerovanou SQL
2. Commitni migraci do gitu, PR do `develop`
3. Appka se automaticky nasadí do dev (viz `CONTRIBUTING.md`) — **migrace se ale nespustí sama**, appka jen běží s novým kódem
4. Spusť migraci ručně proti dev databázi (příkaz výše)
5. Ověř appku, pak stejný postup zopakuj pro produkci (až bude existovat) — vlastní databáze, vlastní spuštění migrace, nikdy sdílené s dev

> Proč se migrace nespouští automaticky při deployi? Schéma databáze je citlivá operace
> (nevratná bez zálohy) — chceme mít vždy vědomou kontrolu nad tím, kdy přesně se spustí,
> ne že se to stane jako vedlejší efekt běžného nasazení kódu.
