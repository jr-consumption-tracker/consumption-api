# Contributing

Tato sekce popisuje, jak vyvíjet, jak fungují branche, a jak se appka nasazuje/vydává.

## Vývoj

Appka lokálně běží přes Docker Compose (appka + Nginx, MySQL, Redis, phpMyAdmin, Mailhog,
Xdebug) — kompletní příkazy jsou v [`DOCKER_COMMANDS.md`](DOCKER_COMMANDS.md). Rychlý start:

```bash
cd docker
docker compose up -d
```

## Branch strategie

Jedna integrační větev, žádná `develop`:

```
feature/xxx, fix/xxx  → PR do main
main                    → KAZDY merge automaticky nasadi appku do "dev" prostredi
tag vX.Y.Z na main      → automaticky nasadi appku do "prod" prostredi (az bude hosting)
```

- **Feature/fix větve** — vytváříš z `main`, PR míří zpátky do `main`. CI (testy,
  PHPStan, Docker build test) proběhne na PR, appka se ale nikam nenasazuje.
- **`main`** — jakmile se do něj něco smerguje (bez tagu), CI automaticky postaví Docker
  image, pošle ho do GHCR a **samo** upraví tag v `consumption-gitops` — appka v "dev"
  prostředí (`https://spotreba-energie.local/api/`) se do pár minut aktualizuje bez
  jakéhokoliv ručního zásahu. Žádné SSH na server, žádný ruční `kubectl apply`.
- **Tag na `main`** (`vX.Y.Z`) — samostatný, vědomý krok navíc k běžnému merge (viz
  "Vydávání nových verzí" níže). Spouštěčem nasazení do produkce je jen tag, ne merge.

Jak přesně appka běží v Kubernetes (dev/prod rozdíly, jak funguje sdílený Docker image
napříč prostředími) je popsané v `consumption-gitops` repu,
[`docs/DEPLOYMENT_GUIDE.md`](https://github.com/jr-consumption-tracker/consumption-gitops/blob/main/docs/DEPLOYMENT_GUIDE.md).

## Nasazování

**Do dev prostředí:** automaticky, viz výše — stačí smergovat PR do `main`.

**Do produkce (release):** vytvoření tagu na `main`, viz sekce níže
("Vydávání nových verzí"). Dokud neexistuje produkční hosting, tag appku nikam
nenasadí (jen postaví a otaguje image) — jakmile hosting bude, CI i gitops repo jsou
na to už teď připravené (`overlays/prod/` bude potřeba jen založit, viz
`DEPLOYMENT_GUIDE.md` sekce 7).

## Vydávání nových verzí (Release Process)

Pro vydání nové verze používáme sadu automatizovaných skriptů v `src/api`.
Tyto skripty zajišťují konzistenci, generování changelogu a správné tagování v hlavním repozitáři.

### Prerekvizity

Musíš mít nastavený `upstream` remote, který směřuje na **hlavní repozitář** (ne na tvůj fork).
Ověř pomocí: `git remote -v`. Pokud chybí, přidej ho:

```bash
git remote add upstream https://github.com/jr-consumption-tracker/consumption-api.git
```

### Krok 1: Zvýšení verze
Spusť v `src/api`:
```bash
composer release:version
```
- Interaktivně se zeptá na typ verze (Patch, Minor, Major, Pre-release).
- Aktualizuje `composer.json` a `.env`.
- **Automaticky necommituje**, aby sis mohl změny zkontrolovat.

### Krok 2: Generování Changelogu
Vygeneruj náhled changelogu (stahuje tagy z upstreamu pro přesné porovnání):
```bash
composer release:changelog
```
- Zkopíruj si výstup a případně ho vlož do `CHANGELOG.md` nebo si ho schovej pro GitHub Release.

### Krok 3: Commit změn
Vytvoř commit s novou verzí (a případně aktualizovaným changelogem):
```bash
git commit -am "chore: release v0.2.1"
```

### Krok 4: Push a Pull Request
Release branch vytváříš z `main` (žádná zvláštní výjimka — je to stejný postup jako
u kteréhokoliv jiného PR, protože `main` je jediná integrační větev):
```bash
git push origin feature/release-v0.2.1
```
1. Otevři Pull Request na GitHubu, cílová větev `main`.
2. Počkej na CI/CD testy a review.
3. **Zmerguj** PR do `main`.

### Krok 5: Příprava na Tagování
Přepni se na `main` a stáhni aktuální stav z upstreamu:
```bash
git checkout main
git pull upstream main
```

### Krok 6: Vytvoření a Push Tagu
Finální krok, který vytvoří tag a pošle ho do světa:
```bash
composer release:tag
```
- Kontroluje, zda máš vše commmitnuté a pushnuté v `main`.
- Ověří, zda tag už neexistuje na upstreamu (pokud existuje lokálně, opraví ho).
- Vytvoří tag (např. `v0.2.1`) a **pushne ho přímo na upstream**.

### Krok 7: GitHub Release
1. Jdi na GitHub do záložky **Releases**.
2. Klikni na **Draft a new release**.
3. Vyber tag, který jsi právě vytvořil.
4. Do popisu vlož text z **Kroku 3**.
5. Zveřejni release.

---
**Poznámka:** Všechny skripty jsou interaktivní a v případě chyby (např. neexistující upstream) tě včas zastaví.
