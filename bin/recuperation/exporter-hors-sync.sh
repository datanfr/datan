#!/usr/bin/env bash
#
# Exporte de la base du PORT tout ce que la synchronisation ne remplit pas :
# comptes, contenu éditorial, données récupérées du legacy, et les colonnes
# posées hors sync sur des lignes du sync. Le résultat est un script SQL.
#
#   bin/recuperation/exporter-hors-sync.sh [--base NOM] [--mycnf FICHIER] [--sortie FICHIER.sql.gz]
#
#   --base    base du port (défaut : celle du DATABASE_URL de l'application)
#   --mycnf   identifiants mariadb (défaut : <racine>/.my.cnf, celui de la sauvegarde nocturne)
#   --sortie  défaut : ~/recuperation-hors-sync/hors-sync-AAAAMMJJ-HHMMSS.sql.gz
#
# Restauration, sur une base neuve :
#   php bin/console doctrine:migrations:migrate
#   php bin/console app:sync:quotidien --tout   (+ commandes régénérables, DEPLOIEMENT.md)
#   gunzip -c hors-sync-….sql.gz | mariadb --defaults-file=… BASE
# Le script SQL affiche en fin de course le nombre de lignes de chaque table,
# à comparer à celui qu'il annonce en tête, et les rattachements qu'il n'a pas
# su retraduire.
#
# Ce qu'il exporte est décrit dans bin/recuperation/hors-sync.conf (source
# unique, et point d'extension). Les identifiants des tables du sync ne
# survivent pas à une reconstruction : tout ce qui les pointe est retraduit par
# clé naturelle (uid du scrutin, mp_id du député, identifiant d'amendement).
#
# Le fichier contient des mots de passe hachés et des adresses : dossier en
# 700, fichier en 600, jamais sous public/ ni dans le dépôt.
#
# Écrit pour la cage CloudLinux d'o2switch : bash 4, pas de substitution de
# processus, client mariadb (ou mysql), mariadb-dump (ou mysqldump), gzip.

set -euo pipefail
# Les lignes de hors-sync.conf se découpent par `set -- $ligne` : pas de
# développement de motifs, une condition SQL pourrait contenir un `*`.
set -f

ICI=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)
APPLI=$(cd "$ICI/../.." && pwd -P)
# Déployé, le script vit sous <racine>/releases/<id>/bin/recuperation.
RACINE=$(cd "$APPLI/../.." && pwd -P)
CONF="$ICI/hors-sync.conf"

BASE=""
MYCNF="$RACINE/.my.cnf"
SORTIE="$HOME/recuperation-hors-sync/hors-sync-$(date +%Y%m%d-%H%M%S).sql.gz"

while [ $# -gt 0 ]; do
  case "$1" in
    --base) BASE="$2"; shift 2 ;;
    --base=*) BASE="${1#*=}"; shift ;;
    --mycnf) MYCNF="$2"; shift 2 ;;
    --mycnf=*) MYCNF="${1#*=}"; shift ;;
    --sortie) SORTIE="$2"; shift 2 ;;
    --sortie=*) SORTIE="${1#*=}"; shift ;;
    -h|--help) sed -n '2,30p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    *) echo "Option inconnue : $1" >&2; exit 2 ;;
  esac
done

[ -r "$MYCNF" ] || { echo "Identifiants introuvables : $MYCNF (--mycnf)." >&2; exit 2; }

