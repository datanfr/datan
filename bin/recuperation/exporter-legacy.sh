#!/usr/bin/env bash
#
# Exporte de la base du legacy (datan.fr) tous les fichiers TSV qu'attendent les
# commandes de récupération, en une commande, sur le serveur.
#
#   bin/recuperation/exporter-legacy.sh [--base NOM] [--dossier DOSSIER] [nom…]
#
#   --base     base du legacy (défaut : $DATABASE_NAME, posée par ~/.bashrc)
#   --dossier  où écrire (défaut : ~/recuperation-legacy/AAAAMMJJ-HHMMSS)
#   nom…       n'exporter que ces requêtes (sans .sql) ; toutes par défaut
#
# Puis : php bin/console app:recuperation:legacy --dossier=DOSSIER
#
# Source unique des requêtes : bin/recuperation/legacy/<nom>.sql produit
# <nom>.tsv. Ajouter une table à la récupération, c'est y déposer un fichier —
# le script le prend sans autre modification (point d'extension des tables
# traitées ailleurs : amendements_ia, municipales…). Une requête dont l'import
# lit un TSV sans en-tête porte la ligne `-- datan:sans-en-tete`.
#
# Les identifiants de la base viennent de l'environnement du compte, comme pour
# le legacy lui-même : DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD
# (~/.bashrc sur o2switch, chargé ici s'ils manquent). Le mot de passe passe
# par MYSQL_PWD et jamais sur la ligne de commande, où `ps` le montrerait.
#
# Les exports contiennent des mots de passe hachés et des adresses : dossier en
# 700, fichiers en 600, hors de public/ et hors du dépôt. Les supprimer une fois
# la récupération faite.
#
# Écrit pour la cage CloudLinux d'o2switch : bash 4, pas de substitution de
# processus, rien d'autre que le client mariadb (ou mysql) et coreutils.

set -o pipefail

if [ -z "${DATABASE_USERNAME:-}" ] && [ -f "$HOME/.bashrc" ]; then
  # shellcheck disable=SC1091
  . "$HOME/.bashrc" >/dev/null 2>&1
fi

set -eu

REQUETES="$(cd "$(dirname "$0")/legacy" && pwd)"
BASE="${DATABASE_NAME:-}"
DOSSIER="$HOME/recuperation-legacy/$(date +%Y%m%d-%H%M%S)"
NOMS=()

while [ $# -gt 0 ]; do
  case "$1" in
    --base) BASE="$2"; shift 2 ;;
    --base=*) BASE="${1#*=}"; shift ;;
    --dossier) DOSSIER="$2"; shift 2 ;;
    --dossier=*) DOSSIER="${1#*=}"; shift ;;
    -h|--help) sed -n '2,30p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    -*) echo "Option inconnue : $1" >&2; exit 2 ;;
    *) NOMS+=("${1%.sql}"); shift ;;
  esac
done

if [ -z "$BASE" ]; then
  echo "Base du legacy inconnue : --base NOM, ou DATABASE_NAME dans l'environnement." >&2
  exit 2
fi
if [ -z "${DATABASE_USERNAME:-}" ]; then
  echo "DATABASE_USERNAME absent de l'environnement (cf. ~/.bashrc)." >&2
  exit 2
fi

CLIENT=mariadb
command -v mariadb >/dev/null 2>&1 || CLIENT=mysql

if [ ${#NOMS[@]} -eq 0 ]; then
  for f in "$REQUETES"/*.sql; do
    n="$(basename "$f" .sql)"
    NOMS+=("$n")
  done
fi

umask 077
mkdir -p "$DOSSIER"
chmod 700 "$DOSSIER"

export MYSQL_PWD="${DATABASE_PASSWORD:-}"

MANIFESTE="$DOSSIER/MANIFESTE.tsv"
printf 'fichier\tlignes\toctets\tsha256\texporte_le\n' > "$MANIFESTE"

echo "Export de « $BASE » vers $DOSSIER"
echecs=0
for n in "${NOMS[@]}"; do
  requete="$REQUETES/$n.sql"
  if [ ! -f "$requete" ]; then
    echo "  $n : requête introuvable ($requete)" >&2
    echecs=$((echecs + 1))
    continue
  fi

  # Les commandes ImportLegacyCommand sautent une ligne d'en-tête ; les imports
  # plus anciens (app:import:votes…) lisent un TSV nu. Une requête le
  # demande par la ligne `-- datan:sans-en-tete`.
  entete=1
  options=()
  if grep -q '^-- datan:sans-en-tete' "$requete"; then
    entete=0
    options+=(--skip-column-names)
  fi

  # Écrit à côté puis renommé : un export interrompu ne laisse pas un TSV
  # tronqué que la récupération lirait comme complet.
  if "$CLIENT" -h "${DATABASE_HOST:-localhost}" ${DATABASE_PORT:+--port="$DATABASE_PORT"} -u "$DATABASE_USERNAME" "$BASE" \
      --default-character-set=utf8mb4 --batch --quick ${options[@]+"${options[@]}"} < "$requete" > "$DOSSIER/$n.tsv.partiel"; then
    mv "$DOSSIER/$n.tsv.partiel" "$DOSSIER/$n.tsv"
    chmod 600 "$DOSSIER/$n.tsv"
    total=$(wc -l < "$DOSSIER/$n.tsv")
    lignes=$((total > entete ? total - entete : 0))
    octets=$(wc -c < "$DOSSIER/$n.tsv")
    empreinte=$(sha256sum "$DOSSIER/$n.tsv" | cut -d' ' -f1)
    printf '%s.tsv\t%d\t%d\t%s\t%s\n' "$n" "$lignes" "$octets" "$empreinte" "$(date '+%F %T')" >> "$MANIFESTE"
    printf '  %-34s %9d ligne(s)\n' "$n.tsv" "$lignes"
  else
    rm -f "$DOSSIER/$n.tsv.partiel"
    echo "  $n : ÉCHEC de la requête" >&2
    echecs=$((echecs + 1))
  fi
done

chmod 600 "$MANIFESTE"

if [ "$echecs" -gt 0 ]; then
  echo "$echecs export(s) en échec." >&2
  exit 1
fi

echo "Terminé. Récupération : php bin/console app:recuperation:legacy --dossier=$DOSSIER"
