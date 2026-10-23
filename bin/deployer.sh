#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Mise en ligne d'une version, côté serveur. Lancé par le workflow
# « Déploiement » une fois l'artefact déposé dans $RACINE/depot/ :
#
#   bash ~/symfony.datan.fr/depot/bin/deployer.sh <identifiant>
#
# Variables facultatives : PHP_BIN (chemin d'un PHP ≥ 8.4, sinon détecté),
# CONSERVER (versions gardées, 3 par défaut).
#
# Tout ce qui peut échouer se passe AVANT la bascule, dans un répertoire que
# personne ne sert : dépendances de plateforme, conteneur compilé, migrations.
# La bascule elle-même est un `mv` de lien symbolique, atomique. Une version
# ratée est effacée, et le site continue de servir la précédente.
#
# Ce que la bascule ne règle pas : les migrations, elles, sont déjà passées.
# L'ancienne version sert donc quelques secondes sur le nouveau schéma — comme
# avec tout déploiement à base unique. Une migration qui supprime une colonne
# encore lue se fait en deux temps.
# ---------------------------------------------------------------------------
set -euo pipefail

RACINE=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
# shellcheck source=bin/serveur-commun.sh
source "$RACINE/depot/bin/serveur-commun.sh"

ID=${1:?Usage : deployer.sh <identifiant de version>}
DEPOT=$RACINE/depot
PARTAGE=$RACINE/shared
CIBLE=$RACINE/releases/$ID
CONSERVER=${CONSERVER:-3}

export APP_ENV=prod APP_DEBUG=0

# Ce qui doit survivre d'une version à l'autre. Les portraits ne sont pas dans
# le dépôt : le sync quotidien les régénère dans public/ (ImportPhotosCommand,
# DetourerPhotosCommand), et une version neuve qui ne les verrait pas servirait
# des fiches sans photo jusqu'au lendemain. Les professions de foi sont des PDF
# déposés à la main, jamais dans Git.
DOSSIERS_PARTAGES=(
  var/log
  var/tricoteuses
  public/assets/imgs/deputes
  public/assets/imgs/deputes_detoures
  public/assets/data/professions
)
# Le journal du scraper retient les pages vues sans résultat : le perdre à
# chaque version ferait redemander à assemblee-nationale.fr ce qu'on sait vide.
FICHIERS_PARTAGES=(
  var/scraper-scrutins.json
)

bascule=0
nettoyer_si_echec() {
  if [[ $bascule -eq 0 && -d "$CIBLE" ]]; then
    printf '!! Échec avant la bascule : version %s effacée, le site sert toujours la précédente.\n' "$ID" >&2
    rm -rf "$CIBLE"
  fi
}
trap nettoyer_si_echec EXIT

etape "PHP"
PHP=$(preparer_php)
echo "   → $PHP"

etape "Préalables"
[[ -f "$RACINE/.env.local" ]] \
  || echec "$RACINE/.env.local manquant : APP_SECRET, DATABASE_URL, DEFAULT_URI… (DEPLOIEMENT.md, « Amorçage »)."
if grep -q '^AuthUserFile' "$DEPOT/public/.htaccess" && [[ ! -s "$RACINE/.htpasswd" ]]; then
  # Sans le fichier, LiteSpeed répond 500 à toutes les adresses.
  echec "Le verrou de préproduction est actif mais $RACINE/.htpasswd est absent ou vide."
fi

# Vider var/cache/prod sous une commande en cours la fait échouer sans message
# exploitable (CLAUDE.md). Chaque version a son propre var/cache, mais la
# bascule interrompt le worker et les migrations écrivent dans les tables que
# le sync remplit : on refuse plutôt que de casser la moisson du jour.
if ps -u "$(id -un)" -o args= 2>/dev/null | grep -q '[a]pp:sync:quotidien'; then
  echec "Synchronisation quotidienne en cours. Relancer le déploiement une fois finie."
fi

etape "Version $ID"
mkdir -p "$RACINE/releases"
# Liens durs : quelques secondes pour 20 000 fichiers, et presque rien sur le
# disque. Sans danger, car rsync remplace un fichier modifié par un nouveau
# (fichier temporaire puis renommage) au lieu de le réécrire sur place : la
# version figée garde l'ancien contenu.
cp -al "$DEPOT" "$CIBLE" 2>/dev/null || { rm -rf "$CIBLE"; cp -a "$DEPOT" "$CIBLE"; }

mkdir -p "$CIBLE/var"
for chemin in "${DOSSIERS_PARTAGES[@]}"; do
  mkdir -p "$PARTAGE/$chemin" "$(dirname "$CIBLE/$chemin")"
  rm -rf "${CIBLE:?}/$chemin"
  ln -s "$PARTAGE/$chemin" "$CIBLE/$chemin"
done
for chemin in "${FICHIERS_PARTAGES[@]}"; do
  mkdir -p "$(dirname "$PARTAGE/$chemin")" "$(dirname "$CIBLE/$chemin")"
  rm -f "$CIBLE/$chemin"
  ln -s "$PARTAGE/$chemin" "$CIBLE/$chemin"
done
# Symfony lit .env.local à la racine du projet, c'est-à-dire de la version.
ln -sfn "$RACINE/.env.local" "$CIBLE/.env.local"

