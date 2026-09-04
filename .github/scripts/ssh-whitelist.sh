#!/usr/bin/env bash
# Gestion de la liste blanche SSH o2switch via l'API cPanel (jeton d'API).
# Documentation : https://faq.o2switch.fr/cpanel/outils/exception-parefeu/
#
# Variables d'environnement requises :
#   CPANEL_SERVER     adresse du serveur cPanel (ex : monserveur.o2switch.net)
#   CPANEL_USERNAME   identifiant cPanel
#   CPANEL_API_TOKEN  jeton créé dans cPanel > Sécurité > Gérer les jetons d'API
# Optionnelle :
#   CPANEL_API_BASE   remplace https://$CPANEL_SERVER:2083 (utilisé par les tests locaux)
#
# Usage :
#   ssh-whitelist.sh list          affiche la liste et écrit "ips=a,b,c" dans $GITHUB_OUTPUT s'il est défini
#   ssh-whitelist.sh remove <ip>   supprime toutes les entrées de l'IP (entrantes et sortantes)
#   ssh-whitelist.sh add <ip>      ajoute l'IP sur le port 22 (sans erreur si elle est déjà présente)
#   ssh-whitelist.sh verify <ip>   échoue si l'IP n'est pas dans la liste
set -euo pipefail

usage() {
  sed -n '2,/^set -euo/{/^set -euo/!s/^# \{0,1\}//p}' "$0" >&2
  exit 2
}

for var in CPANEL_SERVER CPANEL_USERNAME CPANEL_API_TOKEN; do
  if [[ -z "${!var:-}" ]]; then
    echo "::error::Variable $var manquante (secret GitHub non défini ?)" >&2
    exit 1
  fi
done

API_BASE="${CPANEL_API_BASE:-https://$CPANEL_SERVER:2083}/execute/SshWhitelist"
AUTH_HEADER="Authorization: cpanel $CPANEL_USERNAME:$CPANEL_API_TOKEN"

# api <fonction> [paramètres de requête]
# Affiche sur stdout l'objet résultat UAPI ({"status":1,"data":...,"messages":...}) ou échoue avec un diagnostic.
api() {
  local func="$1" query="${2:-}" body_file http_code raw result
  body_file=$(mktemp)
  # -m 45 : l'ajout est "pseudo-bloquant" côté o2switch (jusqu'à 35 s).
  # --retry ne rejoue que les erreurs transitoires (réseau, 5xx), jamais un 401.
  # Le corps est écrit dans un fichier car curl le tronque à chaque nouvelle tentative (pas possible sur stdout).
  http_code=$(curl -sS -m 45 --retry 3 --retry-delay 5 -H "$AUTH_HEADER" \
    -o "$body_file" -w '%{http_code}' "$API_BASE/$func${query:+?$query}")
  raw=$(cat "$body_file")
  rm -f "$body_file"

  # Selon la version de cPanel, le résultat est renvoyé tel quel ou enveloppé dans {"result": {...}}.
  if [[ "$http_code" == "200" ]] \
    && result=$(printf '%s\n' "$raw" | jq -ce 'if type == "object" and has("result") then .result else . end' 2>/dev/null) \
    && [[ "$(printf '%s\n' "$result" | jq -r '.status // empty')" == "1" ]]; then
    printf '%s\n' "$result"
    return 0
  fi

  echo "::error::Appel cPanel SshWhitelist/$func échoué (HTTP $http_code)." >&2
  # Réponse JSON avec status 0 : cPanel explique l'échec dans "errors" (ex : limite de 5 exceptions atteinte)
  local errors
  errors=$(printf '%s\n' "$raw" | jq -r 'if type == "object" then (.result // .) | .errors // [] | join(" ") else empty end' 2>/dev/null || true)
  if [[ -n "$errors" ]]; then
    echo "Erreur renvoyée par cPanel : $errors" >&2
    return 1
  fi
  if [[ "$http_code" == "401" ]]; then
    echo "Authentification refusée : vérifier CPANEL_USERNAME et le jeton CPANEL_API_TOKEN (cPanel > Sécurité > Gérer les jetons d'API)." >&2
    echo "Si la double authentification est activée sur le cPanel, elle s'applique aussi à l'API." >&2
  fi
  echo "Réponse brute (40 premières lignes) :" >&2
  printf '%s\n' "$raw" | sed -n '1,40p' >&2
  return 1
}

messages() {
  jq -r '.messages // [] | join(" ")'
}

cmd="${1:-}"
ip="${2:-}"
if [[ "$cmd" != "list" && -z "$ip" ]]; then
  usage
fi

case "$cmd" in
  list)
    result=$(api list)
    echo "Whitelisted IPs (JSON):"
    printf '%s\n' "$result" | jq '.data'
    ips=$(printf '%s\n' "$result" | jq -r '.data.list[].address' | sort -u | paste -sd ',' -)
    echo "IPs: ${ips:-(aucune)}"
    if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
      echo "ips=$ips" >> "$GITHUB_OUTPUT"
    fi
    ;;

  remove)
    result=$(api list)
    directions=$(printf '%s\n' "$result" | jq -r --arg ip "$ip" '.data.list[] | select(.address == $ip) | .direction')
    if [[ -z "$directions" ]]; then
      echo "IP $ip absente de la liste blanche, rien à supprimer."
      exit 0
    fi
    for direction in $directions; do
      msg=$(api remove "address=$ip&port=22&direction=$direction" | messages)
      echo "  [$direction] ${msg:-ok}"
    done
    ;;

  add)
    result=$(api list)
    if printf '%s\n' "$result" | jq -e --arg ip "$ip" '.data.list[] | select(.address == $ip)' >/dev/null; then
      echo "IP $ip déjà présente dans la liste blanche, ajout ignoré."
      exit 0
    fi
    msg=$(api add "address=$ip&port=22" | messages)
    echo "${msg:-ok}"
    ;;

  verify)
    result=$(api list)
    if ! printf '%s\n' "$result" | jq -e --arg ip "$ip" '.data.list[] | select(.address == $ip)' >/dev/null; then
      echo "::error::L'IP $ip n'apparaît pas dans la liste blanche cPanel." >&2
      printf '%s\n' "$result" | jq '.data' >&2
      exit 1
    fi
    echo "IP $ip is whitelisted."
    ;;

  *)
    usage
    ;;
esac
