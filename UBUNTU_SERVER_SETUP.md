# Nastavení domácího Ubuntu serveru

Tento dokument popisuje kompletní postup instalace a konfigurace domácího serveru krok za krokem.
Slouží jako reference pro budoucí úpravy nebo přenastavení. Všechny příkazy jsou vysvětleny,
aby bylo jasné, co dělají a proč.

> **Poznámka k verzím:** Všude, kde se objeví označení `phpX.Y`, nahraď číslem verze,
> kterou aktuálně instaluješ (např. `8.3` nebo `8.4`).
> Aktuálně dostupné verze zjistíš příkazem: `apt-cache search php | grep fpm`

---

## 1. Instalace Ubuntu Server

Při instalaci postupuj takto:

1. **Jazyk:** zvol **anglicky** — anglická dokumentace a návody jsou výrazně dostupnější
2. **Proxy:** ponech prázdné, pokud nepoužíváš firemní nebo školní proxy
3. **Mirror (zdroj balíčků):** zvol standardní, nebo český:
   ```
   http://cz.archive.ubuntu.com/ubuntu
   ```
4. **Diskové oddíly** (konkrétní příklad použitý při instalaci):
   - `/` – 100 GB (kořenový oddíl, systém a programy)
   - `/var` – 250 GB (logy, databáze, weby — roste nejvíc)
   - `swap` – 2 GB (virtuální RAM, při 8 GB RAM dostačuje)
   - Zbytek ponech neformátovaný — pro pozdější použití s LVM nebo Docker storage
5. **Uživatelské jméno:** zadej své (např. `honza`) — vytvoří se automaticky administrátorský účet
6. **SSH:** zaškrtni **OpenSSH server** — umožní vzdálené přihlášení
7. **Import SSH klíče:** klíče neimportuj, pak se nejde přihlásit heslem
8. **Snaps:** zvol pouze to, co potřebuješ (např. `powershell`), ostatní přeskočit

Po dokončení instalace se server restartuje a přihlásíš se buď přímo, nebo přes SSH.

---

## 2. První přihlášení a základní kontrola

### Přihlášení přes SSH ze svého počítače

```bash
ssh honza@192.168.0.143
# Obecně: ssh TVŮJ_UŽIVATEL@IP_SERVERU
```

> SSH = Secure Shell — šifrované vzdálené připojení k příkazové řádce serveru.

### Ověření, že jsi přihlášen jako správný uživatel

```bash
whoami   # vypíše tvé uživatelské jméno
id       # vypíše skupiny, ve kterých jsi — důležité pro Docker a oprávnění
```

### Povolení přihlášení heslem přes SSH (pokud nechceš klíče)

Otevři konfigurační soubor SSH:

```bash
sudo nano /etc/ssh/sshd_config
```

Případně něco uprav

Ulož soubor (`Ctrl+O`, pak `Enter`, pak `Ctrl+X`) a restartuj SSH:

```bash
sudo systemctl restart ssh
```

> ⚠️ SSH restartuj vždy po změně jeho konfigurace. Stávající připojení zůstane aktivní,
> nové přihlášení ale bude fungovat s novým nastavením.

---

## 3. Aktualizace systému

Toto proveď vždy jako první krok po čisté instalaci:

```bash
sudo apt update        # stáhne seznam dostupných balíčků
sudo apt upgrade -y    # nainstaluje všechny dostupné aktualizace
sudo apt autoremove -y # odstraní nepotřebné závislosti
```

> `sudo` = spusť příkaz jako administrátor (superuser). Server si může vyžádat tvé heslo.

---

## 4. Firewall (UFW)

UFW (Uncomplicated Firewall) je jednoduchý firewall integrovaný v Ubuntu.
Nastavíme ho tak, aby byly povoleny jen potřebné služby.

### Instalace (pokud není přítomen)

```bash
sudo apt install ufw -y
```

### Nastavení pravidel

```bash
# Výchozí politika — odmítni vše příchozí, povol vše odchozí
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Povol SSH — VŽDY jako první, jinak se odřízneš od serveru!
sudo ufw allow ssh         # ekvivalent: sudo ufw allow 22/tcp
sudo ufw allow OpenSSH    # podle pojmenovaného profilu UFW

# Povol HTTP a HTTPS (webový server)
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
```

### Zapnutí firewallu

```bash
sudo ufw enable
```

Potvrď výzvou `y`. Firewall se spustí a bude aktivní i po každém restartu serveru.

### Ověření stavu

```bash
sudo ufw status verbose
```

Výstup by měl vypadat přibližně takto:

```
Status: active
To                         Action      From
--                         ------      ----
22/tcp                     ALLOW IN    Anywhere
80/tcp                     ALLOW IN    Anywhere
443/tcp                    ALLOW IN    Anywhere
```

> ⚠️ Pokud přidáš novou službu vyžadující síťový přístup, nezapomeň přidat
> pravidlo i do firewallu. Bez toho bude port blokován, i když služba běží.

---

## 5. Nastavení statické IP adresy

Aby server měl vždy stejnou IP adresu v síti — nutné pro DNS záznamy a přístup z ostatních zařízení.

### Zjisti název síťového rozhraní

```bash
ip link show
```

Hledej název jako `enp3s0`, `eth0`, `ens18` apod. — závisí na hardwaru serveru.

### Uprav konfigurační soubor Netplan

