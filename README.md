# Datan

Décryptage des votes de l'Assemblée nationale — <https://datan.fr>.
Symfony 8.1, PHP 8.4, MySQL 8 en local (MariaDB 11.4 en production).

Conventions et pièges métier : [CLAUDE.md](CLAUDE.md). Mise en production :
[DEPLOIEMENT.md](DEPLOIEMENT.md). Planification : [CRON.md](CRON.md).

## Prérequis

- **PHP ≥ 8.4** avec `intl`, `mysqli`, `gd`, `mbstring`, `xml`, `curl`, `zip`.
  Sous Debian/Ubuntu (dépôt `ondrej/php`) :
  `php8.4-{cli,fpm,mysql,intl,gd,mbstring,xml,curl,zip}`. `intl` n'est pas
  facultative : sans elle, les fiches de députés tombent.
- **Composer 2** et **Git** — Git sert aussi à l'exécution, pour moissonner les
  données.
- **MySQL 8.0** (ou MariaDB ≥ 11.4).
- **~5 Go de disque** : 1,7 Go de base, 1,8 Go de clones de données.

## 1. Code et base

```bash
git clone -b symfony git@github.com:datanfr/datan.git && cd datan

sudo mysql <<'SQL'
CREATE DATABASE datan_symfony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'datan'@'localhost' IDENTIFIED BY 'datan';
GRANT ALL PRIVILEGES ON datan_symfony.* TO 'datan'@'localhost';
SQL
```

## 2. Configuration

Créer `.env.local` (non versionné) **avant** `composer install`, dont les
scripts démarrent le noyau. Sans lui, Symfony prend les valeurs de `.env.dist` —
production, base fictive — et toute page répond 500.

```dotenv
APP_ENV=dev
APP_SECRET=          # php -r 'echo bin2hex(random_bytes(32));'
DATABASE_URL="mysqli://datan:datan@127.0.0.1:3306/datan_symfony?serverVersion=8.0.46&charset=utf8mb4"
DEFAULT_URI=http://127.0.0.1:8000
```

`serverVersion` doit dire la version réelle du serveur (`mysql --version`),
sinon Doctrine produit des diffs de schéma fantômes. Les autres variables
(courriel, Mailjet, IA, Matomo) sont décrites dans `.env.dist` ; vides, elles
désactivent leur fonctionnalité.

```bash
composer install
```

## 3. Données

Deux voies, au choix.

### A. Depuis un dump — recommandé

Demander un dump récent à l'équipe (fabriqué selon DEPLOIEMENT.md §2.a), puis :

```bash
zcat datan_2*.sql.gz | sed 's/utf8mb4_uca1400_ai_ci/utf8mb4_unicode_ci/g' \
  | mysql -u datan -pdatan datan_symfony
php bin/console doctrine:migrations:migrate -n   # migrations postérieures au dump
php bin/console messenger:setup-transports       # le dump exclut messenger_messages
APP_ENV=prod php bin/console app:sync:quotidien  # votes récents + portraits des députés
```

Le dump vient de MariaDB : le `sed` traduit la seule collation que MySQL ne
connaît pas. Le premier `app:sync:quotidien` clone 1,8 Go de dépôts et
réimporte tout ; les suivants ne lisent que le delta (~50 s). Les portraits
(`public/assets/imgs/deputes/`) ne sont pas versionnés : c'est lui qui les
produit.

### B. Depuis l'open data

```bash
php bin/console doctrine:migrations:migrate -n
APP_ENV=prod php bin/console app:sync:quotidien --tout
```

On obtient la législature en cours. Pour les législatures 14 à 16 (facultatif) :

```bash
APP_ENV=prod php bin/console app:tricoteuses:sync --depot=scrutins-xiv --depot=scrutins-xv --depot=scrutins-xvi
for d in xiv xv xvi; do APP_ENV=prod php bin/console app:import:scrutins --depot=scrutins-$d --tout; done
for n in 14 15 16; do APP_ENV=prod php bin/console app:calcul:statistiques-deputes --legislature=$n; done
```

Le contenu éditorial — décryptages, explications de vote, blog — et la
géographie électorale — communes, résultats des élections — ne sont pas dans
l'open data : seul le dump les apporte.

## 4. Lancer

```bash
symfony server:start                # ou : php -S 127.0.0.1:8000 -t public
```

Site sur <http://127.0.0.1:8000>. Pour accéder à la rédaction (`/login`) :

```bash
php bin/console app:utilisateur:creer moi "Mon Nom" --admin
```

## Pièges

- **Imports toujours en `APP_ENV=prod`.** En dev, Doctrine garde chaque requête
  en mémoire et l'import échoue.
- **Le conteneur prod ne voit pas les changements de code.** Après une
  modification, `rm -rf var/cache/prod` avant la prochaine commande en
  `APP_ENV=prod` — jamais pendant qu'une synchronisation tourne.
- **Un 404 sur une route neuve** : vérifier `.env.local`. En `APP_ENV=prod`, le
  site passe par le cache HTTP et le conteneur compilé ; une page de dev porte
  l'en-tête `X-Debug-Token`, une page de prod l'en-tête `Age`.
- **Les migrations visent MariaDB**, le moteur de production. Une migration
  générée contre MySQL se relit avant d'être commitée.
