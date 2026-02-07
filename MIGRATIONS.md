# Database Migrations

This document focuses exclusively on managing database schema changes using Doctrine Migrations.

## Running Migrations

These commands apply versioned changes to your database schema.

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

---

## Development & Maintenance

### Check Status

Displays information about the current state of migrations.

```bash
sudo docker compose exec app php tracker migrations:status
```

**Parameters:**

- `--show-versions`: List available migration versions and their status.

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

Creates an empty migration file for manual SQL writing.

```bash
sudo docker compose exec app php tracker migrations:generate
```

**Parameters:**

- `--namespace`: Specify a custom namespace for the migration class.

### Current Version

Display the version currently applied to the database.

```bash
sudo docker compose exec app php tracker migrations:current
```

### List All Migrations

Shows a list of all migration versions available in the project.

```bash
sudo docker compose exec app php tracker migrations:list
```

### Sync Metadata

Synchronizes the migration metadata table with the available migration files.

```bash
sudo docker compose exec app php tracker migrations:sync-metadata-storage
```