```bash
sudo nano /etc/netplan/00-installer-config.yaml
```

Obsah souboru (příklad použitý při konfiguraci):

```yaml
network:
  version: 2
  renderer: networkd
  ethernets:
    enp3s0: # název tvého síťového rozhraní
      dhcp4: no # vypne automatické přidělování IP
      addresses:
        - 192.168.0.143/24 # statická IP serveru
      gateway4: 192.168.0.1 # IP tvého routeru
      nameservers:
        addresses: [192.168.0.1, 8.8.8.8] # DNS servery (router + Google)
```

> **Pozor na odsazení!** YAML soubory jsou citlivé na mezery — vždy používej mezery, ne tabulátory.

### Aplikuj konfiguraci

```bash
sudo netplan apply
```

### Ověř novou IP

```bash
ip a
```

---

## 6. Docker a uživatelská práva

Docker umožňuje spouštět aplikace (databáze, Redis apod.) izolovaně v kontejnerech.

### Instalace Dockeru

```bash
sudo apt update
sudo apt install docker.io -y
```

```
> Pro instalaci docker compose si přečti návod zde: https://docs.docker.com/engine/install/ubuntu/
```

### Přidej svého uživatele do skupiny `docker`

Bez toho bys musel před každý Docker příkaz psát `sudo`:

```bash
sudo usermod -aG docker $USER
```

> `$USER` = proměnná obsahující tvé aktuální uživatelské jméno. Nemusíš ho psát ručně.

### Aktivuj změnu skupiny bez odhlášení

```bash
newgrp docker
```

### Ověř, že Docker funguje

```bash
docker run hello-world
```

Pokud uvidíš zprávu `Hello from Docker!`, vše funguje správně.

### Ověř verze

```bash
docker --version
docker-compose --version
```

**Kdy restartovat Docker?**
Po změně `/etc/docker/daemon.json` nebo po systémových konfiguracích:

```bash
sudo systemctl restart docker
```

Po přidání uživatele do skupiny stačí `newgrp docker` nebo nové přihlášení — restart není nutný.

**Pro Docker** — tady je důležité varování, které by rozhodně mělo být v dokumentu. Docker ve výchozím nastavení **obchází UFW** tím, že přímo manipuluje s iptables. To znamená, že pokud Docker otevře port, UFW ho neblokuje, i kdyby pravidlo neexistovalo. Toto patří do **sekce 6 (Docker)** jako poznámka:

> ⚠️ Docker a UFW: Docker ve výchozím nastavení obchází UFW pravidla
> tím, že přímo manipuluje s iptables. Porty namapované v docker-compose.yml
> (např. "3306:3306") jsou dostupné z celé sítě, i bez pravidla v UFW.
> Pokud nechceš, aby byl MySQL dostupný z celé sítě, namapuj port pouze
> na localhost: "127.0.0.1:3306:3306"

---

## 7. PHP a PHP-FPM

PHP běží nativně na serveru (ne v Dockeru) pro rychlejší integraci s Nginx.

### Přidání repozitáře s aktuálními verzemi PHP

Základní Ubuntu repozitáře nemusí obsahovat nejnovější PHP. Přidej repozitář Ondřeje Surého:

```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### Instalace PHP a rozšíření

Nahraď `X.Y` požadovanou verzí (např. `8.3` nebo `8.4`):

```bash
sudo apt install phpX.Y phpX.Y-fpm phpX.Y-mysql phpX.Y-curl phpX.Y-mbstring phpX.Y-xml phpX.Y-zip phpX.Y-redis -y
```

### Spuštění a povolení PHP-FPM

```bash
sudo systemctl start phpX.Y-fpm    # spustí službu okamžitě
sudo systemctl enable phpX.Y-fpm   # zapne automatický start po každém restartu serveru
```

### Ověření stavu

```bash
sudo systemctl status phpX.Y-fpm
```

Hledej řádek `Active: active (running)` — zelený text.

### Kde se nachází PHP-FPM socket

Socket soubor, přes který Nginx komunikuje s PHP:

```
/run/php/phpX.Y-fpm.sock
```

> **Kdy restartovat PHP-FPM?**
> Po každé změně konfigurace PHP (soubory `php.ini` nebo `www.conf`):
>
> ```bash
> sudo systemctl restart phpX.Y-fpm
> ```

---

## 8. Nginx

Nginx je webový server, který přijímá HTTP/HTTPS požadavky, servíruje statické soubory
a předává PHP požadavky PHP-FPM.

### Instalace

```bash
sudo apt install nginx -y
```

### Spuštění a povolení

```bash
sudo systemctl start nginx
sudo systemctl enable nginx
```

### Ověření stavu

```bash
sudo systemctl status nginx
```

---

## 9. Struktura adresářů webu

### Vytvoření adresářů (ve správném pořadí)

```bash
# Vytvoř hlavní adresář webu
sudo mkdir -p /var/www/spotreby-energii

