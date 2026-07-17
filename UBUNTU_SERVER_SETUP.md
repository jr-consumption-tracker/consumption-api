# Nastavení domácího Ubuntu serveru

Tento dokument popisuje kompletní postup instalace a konfigurace domácího serveru krok za krokem.
Slouží jako reference pro budoucí úpravy, přenastavení nebo přidání dalšího projektu.
Všechny příkazy jsou vysvětleny, aby bylo jasné, co dělají a proč.

**Architektura:** appky neběží nativně na hostu (žádné PHP-FPM/Nginx přímo na serveru) — běží
jako kontejnery v [k3s](https://k3s.io/) (lehká distribuce Kubernetes), nasazované automaticky
přes [ArgoCD](https://argo-cd.readthedocs.io/) (GitOps) na základě obsahu gitops repozitáře.
Databáze (MySQL, Redis) běží mimo cluster, přímo v Dockeru na hostu.

---

## Rychlý přístup (pro časté použití)

Instalace obojího je popsaná níže (sekce 9 a 13) — tohle je jen stručná referenční kartička,
ať to nemusíš pokaždé hledat.

### ArgoCD UI

```bash
# na serveru — nech bezet, dokud UI potrebujes otevrene
sudo kubectl port-forward svc/argocd-server -n argocd 8090:443 --address 0.0.0.0

# heslo (pokud sis ho neulozil pri prvni instalaci)
kubectl -n argocd get secret argocd-initial-admin-secret -o jsonpath="{.data.password}" | base64 -d
```

Pak v prohlížeči `https://IP_SERVERU:8090`, přihlásit se jako `admin`. Certifikát je
self-signed, potvrď výjimku ("Advanced" → "Proceed").

> Pokud je port `8090` obsazený, zkontroluj `sudo lsof -i :8090` a použij jiný (`8091`...).

### phpMyAdmin

phpMyAdmin poslouchá jen na `127.0.0.1:8081` na serveru (ne veřejně) — přístup přes SSH tunel:

```bash
# na svem laptopu
ssh -L 8081:localhost:8081 honza@192.168.0.50
```

Pak v prohlížeči na laptopu `http://localhost:8081`. Přihlas se `root` + heslo z
`MYSQL_ROOT_PASSWORD` v `host-services.env` na serveru
(`~/api-tmp/docker/host-services.env`, nebo kdekoliv máš ten soubor uložený).

> Tunel musí zůstat aktivní (terminál otevřený) po celou dobu, co v phpMyAdmin pracuješ —
> zavřením terminálu se přístup ukončí.

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
   - `/var` – 250 GB (logy, databáze, kontejnery — roste nejvíc)
   - `swap` – 2 GB (virtuální RAM, při 8 GB RAM dostačuje)
   - Zbytek ponech neformátovaný — pro pozdější použití s LVM nebo Docker storage
5. **Uživatelské jméno:** zadej své (např. `honza`) — vytvoří se automaticky administrátorský účet
6. **SSH:** zaškrtni **OpenSSH server** — umožní vzdálené přihlášení
7. **Import SSH klíče:** klíče neimportuj, pak se nejde přihlásit heslem
8. **Snaps:** zvol pouze to, co potřebuješ, ostatní přeskočit

Po dokončení instalace se server restartuje a přihlásíš se buď přímo, nebo přes SSH.

---

## 2. První přihlášení a základní kontrola

### Přihlášení přes SSH ze svého počítače

```bash
ssh honza@192.168.0.50
# Obecně: ssh TVŮJ_UŽIVATEL@IP_SERVERU
```

> SSH = Secure Shell — šifrované vzdálené připojení k příkazové řádce serveru.

### Ověření, že jsi přihlášen jako správný uživatel

```bash
whoami   # vypíše tvé uživatelské jméno
id       # vypíše skupiny, ve kterých jsi — důležité pro Docker a oprávnění
```

### Povolení přihlášení heslem přes SSH (pokud nechceš klíče)

```bash
sudo nano /etc/ssh/sshd_config
```

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

---

## 4. Firewall (UFW)

```bash
sudo apt install ufw -y

# Výchozí politika — odmítni vše příchozí, povol vše odchozí
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Povol SSH — VŽDY jako první, jinak se odřízneš od serveru!
sudo ufw allow OpenSSH

# Povol HTTP a HTTPS — používá je Traefik (ingress v k3s), ne přímo host
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

sudo ufw enable
sudo ufw status verbose
```

> ⚠️ **Docker obchází UFW.** Docker ve výchozím nastavení manipuluje přímo s `iptables`,
> takže port namapovaný v `docker-compose.yml` (např. `"3307:3306"`) je dostupný z celé
> lokální sítě, i bez pravidla v UFW. Proto máme MySQL/Redis/phpMyAdmin v
> `docker-compose.host-services.yml` (viz níž) namapované jen na porty, ne veřejně —
> a phpMyAdmin dokonce jen na `127.0.0.1` (přístup přes SSH tunel, viz sekce 15).

> ⚠️ Port pro k3s API server (`6443`) ani porty pro ArgoCD/kubectl nemusíš do UFW přidávat —
> k oběma přistupuješ vždy lokálně na serveru přes SSH, ne zvenku sítě.

---

## 5. Nastavení statické IP adresy

### Zjisti název síťového rozhraní

```bash
ip link show
```

### Uprav konfigurační soubor Netplan

```bash
sudo nano /etc/netplan/00-installer-config.yaml
```

```yaml
network:
  version: 2
  renderer: networkd
  ethernets:
    enp3s0: # název tvého síťového rozhraní
      dhcp4: no
      addresses:
        - 192.168.0.50/24 # statická IP serveru
      gateway4: 192.168.0.1 # IP tvého routeru
      nameservers:
        addresses: [192.168.0.1, 8.8.8.8]
```

> **Pozor na odsazení!** YAML soubory jsou citlivé na mezery — vždy používej mezery, ne tabulátory.

```bash
sudo netplan apply
ip a   # ověření
```

---

## 6. Docker a uživatelská práva

Docker tu slouží pro databázové a pomocné služby, které záměrně běží **mimo** k3s cluster
(viz sekce 15) — appky samotné běží v k3s, ne přímo v Dockeru na hostu.

```bash
sudo apt update
sudo apt install docker.io -y
```

> Pro instalaci `docker compose` pluginu si přečti návod:
> https://docs.docker.com/engine/install/ubuntu/

### Přidej svého uživatele do skupiny `docker`

```bash
sudo usermod -aG docker $USER
newgrp docker   # aktivace bez odhlášení
```

### Ověření

```bash
docker run hello-world
docker --version
docker compose version
```

---

## 7. Instalace k3s

[k3s](https://k3s.io/) je lehká, plnohodnotná distribuce Kubernetes od Rancher — jeden binárák,
minimální overhead, ideální pro single-node domácí server.

```bash
curl -sfL https://get.k3s.io | sh -

sudo systemctl status k3s
```

Hledej `Active: active (running)`.

### Nastavení přístupu bez `sudo`

Ve výchozím stavu je konfigurační soubor pro `kubectl` čitelný jen rootem:

```bash
sudo chmod 644 /etc/rancher/k3s/k3s.yaml
```

Pak nastav `kubectl`, ať tenhle soubor používá:

```bash
mkdir -p ~/.kube
sudo k3s kubectl config view --raw | tee ~/.kube/config
chmod 600 ~/.kube/config
export KUBECONFIG=~/.kube/config
```

Aby se `KUBECONFIG` nastavil automaticky při každém přihlášení, přidej řádek do `~/.bashrc`:

```bash
echo 'export KUBECONFIG=~/.kube/config' >> ~/.bashrc
```

### Ověření

```bash
kubectl get nodes
```

Node by měl být ve stavu `Ready`, verze by měla obsahovat `+k3s` (potvrzuje, že jde o k3s,
ne vanilla Kubernetes).

> k3s má **zabudovaný** ingress controller (Traefik) a `local-path-provisioner` — není potřeba
> instalovat je zvlášť. Traefik automaticky poslouchá na portech 80/443 na IP serveru.

---

## 8. Instalace Sealed Secrets

Appky potřebují hesla a tokeny (databáze, JWT klíče...) — ty se **nikdy** neukládají do gitu
v čitelné podobě. [Sealed Secrets](https://github.com/bitnami-labs/sealed-secrets) šifruje
Kubernetes Secret tak, že rozšifrovat ho umí jen konkrétní cluster (má k tomu privátní klíč,
který nikdy neopustí cluster) — výsledný `SealedSecret` je pak bezpečné commitnout do gitu.

### Controller do clusteru

```bash
kubectl apply -f https://github.com/bitnami-labs/sealed-secrets/releases/latest/download/controller.yaml
kubectl get pods -n kube-system -l name=sealed-secrets-controller
```

### `kubeseal` CLI (klient pro šifrování)

```bash
KUBESEAL_VERSION='0.31.0'   # zkontroluj aktuální verzi: https://github.com/bitnami-labs/sealed-secrets/releases
curl -OL "https://github.com/bitnami-labs/sealed-secrets/releases/download/v${KUBESEAL_VERSION}/kubeseal-${KUBESEAL_VERSION}-linux-amd64.tar.gz"
tar -xvzf "kubeseal-${KUBESEAL_VERSION}-linux-amd64.tar.gz" kubeseal
sudo install -m 755 kubeseal /usr/local/bin/kubeseal
rm kubeseal "kubeseal-${KUBESEAL_VERSION}-linux-amd64.tar.gz"

kubeseal --version
```

> Jak zašifrovat konkrétní Secret pro appku — viz sekce 16.

---

## 9. Instalace ArgoCD

ArgoCD hlídá gitops repozitář a automaticky udržuje stav clusteru podle toho, co je v gitu
(GitOps princip — žádné ruční `kubectl apply` po appce).

```bash
kubectl create namespace argocd
kubectl apply -n argocd -f https://raw.githubusercontent.com/argoproj/argo-cd/stable/manifests/install.yaml

kubectl get pods -n argocd -w
```

Počkej, až budou všechny pody `1/1 Running` (`Ctrl+C` pro ukončení sledování).

> ⚠️ **Známý zádrhel:** `kubectl apply -f install.yaml` občas nestihne založit CRD
> `applicationsets.argoproj.io` (velký soubor, race condition). Pokud
> `argocd-applicationset-controller` pořád padá (`CrashLoopBackOff`), zkontroluj:
> ```bash
> kubectl get crd | grep argoproj.io
> ```
> Měly by být tři: `applications`, `applicationsets`, `appprojects`. Pokud jedna chybí, založ ji zvlášť:
> ```bash
> kubectl apply -f https://raw.githubusercontent.com/argoproj/argo-cd/stable/manifests/crds/applicationset-crd.yaml
> kubectl rollout restart deployment argocd-applicationset-controller -n argocd
> ```

### Přístup k webovému UI

ArgoCD zatím nemá vlastní doménu/Ingress (řešíme case-by-case), takže se k němu přistupuje
přes dočasný port-forward:

```bash
kubectl port-forward svc/argocd-server -n argocd 8090:443 --address 0.0.0.0
```

> Pokud je port `8090` obsazený (zkontroluj `sudo lsof -i :8090`), použij jiný, např. `8091`.

Zjisti heslo administrátora:

```bash
kubectl -n argocd get secret argocd-initial-admin-secret -o jsonpath="{.data.password}" | base64 -d
```

Otevři v prohlížeči `https://IP_SERVERU:8090`, přihlas se jako `admin` s heslem výše
(certifikát je self-signed, potvrď výjimku).

### Napojení gitops repozitáře

V UI: **Settings → Repositories → + CONNECT REPO**, metoda **VIA HTTPS**:
- **Repository URL**: `https://github.com/ORG/NAZEV-gitops.git`
- **Username**: tvůj GitHub účet
- **Password**: GitHub token (classic, scope `repo`) — https://github.com/settings/tokens

> Fine-grained tokeny s ArgoCD i GHCR historicky nespolehlivě fungují (hláška "Write access
> to repository not granted" i pro čtení) — použij radši classic token se scope `repo`.

---

## 10. Lokální SSL certifikáty (mkcert)

`mkcert` generuje důvěryhodné lokální HTTPS certifikáty bez varování v prohlížeči.

```bash
sudo apt install libnss3-tools mkcert -y
mkcert -install
```

> Tím se vytvoří lokální CA (Certificate Authority) — tvůj počítač i server jí začnou důvěřovat.

### Konvence uložení — jeden adresář na doménu/projekt

```bash
cd ~
mkcert spotreba-energie.local
# vytvoří spotreba-energie.local.pem (cert) a spotreba-energie.local-key.pem (klíč)

sudo mkdir -p /etc/ssl/localcerts/spotreba-energie.local
sudo mv spotreba-energie.local.pem /etc/ssl/localcerts/spotreba-energie.local/cert.pem
sudo mv spotreba-energie.local-key.pem /etc/ssl/localcerts/spotreba-energie.local/key.pem
```

> Každá doména/projekt má vlastní podadresář (`/etc/ssl/localcerts/<doména>/cert.pem` +
> `key.pem`) — stejná konvence jako u Let's Encrypt/certbot, přehledné i s víc projekty na
> serveru.

Tenhle certifikát pak zabalíš do Kubernetes Secretu a zašifruješ přes `kubeseal` — přesný
postup je v sekci 16 (je to jen speciální případ "obyčejného" Secretu).

---

## 11. Hosts soubor pro lokální DNS

Doména jako `spotreba-energie.local` neexistuje na internetu — je potřeba říct zařízením,
kde ji hledat (IP serveru).

### Na serveru samotném (kvůli `curl` testům přímo z něj)

```bash
echo "192.168.0.50 spotreba-energie.local" | sudo tee -a /etc/hosts
```

### Na Windows (na tvém laptopu/PC)

Soubor: `C:\Windows\System32\drivers\etc\hosts` — otevři jako administrátor a přidej:

```
192.168.0.50   spotreba-energie.local
```

### Na Linuxu / macOS

```bash
sudo nano /etc/hosts
```

Přidej stejný řádek.

### Ověření

```bash
curl -kv https://spotreba-energie.local/api/
```

`-k` = ignoruj chybu certifikátu (self-signed CA z mkcertu tvůj systém globálně nezná,
i když prohlížeč po `mkcert -install` ano).

---

## 12. GitOps — struktura a konvence pro víc projektů

Appky se nenasazují ručním `kubectl apply`, ale přes **gitops repozitář** — samostatné repo
(ne appkový kód), které obsahuje jen Kubernetes manifesty. ArgoCD ho sleduje a automaticky
udržuje cluster v souladu s tím, co je v gitu.

### Konvence, ať server zůstane přehledný i s víc projekty

- **Namespace vždy `<projekt>-<prostředí>`**, nikdy jen `dev`/`prod` (např. `consumption-dev`,
  `consumption-prod`) — jiný projekt dostane svůj vlastní prefix, žádné kolize jmen.
- **Vlastní gitops repo na projekt** (např. `consumption-gitops`) — appkový kód a
  nasazovací manifesty jsou schválně oddělené repo.
- **Vlastní ArgoCD `AppProject` na projekt** — seskupuje Applications v ArgoCD UI a
  omezuje, že smí číst jen z vlastního gitops repa a nasazovat jen do `<projekt>-*`
  namespaces. Samotné ArgoCD je ale jedno pro celý cluster, sdílené všemi projekty.

### Typická struktura gitops repa

```
apps/
  api/                    # jedna appka (např. backend)
    base/                 # spolecne manifesty (Deployment, Service, NetworkPolicy...)
    overlays/
      dev/                # hodnoty specificke pro dev (ConfigMap, Ingress, sealed Secrets...)
      prod/                # az bude prod hosting
argocd/
  <projekt>-appproject.yaml
  <app>-dev-application.yaml
```

### Založení projektu v ArgoCD

```bash
kubectl apply -f argocd/<projekt>-appproject.yaml
kubectl apply -f argocd/<app>-dev-application.yaml
```

Odteď appku spravuje ArgoCD samo — commit do gitops repa se do ~3 minut automaticky projeví
v clusteru (nebo hned po kliknutí **REFRESH → SYNC** v UI).

---

## 13. Databáze a pomocné služby mimo cluster

MySQL, Redis (a volitelně phpMyAdmin) běží záměrně **mimo** k3s, přímo v Dockeru na hostu —
jednodušší zálohování, appky v k3s zůstávají bezstavové.

### Spuštění

```bash
git clone https://github.com/ORG/NAZEV-api.git api-tmp
cd api-tmp/docker

cp host-services.env.example host-services.env
nano host-services.env   # doplň MYSQL_ROOT_PASSWORD, MYSQL_USER, MYSQL_PASSWORD, REDIS_PASSWORD

sudo docker compose -f docker-compose.host-services.yml --env-file host-services.env up -d
sudo docker ps   # over, ze vsechno je (healthy)
```

> **Heslo v `host-services.env` musí přesně sedět** s tím, co appka v k8s čeká v
> zašifrovaném Secretu (`DB_USER`/`DB_PASS`/`REDIS_PASSWORD`) — appka se k databázi
> přihlašuje přesně těmito údaji. Pokud se neshodují, appka spadne s chybou připojení.

### Napojení appky v k8s na tyhle služby

Appka v k8s je v jiné síti než Docker na hostu — potřebuje `Service` + `EndpointSlice`
ukazující na IP hosta (viz `apps/api/overlays/dev/external-services.yaml` v gitops repu).

> ⚠️ **Nepoužívej `type: ExternalName` s IP adresou** — CoreDNS ji neumí spolehlivě přeložit
> (`NXDOMAIN`). Funguje jen `ExternalName` s doménovým jménem. Pro směrování na pevnou IP
> je správný vzor `Service` bez selectoru + ruční `EndpointSlice`:
> ```yaml
> apiVersion: v1
> kind: Service
> metadata:
>   name: mysql-host
> spec:
>   ports:
>     - port: 3307
>       targetPort: 3307
>       protocol: TCP
> ---
> apiVersion: discovery.k8s.io/v1
> kind: EndpointSlice
> metadata:
>   name: mysql-host
>   labels:
>     kubernetes.io/service-name: mysql-host
> addressType: IPv4
> ports:
>   - port: 3307
>     protocol: TCP
> endpoints:
>   - addresses:
>       - "192.168.0.50"
>     conditions: {}
> ```
> `protocol`/`conditions` piš explicitně — Kubernetes server je stejně doplní automaticky,
> a bez nich ArgoCD hlásí trvalý `OutOfSync` drift.

> ⚠️ **ArgoCD defaultně `Endpoints`/`EndpointSlice` úplně ignoruje** (`resource.exclusions`
> v `argocd-cm`, kvůli omezení počtu sledovaných událostí). Pokud appka nedokáže přeložit
> `mysql-host`/`redis-host`, zkontroluj:
> ```bash
> kubectl get configmap argocd-cm -n argocd -o yaml | grep -A10 resource.exclusions
> ```
> a odeber blok vylučující `Endpoints`/`EndpointSlice` (zbytek vyloučení nech být), pak
> restartuj `argocd-application-controller` a `argocd-repo-server`.

### Přístup do databáze (phpMyAdmin)

phpMyAdmin v `docker-compose.host-services.yml` poslouchá jen na `127.0.0.1:8081` — z
laptopu se k němu dostaneš přes SSH tunel:

```bash
ssh -L 8081:localhost:8081 honza@192.168.0.50
```

a pak otevřít `http://localhost:8081` v prohlížeči.

---

## 14. Šifrování Secrets (Sealed Secrets workflow)

Postup je stejný pro jakýkoliv Secret (hesla appky, TLS certifikát, GHCR pull secret...).

```bash
git clone https://github.com/ORG/NAZEV-gitops.git gitops-tmp
cd gitops-tmp/apps/<app>/overlays/<prostredi>

cp secret.example.yaml secret.yaml
nano secret.yaml   # dopln skutecne hodnoty (viz sablona pro presny seznam klicu)
```

Formát `secret.yaml` je **YAML**, ne `.env`/dotenv syntax:

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: api-secret
  namespace: consumption-dev
type: Opaque
stringData:
  DB_USER: "hodnota"
  DB_PASS: "hodnota"
```

Náhodné hodnoty (tokeny, klíče) vygeneruješ:

```bash
openssl rand -base64 64
```

Zašifrování:

```bash
cd ~
sudo kubeseal --format=yaml \
  --kubeconfig=/etc/rancher/k3s/k3s.yaml \
  --controller-namespace=kube-system \
  --controller-name=sealed-secrets-controller \
  < gitops-tmp/apps/<app>/overlays/<prostredi>/secret.yaml > sealed-secret.yaml

sudo chown $USER:$USER sealed-secret.yaml
rm gitops-tmp/apps/<app>/overlays/<prostredi>/secret.yaml   # nesifrovanou verzi nikdy necommitovat

cat sealed-secret.yaml
```

Výsledný `sealed-secret.yaml` je **bezpečné commitnout do gitu** (i do veřejného repa) —
rozšifrovat ho umí jen sealed-secrets controller v tomhle konkrétním clusteru. Přidej ho
do `kustomization.yaml` daného overlaye a commitni do gitops repa.

> **Změna hesla později?** Postup se opakuje od začátku (`cp secret.example.yaml
> secret.yaml`, doplnit **všechny** hodnoty znovu, `kubeseal`, přepsat starý soubor) —
> zašifrovaný soubor nejde upravit po částech. A appka novou hodnotu použije až po
> restartu Podu (`kubectl rollout restart deployment ... `) — proměnné prostředí se
> nastavují jen při startu kontejneru.

### TLS certifikát stejným postupem

```bash
sudo kubectl create secret tls <jmeno>-tls \
  --cert=/etc/ssl/localcerts/<domena>/cert.pem \
  --key=/etc/ssl/localcerts/<domena>/key.pem \
  -n <namespace> \
  --dry-run=client -o yaml > tls-secret.yaml

sudo kubeseal --format=yaml \
  --kubeconfig=/etc/rancher/k3s/k3s.yaml \
  --controller-namespace=kube-system \
  --controller-name=sealed-secrets-controller \
  < tls-secret.yaml > sealed-tls-secret.yaml

rm tls-secret.yaml
```

### GHCR imagePullSecret (privátní Docker registry)

Pokud je appkový repo private, i image v GHCR je private — k3s potřebuje přihlašovací
údaje k jeho stažení:

```bash
sudo kubectl create secret docker-registry ghcr-pull-secret \
  --docker-server=ghcr.io \
  --docker-username=GITHUB_USERNAME \
  --docker-password=GITHUB_TOKEN \
  -n <namespace> \
  --dry-run=client -o yaml > pull-secret.yaml

sudo kubeseal --format=yaml \
  --kubeconfig=/etc/rancher/k3s/k3s.yaml \
  --controller-namespace=kube-system \
  --controller-name=sealed-secrets-controller \
  < pull-secret.yaml > sealed-pull-secret.yaml

rm pull-secret.yaml
```

Token vytvoříš na https://github.com/settings/tokens (classic), scope `read:packages`
(u fine-grained tokenů tahle možnost historicky chybí/nefunguje spolehlivě).

Referenci na Secret přidej do `Deployment`:

```yaml
spec:
  template:
    spec:
      imagePullSecrets:
        - name: ghcr-pull-secret
```

---

## 15. CI/CD a nasazení appky

Appka se nenasazuje ručně. Postup je plně automatický:

```
push do develop        → CI (test, lint, PHPStan) → build image → push do GHCR
                        → CI SAMO commitne nový tag do gitops repa
                        → ArgoCD do ~3 minut appku s novým image nasadí

git tag v1.0.0 na main  → stejný postup, jen s produkčním tagem (až bude prod hosting)
```

Žádný krok tady nevyžaduje ruční zásah — jakmile je jednou nastavený `GITOPS_REPO_TOKEN`
secret v CI (classic GitHub token, scope `repo`, uložený přes `gh secret set
GITOPS_REPO_TOKEN --repo ORG/NAZEV-api`), appka se nasazuje sama při každém merge do
`develop`.

### Ruční ověření/vynucení nasazení

Pokud nechceš čekat na automatický ~3minutový cyklus ArgoCD:

```bash
# v ArgoCD UI: karta appky -> REFRESH -> SYNC
```

Po nasazení nové verze appky se **restartuje Pod automaticky** (nová verze image = jiný
tag = ArgoCD udělá rolling update). Restart naschvál (např. po změně Secretu/ConfigMapu,
což samo restart nevyvolá):

```bash
kubectl rollout restart deployment <app> -n <namespace>
kubectl get pods -n <namespace> -w
```

---

## 16. Přidání dalšího projektu na server

Postup je vždy stejný, ať jde o appku související s existujícím projektem, nebo úplně
nový, nesouvisející projekt:

1. **Nové repo appky** + **nové gitops repo** (`novy-projekt-gitops`)
2. **Nový namespace** `novy-projekt-dev` (a časem `novy-projekt-prod`) — žádná kolize s
   existujícími projekty
3. **Nový `AppProject`** v ArgoCD, scoped jen na vlastní gitops repo a `novy-projekt-*`
   namespaces
4. **`ResourceQuota`/`LimitRange`** na nový namespace — ať appka nemůže vzít zdroje
   ostatním projektům na sdíleném serveru
5. Zbytek (Sealed Secrets, host-services databáze, Ingress...) stejný postup jako v
   sekcích 13–15, jen s jiným jménem/doménou

Samotné k3s, ArgoCD ani Sealed Secrets controller se znovu neinstalují — jsou to
cluster-wide služby sdílené všemi projekty.

---

## 17. Ověření funkčnosti celého stacku

```bash
# Cluster a appky
kubectl get nodes
kubectl get pods --all-namespaces
kubectl get application -n argocd

# Docker (host-services)
sudo docker ps

# Firewall
sudo ufw status verbose

# Odpoved appky
curl -kv https://spotreba-energie.local/api/web/auth/refreshToken
```

### Logy appky

PHP chyby appka píše do souboru uvnitř kontejneru (ne na stdout, takže je `kubectl logs`
nevidí):

```bash
kubectl logs -n <namespace> -l app=<app> -c app --tail=50          # pristupovy log FPM
kubectl exec -n <namespace> -it deploy/<app> -c app -- \
  tail -50 storage/logs/php_errors.log                              # skutecne PHP chyby
```

### Logy ArgoCD synchronizace

V UI: appka → klikni na konkrétní resource → **Diff**/**Events**. Nebo:

```bash
kubectl describe application <app> -n argocd
```

---

## 18. Řešení běžných problémů

| Příznak | Příčina | Řešení |
|---|---|---|
| `ImagePullBackOff` | Tag image v `kustomization.yaml` neodpovídá žádnému skutečně sestavenému tagu, nebo chybí `imagePullSecret` | Zkontroluj `docker-build-check`/`build-and-push` v CI, ověř `imagePullSecrets` v Deploymentu |
| `getaddrinfo failed` / `NXDOMAIN` na `redis-host`/`mysql-host` | `ExternalName` Service s IP adresou (nefunguje), nebo ArgoCD `Endpoints`/`EndpointSlice` vylučuje ze sledování | Viz sekce 13 — `Service` bez selectoru + `EndpointSlice`, uprav `argocd-cm` |
| `500` s `RedisException: Connection refused` na `127.0.0.1` | Knihovna/framework má vlastní, neconfigurovaný fallback na `127.0.0.1` (typicky Doctrine ORM cache) | Explicitně předat správně nakonfigurovaný cache adapter, nespoléhat na auto-detekci |
| `500` s `"Invalid CSRF storage"` | CSRF middleware potřebuje aktivní PHP session, nic ji nespouští | Middleware pro `session_start()` **před** CSRF middlewarem (pozor na pořadí u Slim — LIFO) |
| ArgoCD appka trvale `OutOfSync` | Kubernetes server automaticky doplňuje pole (např. `protocol: TCP`, `conditions: {}`), která v manifestu nejsou explicitně napsaná | Doplnit přesně to, co server sám přidává — zjistíš přes `kubectl get <resource> -o yaml` |
| `kubectl edit` se "zasekne" | Otevřel se needitovatelný/neznámý editor (typicky `vi`) | Nové okno terminálu → `pkill -9 -f "kubectl edit"` → použij `kubectl patch` místo `edit` |
| `composer validate` shazuje CI | `--strict` dělá z jakéhokoliv upozornění chybu (i neškodných, např. "version pole se nedoporučuje") | Odebrat `--strict`, pokud je upozornění záměrné a nedá se splnit |

---

## 19. Shrnutí architektury

```
Internet / lokální síť
        │
        ▼
  UFW Firewall (22, 80, 443)
        │
        ▼
   k3s cluster (Traefik ingress, porty 80/443)
        │
        ├── namespace consumption-dev
        │     └── Pod: nginx + php-fpm (appka)
        │           │
        │           ▼
        │     Service/EndpointSlice → mimo cluster
        │
        ├── namespace argocd (ArgoCD — sleduje gitops repo, nasazuje appky)
        │
        └── namespace kube-system (sealed-secrets-controller, Traefik)

Docker na hostu (mimo k3s)
  ├── MySQL   (docker-compose.host-services.yml)
  ├── Redis
  └── phpMyAdmin (jen 127.0.0.1, přístup přes SSH tunel)
```

- **k3s** — cluster pro appky, sdílený všemi projekty na serveru
- **ArgoCD** — GitOps, appky se nasazují automaticky podle obsahu gitops repa
- **Sealed Secrets** — hesla/tokeny bezpečně v gitu, šifrované pro konkrétní cluster
- **Docker (host)** — jen stavové služby (databáze), záměrně mimo cluster kvůli
  jednoduššímu zálohování a menší zátěži k3s

---

## 20. Doporučení a průběžná údržba

**Pravidelné aktualizace systému:**

```bash
sudo apt update && sudo apt upgrade -y
sudo apt autoremove -y
```

**Záloha databáze:**

```bash
sudo docker exec consumption-tracker-mysql mysqldump -u root -p<heslo> <databaze> \
  > zaloha_$(date +%F).sql
```

> Zatím žádné automatické zálohování — pro produkci by mělo být řešené (cron + rotace).

**Sledování stavu clusteru:**

```bash
kubectl get pods --all-namespaces
kubectl top nodes    # potrebuje metrics-server, zatim nenainstalovany
```

**Pro produkční/veřejně dostupný server:** místo `mkcert` použij `cert-manager` +
Let's Encrypt (zdarma, automatická obnova, veřejně důvěryhodné certifikáty).

---

> 💡 **Tip:** Tento dokument si udržuj aktuální. Kdykoliv změníš konfiguraci nebo přidáš
> novou službu/projekt, doplň sem postup — až ho budeš za půl roku potřebovat, budeš rád.
