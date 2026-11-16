#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Vérifie, et remet si besoin, le branchement de la configuration du sélecteur
# PHP sur la version qu'on utilise (cf. brancher_config_selecteur dans
# bin/serveur-commun.sh). Appelé par cron chaque minute : silencieux tant que
# le lien est bon, une ligne de journal quand il a fallu le remettre.
# ---------------------------------------------------------------------------
set -euo pipefail
ICI=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)
RACINE=$(cd "$ICI/../../.." && pwd)
# shellcheck source=bin/serveur-commun.sh
source "$ICI/serveur-commun.sh"
# Le lanceur bin/php, et non trouver_php : on veut la version réellement
# utilisée par le cron et le déploiement, pas une nouvelle détection.
PHP=$(sed -n 's/^exec \([^ ]*\) .*/\1/p' "$RACINE/bin/php" 2>/dev/null || true)
[[ -n "$PHP" ]] || exit 0
brancher_config_selecteur "$PHP"
