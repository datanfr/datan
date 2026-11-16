#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# MariaDB 11.4.13 de développement, installée dans le dossier personnel.
#
#   bin/mariadb-locale.sh installer    télécharge, initialise, crée la base
#   bin/mariadb-locale.sh demarrer | arreter | redemarrer | etat
#   bin/mariadb-locale.sh console [args]   client `mariadb`, compte datan
#   bin/mariadb-locale.sh admin [args]     client `mariadb`, tous les droits
#   bin/mariadb-locale.sh dump [args]      `mariadb-dump`, compte datan
#   bin/mariadb-locale.sh chemin           dossier des binaires (pour le PATH)
#
# Pourquoi la même version que la production, au correctif près : le poste a
# longtemps tourné sur un MySQL 8 système, et du SQL que MySQL acceptait est
# parti en production sans avoir jamais été exécuté sous MariaDB. Les deux
# moteurs divergent justement là où le code ne regarde pas : mode SQL, collations
# par défaut, DDL des migrations.
#
# Pourquoi l'archive binaire et pas apt : Ubuntu 24.04 ne propose que la 10.11,
# et le dépôt de MariaDB demande un sudo. Tout tient donc dans ~/.local, sans
# droit particulier :
#
#   ~/.local/opt/mariadb-11.4.13-linux-systemd-x86_64/   binaires
#   ~/.local/share/mariadb-datan/my.cnf                  configuration (réécrite
#                                                        à chaque `installer`)
#   ~/.local/share/mariadb-datan/donnees/                fichiers de la base
#   ~/.local/state/mariadb-datan/                        socket, pid, journal
#   ~/.config/systemd/user/mariadb-datan.service         démarrage automatique
#
# Pas sous var/ du dépôt : un `rm -rf var/cache` trop large ou un nouveau clone
# emporterait la base. Le script est idempotent : `installer` se relance sans
# risque, ne retélécharge pas, ne réinitialise jamais un dossier de données qui
# existe — il réécrit seulement la configuration et l'unité.
# ---------------------------------------------------------------------------
set -euo pipefail

VERSION=11.4.13
ARCHIVE="mariadb-$VERSION-linux-systemd-x86_64"
URL="https://archive.mariadb.org/mariadb-$VERSION/bintar-linux-systemd-x86_64/$ARCHIVE.tar.gz"
# Somme épinglée plutôt que relue sur le serveur qui sert l'archive : un miroir
# compromis servirait aussi une somme compromise. Elle concorde avec le
# sha256sums.txt publié et avec la signature GPG de MariaDB (clé F1656F24C74CD1D8),
# vérifiés à l'installation du 16 novembre 2026.
SHA256=3cebf63914df3154b7cb6d548337eb2be42c773aeb641110446b72e2746cfed7

PORT=3307
BASE=datan_symfony
UTILISATEUR=datan
MOT_DE_PASSE=datan

BINAIRES="$HOME/.local/opt/$ARCHIVE"
DOSSIER="$HOME/.local/share/mariadb-datan"
DONNEES="$DOSSIER/donnees"
CNF="$DOSSIER/my.cnf"
ETAT="$HOME/.local/state/mariadb-datan"
SOCKET="$ETAT/mariadb.sock"
TELECHARGEMENTS="$HOME/.cache/mariadb-datan"
UNITE=mariadb-datan.service
FICHIER_UNITE="$HOME/.config/systemd/user/$UNITE"
# Bibliothèques de compatibilité : l'archive est construite pour des systèmes
# où libaio s'appelle libaio.so.1, Ubuntu 24.04 ne livre que libaio.so.1t64.
# Un lien dans un dossier privé suffit, sans toucher au système.
COMPAT="$BINAIRES/lib-compat"

etape() { printf '→ %s\n' "$*"; }
echec() { printf '!! %s\n' "$*" >&2; exit 1; }

systemd_utilisateur() {
    # « degraded » (une unité tierce en échec) n'empêche pas de gérer les nôtres ;
    # seule l'absence de gestionnaire utilisateur compte.
    command -v systemctl >/dev/null && systemctl --user show-environment >/dev/null 2>&1
}

client() {
    local outil=$1; shift
    LD_LIBRARY_PATH="$COMPAT${LD_LIBRARY_PATH:+:$LD_LIBRARY_PATH}" \
        "$BINAIRES/bin/$outil" --defaults-file="$CNF" "$@"
}

repond() { client mariadb-admin --protocol=socket --user="$(id -un)" ping >/dev/null 2>&1; }

attendre() {
    local i
    for i in $(seq 1 60); do
        repond && return 0
        sleep 0.5
    done
    echec "MariaDB ne répond pas au bout de 30 s — journal : $ETAT/erreurs.log"
}

