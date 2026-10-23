<?php

namespace App;

/**
 * Segment central du `<title>` d'une fiche de scrutin.
 *
 * Portage à l'identique du `title_meta` de l'application d'origine, qui
 * compose « Vote n°N - {title_meta} - Le législature | Datan ». L'assemblage
 * vient du CASE SQL de `Votes_model::get_individual_vote()` (lignes 167-177)
 * et privilégie le titre du dossier, plus parlant que le libellé brut du
 * scrutin : « Vote final - Projet de loi… » plutôt que « l'ensemble du projet
 * de loi… (première lecture). ».
 *
 * Les numéros d'amendement et d'article que le CASE consomme
 * (`votes_info.amdt` et `.article`) n'ont pas d'équivalent en base : le
 * legacy les extrayait du libellé à l'import (`daily.php`, lignes 1788-1810).
 * On refait ici la même extraction à l'affichage, sur le même libellé —
 * mêmes fenêtres de caractères, mêmes cas particuliers.
 */
final class TitreMeta
{
    public static function pour(?string $titre, ?string $natureVote, ?string $dossierTitre): string
    {
        $titre = (string) $titre;

        if ($dossierTitre !== null && $dossierTitre !== '') {
            if ($natureVote === NatureVote::FINALE) {
                return 'Vote final - ' . $dossierTitre;
            }

            if ($natureVote === 'motion de renvoi en commission') {
                return 'Motion de renvoi en commission - ' . $dossierTitre;
            }

            $amendement = self::numeroAmendement($titre, $natureVote);
            if ($amendement !== '') {
                return 'Amendement n°' . $amendement . ' - ' . $dossierTitre;
            }

            if ($natureVote === 'article') {
                $article = self::numeroArticle($titre);
                if ($article !== '') {
                    return 'Article n°' . $article . ' - ' . $dossierTitre;
                }
            }

            if ($natureVote === 'motion de rejet préalable') {
                return 'Motion de rejet préalable - ' . $dossierTitre;
            }

            return $titre;
        }

        // Sans dossier rattaché, deux natures ont un libellé court dédié —
        // c'est ce qui donne « Vote n°1 - Motion de censure - 16e législature ».
        if ($natureVote === 'declaration de politique generale') {
            return 'Déclaration de politique générale';
        }

        if ($natureVote === 'motion de censure') {
            return 'Motion de censure';
        }

        return $titre;
    }

    /**
     * Numéro de l'amendement (ou du sous-amendement), extrait du libellé
     * comme `daily.php:1790-1795` : ce qui suit « n° », dont on retire les
     * mentions de rectification (« 993 (2e rect.) » ajouterait un 2 parasite),
     * puis les chiffres des 15 premiers caractères — la fenêtre est assez
     * courte pour ne pas attraper un numéro d'article cité plus loin
     * (« l'amendement n° 223 de M. Aviragnet à l'article 5 » donne bien 223).
     */
    private static function numeroAmendement(string $titre, ?string $natureVote): string
    {
        if ($natureVote !== NatureVote::AMENDEMENT && $natureVote !== NatureVote::SOUS_AMENDEMENT) {
            return '';
        }

        $suite = strstr($titre, 'n°');

        if ($suite === false) {
            return '';
        }

        $suite = str_replace(['2e rect', '2ème rect', '2éme rect'], '', $suite);

        return preg_replace('/[^0-9]/', '', substr($suite, 0, 15)) ?? '';
    }

    /**
     * Numéro de l'article, extrait comme `daily.php:1801-1810` : les articles
     * sans numéro (« premier », « unique », « liminaire ») valent 1, sinon les
     * chiffres des 20 premiers caractères — « l'article 13 ter du projet… »
     * donne 13, le « ter » restant à part comme dans la colonne `bister` du
     * legacy.
     */
    private static function numeroArticle(string $titre): string
    {
        if (str_contains($titre, 'article premier')
            || str_contains($titre, 'article unique')
            || str_contains($titre, 'article liminaire')) {
            return '1';
        }

        return preg_replace('/[^0-9]/', '', substr($titre, 0, 20)) ?? '';
    }
}
