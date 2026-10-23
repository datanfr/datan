# Mise en production

Cible actuelle : **symfony.datan.fr**, sur le compte o2switch (cPanel,
LiteSpeed) qui sert déjà `datan.fr` — préproduction d'abord, puis remplaçante
du legacy si tout va bien. Le §4 dit comment elle se déploie et ce qu'il faut
poser une fois à la main ; les §1 et §6 décrivent aussi une cible **VPS nu**
(nginx + PHP-FPM), conservée pour le jour où le site quitterait le mutualisé,
et `datan.remikel.fr`, la préproduction précédente.
La base se constitue par **restauration d'un dump**, puis les imports prennent
le relais.

`CRON.md` tient la planification, `TODO.md` §2 la liste de ce qui reste à faire
le jour J. Ce fichier-ci dit comment monter la machine et y poser le code.

---

## 0. À corriger dans le dépôt, avant tout

Trois choses bloquent aujourd'hui un déploiement, et aucune ne se répare depuis
le serveur.

- [x] **`APP_SECRET` est vide** dans `.env`. Il signe les cookies de session et
      les jetons CSRF : vide, la connexion et les formulaires de la rédaction ne
      tiennent pas. Il se pose dans `.env.local` **sur le serveur**, jamais dans
      `.env` qui est suivi par Git.
      ```bash
      php -r 'echo bin2hex(random_bytes(32)), "\n";'
      ```
- [x] **Deux migrations ne sont pas commitées** — `Version20260805090000.php` et
      `Version20260805120000.php`. Elles sont appliquées sur la base de
      développement, donc invisibles ici, mais le serveur ne les aura pas.
- [x] **`compose.yaml` décrit un PostgreSQL** hérité du squelette Symfony, alors
      que le projet tourne sur MariaDB. Il ne sert à rien en production, mais il
      ment à quiconque le lit — à supprimer ou à réécrire.

Et une vérification : `.env` porte `APP_ENV=prod`. C'est voulu, mais cela veut
dire qu'un poste de développement a besoin d'un `.env.local` pour repasser en
`dev` — et que le serveur, lui, n'a rien à redire à ce sujet.

---

## 1. Préalables serveur

### Dimensionnement

| Poste | Taille | Pourquoi |
| --- | --- | --- |
| Base | 1,71 Go | 6,7 M lignes, dont 2,46 M votes (`vote` pèse 844 Mo à elle seule) |
| Clones Tricoteuses | 1,8 Go | `var/tricoteuses/`, neuf dépôts Git |
| Code + `vendor` + assets | ~600 Mo | |
| Marge de restauration | ~2 Go | MariaDB écrit son journal pendant le rechargement |

**40 Go de disque et 4 Go de RAM** sont un socle confortable. En dessous de
2 Go de RAM, les imports volumineux (3,1 M de résultats électoraux) deviennent
inconfortables.

### Paquets

Le projet exige **PHP 8.4** (`composer.json`, dont `config.platform.php` fige
la résolution des dépendances sur 8.4.1). Ni Debian 12 (8.2) ni Ubuntu 24.04
(8.3) ne le livrent : il faut d'abord le dépôt d'Ondřej Surý.

```bash
# Debian 12
sudo apt install -y lsb-release ca-certificates curl
sudo curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
  | sudo tee /etc/apt/sources.list.d/php.list
# Ubuntu 24.04 : sudo add-apt-repository ppa:ondrej/php

sudo apt update && sudo apt install -y \
  nginx git curl unzip \
  php8.4-fpm php8.4-cli php8.4-mysql php8.4-intl php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-zip php8.4-opcache
```

Trois pièges :

- **`php8.4-mysql` fournit `mysqli`** (et `pdo_mysql`) ; il n'existe pas de
  paquet `php8.4-mysqli`. Le DSN du projet est `mysqli://` : sans cette
  extension, la connexion échoue au premier appel.
- **`php8.4-intl` n'est pas optionnelle**, alors que `composer.json` ne la
  déclare pas. `DeputeController`, `ElectionController` et
  `ResultatCirconscriptionRepository` construisent des `IntlDateFormatter` en
  `fr_FR`, `GroupeController` un `Collator('fr_FR')`, et `ImportActeursCommand`
  un `Transliterator`. Le polyfill `symfony/polyfill-intl-icu` **lève une
  exception dès que la locale n'est pas « en »**, et ne couvre pas
  `Transliterator` du tout : sans l'extension, les fiches de députés, les pages
  d'élection et l'import des acteurs tombent tous.
- **`git` est une dépendance d'exécution**, pas seulement de développement :
  `Moissonneur.php` lance le binaire pour moissonner les Tricoteuses, qui ne
  publient pas d'API HTTP.

### MariaDB 11.7

Debian 12 livre MariaDB 10.11. Le DSN annonce `serverVersion=11.7.2-MariaDB` et
il ne faut pas le mentir : une version déclarée fausse fait générer à Doctrine
des diffs de schéma fantômes. Passer par le dépôt officiel :

```bash
curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | sudo bash -s -- --mariadb-server-version=11.7
sudo apt update && sudo apt install -y mariadb-server mariadb-client
sudo mariadb-secure-installation
```

Puis la base et son compte :

```sql
CREATE DATABASE datan_symfony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'datan'@'localhost' IDENTIFIED BY '<mot de passe long>';
GRANT ALL PRIVILEGES ON datan_symfony.* TO 'datan'@'localhost';
FLUSH PRIVILEGES;
```

