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
php bin/console sass:build --watch  # compile la feuille de style (cf. plus bas)
symfony server:start                # ou : php -S 127.0.0.1:8000 -t public
```

Site sur <http://127.0.0.1:8000>. Pour accéder à la rédaction (`/login`) :

```bash
php bin/console app:utilisateur:creer moi "Mon Nom" --admin
```

## CSS et JavaScript

### Où écrire

| Quoi | Où | Appel dans un gabarit |
|---|---|---|
| Styles du site | `assets/styles/main.scss` | `asset('styles/main.scss')` (déjà dans `base.html.twig`) |
| Styles de la rédaction | `assets/styles/admin/*.css` | `asset('styles/admin/….css')` |
| Scripts du site | `assets/scripts/*.js` | `asset('scripts/….js')` |
| CSS critique (généré) | `assets/styles/critical/` | bloc `css_critique` (cf. plus bas) |
| Bibliothèques tierces, images, polices, documents | `public/assets/` | `asset('assets/…')` |

Tout ce qui est dans `assets/` passe par **AssetMapper** : servi sous `/build/`,
avec un condensat du contenu dans le nom (`main-lkKi-Il.css`). Le navigateur
peut donc le garder un an, et une modification change l'adresse. Ce qui est
dans `public/assets/` est servi tel quel, sous la même adresse depuis toujours :
on n'y écrit pas de code, seulement des fichiers tiers ou binaires.

- **Bootstrap 4.6.2** vient de Composer (`twbs/bootstrap`) et non de npm.
  `main.scss` l'importe avec `@import "bootstrap"`, et ses variables se
  surchargent en tête du fichier. La version est figée : la 5 casserait toutes
  les classes du site.
- **Dans le SCSS, les `url()` vers les images s'écrivent en absolu**
  (`/assets/imgs/…`). La feuille est servie depuis `/build/styles/`, si bien
  qu'un chemin relatif ne retombe plus sur `public/assets/`.
- **Les scripts sont des scripts classiques**, pas des modules ES : ils
  s'appuient sur le jQuery global chargé par `base.html.twig`. Pas d'`import`,
  donc. AssetMapper analyse les `import` et refuserait un paquet absent de
  `importmap.php`.
- **Exception : les scripts de la rédaction** (`public/assets/js/dashboard/`).
  `init-ckeditor.js` est un module qui importe `ckeditor5` par l'importmap écrit
  à la main dans les formulaires d'administration. Il reste où il est.
- `public/assets/js/dist/chart.min.js` est un paquet figé (Chart.js 4.4.1 et le
  greffon datalabels 2.2.0), construit autrefois par le rollup du legacy à
  partir de `public/assets/js/custom/import_chartjs.js`. On ne le retouche pas,
  on le remplace.

### Compiler

En **développement**, seul le SCSS se compile ; les scripts sont servis
directement depuis `assets/scripts/`, lisibles et non minifiés :

```bash
php bin/console sass:build --watch   # recompile main.scss à chaque enregistrement
```

Sans cette compilation, toute page répond 500 avec un message explicite
(« `var/sass/main-….output.css` doesn't exist, run `php bin/console sass:build` »).
dart-sass est téléchargé dans `var/dart-sass/` au premier lancement ; ni Node ni
npm ne sont nécessaires.

En **production**, le workflow de déploiement exécute :

```bash
php bin/console sass:build           # assets/styles/main.scss → var/sass/
php bin/console asset-map:compile    # tout assets/ → public/build/, minifié
```

La minification (CSS et JS) est faite au moment d'`asset-map:compile` par
`sensiolabs/minify-bundle`, qui télécharge son binaire dans `var/minify/`. Elle
est coupée en dev.

**Ne pas lancer `asset-map:compile` en local**, ou bien supprimer `public/build/`
juste après. Tant que `public/build/manifest.json` existe, le mode dev sert ces
fichiers figés au lieu de vos sources, sans le signaler.

### CSS critique

Sur trois pages, l'accueil, la ville et le groupe (comme sur datan.fr), le
`<head>` ne charge d'abord qu'un extrait de `main.css` : ce qu'il faut pour
afficher le premier écran. La feuille complète arrive ensuite sans bloquer le
rendu. Une page s'y inscrit par un bloc :

```twig
{% block css_critique %}city{% endblock %}   {# → assets/styles/critical/city.css et city-mobile.css #}
```

Les extraits se mesurent dans un vrai navigateur, sur une page réellement
servie. C'est la seule étape qui demande Node (≥ 22) :

```bash
npm ci && npx playwright install chromium     # une fois
BASE_URL=https://symfony.datan.fr npm run critique
```

`BASE_URL` doit pointer sur un site **aux données complètes** et qui sert **la
même version des gabarits**. Une base locale incomplète raccourcit les pages,
et l'extrait omet alors des blocs que la production affiche au premier écran.
Pour mesurer le HTML de la préproduction avec un `main.css` compilé sur votre
poste, ajouter `CSS_URL=http://127.0.0.1:8000/build/styles/main-….css`.

**À relancer** quand le haut de l'une de ces trois pages change de mise en page,
ou qu'une modification de `main.scss` touche à ce qui s'y affiche. Puis
committer `assets/styles/critical/`. Un extrait périmé ne casse rien, puisque
`main.css` arrive juste après et a le dernier mot, mais la page « saute » au
moment où la feuille complète s'applique. Pages, adresses et dimensions sont
déclarées en tête de `bin/css-critique.mjs`.

### Ce que faisait Grunt (legacy)

| Gruntfile | Aujourd'hui |
|---|---|
| `sass` (`main.scss`, `bootstrap.scss`) | `sass:build` (`main.scss` seul) |
| `purifycss` + `concat` | supprimés : `main.scss` importait déjà Bootstrap en entier, le Bootstrap purifié mis devant n'était qu'un doublon (98 Ko sur 315) |
| `babel` (ES5) | supprimé : tous les navigateurs lisent l'ES2015 |
| `uglify` | `minify-bundle`, pendant `asset-map:compile` |
| `rollup` (Chart.js) | paquet figé `public/assets/js/dist/chart.min.js` |
| `critical` | `npm run critique` |
| `?v=<version>` dans les adresses | condensat dans le nom du fichier |

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
