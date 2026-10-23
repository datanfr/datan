# shellcheck shell=bash
# ---------------------------------------------------------------------------
# Fonctions partagées par bin/deployer.sh et bin/cron.sh, à sourcer.
#
# Arborescence sur le serveur (compte o2switch de datan.fr), relative à $RACINE :
#
#   depot/          miroir de l'artefact construit par le workflow (rsync)
#   releases/<id>/  une version complète, figée — copie de depot/ en liens durs
#   current ->      lien symbolique vers la version servie ; la racine web du
#                   sous-domaine est current/public
#   shared/         ce qui survit aux versions : .env.local, .htpasswd, .my.cnf,
#                   var/log, var/tricoteuses (1,8 Go de clones), les portraits
#                   régénérés par le sync, les sauvegardes, les verrous de cron
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
# exception dès que la locale n'est pas « en » (DEPLOIEMENT.md §1). Un alt-php
# appelé par son chemin complet ne charge pas forcément les extensions cochées
# dans le sélecteur PHP de cPanel : c'est ici que ça se voit.
verifier_extensions() {
  local php="$1" manquantes
  manquantes=$("$php" -r '
    $m = array_filter(["intl", "mysqli", "ctype", "iconv"], fn ($e) => !extension_loaded($e));
    echo implode(", ", $m);')
  [[ -z "$manquantes" ]] || echec "Extensions absentes de $php : $manquantes (sélecteur PHP de cPanel)."
}