# AuthUserFile exige un chemin absolu, que le dépôt ne peut pas connaître : il
# dépend du compte. `sed -i` écrit un nouveau fichier et casse le lien dur, le
# miroir depot/ n'est donc pas touché.
sed -i "s|^AuthUserFile .*|AuthUserFile $RACINE/.htpasswd|" "$CIBLE/public/.htaccess"

etape "Plateforme"
# Le contrôle de Composer (vendor/composer/platform_check.php) s'exécute au
# chargement de l'autoloader : c'est lui qui dira, par exemple, qu'une
# extension déclarée manque.
"$PHP" -r 'require $argv[1];' "$CIBLE/vendor/autoload.php"

# Un serverVersion qui ment fait générer à Doctrine du DDL que le serveur
# refuse — c'est ainsi qu'une restauration s'est arrêtée sur
# `utf8mb4_uca1400_ai_ci` (CLAUDE.md, DEPLOIEMENT.md §4). On compare à ce que
# la base répond avant de lancer la moindre migration.
"$PHP" /dev/stdin "$CIBLE" <<'PHP'
<?php
require $argv[1] . '/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv($argv[1] . '/.env');

$url = $_SERVER['DATABASE_URL'] ?? '';
parse_str((string) parse_url($url, PHP_URL_QUERY), $requete);
$declaree = (string) ($requete['serverVersion'] ?? '');
try {
    $reelle = (string) Doctrine\DBAL\DriverManager::getConnection((new Doctrine\DBAL\Tools\DsnParser())->parse($url))
        ->fetchOne('SELECT VERSION()');
} catch (Throwable $e) {
    fwrite(STDERR, '!! Base injoignable avec le DATABASE_URL de ~/symfony.datan.fr/.env.local : ' . $e->getMessage() . "\n");
    exit(1);
}

preg_match('/^(\d+\.\d+)\.(\d+)/', $declaree, $d);
preg_match('/^(\d+\.\d+)\.(\d+)/', $reelle, $r);
printf("   base : %s (serverVersion déclaré : %s)\n", $reelle, $declaree ?: 'aucun');
if (!$d || !$r || $d[1] !== $r[1]) {
    fwrite(STDERR, "!! serverVersion de DATABASE_URL à corriger dans ~/symfony.datan.fr/.env.local : la base est en $reelle.\n");
    exit(1);
}
if ($d[2] !== $r[2]) {
    echo "   (correctif différent : sans effet sur le DDL, mais à réaligner)\n";
}
PHP

etape "Conteneur et cache"
"$PHP" "$CIBLE/bin/console" cache:warmup --no-ansi

etape "Migrations"
"$PHP" "$CIBLE/bin/console" doctrine:migrations:migrate --no-interaction --allow-no-migration --no-ansi

etape "Bascule"
if [[ -L "$RACINE/current" ]]; then
  # Le worker en cours lit son signal d'arrêt dans le cache.app de SA version :
  # c'est donc l'ancienne console qu'il faut appeler. Il finit son message et
  # sort ; le cron des courriels le relance sur la nouvelle au passage suivant.
  "$PHP" "$RACINE/current/bin/console" messenger:stop-workers --no-ansi || true
elif [[ -e "$RACINE/current" ]]; then
  # cPanel crée la racine web du sous-domaine s'il ne la trouve pas : un vrai
  # dossier current/ empêcherait la bascule. Écarté, jamais effacé.
  mv "$RACINE/current" "$RACINE/current.avant-$(date +%Y%m%d-%H%M%S)"
fi
ln -sfn "releases/$ID" "$RACINE/current.nouveau"
mv -Tf "$RACINE/current.nouveau" "$RACINE/current"
bascule=1
echo "   current → releases/$ID"

# La racine web du sous-domaine est ~/symfony.datan.fr/public : un lien fixe
# vers current/public, posé une fois. Seul `current` change à chaque version,
# la bascule reste donc un unique `mv`. Un vrai dossier public/ (créé par
# cPanel, ou reste d'un ancien clone) est écarté, jamais effacé.
if [[ ! -L "$RACINE/public" ]]; then
  [[ -e "$RACINE/public" ]] && mv "$RACINE/public" "$RACINE/public.avant-$(date +%Y%m%d-%H%M%S)"
  ln -s current/public "$RACINE/public"
  echo "   public → current/public"
fi

etape "Ménage"
# Trois versions au moins : PHP garde en cache la résolution des liens
# symboliques (realpath_cache_ttl, 120 s par défaut), si bien que des requêtes
# arrivent encore sur l'ancienne version juste après la bascule. Elle doit être
# encore là, intacte.
ls -1 "$RACINE/releases" | sort | head -n "-$CONSERVER" | while read -r ancienne; do
  if [[ "$ancienne" != "$ID" ]]; then
    rm -rf "${RACINE:?}/releases/$ancienne"
    echo "   - $ancienne"
  fi
done

etape "Réchauffage du cache HTTP (arrière-plan, var/log/rechauffage.log)"
# Le magasin du cache HTTP vit sous var/cache : la nouvelle version part à
# froid. Le réchauffage dure une quinzaine de minutes et ne doit pas retenir le
# workflow — d'où le détachement complet de la session SSH.
cd "$RACINE/current"
nohup "$PHP" bin/console app:cache:rechauffer --sans-purge --no-ansi \
  >> var/log/rechauffage.log 2>&1 < /dev/null &

echo
echo "✓ Version $ID en ligne"