# Vytvoř podadresáře pro administraci a API
sudo mkdir -p /var/www/spotreby-energii/admin
sudo mkdir -p /var/www/spotreby-energii/api
```

> `-p` = vytvoř i nadřazené adresáře, pokud neexistují. Nevyhodí chybu, pokud adresář už existuje.

### Nastavení vlastníka

```bash
sudo chown -R $USER:$USER /var/www/spotreby-energii
```

> `chown` = změna vlastníka souboru nebo adresáře  
> `-R` = rekurzivně (vztahuje se i na všechny podadresáře a soubory)  
> `$USER:$USER` = vlastník i skupina = tvůj přihlášený uživatel

### Nastavení oprávnění

```bash
sudo chmod -R 755 /var/www/spotreby-energii
```

> `chmod 755` znamená:
>
> - **7** = vlastník může číst, psát a spouštět
> - **5** = skupina může číst a spouštět
> - **5** = ostatní mohou číst a spouštět
>
> Pro webový server je to standardní nastavení — Nginx může soubory číst a procházet adresáře,
> ale upravovat je může jen tvůj uživatel.

### Výsledná struktura

```
/var/www/spotreby-energii/
├── index.html          ← hlavní stránka
├── admin/
│   └── index.html      ← administrační rozhraní
└── api/
    └── index.php       ← vstupní bod API
```

---

## 10. Lokální SSL certifikáty (mkcert)

`mkcert` generuje důvěryhodné lokální HTTPS certifikáty pro vývoj bez bezpečnostních varování v prohlížeči.

### Instalace závislostí a mkcert

```bash
sudo apt install libnss3-tools -y   # potřebná závislost pro správu certifikátů

# Stáhni nejnovější verzi mkcert pro Linux (64bit)
sudo apt install mkcert

# Udělej soubor spustitelným - toto asi není potřeba
chmod +x mkcert

# Přesuň ho do systémové cesty, aby byl dostupný jako příkaz - toto asi není potřeba
sudo mv mkcert /usr/local/bin/mkcert
```

> Aktuální verzi a odkaz ke stažení vždy zkontroluj na: https://github.com/FiloSottile/mkcert/releases

### Instalace lokální certifikační autority

```bash
mkcert -install
```

> Tím se vytvoří lokální CA (Certificate Authority) — tvůj počítač i server jí začnou důvěřovat.
> Prohlížeč pak nebude zobrazovat varování o nedůvěryhodném certifikátu.

### Vygenerování certifikátu pro doménu

```bash
cd ~   # přejdi do domovského adresáře, aby se soubory vytvořily tam
mkcert spotreby-energii.local
```

Příkaz vytvoří dva soubory v aktuálním adresáři:

- `spotreby-energii.local.pem` — certifikát (veřejný, sdílí se s Nginx)
- `spotreby-energii.local-key.pem` — privátní klíč (tajný! nikdy nesdílet)

### Příprava adresáře pro certifikáty

Toto proveď ve správném pořadí:

```bash
# 1. Vytvoř adresář pro certifikáty
sudo mkdir -p /etc/ssl/localcerts

# 2. Nastav vlastníka adresáře na roota
sudo chown root:root /etc/ssl/localcerts

# 3. Nastav oprávnění adresáře (čitelný pro všechny, zapisovat může jen root)
sudo chmod 755 /etc/ssl/localcerts
```

### Přesunutí certifikátů do adresáře

```bash
sudo mv ~/spotreby-energii.local.pem /etc/ssl/localcerts/
sudo mv ~/spotreby-energii.local-key.pem /etc/ssl/localcerts/
```

### Nastavení správných oprávnění certifikátů

```bash
# Privátní klíč — čitelný POUZE rootem (tajný!)
sudo chmod 600 /etc/ssl/localcerts/spotreby-energii.local-key.pem

# Certifikát — čitelný všemi (veřejný, Nginx ho potřebuje číst)
sudo chmod 644 /etc/ssl/localcerts/spotreby-energii.local.pem

# Vlastník obou souborů = root
sudo chown root:root /etc/ssl/localcerts/spotreby-energii.local-*.pem
```

> **Proč taková přísná práva na privátní klíč?**
> Privátní klíč je jako heslo k certifikátu. Pokud by ho někdo získal,
> mohl by se vydávat za tvůj server a dešifrovat komunikaci. Proto ho smí číst pouze root.

### Ověření výsledku

```bash
ls -la /etc/ssl/localcerts/
```

Výstup by měl vypadat přibližně takto:

```
drwxr-xr-x  2 root root 4096 ...
-rw-r--r--  1 root root xxxx ... spotreby-energii.local.pem
-rw-------  1 root root xxxx ... spotreby-energii.local-key.pem
```

---

## 11. Konfigurace Nginx pro web

Nginx konfiguraci pro každý web ukládáme do `/etc/nginx/sites-available/`
a aktivujeme symbolickým odkazem do `/etc/nginx/sites-enabled/`.

### Vytvoření konfiguračního souboru

```bash
sudo nano /etc/nginx/sites-available/spotreby-energii.local
```

Obsah souboru (nahraď `X.Y` svou verzí PHP):

```nginx
# ── Přesměrování HTTP → HTTPS ─────────────────────────────────────
server {
    listen 80;
    server_name spotreby-energii.local;

    # Všechny HTTP požadavky přesměruj na HTTPS
    # 301 = trvalé přesměrování (prohlížeč si ho zapamatuje)
    return 301 https://$host$request_uri;
}