### Arborescence

```bash
sudo mkdir -p /var/www/datan/{releases,shared/log,shared/tricoteuses}
sudo chown -R deploy:www-data /var/www/datan
```

Le déploiement écrit dans `releases/<sha>` et bascule `current` par lien
symbolique. `shared/` porte ce qui doit survivre : `.env.local`, les journaux et
les 1,8 Go de clones Tricoteuses.

---

## 2. Base de données

### a. Dump depuis le poste de développement

À faire **après** avoir appliqué toutes les migrations en local, pour que la
table `doctrine_migration_versions` parte cohérente avec le schéma — sinon le
serveur rejouera des migrations déjà présentes et échouera.

Le client MariaDB n'est **pas** dans le `PATH` du poste — c'est celui de MySQL
8.0 qui y est, et son `mysqldump` produit un fichier que MariaDB relit mal.
Chemin complet obligatoire.

```powershell
$mdb  = 'C:\Program Files\MariaDB 11.7\bin\mariadb-dump.exe'
$gzip = 'C:\Program Files\Git\usr\bin\gzip.exe'
$date = Get-Date -Format 'yyyyMMdd'
$dst  = "$env:USERPROFILE\Downloads"

& $mdb -h 127.0.0.1 -P 3307 -u datan -pdatan `
  --single-transaction --quick --default-character-set=utf8mb4 `
  --ignore-table=datan_symfony.messenger_messages `
  --result-file="$dst\datan_$date.sql" datan_symfony
if ($LASTEXITCODE -ne 0) { throw "dump principal en échec ($LASTEXITCODE)" }

& $mdb -h 127.0.0.1 -P 3307 -u datan -pdatan `
  --default-character-set=utf8mb4 --no-data `
  --result-file="$dst\datan_${date}_messenger.sql" datan_symfony messenger_messages
if ($LASTEXITCODE -ne 0) { throw "dump messenger en échec ($LASTEXITCODE)" }

& $gzip -9 -f "$dst\datan_$date.sql" "$dst\datan_${date}_messenger.sql"
```

Quatre choix à ne pas défaire :

- **`--result-file=` et non `>`.** La redirection PowerShell fait transiter le
  dump par sa couche d'encodage de texte ; `mariadb-dump` écrit ici lui-même, en
  octets bruts.
- **`--ignore-table` sur `messenger_messages`** : la file d'attente des courriels
  ne se transporte pas. Sa **structure** part dans le second fichier — le DSN
  porte `auto_setup=0`, donc Symfony ne la créera pas seul et le worker
  tomberait. Alternative plus propre : ne pas faire ce second dump et lancer
  `php bin/console messenger:setup-transports` sur le serveur.
- **`--single-transaction`** : dump cohérent sans verrouiller les tables.
- **`--quick`** : sinon `vote` (844 Mo) est chargée en mémoire d'un bloc.

Compter ~1 Go brut, ~116 Mo compressé.

### b. Transfert et restauration

`scp` et `ssh` sont natifs sous Windows, pas besoin de Git Bash :

```powershell
scp "$dst\datan_$date.sql.gz" "$dst\datan_${date}_messenger.sql.gz" deploy@datan.remikel.fr:/tmp/
```

Puis, sur le serveur, **dans un `screen`** : l'import dure 10 à 20 minutes et une
coupure SSH le tuerait au milieu d'une table.

```bash
screen -S import
zcat /tmp/datan_*_messenger.sql.gz | mariadb -u datan -p datan_symfony
zcat /tmp/datan_2*.sql.gz          | mariadb -u datan -p datan_symfony
```

Le glob `datan_2*` évite de recharger le fichier `_messenger` déjà passé.

> **Ne pas importer par phpMyAdmin.** Deux obstacles rédhibitoires : la première
> ligne du dump, `/*M!999999\- enable the sandbox mode */`, que plusieurs
> versions ne savent pas analyser ; et la taille, qui dépasse
> `upload_max_filesize` comme `max_execution_time`. L'import s'arrête alors **au
> milieu d'une table**, et l'état obtenu n'est pas réparable autrement qu'en
> repartant d'une base vide.

### c. Contrôle de la restauration

54 tables attendues, et les compteurs suivants à l'unité :

| Table | Lignes | | Table | Lignes |
| --- | --- | --- | --- | --- |
| `scrutin` | 18 311 | | `decryptage` | 250 |
| `vote` | 2 464 905 | | `commune` | 35 720 |
| `vote_groupe` | 196 195 | | `resultat_legislative` | 431 267 |
| `depute` | 3 117 | | `cr_parole` | 263 808 |
| `amendement` | 128 958 | | `doctrine_migration_versions` | 45 |
| `dossier` | 14 017 | | | |

`vote_groupe` à 196 195 confirme que les corrections `PO0` ont voyagé,
`doctrine_migration_versions` à 45 que le serveur ne rejouera aucune migration,
et `decryptage` à 250 rappelle que ce chiffre vient du backup **anonymisé** — la
vraie base en a davantage (cf. §2.e).

### d. Clones Tricoteuses

Le dump apporte les données, pas les dépôts. Sans eux, le premier
`app:sync:quotidien` reclone tout (1,8 Go) au lieu de lire un delta.

```bash
cd /var/www/datan/current
APP_ENV=prod php bin/console app:tricoteuses:sync --liste     # ce qui sera cloné
APP_ENV=prod php bin/console app:tricoteuses:sync             # ~1,8 Go, une fois
```

Les dépôts figés (`scrutins-xiv`, `-xv`, `-xvi`, `dossiers-xv`, `dossiers-xvi`)
sont **hors moisson nocturne** : ils ne se clonent que si on les demande
nommément. Le dump contenant déjà leurs données, ce n'est utile que pour rejouer
leurs imports plus tard.

### d. Imports de récupération, contre la vraie base

C'est le point le plus délicat, et il est déjà listé en `TODO.md` §2. Le backup
public `datan.fr/assets/dataset_backup` est **réduit et anonymisé** : un seul
compte, `users_mp` vide. La base de développement en porte donc les traces. À
rejouer contre la base de production réelle, **avant l'ouverture** :

```bash
APP_ENV=prod php bin/console app:import:utilisateurs   # comptes + mots de passe repris tels quels
APP_ENV=prod php bin/console app:import:decryptages    # la vraie base en a plus que les 250 du backup
APP_ENV=prod php bin/console app:import:articles
APP_ENV=prod php bin/console app:import:professions-foi  # avec la copie de assets/data/professions/
```

Puis purger les comptes d'essai `redaction`, `editeur`, `guibert`.

> **Un import de récupération écrase les écritures locales.** Une fois
> l'application devenue la source — un rédacteur qui publie, un député qui écrit
> —, le rejouer détruit leur travail. Aucun n'entre dans une tâche planifiée.

### e. Sauvegarde, dès le premier jour

Le décryptage éditorial ne se régénère pas. Une entrée cron, avant même
l'ouverture :

```cron
30 3 * * * mariadb-dump -u datan -p<mdp> --single-transaction --quick datan_symfony \
  | gzip > /var/backups/datan/$(date +\%Y-\%m-\%d).sql.gz && \
  find /var/backups/datan -name '*.sql.gz' -mtime +30 -delete
