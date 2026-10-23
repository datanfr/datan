<?php

namespace App;

/**
 * Filiation des groupes parlementaires, portée à l'identique de
 * `Groupes_model::get_history()` de l'application d'origine.
 *
 * Un groupe change de nom, se scinde ou se reconstitue d'une législature à
 * l'autre : Renaissance a été LAREM puis RE avant d'être EPR, et la droite
 * ciottiste a été À Droite, puis UDR, puis UDDPLR — au sein d'une même
 * législature. Les organes de l'Assemblée ne portent aucun lien vers le groupe
 * précédent et le sigle ne suffit pas : cette filiation est un choix éditorial,
 * qui doit donc être déclaré.
 */
final class FamilleGroupe
{
    /**
     * Chaque famille regroupe les identifiants d'organe d'un même groupe à
     * travers ses changements de nom, du plus récent au plus ancien.
     *
     * @var list<list<string>>
     */
    private const FAMILLES = [
        ['PO845407', 'PO800538', 'PO730964'], // Renaissance
        ['PO845413', 'PO730958', 'PO800490'], // France insoumise
        ['PO845425', 'PO270903', 'PO389395', 'PO656006', 'PO707869', 'PO730934', 'PO800508', 'PO684957'], // Les Républicains
        ['PO845454', 'PO730970', 'PO774834', 'PO800484'], // MoDem
        ['PO845419', 'PO758835', 'PO730946', 'PO389507', 'PO656002', 'PO713077', 'PO270907', 'PO800496', 'PO830170'], // Socialistes
        ['PO845439', 'PO656014', 'PO800526'], // Écologistes
        ['PO845514', 'PO270915', 'PO389635', 'PO656018', 'PO730940', 'PO800502'], // Communistes
        ['PO845485', 'PO759900', 'PO800532'], // LIOT
        ['PO793087', 'PO723569', 'PO645633', 'PO387155', 'PO266900'], // Non-inscrits
        ['PO845470', 'PO800514'], // Horizons
        ['PO845401', 'PO800520'], // Rassemblement national
        ['PO845520', 'PO847173', 'PO872880'], // À Droite / UDR / UDDPLR
    ];

    /**
     * Les organes apparentés au groupe donné, lui compris. Un groupe sans
     * filiation déclarée est seul dans sa famille.
     *
     * @return list<string>
     */
    public static function pour(string $uid): array
    {
        foreach (self::FAMILLES as $famille) {
            if (\in_array($uid, $famille, true)) {
                return $famille;
            }
        }

        return [$uid];
    }
}