# ── Hlavní HTTPS server ────────────────────────────────────────────
server {
    listen 443 ssl;
    server_name spotreby-energii.local;

    # Cesta k SSL certifikátům
    ssl_certificate     /etc/ssl/localcerts/spotreby-energii.local.pem;
    ssl_certificate_key /etc/ssl/localcerts/spotreby-energii.local-key.pem;

    # Kořenový adresář webu
    root  /var/www/spotreby-energii;
    index index.html index.php;

    # Hlavní stránka
    # Nginx hledá: nejdřív soubor, pak adresář, pak zobrazí index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Administrace
    location /admin/ {
        try_files $uri $uri/ /admin/index.html;
    }

    # API — fallback na index.php s předáním query parametrů
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
    }

    # Zpracování PHP souborů přes PHP-FPM
    # POZOR: nahraď X.Y svojí verzí PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/phpX.Y-fpm.sock;
    }
}
```

### Aktivace konfigurace

```bash
# Vytvoř symbolický odkaz — aktivuje konfiguraci pro Nginx
sudo ln -s /etc/nginx/sites-available/spotreby-energii.local /etc/nginx/sites-enabled/
```

> Symbolický odkaz funguje jako zástupce. Nginx čte ze `sites-enabled`,
> soubor ale fyzicky leží v `sites-available`.
> Deaktivace webu = smazání odkazu (bez mazání samotné konfigurace):
>
> ```bash
> sudo rm /etc/nginx/sites-enabled/spotreby-energii.local
> ```

### Test konfigurace (vždy před reloadem!)

```bash
sudo nginx -t
```

Výstup by měl obsahovat:

```
nginx: configuration file /etc/nginx/nginx.conf syntax is OK
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

Pokud je chyba, Nginx přesně řekne, na kterém řádku a v jakém souboru.
**Nikdy nedělej reload, pokud test selže** — Nginx by se mohl zastavit.

### Reload Nginx

```bash
sudo systemctl reload nginx
```

> **`reload` vs `restart` — jaký je rozdíl?**
>
> | Příkaz    | Co dělá                                       | Kdy použít                    |
> | --------- | --------------------------------------------- | ----------------------------- |
> | `reload`  | Načte novou konfiguraci bez přerušení spojení | Po každé změně konfigurace ✅ |
> | `restart` | Úplně zastaví a znovu spustí Nginx            | Pouze pokud reload nestačí ⚠️ |
>
> Po změně konfiguračního souboru vždy používej `reload`.

---

## 12. Hosts soubor pro lokální DNS

Doména `spotreby-energii.local` neexistuje na internetu — musíme počítači říct, kde ji hledat.

### Na Windows

Soubor: `C:\Windows\System32\drivers\etc\hosts`

Otevři Poznámkový blok jako administrátor (pravý klik → Spustit jako správce) a přidej:

```
192.168.0.143   spotreby-energii.local
```

### Na Linuxu / macOS

```bash
sudo nano /etc/hosts
```

Přidej stejný řádek a ulož.

### Ověření

Otevři prohlížeč a zadej:

```
http://spotreby-energii.local
```

Server by měl automaticky přesměrovat na `https://spotreby-energii.local`.

---

## 13. Ověření funkčnosti celého stacku

### Stav všech služeb najednou

```bash
sudo systemctl status nginx
sudo systemctl status phpX.Y-fpm
sudo systemctl status docker
sudo ufw status verbose
```

### Které porty jsou otevřeny a naslouchají

```bash
sudo ss -tlnp
```

### Test odpovědi webového serveru

```bash
curl -Ik https://spotreby-energii.local
# -I = zobraz jen hlavičky odpovědi
# -k = ignoruj chybu certifikátu při lokálním testování
```

### Logy při řešení problémů

```bash
# Nginx chybový log (nejdůležitější)
sudo tail -f /var/log/nginx/error.log

# Nginx přístupový log (kdo přistupoval)
sudo tail -f /var/log/nginx/access.log

# PHP-FPM log
sudo tail -f /var/log/phpX.Y-fpm.log

# Systémový log (obecné chyby)
sudo journalctl -xe
```

> `tail -f` = sleduj soubor v reálném čase — vypisuje nové řádky průběžně.
> Ukonči stiskem `Ctrl+C`.

---

## 14. Přehled — kdy restartovat jakou službu

| Situace                                  | Příkaz                                         |
| ---------------------------------------- | ---------------------------------------------- |
| Změna `/etc/ssh/sshd_config`             | `sudo systemctl restart ssh`                   |
| Změna Nginx konfigurace                  | `sudo nginx -t && sudo systemctl reload nginx` |
| Změna `php.ini` nebo PHP-FPM konfigurace | `sudo systemctl restart phpX.Y-fpm`            |
| Změna `/etc/docker/daemon.json`          | `sudo systemctl restart docker`                |
| Změna síťové konfigurace Netplan         | `sudo netplan apply`                           |
| Přidání uživatele do skupiny Docker      | `newgrp docker` nebo nové přihlášení           |
| Změna pravidel UFW                       | `sudo ufw reload`                              |

---

## 15. Shrnutí architektury

```
Lokální síť
      │
      ▼
 UFW Firewall
 (port 22, 80, 443)
      │
      ▼
   Nginx
 (webový server)
 ┌────┴────┐
 │         │
 ▼         ▼
statické  PHP-FPM
soubory  (phpX.Y)
             │
             ▼
          Docker
   (MySQL, Redis, ...)
```

- **UFW** — firewall, první linie obrany
- **Nginx** — přijímá HTTP/HTTPS požadavky, servíruje statické soubory
- **PHP-FPM** — zpracovává PHP skripty, běží nativně pro rychlost
- **Docker** — izolované kontejnery pro databáze a další služby