telecharger() {
    [[ -x "$BINAIRES/bin/mariadbd" ]] && return 0
    mkdir -p "$TELECHARGEMENTS" "$(dirname "$BINAIRES")"
    local fichier="$TELECHARGEMENTS/$ARCHIVE.tar.gz"
    if [[ ! -f "$fichier" ]] || ! echo "$SHA256  $fichier" | sha256sum -c --status; then
        etape "Téléchargement de MariaDB $VERSION (~350 Mo)"
        curl -fL --progress-bar -o "$fichier.partiel" "$URL"
        mv "$fichier.partiel" "$fichier"
    fi
    echo "$SHA256  $fichier" | sha256sum -c --status \
        || echec "Somme de contrôle fausse pour $fichier : supprimer le fichier et relancer."
    etape "Extraction dans $BINAIRES"
    local tmp
    tmp=$(mktemp -d "$(dirname "$BINAIRES")/.extraction-XXXXXX")
    tar xzf "$fichier" -C "$tmp"
    mv "$tmp/$ARCHIVE" "$BINAIRES"
    rmdir "$tmp"
}

bibliotheques() {
    mkdir -p "$COMPAT"
    if ldd "$BINAIRES/bin/mariadbd" | grep -q 'libaio.so.1 => not found'; then
        local cible
        cible=$(ldconfig -p | awk '/libaio\.so\.1t64 /{print $NF; exit}')
        [[ -n "$cible" ]] || echec "libaio introuvable : ni libaio.so.1 ni libaio.so.1t64."
        ln -sfn "$cible" "$COMPAT/libaio.so.1"
    fi
    local manque
    manque=$(LD_LIBRARY_PATH="$COMPAT" ldd "$BINAIRES/bin/mariadbd" | grep 'not found' || true)
    [[ -z "$manque" ]] || echec "Bibliothèques manquantes pour mariadbd :"$'\n'"$manque"
}

ecrire_configuration() {
    mkdir -p "$DONNEES" "$ETAT"
    # Ce qui change le résultat d'une requête suit les défauts de MariaDB 11.4,
    # ceux de la production : on ne pose NI sql_mode (défaut :
    # STRICT_TRANS_TABLES, ERROR_FOR_DIVISION_BY_ZERO, NO_AUTO_CREATE_USER,
    # NO_ENGINE_SUBSTITUTION — sans ONLY_FULL_GROUP_BY, à l'inverse de MySQL 8)
    # NI collation-server NI character_set_collations (vide en 11.4 officielle :
    # utf8mb4 y vaut utf8mb4_general_ci ; uca1400 ne devient le défaut qu'en
    # 11.5+ ou par la configuration des paquets Debian). D'où un piège identique
    # à la production : une table déclarée `CHARACTER SET utf8mb4` sans COLLATE
    # prend utf8mb4_general_ci, et non la collation de la base.
    # Seul écart volontaire : le jeu de caractères du serveur, latin1 par défaut
    # — la production a corrigé le sien au niveau de la base (DEPLOIEMENT.md,
    # « Amorçage »), ce qui revient au même pour toute table de datan_symfony.
    # Le reste ne touche qu'aux performances d'un poste à 32 Go et à un disque
    # WSL : un import de 1 Go s'y écrit plusieurs fois plus vite.
    cat >"$CNF" <<CNF
# Généré par bin/mariadb-locale.sh — modifier le script, pas ce fichier.
[client]
port = $PORT
socket = $SOCKET
default-character-set = utf8mb4

[mariadbd]
basedir = $BINAIRES
datadir = $DONNEES
socket = $SOCKET
pid-file = $ETAT/mariadb.pid
log-error = $ETAT/erreurs.log
port = $PORT
bind-address = 127.0.0.1
skip-name-resolve
character-set-server = utf8mb4
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
CNF
}

initialiser() {
    [[ -d "$DONNEES/mysql" ]] && return 0
    etape "Initialisation de $DONNEES"
    # Authentification par socket : root et l'utilisateur système courant
    # entrent sans mot de passe, et seulement depuis ce compte Unix.
    LD_LIBRARY_PATH="$COMPAT" "$BINAIRES/scripts/mariadb-install-db" \
        --defaults-file="$CNF" --basedir="$BINAIRES" --datadir="$DONNEES" \
        --auth-root-authentication-method=socket --skip-test-db >/dev/null
}

