<?php

namespace App;

/**
 * Couleurs de groupe corrigées à l'affichage, portées de
 * Groupes_model::get_groupe_color() : deux groupes sont rendus avec une couleur
 * choisie par la rédaction plutôt qu'avec celle de l'open data, jugée trop
 * pâle pour servir de liseré ou de secteur de graphique.
 */
final class CouleurGroupe
{
    /**
     * Expression SQL à substituer à `g.couleur`, la table `groupe` devant être
     * aliasée `g` dans la requête appelante.
     */
    public const SQL = "CASE g.libelle_abrev
                            WHEN 'SOC' THEN '#e30040'
                            WHEN 'UDI_I' THEN '#5c5e8e'
                            ELSE g.couleur
                        END";
}
