#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Pose la planification de Datan dans la crontab du compte, sans toucher au
# reste : ce compte est celui de datan.fr, et sa table porte les tâches du
# legacy. Seules les lignes entre les deux marqueurs ci-dessous sont à nous.
#
#   bash ~/symfony.datan.fr/current/bin/cron.sh afficher    diff, sans rien écrire
#   bash ~/symfony.datan.fr/current/bin/cron.sh installer   pose ou remplace le bloc
#   bash ~/symfony.datan.fr/current/bin/cron.sh retirer     enlève le bloc
#
# Le gabarit est bin/crontab.prod ; ses @MARQUEURS@ sont remplacés ici par des
# chemins en dur (cf. son en-tête pour la raison).
# ---------------------------------------------------------------------------
set -euo pipefail
# Sans elle, un échec à l'intérieur de $( … ) ne fait pas échouer la
# substitution, et une table incomplète partirait vers `crontab -`.
shopt -s inherit_errexit

ICI=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)
# shellcheck source=bin/serveur-commun.sh
source "$ICI/serveur-commun.sh"

# Le script tourne depuis releases/<id>/bin : la racine est trois crans plus haut.
RACINE=$(cd "$ICI/../../.." && pwd)
PARTAGE=$RACINE/shared
DEBUT='# >>> datan-symfony — bloc géré par bin/cron.sh, ne pas modifier à la main'
FIN='# <<< datan-symfony'

[[ -L "$RACINE/current" ]] || echec "$RACINE/current absent : déployer avant d'installer la planification."

# « Pas de table » n'est pas une erreur ; tout autre échec de `crontab -l` en
# est une. Le confondre avec une table vide ferait installer notre seul bloc,
# et effacerait les tâches du legacy.
table_actuelle() {
  local sortie
  if sortie=$(crontab -l 2>&1); then
    printf '%s\n' "$sortie"
  elif [[ "$sortie" != *"no crontab"* ]]; then
    echec "crontab -l a échoué : $sortie"
  fi
}

# Tout sauf notre bloc, marqueurs compris.
table_sans_bloc() {
  table_actuelle | awk -v debut="$DEBUT" -v fin="$FIN" '
    $0 == debut { dedans = 1; next }
    $0 == fin   { dedans = 0; next }
    !dedans'
}

bloc() {
  local php base
  php=$(preparer_php)
  # Par Dotenv, comme l'application : guillemets, surcharges et .env.dist
  # sont résolus de la même façon.
  base=$("$php" -r '
    require $argv[1] . "/vendor/autoload.php";
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv($argv[1] . "/.env");
    echo ltrim((string) parse_url($_SERVER["DATABASE_URL"] ?? "", PHP_URL_PATH), "/");' "$RACINE/current")
  [[ -n "$base" ]] || echec "Nom de base introuvable dans le DATABASE_URL de $RACINE/.env.local."
  [[ -f "$RACINE/.my.cnf" ]] \
    || printf '   (avertissement : %s/.my.cnf absent, la sauvegarde de 3 h 30 échouera)\n' "$RACINE" >&2

  mkdir -p "$PARTAGE/var" "$PARTAGE/sauvegardes"
  echo "$DEBUT"
  sed -e "s|@COURANT@|$RACINE/current|g" \
      -e "s|@PARTAGE@|$PARTAGE|g" \
      -e "s|@RACINE@|$RACINE|g" \
      -e "s|@PHP@|$php|g" \
      -e "s|@BASE@|$base|g" \
      "$ICI/crontab.prod"
  echo "$FIN"
}

case "${1:-}" in
  afficher)
    nouvelle=$(table_sans_bloc; bloc)
    # Des fichiers, et non `diff <(…) <(…)` : la substitution de processus
    # passe par /dev/fd, absent de la cage CloudLinux d'o2switch.
    avant=$(mktemp); apres=$(mktemp)
    table_actuelle > "$avant"
    printf '%s\n' "$nouvelle" > "$apres"
    diff -u "$avant" "$apres" && echo "Aucun changement." || true
    rm -f "$avant" "$apres"
    ;;
  installer)
    # Rendue en entier avant d'écrire : `crontab -` installe ce qu'il a reçu,
    # même tronqué par un échec en cours de route.
    nouvelle=$(table_sans_bloc; bloc)
    copie="$PARTAGE/crontab.avant-$(date +%Y%m%d-%H%M%S)"
    table_actuelle > "$copie"
    printf '%s\n' "$nouvelle" | crontab -
    echo "Bloc datan-symfony posé. Table précédente : $copie"
    ;;
  retirer)
    copie="$PARTAGE/crontab.avant-$(date +%Y%m%d-%H%M%S)"
    nouvelle=$(table_sans_bloc)
    table_actuelle > "$copie"
    printf '%s\n' "$nouvelle" | crontab -
    echo "Bloc datan-symfony retiré. Table précédente : $copie"
    ;;
  *)
    sed -n '7,9p' "$0" | sed 's/^# //' >&2
    exit 2
    ;;
esac