---

## 16. Doporučení a průběžná údržba

**Více webů na jednom serveru:**
Pro každý web vytvoř samostatný soubor v `/etc/nginx/sites-available/` a aktivuj symbolickým odkazem. Každý web může mít vlastní doménu, SSL certifikát a kořenový adresář.

**Pravidelné aktualizace systému:**

```bash
sudo apt update && sudo apt upgrade -y
sudo apt autoremove -y
```

**Aktualizace Docker kontejnerů:**

```bash
docker pull nazev-image
docker-compose up -d   # restartuje kontejnery s novou verzí
```

**Záloha databáze přes Docker:**

```bash
docker exec nazev-kontejneru mysqldump -u root -p nazev_databaze > zaloha_$(date +%F).sql
```

**Monitorování:**

```bash
systemctl status nginx phpX.Y-fpm docker
docker ps             # seznam běžících kontejnerů
docker stats          # využití CPU a RAM v reálném čase
```

**Pro produkční nebo veřejný server:** místo mkcert použij certifikáty od Let's Encrypt (zdarma, automatická obnova přes `certbot`).

---

> 💡 **Tip:** Tento dokument si udržuj aktuální. Kdykoliv změníš konfiguraci nebo přidáš
> novou službu, doplň sem postup — až ho budeš za půl roku potřebovat, budeš rád.

# Infrastruktura aplikace — Docker, MySQL, phpMyAdmin

Tato sekce navazuje na základní nastavení serveru. Popisuje, jak na serveru
spravovat databáze a další služby přes Docker pro každou aplikaci zvlášť.

---

## Filosofie uspořádání

Na serveru jsou dvě oddělené věci pro každou aplikaci:

- **Infrastruktura** (`/srv/nazev-aplikace/`) — docker-compose, .env soubory, databáze
- **Kód aplikace** (`/var/www/nazev-aplikace/`) — PHP soubory nasazené deploy scriptem

Toto oddělení zajišťuje, že při nasazení nové verze aplikace se nikdy nesáhne
na databázi ani na konfiguraci služeb.

```
/srv/
└── spotreby-energii/
    ├── docker-compose.yml   ← definice služeb (MySQL, phpMyAdmin...)
    └── .env                 ← hesla a proměnné (nikdy v Gitu!)

/var/www/
└── spotreby-energii/
    └── api/                 ← kód aplikace nasazený deploy scriptem
```

---

## Co běží v Dockeru a co nativně

| Služba     | Kde běží           | Důvod                                   |
| ---------- | ------------------ | --------------------------------------- |
| PHP-FPM    | nativně na serveru | rychlejší integrace s Nginx             |
| Nginx      | nativně na serveru | správa více webů na jednom serveru      |
| Redis      | nativně na serveru | sdílený mezi aplikacemi, nižší overhead |
| MySQL      | Docker             | izolace dat každé aplikace zvlášť       |
| phpMyAdmin | Docker             | správa databáze přes prohlížeč          |

---

## Struktura docker-compose.yml pro aplikaci

Každá aplikace má vlastní `docker-compose.yml` v `/srv/nazev-aplikace/`.
Hesla a proměnné jsou vždy v `.env` souboru ve stejné složce — nikoliv přímo v docker-compose.

```yaml
name: spotreby-energii

services:
  db:
    image: mysql:8.0
    container_name: spotreby-energii-mysql
    restart: always
    ports:
      # Pouze localhost — PHP-FPM se připojí přes 127.0.0.1:3306
      # Port není viditelný zvenku sítě serveru
      - "127.0.0.1:3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    volumes:
      # Data přežijí restart i update kontejneru
      - db_data:/var/lib/mysql
    networks:
      - spotreby-energii-network

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: spotreby-energii-phpmyadmin
    restart: always
    ports:
      # Pouze localhost — přístup zvenku zajistí Nginx jako proxy
      - "127.0.0.1:8080:80"
    environment:
      PMA_HOST: db
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
    depends_on:
      - db
    networks:
      - spotreby-energii-network

networks:
  spotreby-energii-network:
    driver: bridge

volumes:
  db_data:
```

> **Proč `127.0.0.1:3306` a ne jen `3306`?**
> Bez prefixu `127.0.0.1` by port byl dostupný na všech síťových rozhraních serveru —
> tedy i z venku domácí sítě. Takto ho vidí pouze samotný server.

---

## Soubor .env

Vedle `docker-compose.yml` vytvoř soubor `.env` s reálnými hesly:

```bash
sudo nano /srv/spotreby-energii/.env
```

Obsah:

```
# MySQL
MYSQL_ROOT_PASSWORD=zmen_na_silne_heslo
MYSQL_DATABASE=spotreby-energii
MYSQL_USER=appuser
MYSQL_PASSWORD=zmen_na_silne_heslo_app
```

Zabezpeč soubor tak, aby ho četl pouze tvůj uživatel:

```bash
sudo chown $USER:$USER /srv/spotreby-energii/.env
sudo chmod 600 /srv/spotreby-energii/.env
```

> `.env` soubor nikdy nedávej do Gitu. Přidej `.env` do `.gitignore` projektu.
> Do Gitu patří pouze `.env.example` s prázdnými nebo vzorovými hodnotami.