```

---

## 3. Git

Le dépôt est `git@github.com:datanfr/datan.git` : l'application Symfony y vit
sur la branche `symfony`, le legacy sur `master`.

- [ ] **Committer le travail en cours.** `git status` montre aujourd'hui une
      vingtaine de fichiers modifiés et cinq non suivis, dont les deux migrations
      du §0. Rien ne se déploie tant que `master` ne les porte pas.
- [ ] **Ne jamais committer `.env.local`.** Il est déjà dans `.gitignore` ; c'est
      lui qui portera `APP_SECRET`, le mot de passe MariaDB, `ANTHROPIC_API_KEY`
      et le `MAILER_DSN` Mailjet.
- [ ] **Clé de déploiement.** Générer une paire dédiée sur le poste, poser la
      publique dans `~/.ssh/authorized_keys` de l'utilisateur `deploy` du
      serveur, la privée dans les secrets GitHub :
      ```bash
      ssh-keygen -t ed25519 -C "deploiement datan" -f ~/.ssh/datan_deploy -N ""
      ssh-keyscan -p 22 datan.remikel.fr                # pour SSH_HOTE_CLE_PUBLIQUE
      ```
- [ ] **Protéger `master`** : exiger que le workflow *Qualité* passe avant fusion.
      C'est le seul garde-fou, le dépôt n'ayant aucun test.

---

## 4. GitHub Actions

Deux workflows sont posés dans `.github/workflows/`.

### `qualite.yml` — à chaque push et pull request

Syntaxe PHP, gabarits Twig, YAML, conteneur de services, puis **les migrations
rejouées sur une base vierge** suivies de `doctrine:schema:validate`.

La base d'intégration est en **MariaDB 11.4, celle du serveur (11.4.13) — pas
11.7 comme le poste de développement**. C'est tout l'intérêt du contrôle : une
migration écrite sur une version peut produire du DDL qu'une autre refuse.
C'est arrivé lors de la première restauration, sur une préproduction en 10.6 — `profession_foi` avait hérité de
`utf8mb4_uca1400_ai_ci`, collation apparue en MariaDB 11.4, et l'import s'est
arrêté dessus (`#1273 Unknown collation`). Avec ce workflow, l'écart se voit à la
pull request, pas au milieu d'un chargement de 1 Go.

C'est aussi le seul filet du dépôt : il n'y a aucun test.

### `deploiement.yml` — sur push vers `symfony`

Cible : **symfony.datan.fr**, sur le **compte o2switch de datan.fr**. Le
legacy y vit dans `~/public_html`, déployé depuis `master` par `deploy.yml` ;
l'application Symfony vit à côté, dans `~/symfony.datan.fr`, et se déploie
depuis la branche `symfony` (ou à la main, *Run workflow*). Le contrôle
*Qualité* passe d'abord : rien ne part sur le serveur sans lui.

1. **Construction sur le runner** : `composer install --no-dev`, paquets
   JavaScript (`importmap:install`, lancé par Composer), `asset-map:compile`. Le
   serveur ne reçoit que du tout-fait — il n'a besoin ni de Composer ni d'accès
   à GitHub.
2. **Liste blanche SSH** : le pare-feu o2switch n'ouvre le port 22 qu'aux
   adresses déclarées. Le runner s'ajoute par l'API cPanel
   (`.github/scripts/ssh-whitelist.sh`, copie conforme de celui de `master`) et
   se retire à la fin, même en cas d'échec.