if [ -z "$BASE" ]; then
  # Comme bin/cron.sh : le nom de la base se lit dans le DATABASE_URL de
  # l'application, par Dotenv, guillemets et surcharges résolus pareil.
  PHP="$RACINE/bin/php"
  [ -x "$PHP" ] || PHP=php
  BASE=$("$PHP" -r '
    require $argv[1] . "/vendor/autoload.php";
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv($argv[1] . "/.env");
    echo ltrim((string) parse_url($_SERVER["DATABASE_URL"] ?? "", PHP_URL_PATH), "/");' "$APPLI")
fi
[ -n "$BASE" ] || { echo "Base du port inconnue : --base NOM." >&2; exit 2; }

CLIENT=mariadb
command -v mariadb >/dev/null 2>&1 || CLIENT=mysql
DUMP=mariadb-dump
command -v mariadb-dump >/dev/null 2>&1 || DUMP=mysqldump

# --raw : QUOTE() a déjà échappé ce qu'il fallait, le client ne doit rien y ajouter.
q() { "$CLIENT" --defaults-file="$MYCNF" --default-character-set=utf8mb4 -N -B --raw "$BASE" -e "$1"; }

existe() { [ "$(q "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$1'")" = 1 ]; }
colonne_existe() { [ "$(q "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$1' AND column_name = '$2'")" = 1 ]; }
nullable() { [ "$(q "SELECT is_nullable FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$1' AND column_name = '$2'")" = YES ]; }

umask 077
mkdir -p "$(dirname "$SORTIE")"
chmod 700 "$(dirname "$SORTIE")"
SQL="${SORTIE%.gz}.partiel"
TMP="$SORTIE.partiel"
: > "$SQL"
chmod 600 "$SQL"

TABLES=()
CLES=()
COLONNES=()
LIGNES=()
while IFS= read -r ligne || [ -n "$ligne" ]; do
  ligne="${ligne%%#*}"
  set -- $ligne
  [ $# -eq 0 ] && continue
  case "$1" in
    table)
      if existe "$2"; then TABLES+=("$2"); else echo "  (table $2 absente de la base : ignorée)" >&2; fi ;;
    cle) CLES+=("$2 $3 $4 $5") ;;
    colonnes) shift; COLONNES+=("$*") ;;
    lignes) shift; LIGNES+=("$*") ;;
    *) echo "Directive inconnue dans $CONF : $1" >&2; exit 2 ;;
  esac
done < "$CONF"

echo "Export hors synchronisation de « $BASE » vers $SORTIE"

{
  echo "-- Export hors synchronisation de la base « $BASE », $(date '+%F %T')."
  echo "-- Produit par bin/recuperation/exporter-hors-sync.sh d'après hors-sync.conf."
  echo "-- À rejouer sur une base migrée et synchronisée (app:sync:quotidien --tout)."
  echo "--"
  echo "-- Lignes à l'export :"
  for t in "${TABLES[@]}"; do
    printf -- '--   %-32s %s\n' "$t" "$(q "SELECT COUNT(*) FROM \`$t\`")"
  done
  echo "SET NAMES utf8mb4;"
  echo "SET FOREIGN_KEY_CHECKS = 0;"
  echo "SET UNIQUE_CHECKS = 1;"
} >> "$SQL"

# 1. Lignes entières d'une table du sync, sans leur identifiant.
#    Forme INSERT … SELECT : une colonne qui pointe une table du sync est
#    retraduite par jointure sur la clé naturelle, et si la cible manque, la
#    ligne n'est simplement pas insérée — une erreur arrêterait la restauration.
for spec in "${LIGNES[@]}"; do
  set -- $spec
  t=$1; cles=$2; shift 2; condition="$*"
  existe "$t" || continue
  # clés : `col` ou `col=TABLE.CLE`, séparées par des virgules
  declare -A REF=()
  declare -A CLE=()
  for k in ${cles//,/ }; do
    c="${k%%=*}"
    CLE[$c]=1
    [ "$k" != "$c" ] && REF[$c]="${k#*=}"
  done
  cols=$(q "SELECT GROUP_CONCAT(column_name ORDER BY ordinal_position SEPARATOR ' ') FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$t' AND column_name <> 'id'")
  liste=""; select=""; depuis=""; ou=""; maj=""; jointures=""; n=0
  for c in $cols; do
    liste="$liste${liste:+, }\`$c\`"
    if [ -n "${REF[$c]:-}" ]; then
      n=$((n + 1)); ref="${REF[$c]%%.*}"; nat="${REF[$c]#*.}"
      jointures="$jointures JOIN \`$ref\` s$n ON s$n.id = t.\`$c\`"
      select="$select${select:+, }'j$n.id'"
      depuis="$depuis${depuis:+, }\`$ref\` j$n"
      ou="$ou${ou:+, ' AND ', }'j$n.\`$nat\` = ', QUOTE(s$n.\`$nat\`)"
    else
      select="$select${select:+, }QUOTE(t.\`$c\`)"
    fi
    [ -n "${CLE[$c]:-}" ] || maj="$maj${maj:+, }\`$t\`.\`$c\` = VALUES(\`$c\`)"
  done
  if [ -n "$depuis" ]; then
    source_sql="CONCAT(' FROM $depuis WHERE ', $ou)"
  else
    source_sql="''"
  fi
  echo "  lignes $t ($condition) : $(q "SELECT COUNT(*) FROM \`$t\` t WHERE $condition")"
  echo "-- lignes $t où $condition" >> "$SQL"
  q "SELECT CONCAT('INSERT INTO \`$t\` ($liste) SELECT ', CONCAT_WS(', ', $select), $source_sql, ' ON DUPLICATE KEY UPDATE $maj;') FROM \`$t\` t$jointures WHERE $condition" >> "$SQL"
  unset REF CLE
done

# 2. Tables entières, identifiants compris. REPLACE : un second passage converge.
if [ ${#TABLES[@]} -gt 0 ]; then
  echo "  tables : ${#TABLES[@]}"
  "$DUMP" --defaults-file="$MYCNF" --default-character-set=utf8mb4 --single-transaction --quick \
    --no-create-info --skip-triggers --replace --complete-insert --hex-blob --skip-comments --skip-add-locks \
    "$BASE" "${TABLES[@]}" >> "$SQL"
  echo "SET FOREIGN_KEY_CHECKS = 0;" >> "$SQL"
fi

# 3. Colonnes qui pointent le sync : décalées hors de portée, puis retraduites
#    valeur par valeur (une instruction par cible distincte, toutes les lignes
#    qui la pointent d'un coup — une table sans `id`, comme amendement_tag,
#    s'y prête aussi). Le décalage évite qu'une valeur déjà retraduite heurte
#    l'ancienne valeur d'une autre ligne sur une clé unique (contact_depute).
DECALAGE=1000000000
for spec in "${CLES[@]}"; do
  set -- $spec
  t=$1; c=$2; ref=$3; nat=$4
  existe "$t" && existe "$ref" || continue
  echo "-- $t.$c → $ref.$nat" >> "$SQL"
  echo "UPDATE \`$t\` SET \`$c\` = \`$c\` + $DECALAGE WHERE \`$c\` IS NOT NULL;" >> "$SQL"
  q "SELECT CONCAT('UPDATE \`$t\` SET \`$c\` = COALESCE((SELECT r.id FROM \`$ref\` r WHERE r.\`$nat\` = ', QUOTE(r.\`$nat\`), '), \`$c\`) WHERE \`$c\` = ', r.id + $DECALAGE, ';') FROM \`$ref\` r WHERE r.id IN (SELECT \`$c\` FROM \`$t\`)" >> "$SQL"
done

# 4. Colonnes posées hors sync sur des lignes du sync.
for spec in "${COLONNES[@]}"; do
  set -- $spec
  t=$1; cle=$2; shift 2
  existe "$t" || continue
  affectations=""; jointures=""; condition=""; n=0
  for col in "$@"; do
    c="${col%%=*}"
    colonne_existe "$t" "$c" || { echo "  ($t.$c absente : ignorée)" >&2; continue; }
    if [ "$col" != "$c" ]; then
      cible="${col#*=}"; ref="${cible%%.*}"; nat="${cible#*.}"; n=$((n + 1))
      jointures="$jointures LEFT JOIN \`$ref\` j$n ON j$n.id = t.\`$c\`"
      affectations="$affectations${affectations:+, }IF(t.\`$c\` IS NULL, '\`$c\` = NULL', CONCAT('\`$c\` = COALESCE((SELECT r.id FROM \`$ref\` r WHERE r.\`$nat\` = ', QUOTE(j$n.\`$nat\`), '), \`$c\`)'))"
    else
      affectations="$affectations${affectations:+, }CONCAT('\`$c\` = ', QUOTE(t.\`$c\`))"
    fi
    nullable "$t" "$c" && condition="$condition${condition:+ OR }t.\`$c\` IS NOT NULL"
  done
  [ -n "$affectations" ] || continue
  [ -n "$condition" ] || condition="1 = 1"
  echo "  colonnes $t : $(q "SELECT COUNT(*) FROM \`$t\` t WHERE $condition") ligne(s)"
  echo "-- colonnes hors sync de $t" >> "$SQL"
  q "SELECT CONCAT('UPDATE \`$t\` SET ', CONCAT_WS(', ', $affectations), ' WHERE \`$cle\` = ', QUOTE(t.\`$cle\`), ';') FROM \`$t\` t$jointures WHERE $condition" >> "$SQL"
done

# 5. Contrôles, affichés par la restauration.
{
  echo "SET FOREIGN_KEY_CHECKS = 1;"
  echo "-- Contrôles : lignes en base après restauration, à comparer à l'en-tête."
  for t in "${TABLES[@]}"; do
    echo "SELECT '$t' AS \`table\`, COUNT(*) AS lignes FROM \`$t\`;"
  done
  for spec in "${CLES[@]}"; do
    set -- $spec
    echo "SELECT '$1.$2 non retraduits ($3 absent de la base)' AS controle, COUNT(*) AS lignes FROM \`$1\` WHERE \`$2\` >= $DECALAGE;"
  done
} >> "$SQL"

gzip -9 -c "$SQL" > "$TMP"
rm -f "$SQL"
mv "$TMP" "$SORTIE"
chmod 600 "$SORTIE"

echo "Terminé : $SORTIE ($(du -h "$SORTIE" | cut -f1))."