---

## Spuštění služeb

```bash
cd /srv/spotreby-energii

# Spustí všechny služby na pozadí
docker-compose up -d

# Ověř, že kontejnery běží
docker ps

# Zobrazí logy (volitelně)
docker-compose logs -f
```

> `up -d` = spusť v detached režimu (na pozadí). Bez `-d` by výpisy zaplnily terminál
> a zastavením terminálu by se zastavily i kontejnery.

### Užitečné příkazy pro správu

```bash
# Restart konkrétní služby
docker-compose restart db

# Zastavení všech služeb (data zůstanou)
docker-compose stop

# Zastavení a smazání kontejnerů (data v db_data volume zůstanou!)
docker-compose down

# Zastavení a smazání včetně dat — POZOR, nevratné!
docker-compose down -v
```

---

## phpMyAdmin přes Nginx proxy

phpMyAdmin běží v Dockeru na `127.0.0.1:8080` a není přímo dostupný zvenku.
Nginx ho zpřístupní přes adresu `https://spotreby-energii.local/phpmyadmin/`
a ochrání ho heslem.

### Krok 1 — vytvoření souboru s heslem

Soubor s heslem vytvoříš bez instalace dalších nástrojů pomocí `openssl`:

```bash
echo "honza:$(openssl passwd -apr1 'tvoje_heslo')" | sudo tee /etc/nginx/.htpasswd
```

Nahraď `honza` svým uživatelským jménem a `tvoje_heslo` zvoleným heslem.

Ověř, že soubor vznikl:

```bash
cat /etc/nginx/.htpasswd
# Výstup bude vypadat přibližně takto:
# honza:$apr1$xK2Lj1...$hashovaneheslo
```

### Krok 2 — přidání proxy do Nginx konfigurace

Otevři konfigurační soubor webu:

```bash
sudo nano /etc/nginx/sites-available/spotreby-energii.local
```

Přidej `location` blok pro phpMyAdmin **dovnitř** stávajícího HTTPS server bloku,
těsně před jeho zavírací `}`:

```nginx
server {
    listen 443 ssl;
    server_name spotreby-energii.local;

    # ... stávající konfigurace zůstává beze změny ...

    # ── phpMyAdmin ────────────────────────────────────────────────
    location /phpmyadmin/ {
        # Ochrana heslem — bez správného hesla Nginx vrátí 401
        auth_basic "Restricted";
        auth_basic_user_file /etc/nginx/.htpasswd;

        # Proxy na phpMyAdmin kontejner běžící na localhostu
        proxy_pass http://127.0.0.1:8080/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

}  # ← tato závorka už tam byla, tou server blok končí
```

### Krok 3 — test a reload Nginx

```bash
# Nejdřív vždy otestuj konfiguraci — reload nikdy nedělej naslepo
sudo nginx -t

# Pokud je test OK, reloadni Nginx
sudo systemctl reload nginx
```

### Ověření přístupu

Otevři v prohlížeči:

```
https://spotreby-energii.local/phpmyadmin/
```

Prohlížeč by měl zobrazit výzvu k zadání jména a hesla (HTTP Basic Auth),
a po přihlášení phpMyAdmin rozhraní.

---

## Přidání další aplikace na server

Postup pro každou novou aplikaci je vždy stejný:

```bash
# 1. Vytvoř složku pro infrastrukturu
sudo mkdir -p /srv/nova-aplikace

# 2. Vytvoř docker-compose.yml a .env (viz vzor výše)
sudo nano /srv/nova-aplikace/docker-compose.yml
sudo nano /srv/nova-aplikace/.env
sudo chmod 600 /srv/nova-aplikace/.env

# 3. Spusť služby
cd /srv/nova-aplikace
docker-compose up -d

# 4. Vytvoř adresář pro kód aplikace
sudo mkdir -p /var/www/nova-aplikace/api
sudo chown -R $USER:$USER /var/www/nova-aplikace
sudo chmod -R 755 /var/www/nova-aplikace

# 5. Vytvoř Nginx konfiguraci pro nový web
sudo nano /etc/nginx/sites-available/nova-aplikace.local
sudo ln -s /etc/nginx/sites-available/nova-aplikace.local /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

> Pokud nová aplikace také potřebuje phpMyAdmin, nezapomeň zvolit jiný port
> než `8080` — každá aplikace musí mít svůj vlastní port na localhostu
> (např. `127.0.0.1:8081:80`). Port pak odpovídajícím způsobem uprav v Nginx proxy.

# Nasazení aplikace (Deploy)

Tato sekce popisuje, jak funguje nasazení nové verze API z GitHubu na server,
jak spravovat databázové migrace a co dělat při problémech.

---

## Filosofie nasazení

- **Verze** jsou řízeny přes Git tagy — každé nasazení odpovídá konkrétnímu tagu
- **Kód** se stahuje z GitHubu a nasazuje do `/var/www/spotreby-energii/api/`
- **Konfigurace** (`.env`) se nikdy nepřepisuje — spravuje se ručně na serveru
- **Migrace** se spouštějí ručně po deployi — nikdy automaticky
- **Stará verze** se před přepnutím zálohuje — lze se k ní vrátit

---

## Struktura repozitáře

```
consumption-api/          ← kořen repozitáře na GitHubu
└── src/
    └── api/              ← pouze tento adresář se nasazuje na server
        ├── composer.json
        ├── composer.lock
        └── ...