3. **`rsync`** vers `~/symfony.datan.fr/depot/`, miroir de l'artefact : seul ce
   qui a changé voyage.
4. **`bin/deployer.sh`** sur le serveur, qui fait tout ce qui peut échouer
   *avant* la bascule, dans un répertoire que personne ne sert :
   - trouver un PHP ≥ 8.4 et vérifier `intl` et `mysqli` (cf. plus bas) ;
   - refuser si `app:sync:quotidien` tourne ;
   - copier `depot/` en `releases/<date>-<sha>/` (liens durs : quelques
     secondes) et y relier ce qui survit aux versions, rangé dans `shared/` ;
   - réécrire l'`AuthUserFile` du `.htaccess`, qui veut un chemin absolu ;
   - comparer le `serverVersion` du `DATABASE_URL` à ce que répond la base —
     un écart de version mineure arrête tout, cf. la panne de la collation ;
   - `cache:warmup`, puis `doctrine:migrations:migrate` ;
   - arrêter le worker Messenger de l'ancienne version, **basculer** le lien
     `current` (un `mv`, atomique), garder les trois dernières versions ;
   - relancer le réchauffage du cache HTTP en arrière-plan
     (`shared/var/log/rechauffage.log`) : chaque version part à froid.

   Un échec avant la bascule efface la version ratée ; le site continue de
   servir la précédente.
5. **Contrôle final** : la page d'accueil répond 200, et porte
   `X-Robots-Tag: noindex` tant que l'hôte n'est pas `datan.fr`.

```
~/symfony.datan.fr/
├── depot/                  miroir de l'artefact (rsync)
├── releases/<date>-<sha>/  versions complètes, les 3 dernières
├── current -> releases/…   la version servie
├── public -> current/public  racine web du sous-domaine (lien fixe)
├── .env.local  .my.cnf     au-dessus de la racine web, jamais servis
└── shared/
    ├── var/log/  var/tricoteuses/  var/scraper-scrutins.json  var/*.lock
    ├── public/assets/imgs/deputes/  public/assets/imgs/deputes_detoures/
    ├── public/assets/data/professions/
    └── sauvegardes/
```

Une version est effacée au bout de trois : ce qui doit durer vit donc hors de
`releases/`, et chaque version y pointe par un lien — Symfony trouve ainsi son
`.env.local` à la racine du projet, comme d'habitude. La configuration se range
à la racine, au-dessus de `public/`. Les données vont dans `shared/` : elles
ne peuvent pas vivre à la racine, où `public` est un lien vers la version
servie et non un dossier.

Les portraits sont dans `shared/` parce que le sync quotidien les **régénère
dans `public/`** : une version neuve qui ne les verrait pas servirait des fiches
sans photo jusqu'au lendemain.

### Cohabiter avec le legacy

Le compte est celui du site en production. Trois façons de lui nuire sans le
voir :

- **Ne rien poser sous `~/public_html`.** `deploy.yml` y fait un
  `rsync --delete` : tout ce qu'il ne connaît pas disparaît au déploiement
  suivant du legacy.
- **Ne jamais lancer `crontab bin/crontab.prod`.** La commande remplace la table
  entière, tâches du legacy comprises. `bin/cron.sh` n'écrit qu'entre ses
  marqueurs (§5).
- **Les deux workflows se disputent la liste blanche SSH.** L'étape *Remove
  unneeded whitelisted IPs* de `deploy.yml` retire toute adresse qu'elle ne
  connaît pas — celle de notre runner comprise, au milieu d'un rsync. Le remède
  est une ligne, à ajouter **dans `deploy.yml` sur `master`** :
  ```yaml
  concurrency:
    group: o2switch-datan
    cancel-in-progress: false
  ```
  Même groupe que `deploiement.yml` : GitHub n'exécute alors jamais les deux en
  même temps.

Et `~/.my.cnf`, s'il existe, appartient au legacy : nos identifiants de
sauvegarde vont dans `~/symfony.datan.fr/.my.cnf`.

### PHP 7.4 aujourd'hui, 8.4 demain

Le compte sert encore datan.fr en **PHP 7.4** ; Symfony 8.1 exige 8.4.

- **En ligne de commande**, ce n'est pas bloquant : cPanel installe les
  versions côte à côte, et `bin/serveur-commun.sh` cherche un PHP ≥ 8.4 parmi
  les emplacements habituels (`/opt/alt/php84/usr/bin/php` sous CloudLinux,
  `/opt/cpanel/ea-php84/root/usr/bin/php` sous EasyApache) avant le `php` du
  `PATH`. Si la détection échoue, la variable GitHub `PHP_BIN` impose le
  chemin.
  **Appelé par son chemin, `/opt/alt/php84/usr/bin/php` ne charge pas `intl`**
  (constaté le 23 octobre 2026), alors que le module est installé et que
  `selectorctl --list-user-extensions --version=8.4` le liste. D'où
  **`~/symfony.datan.fr/bin/php`**, un lanceur que chaque déploiement réécrit :
  il ajoute un `-d extension=….so` pour chaque extension requise qui manque
  (`intl`, `mysqli`, `ctype`, `iconv`, `dom`, `simplexml`, `tokenizer` —
  `EXTENSIONS_REQUISES` de `bin/serveur-commun.sh`). C'est lui que le cron
  appelle, et lui qu'on utilise à la main :
  `~/symfony.datan.fr/bin/php bin/console …`.