ecrire_unite() {
    mkdir -p "$(dirname "$FICHIER_UNITE")"
    # Type=notify : l'archive « systemd » signale elle-même qu'elle est prête,
    # si bien que `systemctl --user start` ne rend la main qu'une fois la base
    # joignable. WantedBy=default.target + Linger=yes : démarrée avec le poste,
    # sans session ouverte.
    cat >"$FICHIER_UNITE" <<UNIT
# Généré par bin/mariadb-locale.sh (dépôt Datan).
[Unit]
Description=MariaDB $VERSION de développement (Datan, port $PORT)

[Service]
Type=notify
Environment=LD_LIBRARY_PATH=$COMPAT
ExecStart=$BINAIRES/bin/mariadbd --defaults-file=$CNF
TimeoutStartSec=120
TimeoutStopSec=300
Restart=on-abnormal
LimitNOFILE=32768

[Install]
WantedBy=default.target
UNIT
    systemctl --user daemon-reload
    systemctl --user enable "$UNITE" >/dev/null 2>&1
}

demarrer() {
    repond && return 0
    if systemd_utilisateur && [[ -f "$FICHIER_UNITE" ]]; then
        systemctl --user start "$UNITE"
    else
        # Sans systemd utilisateur, rien ne relancera la base au démarrage du
        # poste : c'est cette commande qu'il faudra relancer à la main.
        LD_LIBRARY_PATH="$COMPAT" nohup "$BINAIRES/bin/mariadbd" --defaults-file="$CNF" \
            >>"$ETAT/erreurs.log" 2>&1 &
    fi
    attendre
}

arreter() {
    if systemd_utilisateur && [[ -f "$FICHIER_UNITE" ]] && systemctl --user is-active -q "$UNITE"; then
        systemctl --user stop "$UNITE"
    elif repond; then
        client mariadb-admin --protocol=socket --user="$(id -un)" shutdown
    fi
}

creer_base() {
    # Compte déclaré pour les deux formes d'adresse : avec skip-name-resolve, une
    # connexion TCP depuis 127.0.0.1 ne correspond plus à « localhost ».
    client mariadb --protocol=socket --user="$(id -un)" <<SQL
CREATE DATABASE IF NOT EXISTS $BASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$UTILISATEUR'@'localhost' IDENTIFIED BY '$MOT_DE_PASSE';
CREATE USER IF NOT EXISTS '$UTILISATEUR'@'127.0.0.1' IDENTIFIED BY '$MOT_DE_PASSE';
GRANT ALL PRIVILEGES ON $BASE.* TO '$UTILISATEUR'@'localhost';
GRANT ALL PRIVILEGES ON $BASE.* TO '$UTILISATEUR'@'127.0.0.1';
SQL
}

installer() {
    telecharger
    bibliotheques
    ecrire_configuration
    initialiser
    if systemd_utilisateur; then
        ecrire_unite
    else
        etape "Pas de systemd utilisateur : la base ne redémarrera pas seule avec le poste ($0 demarrer)."
    fi
    demarrer
    creer_base
    etat
}

etat() {
    if systemd_utilisateur && [[ -f "$FICHIER_UNITE" ]]; then
        printf 'unité        %s : %s, %s\n' "$UNITE" \
            "$(systemctl --user is-enabled "$UNITE" 2>/dev/null || true)" \
            "$(systemctl --user is-active "$UNITE" 2>/dev/null || true)"
    fi
    if repond; then
        client mariadb --protocol=socket --user="$(id -un)" -N -B -e "
            SELECT CONCAT('version      ', VERSION());
            SELECT CONCAT('écoute       ', @@bind_address, ':', @@port);
            SELECT CONCAT('données      ', @@datadir);
            SELECT CONCAT('collation    ', @@character_set_server, ' / ', @@collation_server);
            SELECT CONCAT('sql_mode     ', @@sql_mode);
            SELECT CONCAT('base         ', schema_name, ' (', default_collation_name, ')')
              FROM information_schema.schemata WHERE schema_name = '$BASE';"
    else
        echo "MariaDB : arrêtée (journal : $ETAT/erreurs.log)"
        return 3
    fi
}

commande=${1:-etat}
[[ $# -gt 0 ]] && shift
case "$commande" in
    installer) installer ;;
    demarrer) demarrer; etat ;;
    arreter) arreter ;;
    redemarrer) arreter; demarrer; etat ;;
    etat) etat ;;
    # Par TCP et avec le compte de l'application : on voit la base exactement
    # comme Symfony la voit.
    console) client mariadb --protocol=tcp --host=127.0.0.1 --user="$UTILISATEUR" --password="$MOT_DE_PASSE" "$BASE" "$@" ;;
    admin) client mariadb --protocol=socket --user="$(id -un)" "$@" ;;
    dump) client mariadb-dump --protocol=tcp --host=127.0.0.1 --user="$UTILISATEUR" --password="$MOT_DE_PASSE" "$@" ;;
    chemin) echo "$BINAIRES/bin" ;;
    *) echec "Commande inconnue : $commande (installer, demarrer, arreter, redemarrer, etat, console, admin, dump, chemin)" ;;
esac