```

---

## Požadavky na serveru před prvním deployem

### PHP rozšíření

Nainstaluj všechna rozšíření, která aplikace potřebuje:

```bash
sudo apt install php8.3-xml php8.3-mysql php8.3-curl php8.3-mbstring php8.3-zip -y
sudo systemctl restart php8.3-fpm
```

> Pokud Composer během deploye hlásí chybějící rozšíření (např. `ext-simplexml`),
> vždy ho doinstaluj tímto způsobem a spusť deploy znovu.

### Composer

```bash
# Ověř, zda je Composer nainstalován
composer --version

# Pokud není, nainstaluj ho
sudo apt install composer -y
```

### .env soubor

Před prvním deployem ručně vytvoř `.env` soubor v cílovém adresáři:

```bash
sudo mkdir -p /var/www/spotreby-energii/api
sudo nano /var/www/spotreby-energii/api/.env
```

Deploy skript tento soubor nikdy nepřepíše — zachová ho při každém nasazení.

---

# Infrastruktura aplikace — Docker, MySQL, phpMyAdmin

Tato sekce navazuje na základní nastavení serveru. Popisuje, jak na serveru
spravovat databáze a další služby přes Docker pro každou aplikaci zvlášť.

---

## Filosofie uspořádání

Na serveru jsou dvě oddělené věci pro každou aplikaci:

- **Infrastruktura** (`/srv/nazev-aplikace/`) — docker-compose, .env soubory, databáze
- **Kód aplikace** (`/var/www/nazev-aplikace/`) — PHP soubory nasazené deploy scriptem

Toto oddělení zajišťuje, že při nasazení nové verze aplikace se nikdy nesáhne
na databázi ani na konfiguraci služeb.

```
/srv/
└── spotreby-energii/
    ├── docker-compose.yml   ← definice služeb (MySQL, phpMyAdmin...)
    └── .env                 ← hesla a proměnné (nikdy v Gitu!)

/var/www/
└── spotreby-energii/
    └── api/                 ← kód aplikace nasazený deploy scriptem
```

---

## Co běží v Dockeru a co nativně

| Služba     | Kde běží           | Důvod                                   |
| ---------- | ------------------ | --------------------------------------- |
| PHP-FPM    | nativně na serveru | rychlejší integrace s Nginx             |
| Nginx      | nativně na serveru | správa více webů na jednom serveru      |
| Redis      | nativně na serveru | sdílený mezi aplikacemi, nižší overhead |
| MySQL      | Docker             | izolace dat každé aplikace zvlášť       |
| phpMyAdmin | Docker             | správa databáze přes prohlížeč          |

---

## Struktura docker-compose.yml pro aplikaci

Každá aplikace má vlastní `docker-compose.yml` v `/srv/nazev-aplikace/`.
Hesla a proměnné jsou vždy v `.env` souboru ve stejné složce — nikoliv přímo v docker-compose.

```yaml
name: spotreby-energii

services:
  db:
    image: mysql:8.0
    container_name: spotreby-energii-mysql
    restart: always
    ports:
      # Pouze localhost — PHP-FPM se připojí přes 127.0.0.1:3306
      # Port není viditelný zvenku sítě serveru
      - "127.0.0.1:3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    volumes:
      # Data přežijí restart i update kontejneru
      - db_data:/var/lib/mysql
    networks:
      - spotreby-energii-network

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: spotreby-energii-phpmyadmin
    restart: always
    ports:
      # Pouze localhost — přístup zvenku zajistí Nginx jako proxy
      - "127.0.0.1:8080:80"
    environment:
      PMA_HOST: db
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
    depends_on:
      - db
    networks:
      - spotreby-energii-network

networks:
  spotreby-energii-network:
    driver: bridge

volumes:
  db_data:
```

> **Proč `127.0.0.1:3306` a ne jen `3306`?**
> Bez prefixu `127.0.0.1` by port byl dostupný na všech síťových rozhraních serveru —
> tedy i z venku domácí sítě. Takto ho vidí pouze samotný server.

---

## Soubor .env

Vedle `docker-compose.yml` vytvoř soubor `.env` s reálnými hesly:

```bash
sudo nano /srv/spotreby-energii/.env
```

Obsah:

```
# MySQL
MYSQL_ROOT_PASSWORD=zmen_na_silne_heslo
MYSQL_DATABASE=spotreby-energii
MYSQL_USER=appuser
MYSQL_PASSWORD=zmen_na_silne_heslo_app
```

Zabezpeč soubor tak, aby ho četl pouze tvůj uživatel:

```bash
sudo chmod 600 /srv/spotreby-energii/.env
```

> `.env` soubor nikdy nedávej do Gitu. Přidej `.env` do `.gitignore` projektu.
> Do Gitu patří pouze `.env.example` s prázdnými nebo vzorovými hodnotami.

---

## Spuštění služeb

```bash
cd /srv/spotreby-energii

# Spustí všechny služby na pozadí
docker-compose up -d

# Ověř, že kontejnery běží
docker ps

# Zobrazí logy (volitelně)
docker-compose logs -f
```

> `up -d` = spusť v detached režimu (na pozadí). Bez `-d` by výpisy zaplnily terminál
> a zastavením terminálu by se zastavily i kontejnery.

### Užitečné příkazy pro správu

```bash
# Restart konkrétní služby
docker-compose restart db