- **Côté web**, c'est `public/.htaccess` qui fixe la version, pour ce seul
  dossier : `AddHandler application/x-httpd-alt-php84___lsphp .php`
  (faq.o2switch.fr, « Version de PHP par dossier »). **Ne pas toucher au
  sélecteur « Sélectionner une version de PHP » de cPanel** : il règle tout
  l'hébergement, et ferait passer datan.fr en 8.4 du même coup. La forme
  `x-httpd-php84` de la FAQ n'existe pas sur ce serveur ; la forme `___lsphp`,
  si, et c'est le SAPI natif de LiteSpeed, sans perte de vitesse. Pas
  d'enveloppe `<IfModule LiteSpeed>` : ignorée ici, la page sortait en 7.4.
  **Piège** : un handler inconnu sert les `.php` en clair, sans erreur
  (vérifié avec `alt-php85`). Le contrôle final du workflow refuse une page
  qui contient `<?php`.
- **Les extensions ne suivent pas.** Contrairement à ce que dit la FAQ, une
  version non sélectionnée part de la configuration nue de son paquet : ni
  `intl` ni `dom` (toute 404 tombe alors en 500, le rendu de la page d'erreur
  plantant). CloudLinux branche la configuration du sélecteur par le lien
  `/opt/alt/phpXX/link/conf`, pour la seule version sélectionnée. **Aucune
  directive `.htaccess` n'y remédie** — neuf essayées le 23 octobre 2026
  (`suPHP_ConfigPath`, `SetEnv PHPRC`, `SetEnv PHP_INI_SCAN_DIR`,
  `lsapi_phpini`, CGI `x-httpd-php84`, `x-httpd-alt-php84`, `ea-php84`…) :
  PHP lit sa configuration au démarrage du processus `lsphp`, avant tout
  `.htaccess`, et `ea-php84` n'est qu'un dossier vide. Le réglage par domaine
  de `cloudlinux-selector` est sans effet sur ce compte. Le lien, lui,
  appartient au compte : `brancher_config_selecteur`
  (`bin/serveur-commun.sh`) le pointe vers `/etc/cl.php.d/alt-php84`, à
  chaque déploiement et par cron chaque minute (`bin/verifier-php.sh`), et
  arrête le `lsphp` 8.4 parent, qui garde sinon l'ancienne configuration
  jusqu'à sa mort — `ps` ne le montre que sous le nom `lsphp`, le tri se fait
  sur `/proc/<pid>/exe`.
  **Écarté** : passer le compte en 8.4 en épinglant datan.fr en 7.4 par
  `.htaccess`. Simulation faite (`PHP_INI_SCAN_DIR=/opt/alt/php74/etc/php.d`) :
  la 7.4 non sélectionnée perdrait 30 de ses 59 extensions, dont `json`,
  `mbstring`, `gd`, `PDO` et OPcache — le legacy tomberait.
  **Retour arrière** : `ln -sfn /opt/alt/php84/etc/php.d /opt/alt/php84/link/conf`.
  Le jour où le compte passe en 8.4 : retirer l'`AddHandler` du `.htaccess` et
  l'entrée cron, le sélecteur fera le travail.
- **Le passage du compte en 8.4 concerne aussi le legacy** (CodeIgniter 3, écrit
  pour 7.x) : à vérifier sur datan.fr le jour même. Rien à changer de notre
  côté, sinon relancer `bin/cron.sh installer` si le chemin du PHP retenu a
  changé.

### Secrets et variables

Les secrets sont **ceux de `deploy.yml`**, déjà posés sur le dépôt : même
compte, même clé.

| Nom | Type | Valeur |
| --- | --- | --- |
| `CPANEL_SERVER` | secret | déjà posé (serveur o2switch) |
| `CPANEL_USERNAME` | secret | déjà posé (compte cPanel de datan.fr) |
| `CPANEL_API_TOKEN` | secret | déjà posé (liste blanche SSH) |
| `SSH_PRIVATE_KEY` | secret | déjà posé |
| `SYMFONY_RACINE` | variable | facultative, `symfony.datan.fr` (relatif au `$HOME`) |
| `SYMFONY_URL` | variable | facultative, `https://symfony.datan.fr` |
| `PHP_BIN` | variable | facultative, chemin d'un PHP ≥ 8.4 si la détection échoue |

### Amorçage, à faire une fois

Dans cPanel :

