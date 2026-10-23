# shellcheck shell=bash
# ---------------------------------------------------------------------------
# Fonctions partagées par bin/deployer.sh et bin/cron.sh, à sourcer.
#
# Arborescence sur le serveur (compte o2switch de datan.fr), relative à $RACINE :
#
#   depot/          miroir de l'artefact construit par le workflow (rsync)
#   releases/<id>/  une version complète, figée — copie de depot/ en liens durs
#   current ->      lien symbolique vers la version servie
#   public ->       current/public, racine web du sous-domaine (lien fixe)
#   .env.local  .my.cnf
#                   la configuration, au-dessus de la racine web donc jamais
#                   servie ; chaque version y pointe par un lien
#   shared/         les données qui survivent aux versions : var/log,
#                   var/tricoteuses (1,8 Go de clones), les portraits régénérés
#                   par le sync, les sauvegardes, les verrous de cron. Elles ne
#                   peuvent pas vivre à la racine : public/ y est un lien vers
#                   la version servie, pas un dossier.
#
# Les versions sont effacées au bout de trois : tout ce qui doit durer vit
# hors de releases/, à la racine ou dans shared/.
#
# RACINE n'est surtout pas sous ~/public_html : le legacy s'y déploie par
# `rsync --delete`, qui effacerait tout ce qu'il ne connaît pas.
# ---------------------------------------------------------------------------

etape() { printf '\n→ %s\n' "$*"; }
echec() { printf '!! %s\n' "$*" >&2; exit 1; }

# Le compte sert encore datan.fr en PHP 7.4 : le `php` du PATH n'est pas
# forcément le bon, et cron ne voit pas le même PATH qu'une session SSH. On
# cherche donc un PHP ≥ 8.4 parmi les emplacements habituels d'un cPanel —
# CloudLinux (alt-php) puis EasyApache (ea-php) —, et `PHP_BIN` impose un choix
# quand la détection se trompe. Une fois le compte passé en 8.4, le `php` du
# PATH convient et les chemins alt-php restent valides : rien à changer.
trouver_php() {
  local candidat chemin
  for candidat in "${PHP_BIN:-}" php8.4 php84 \
      /opt/alt/php84/usr/bin/php /opt/cpanel/ea-php84/root/usr/bin/php \
      /usr/local/bin/php /usr/bin/php php; do
    [[ -n "$candidat" ]] || continue
    chemin=$(command -v "$candidat" 2>/dev/null) || continue
    # Le lanceur de preparer_php n'est pas un candidat : s'y retrouver lui-même
    # écrirait un lanceur qui se relance à l'infini.
    [[ -n "${LANCEUR:-}" && "$chemin" -ef "$LANCEUR" ]] && continue
    if "$chemin" -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' 2>/dev/null; then
      printf '%s\n' "$chemin"
      return 0
    fi
    if [[ -n "${PHP_BIN:-}" && "$candidat" == "$PHP_BIN" ]]; then
      echec "PHP_BIN=$PHP_BIN n'est pas un PHP ≥ 8.4 ($("$chemin" -r 'echo PHP_VERSION;' 2>/dev/null || echo '?'))."
    fi
  done
  echec "Aucun PHP ≥ 8.4 trouvé (php du PATH : $(php -r 'echo PHP_VERSION;' 2>/dev/null || echo 'absent')).
   Symfony 8.1 l'exige. Si cPanel propose PHP 8.4, le binaire est en général
   /opt/alt/php84/usr/bin/php : le désigner par la variable GitHub PHP_BIN."
}

# `intl` et `mysqli` ne sont pas déclarées dans composer.json, donc pas
# couvertes par le contrôle de plateforme de Composer, et pourtant
# indispensables : le DSN est `mysqli://`, et le polyfill intl lève une
# exception dès que la locale n'est pas « en » (DEPLOIEMENT.md §1). `dom`,
# `simplexml` et `tokenizer` : sans elles, `cache:warmup` échoue — liste
# établie en retirant les extensions une à une d'un PHP nu (`php -n`).
EXTENSIONS_REQUISES=(intl mysqli ctype iconv dom simplexml tokenizer)

# Rend le chemin du PHP à utiliser partout — déploiement, cron, à la main :
# $RACINE/bin/php, un lanceur réécrit à chaque appel.
#
# Pourquoi un lanceur : sur o2switch, /opt/alt/php84/usr/bin/php appelé par son
# chemin ne charge pas `intl`, alors que le module est installé et que le
# sélecteur PHP le coche pour la 8.4 (constaté le 2026-10-23 : `-m` ne le
# liste pas, `-d extension=intl.so -m` si). Le lanceur ajoute ces `-d` pour
# les seules extensions manquantes. Son chemin ne change jamais, ce qui laisse
# la table cron juste : le jour où l'hébergeur les charge d'office, le
# déploiement suivant réécrit un lanceur sans option — garder un `-d` pour un
# module déjà chargé ferait sortir un avertissement à chaque commande.
preparer_php() {
  local LANCEUR="$RACINE/bin/php" php extension options=()
  php=$(trouver_php)
  for extension in "${EXTENSIONS_REQUISES[@]}"; do
    "$php" -r "exit(extension_loaded('$extension') ? 0 : 1);" && continue
    "$php" -d "extension=$extension.so" -r "exit(extension_loaded('$extension') ? 0 : 1);" 2>/dev/null \
      || echec "Extension $extension absente de $php, et introuvable parmi ses modules."
    options+=(-d "extension=$extension.so")
  done

  mkdir -p "$RACINE/bin"
  printf '#!/bin/sh\n# Écrit par bin/serveur-commun.sh (preparer_php) : ne pas modifier, chaque\n# déploiement le réécrit.\nexec %s %s"$@"\n' \
    "$php" "${options[*]:+${options[*]} }" > "$LANCEUR.nouveau"
  chmod 755 "$LANCEUR.nouveau"
  mv -f "$LANCEUR.nouveau" "$LANCEUR"

  printf '   %s (%s)%s\n' "$php" "$("$php" -r 'echo PHP_VERSION;')" "${options[*]:+ + ${options[*]}}" >&2
  printf '%s\n' "$LANCEUR"
}