# Zastavení všech služeb (data zůstanou)
docker-compose stop

# Zastavení a smazání kontejnerů (data v db_data volume zůstanou!)
docker-compose down

# Zastavení a smazání včetně dat — POZOR, nevratné!
docker-compose down -v
```

---

## phpMyAdmin přes Nginx proxy

phpMyAdmin běží v Dockeru na `127.0.0.1:8080` a není přímo dostupný zvenku.
Nginx ho zpřístupní přes adresu `https://spotreby-energii.local/phpmyadmin/`
a ochrání ho heslem.

### Krok 1 — vytvoření souboru s heslem

Soubor s heslem vytvoříš bez instalace dalších nástrojů pomocí `openssl`:

```bash
echo "honza:$(openssl passwd -apr1 'tvoje_heslo')" | sudo tee /etc/nginx/.htpasswd
```

Nahraď `honza` svým uživatelským jménem a `tvoje_heslo` zvoleným heslem.

Ověř, že soubor vznikl:

```bash
cat /etc/nginx/.htpasswd
# Výstup bude vypadat přibližně takto:
# honza:$apr1$xK2Lj1...$hashovaneheslo
```

### Krok 2 — přidání proxy do Nginx konfigurace

Otevři konfigurační soubor webu:

```bash
sudo nano /etc/nginx/sites-available/spotreby-energii.local
```

Přidej `location` blok pro phpMyAdmin **dovnitř** stávajícího HTTPS server bloku,
těsně před jeho zavírací `}`:

```nginx
server {
    listen 443 ssl;
    server_name spotreby-energii.local;

    # ... stávající konfigurace zůstává beze změny ...

    # ── phpMyAdmin ────────────────────────────────────────────────
    location /phpmyadmin/ {
        # Ochrana heslem — bez správného hesla Nginx vrátí 401
        auth_basic "Restricted";
        auth_basic_user_file /etc/nginx/.htpasswd;

        # Proxy na phpMyAdmin kontejner běžící na localhostu
        proxy_pass http://127.0.0.1:8080/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

}  # ← tato závorka už tam byla, tou server blok končí
```

### Krok 3 — test a reload Nginx

```bash
# Nejdřív vždy otestuj konfiguraci — reload nikdy nedělej naslepo
sudo nginx -t

# Pokud je test OK, reloadni Nginx
sudo systemctl reload nginx
```

### Ověření přístupu

Otevři v prohlížeči:

```
https://spotreby-energii.local/phpmyadmin/
```

Prohlížeč by měl zobrazit výzvu k zadání jména a hesla (HTTP Basic Auth),
a po přihlášení phpMyAdmin rozhraní.

---

## Nastavení oprávnění MySQL uživatele

Po prvním spuštění kontejnerů je potřeba udělit uživateli aplikace přístup k databázi z outside Dockeru. PHP běží nativně a připojuje se z IP `172.18.x.x`, takže nestačí výchozí oprávnění.

### Přihlášení do MySQL jako root

```bash
cd /srv/spotreby-energii
docker compose exec db mysql -u root -p
```

Zadej heslo z `MYSQL_ROOT_PASSWORD` v `.env` souboru.

### Udělení oprávnění

```sql
GRANT ALL PRIVILEGES ON `consumption-tracker`.* TO 'trackeruser'@'%';
FLUSH PRIVILEGES;
EXIT;
```

> `'%'` = přístup z jakékoliv IP adresy. Nutné protože PHP běží mimo Docker síť.
> Toto stačí udělat jednou — oprávnění jsou uložena v databázi.

> ⚠️ Pokud smažeš Docker volume (`docker compose down -v`) a vytvoříš databázi znovu,
> musíš tento krok zopakovat.

---

## Přidání další aplikace na server

Postup pro každou novou aplikaci je vždy stejný:

```bash
# 1. Vytvoř složku pro infrastrukturu
sudo mkdir -p /srv/nova-aplikace

# 2. Vytvoř docker-compose.yml a .env (viz vzor výše)
sudo nano /srv/nova-aplikace/docker-compose.yml
sudo nano /srv/nova-aplikace/.env
sudo chmod 600 /srv/nova-aplikace/.env

# 3. Spusť služby
cd /srv/nova-aplikace
docker-compose up -d

# 4. Vytvoř adresář pro kód aplikace
sudo mkdir -p /var/www/nova-aplikace/api
sudo chown -R $USER:$USER /var/www/nova-aplikace
sudo chmod -R 755 /var/www/nova-aplikace

# 5. Vytvoř Nginx konfiguraci pro nový web
sudo nano /etc/nginx/sites-available/nova-aplikace.local
sudo ln -s /etc/nginx/sites-available/nova-aplikace.local /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

> Pokud nová aplikace také potřebuje phpMyAdmin, nezapomeň zvolit jiný port
> než `8080` — každá aplikace musí mít svůj vlastní port na localhostu
> (např. `127.0.0.1:8081:80`). Port pak odpovídajícím způsobem uprav v Nginx proxy.

### Oprávnění pro cache složku

PHP-FPM běží pod uživatelem `www-data`, ne pod tvým uživatelem. Proto musí vlastnit složku `storage`:

```bash
sudo chown -R www-data:www-data /var/www/spotreby-energii/api/storage
```