1. **Base et compte MariaDB** (*Bases de données MySQL*) — cPanel préfixe les
   deux noms par le compte. Relever la version exacte :
   `mariadb -e 'SELECT VERSION()'` — **11.4.13** au 23 octobre 2026. Si elle
   change, aligner aussi l'image du service de `qualite.yml`, sans quoi le
   contrôle des migrations vérifie une autre version que celle du serveur.
   Le jeu de caractères par défaut du serveur est **latin1** : la base que crée
   cPanel en hérite. Les migrations et le dump précisent `utf8mb4` table par
   table, mais une table créée sans le dire (`messenger:setup-transports`, un
   futur `CREATE TABLE` oublieux) prendrait du latin1. Une fois, dans
   phpMyAdmin :
   `ALTER DATABASE <base> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
2. **Sous-domaine `symfony.datan.fr`**, racine **`symfony.datan.fr/public`** —
   jamais `symfony.datan.fr` : servir la racine exposerait `.env.local`.
   `public` y est un lien fixe vers `current/public`, posé au premier
   déploiement ; seul `current` change ensuite. Si cPanel a créé un vrai
   dossier `public/`, `deployer.sh` l'écarte (`public.avant-…`).
3. **DNS chez Cloudflare** : la zone `datan.fr` y vit, et cPanel ne l'alimente
   pas. Enregistrement `symfony`, même cible que `datan.fr` (l'IP du serveur
   o2switch), en **« DNS only » (nuage gris)** : le trafic va droit à
   o2switch, le certificat se valide, et PHP voit du vrai HTTPS. Le prix : l'IP
   du serveur — celle de datan.fr, que Cloudflare masque — devient publique.
4. **Certificat** : cPanel → Sécurité → *Let's Encrypt* → *Générer* en face de
   `symfony.datan.fr`, validation `http-01`, *Générer la simulation* puis
   *Générer*. `public/.well-known/.htaccess` exempte la validation de la
   redirection HTTPS et du renvoi vers `index.php`, renouvellements compris.
5. **Version de PHP** : rien à faire dans cPanel, `public/.htaccess` s'en
   charge (cf. plus haut). Vérifier seulement que `/opt/alt/php84/usr/bin/php`
   existe en SSH.

En SSH (`ssh <compte>@<serveur>`, depuis une adresse de la liste blanche) :

```bash
# Le dossier n'est PAS un clone Git : le déploiement le gère lui-même, et
# refuse d'écrire dans un clone. Un clone existant s'écarte sans rien perdre
# (y reprendre un éventuel .env.local).
[ -d ~/symfony.datan.fr/.git ] && mv ~/symfony.datan.fr ~/symfony.datan.fr.clone
mkdir -p ~/symfony.datan.fr && cd ~/symfony.datan.fr
php -r 'echo bin2hex(random_bytes(32)), "\n";'     # APP_SECRET
nano .env.local && chmod 600 .env.local             # contenu ci-dessous
printf '[client]\nuser=<compte_base>\npassword=<mdp>\n' > .my.cnf && chmod 600 .my.cnf
```

```dotenv
APP_SECRET=<64 caractères>
DATABASE_URL="mysqli://<compte_base>:<mdp>@127.0.0.1:3306/<base>?serverVersion=<version relevée>-MariaDB&charset=utf8mb4"
DEFAULT_URI=https://symfony.datan.fr
CORS_ALLOW_ORIGIN='^https://(symfony\.datan\.fr|datan\.fr)$'
MAILER_DSN=<DSN Mailjet réel>
MATOMO_URL=
```

`MATOMO_URL` reste vide : les visites d'essai pollueraient les statistiques
réelles. `DEFAULT_URI` doit être l'adresse exacte des visiteurs, sinon le
réchauffage remplit le cache sous une autre clé.

Puis la base, par restauration du dump (§2.a et §2.b) — depuis le poste
Windows, le dépôt est dans `C:\Users\Remi\workspace\datan` ; envoyer les
fichiers dans `~/symfony.datan.fr/shared/` plutôt que `/tmp`, partagé sur un
mutualisé. Et enfin :

1. **Premier déploiement** : *Actions → Déploiement → Run workflow* (ou un push
   sur `symfony`).
2. **Clones Tricoteuses** (1,8 Go, une fois), cf. §2.d, depuis
   `~/symfony.datan.fr/current`, avec `~/symfony.datan.fr/bin/php` (jamais le
   `php` du `PATH`, qui est en 7.4).
3. **Planification** : `bin/cron.sh afficher`, puis `installer` (§5).

La base du legacy est **sur le même serveur** : les imports de récupération
(§2.d) peuvent s'y alimenter directement, en lançant la requête d'export de
chaque docblock avec le client `mariadb` du compte au lieu du `docker exec` —
et c'est la vraie base, pas le backup anonymisé.

## 5. Cron

**`bin/crontab.prod` est un gabarit**, pas une table : le compte porte aussi
les tâches du legacy, et `crontab bin/crontab.prod` les effacerait.
`bin/cron.sh` le rend — chemins absolus, PHP détecté, nom de base lu dans
`.env.local` — et ne l'écrit qu'entre deux marqueurs, en gardant une copie de
la table précédente dans `shared/` :

```bash
bash ~/symfony.datan.fr/current/bin/cron.sh afficher    # diff, sans rien écrire
bash ~/symfony.datan.fr/current/bin/cron.sh installer   # pose ou remplace le bloc
bash ~/symfony.datan.fr/current/bin/cron.sh retirer
crontab -l                                              # relire ce qui est installé
```

Relire `afficher` avant d'installer : les heures du bloc (6 h, 3 h 30, lundi
4 h 15) ne doivent pas tomber sur celles du legacy, qui partage le même CPU.

`CRON.md` porte le détail et les justifications. L'extrait ci-dessous en donne
l'essentiel, **mis en forme pour la lecture** : cron arrête la commande à la fin
de la ligne, ne pas le copier tel quel.

```cron
# Quotidien — 6 h. Une seule entrée : l'ordre est garanti par &&, et la purge du
# cache n'a lieu que si tout ce qui précède a réussi.
0 6 * * * cd /var/www/datan/current && sh -c 'APP_ENV=prod php bin/console app:sync:quotidien --no-ansi \
  && APP_ENV=prod php bin/console app:import:commissions --no-ansi \
  && APP_ENV=prod php bin/console app:calcul:statistiques-deputes --no-ansi \
  && APP_ENV=prod php bin/console app:cache:rechauffer --no-ansi' >> var/log/sync-quotidien.log 2>&1

