<?php

namespace App;

/**
 * Couleurs des partis politiques, portées de Parties_model::get_party_color().
 *
 * Contrairement aux groupes parlementaires, l'open data ne publie aucune couleur
 * pour les organes PARPOL : la rédaction en tient la liste à la main, et tout
 * parti qui n'y figure pas est rendu en gris. La colonne `parti.couleur` reste
 * donc inutilisée à l'affichage.
 */
final class CouleurParti
{
    /**
     * Expression SQL donnant la couleur du liseré, la table `parti` devant être
     * aliasée `p` dans la requête appelante.
     */
    public const SQL = "CASE p.libelle_abrev
                            WHEN 'LAREM' THEN '#2ABAFF'
                            WHEN 'REP'   THEN '#00407D'
                            WHEN 'MODEM' THEN '#EF5222'
                            WHEN 'PS'    THEN '#E8004B'
                            WHEN 'FI'    THEN '#C94324'
                            WHEN 'PCF'   THEN '#E50027'
                            WHEN 'EELV'  THEN '#7AB41D'
                            WHEN 'RPS'   THEN '#d5443f'
                            WHEN 'RN'    THEN '#144478'
                            WHEN 'PRG'   THEN '#F1C417'
                            WHEN 'PPM'   THEN '#E91E26'
                            WHEN 'DEBOU' THEN '#0082C4'
                            WHEN 'CAL'   THEN '#FDB813'
                            WHEN 'CAP'   THEN '#3371A3'
                            WHEN 'TPH'   THEN '#E53420'
                            WHEN 'THN'   THEN '#76C6F0'
                            WHEN 'UDRL'  THEN '#654590'
                            ELSE 'grey'
                        END";
}
