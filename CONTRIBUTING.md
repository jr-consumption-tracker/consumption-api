# Contributing

Tato sekce popisuje proces přispívání a vydávání nových verzí projektu.

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
Nahraj změny na svůj fork a vytvoř Pull Request do hlavní větve (`main`):
```bash
git push origin feature/release-v0.2.1
```
1. Otevři Pull Request na GitHubu.
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