# Hebdomadaire — le seul appel à assemblee-nationale.fr, hors chaîne à dessein.
15 4 * * 1 cd /var/www/datan/current && APP_ENV=prod php bin/console app:scraper:scrutins --no-ansi >> var/log/scraper-scrutins.log 2>&1

# Sauvegarde (cf. §2.e)
30 3 * * * ...
```

### Le worker Messenger n'est pas un cron

`SendEmailMessage` est routé vers le transport `async` (`doctrine://default`).
**Sans processus consommateur, aucun courriel ne part** — ni mot de passe
oublié, ni demande de compte député, ni inscription. C'est un service permanent :

```ini
# /etc/systemd/system/datan-messenger.service
[Unit]
Description=Datan — consommateur Messenger
After=network.target mariadb.service

[Service]
User=deploy
WorkingDirectory=/var/www/datan/current
Environment=APP_ENV=prod
ExecStart=/usr/bin/php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

`--time-limit` fait sortir le processus toutes les heures ; `Restart=always` le
relance, avec le code de la version courante. C'est ce qui évite qu'il continue
d'exécuter du code déployé la semaine dernière.

```bash
sudo systemctl enable --now datan-messenger
```

---

## 6. Autres

### nginx

Cible VPS, non retenue à ce jour : l'hébergement effectif est le mutualisé
N0C sous LiteSpeed, où `public/.htaccess` tient ce rôle (voir « Verrou de
préproduction » plus bas). Conservé pour le jour où le site quitterait le
mutualisé.

```nginx
server {
    listen 443 ssl http2;
    server_name datan.remikel.fr;

    root /var/www/datan/current/public;

    ssl_certificate     /etc/letsencrypt/live/datan.remikel.fr/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/datan.remikel.fr/privkey.pem;

    # Préproduction : jamais d'indexation. Le référencement est le fonds de
    # commerce de datan.fr ; un miroir indexé lui ferait concurrence à
    # lui-même. À retirer le jour de la bascule sur datan.fr.
    #
    # L'application pose désormais le même en-tête d'elle-même (voir « Ne pas
    # indexer la préproduction » plus bas) : cette ligne fait double emploi, et
    # c'est délibéré. Elle couvre ce que PHP ne sert pas — les fichiers du
    # dossier public/ rendus directement par nginx — et tient encore si le
    # serveur reçoit un jour une version du code antérieure à ce garde-fou.
    add_header X-Robots-Tag "noindex, nofollow, noarchive" always;
    auth_basic           "Préproduction";
    auth_basic_user_file /etc/nginx/.htpasswd-datan;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ { return 404; }

    # Les assets d'AssetMapper portent un condensat dans leur nom : ils sont
    # immuables et peuvent se mettre en cache sans réserve.
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    client_max_body_size 8m;
}

server {
    listen 80;
    server_name datan.remikel.fr;
    return 301 https://$host$request_uri;
}
```

`$realpath_root` et non `$document_root` : avec un déploiement par lien
symbolique, la seconde forme fait servir à OPcache le code de la version
précédente.

Certificat : `sudo certbot --nginx -d datan.remikel.fr`.

### Ne pas indexer la préproduction

La préproduction sert le site entier sous un autre nom de domaine : mêmes
pages, mêmes textes, mêmes adresses relatives. Indexée, elle serait un
duplicata intégral de datan.fr, et les moteurs choisiraient eux-mêmes lequel des
deux ils gardent. L'application s'en garde toute seule, sans variable
d'environnement à poser, sur le seul critère qui distingue les deux :
**l'hôte servi** (`App\Referencement\Indexation`, liste
`HOTES_NON_INDEXABLES`). `APP_ENV` ne dirait rien — la préproduction tourne en
`prod`, c'est tout son intérêt.

Trois signaux, chacun suffisant :

| | Où | Portée |
|---|---|---|
| `X-Robots-Tag: noindex, nofollow, noarchive` | en-tête, sur **toute** réponse (`App\Referencement\EnTeteRobots`) | HTML, sitemaps XML, `/api`, erreurs |
| `<meta name="robots" content="noindex, nofollow, noarchive">` | `base.html.twig` et `iframe/depute.html.twig`, via `site_indexable()` | pages HTML |
| `robots.txt` réduit à `User-agent: * / Disallow: /` | `RobotsController` | tout le domaine |

`/llms.txt` (convention llmstxt.org) s'y ajoute, servi par la **seule**
préproduction : il dit aux moissonneurs de modèles de langue que ce domaine est
une copie de travail et renvoie à datan.fr. En production l'adresse répond 404,
comme sur datan.fr, qui n'a pas ce fichier.

`public/robots.txt` a été supprimé au profit de `RobotsController` : nginx sert
les fichiers du dossier avant de passer la main à PHP (`try_files $uri`), et la
préproduction n'avait donc aucun moyen d'en servir un autre. Le texte de
production est repris à l'octet près dans
`templates/referencement/robots.txt.twig` ; **aucune configuration nginx n'est
à ajouter**, le `try_files` existant tombe désormais sur `index.php`.

Le jour de la bascule, rien à faire côté code : `datan.fr` n'est pas dans la
liste et redevient indexable du seul fait de son nom de domaine.

### Pas de verrou de préproduction

symfony.datan.fr est **ouvert, sans mot de passe** — choix du 23 octobre 2026.
Il reste hors des moteurs par deux moyens : l'application, qui reconnaît l'hôte
(`Indexation::HOTES_NON_INDEXABLES` : robots.txt `Disallow: /`, en-tête
`X-Robots-Tag`), et le bloc « PRÉPRODUCTION » de `public/.htaccess`, qui pose
le même en-tête sur ce que le serveur rend sans passer par PHP (images, feuilles
de style, PDF). Ce bloc n'est pas conditionné à l'hôte — LiteSpeed ignore
`Require env`, `Require expr` et `Allow from env=` sans rien dire (vérifié le
9 septembre 2026) — : il **se retire à la main** le jour où ce dossier sert
datan.fr, sans quoi il désindexerait les images du vrai site.

### `.env.local` sur le serveur

Dans `/var/www/datan/shared/.env.local`, en `chmod 600` :

```dotenv
APP_SECRET=<les 64 caractères générés au §0>
DATABASE_URL="mysqli://datan:<mdp>@127.0.0.1:3306/datan_symfony?serverVersion=11.7.2-MariaDB&charset=utf8mb4"
MAILER_DSN=<DSN Mailjet réel>
CORS_ALLOW_ORIGIN='^https://(datan\.remikel\.fr|datan\.fr)$'

