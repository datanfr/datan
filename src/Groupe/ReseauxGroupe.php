<?php

namespace App\Groupe;

/**
 * Liens officiels d'un groupe parlementaire — site, Facebook, X, Wikipédia —
 * transcrits du switch codé en dur de
 * `Groupes_model::get_groupe_social_media()` (lignes 321-427 du legacy).
 *
 * Comme les textes d'{@see EditoGroupe}, rien ici ne vient de l'open data :
 * c'est une table tenue par la rédaction, rattachée au sigle, et elle se
 * reprend telle quelle — adresses comprises, même `http://` sans TLS pour le
 * site du groupe communiste.
 *
 * Le bloc « En savoir plus » du pied de fiche n'affiche que le site, Facebook
 * et X ; le lien Wikipédia ne sert au legacy que dans le JSON-LD des fiches de
 * députés, et il est gardé ici pour le jour où ce balisage sera porté. Le
 * legacy l'écrit sous la clé fautive `wikpedia` pour les seuls MODEM/DEM — une
 * coquille, pas un choix : elle est corrigée, sans effet visible puisque le
 * bloc ne rend pas ce lien.
 */
final class ReseauxGroupe
{
    /**
     * Par sigle : `site` (adresse complète), `facebook` (nom de page),
     * `x` (compte, sans arobase), `wikipedia` (adresse complète).
     */
    private const LIENS = [
        'DR' => self::DROITE_REPUBLICAINE,
        'LR' => self::DROITE_REPUBLICAINE,
        'MODEM' => self::DEMOCRATES,
        'DEM' => self::DEMOCRATES,
        'SOC' => self::SOCIALISTES,
        'SOC-A' => self::SOCIALISTES,
        'AGIR-E' => [
            'x' => 'AgirEnsemble_AN',
            'facebook' => 'AgirEnsembleAN',
            'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_Agir_ensemble',
        ],
        'UDI_I' => [
            'site' => 'https://www.parti-udi.fr/',
            'x' => 'deputesudi_ind',
            'facebook' => 'DeputesUDI.Ind',
            'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_UDI_et_ind%C3%A9pendants',
        ],
        'FI' => self::INSOUMIS,
        'LFI-NUPES' => self::INSOUMIS,
        'LFI' => self::INSOUMIS,
        'LFI-NFP' => self::INSOUMIS,
        'EDS' => [
            'site' => 'https://www.ecologie-democratie-solidarite.fr/',
            'x' => 'EDSAssNat',
            'facebook' => 'EDSAssNat',
            'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_%C3%89cologie_d%C3%A9mocratie_solidarit%C3%A9',
        ],
        'GDR' => self::COMMUNISTES,
        'GDR-NUPES' => self::COMMUNISTES,
        'LT' => self::LIBERTES_TERRITOIRES,
        'LIOT' => self::LIBERTES_TERRITOIRES,
        'RE' => self::RENAISSANCE,
        'LAREM' => self::RENAISSANCE,
        'EPR' => self::RENAISSANCE,
        'RN' => [
            'site' => 'https://deputes-rn.fr/',
            'x' => 'groupeRN_off',
            'facebook' => 'deputesRN',
            'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_Rassemblement_national',
        ],
        'HOR' => [
            'site' => 'https://horizonsleparti.fr/',
            'x' => 'Horizons_AN',
            'facebook' => 'lesdeputeshorizonsetindep',
            'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_Horizons_et_ind%C3%A9pendants',
        ],
        'ECOS' => self::ECOLOGISTES,
        'ECOLO' => self::ECOLOGISTES,
    ];

    private const DROITE_REPUBLICAINE = [
        'site' => 'https://www.deputes-les-republicains.fr/',
        'x' => 'droiterep_an',
        'facebook' => 'droiterepublicaine.an',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_Droite_r%C3%A9publicaine',
    ];

    private const DEMOCRATES = [
        'site' => 'https://www.mouvementdemocrate.fr/',
        'x' => 'DeputesDem',
        'facebook' => 'DeputesDem',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_Les_D%C3%A9mocrates',
    ];

    private const SOCIALISTES = [
        'site' => 'https://www.parti-socialiste.fr/',
        'x' => 'socialistesAN',
        'facebook' => 'socialistesAN',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_socialiste_(Assembl%C3%A9e_nationale)',
    ];

    private const INSOUMIS = [
        'site' => 'https://lafranceinsoumise.fr/',
        'x' => 'FiAssemblee',
        'facebook' => 'FiAssemblee',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_La_France_insoumise',
    ];

    private const COMMUNISTES = [
        'site' => 'http://www.groupe-communiste.assemblee-nationale.fr/',
        'facebook' => 'LesDeputesCommunistes',
        'x' => 'deputesPCF',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_communiste_(Assembl%C3%A9e_nationale)',
    ];

    private const LIBERTES_TERRITOIRES = [
        'x' => 'GroupeLIOT_An',
        'facebook' => 'Groupe-Libertés-et-Territoires-à-lAssemblée-nationale-1898196496883591',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_Libert%C3%A9s,_ind%C3%A9pendants,_outre-mer_et_territoires',
    ];

    private const RENAISSANCE = [
        'x' => 'DeputesRE',
        'facebook' => 'deputesRenaissance',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_Ensemble_pour_la_R%C3%A9publique',
    ];

    private const ECOLOGISTES = [
        'site' => 'https://www.ecologistes-an.fr/',
        'x' => 'Gpe_EcoloSocial',
        'facebook' => 'eelv.fr',
        'wikipedia' => 'https://fr.wikipedia.org/wiki/Groupe_%C3%A9cologiste_(Assembl%C3%A9e_nationale)',
    ];

    /**
     * Les liens connus pour un sigle, vide pour les groupes sans entrée —
     * le bloc « En savoir plus » disparaît alors, comme sur le site.
     *
     * @return array{site?: string, facebook?: string, x?: string, wikipedia?: string}
     */
    public static function liens(string $sigle): array
    {
        return self::LIENS[$sigle] ?? [];
    }
}