# Suivi : à n'activer qu'en production, pas en préproduction — sinon les visites
# d'essai polluent les statistiques réelles.
MATOMO_URL=

IA_MODELE=
ANTHROPIC_API_KEY=

# L'adresse publique telle que PHP la voit. Faute de quoi le réchauffage
# remplit le cache sous une autre adresse que celle des visiteurs, sans erreur.
# https://datan.remikel.fr en préproduction.
DEFAULT_URI=https://datan.fr
```

Pour vérifier qu'elle est juste : après un `app:cache:rechauffer`, la première
visite d'une fiche député doit déjà porter un en-tête `Age:` (la page vient du
cache). Sans lui, les clés ne correspondent pas — schéma ou hôte.

Le port passe de **3307 à 3306** : 3307 est une particularité du poste de
développement.

### Proxy de confiance

Derrière nginx, Symfony ne voit que `127.0.0.1` et croit servir en `http`. Les
URL absolues — canoniques, sitemaps, Open Graph — sortent alors en clair. À
ajouter dans `config/packages/framework.yaml` — c'est aussi ce schéma que le
cache HTTP met dans la clé de chaque page, et `DEFAULT_URI` doit le reproduire :

```yaml
framework:
    trusted_proxies: '127.0.0.1,REMOTE_ADDR'
    trusted_headers: ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port']
```

### OPcache

```ini
; /etc/php/8.3/fpm/conf.d/99-datan.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0     ; le déploiement recharge FPM, inutile de vérifier les dates
realpath_cache_size=4096K
realpath_cache_ttl=600
memory_limit=512M
```

`validate_timestamps=0` suppose que **tout** déploiement recharge FPM. C'est le
cas de `deployer.sh` ; ne pas modifier un fichier à la main sur le serveur en
espérant que ça se voie.

### Recette avant d'ouvrir

- [ ] Une page de vote sort en ~20 ms au second appel (en-tête `Age:` présent).
- [ ] `/sitemap.xml` et chaque sous-sitemap répondent 200, et les adresses
      qu'ils annoncent aussi — un sitemap qui promet des 404 coûte plus cher que
      le silence.
- [ ] `curl https://datan.remikel.fr/robots.txt` rend bien `Disallow: /` (et non
      le fichier de datan.fr), `/llms.txt` répond 200, et
      `curl -I https://datan.remikel.fr/` porte `X-Robots-Tag: noindex`. Le
      contrôle qui compte est celui-ci : la bascule se joue sur le nom de
      domaine servi, donc elle ne se vérifie que depuis le domaine.
- [ ] `curl -I https://datan.remikel.fr/` répond 401 sans identifiants et 200
      avec ; une image de `/assets/imgs/` porte `X-Robots-Tag` — elle ne passe
      pas par PHP, c'est le `.htaccess` qui le pose.
- [ ] Les redirections 301 du legacy fonctionnent (slugs de département : tester
      `francais-de-letranger`).
- [ ] Connexion, mot de passe oublié : le courriel **arrive** (worker Messenger).
- [ ] Espace de rédaction : créer et publier un décryptage d'essai, puis le
      supprimer.
- [ ] `app:sync:quotidien` passe en entier à la main avant d'être planifié.
- [ ] Comparer une dizaine de pages à datan.fr par capture d'écran rendue, pas
      par diff de texte.

### Le jour de la bascule

Le reste — `TODO.md` §2 — est à dérouler à ce moment-là : variables de suivi
réelles, transvasement des abonnés newsletter, redirections des anciennes API
(`api/tables`, `api/votes`, `api/exposes`) qu'API Platform ne reprend pas, et
retrait du bloc « PRÉPRODUCTION » de `public/.htaccess` (en-tête
`X-Robots-Tag` sur les fichiers statiques).

Le garde-fou applicatif, lui, n'est pas à toucher : il ne connaît que
`datan.remikel.fr`, et l'application servie sur `datan.fr` est indexable sans
aucun changement (cf. « Ne pas indexer la préproduction »). Si le domaine de
préproduction change ou qu'un second apparaît, c'est
`Indexation::HOTES_NON_INDEXABLES` qu'on complète.
