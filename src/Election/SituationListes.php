<?php

namespace App\Election;

/**
 * Ce que chaque liste du premier tour est devenue au second : maintien, fusion
 * ou désistement (`Elections_model::get_list_situations()` de l'application
 * d'origine, reprise règle pour règle).
 *
 * Le ministère ne publie pas ce devenir : il se déduit en croisant les listes
 * déposées pour le second tour avec les scores du premier. Les règles sont
 * celles du site, y compris leurs seuils, qui ne sont pas ceux du code
 * électoral :
 *
 * - **maintien** : le numéro de panneau du premier tour se retrouve au second,
 *   et la liste avait fait plus de 10 % des exprimés. Le panneau sert de clé
 *   parce que le ministère le conserve d'un tour à l'autre ;
 * - **fusion** (liste donneuse) : liste à plus de 5 %, absente du second tour,
 *   dont la tête — ou à défaut l'un quelconque de ses colistiers — figure sur
 *   une liste du second tour. La liste qui l'accueille, si elle avait fait plus
 *   de 10 %, est marquée « fusion » elle aussi (receveuse) ;
 * - **désistement** : liste à plus de 10 %, absente du second tour sans y
 *   avoir été accueillie.
 *
 * Une liste qui ne remplit aucune de ces conditions n'a pas de situation : le
 * site ne la mentionne pas.
 *
 * Calculée à l'import plutôt qu'à l'affichage : le croisement demande les
 * colistiers des deux tours — des centaines de milliers de noms — que la page
 * n'a aucune raison de relire à chaque visite.
 */
final class SituationListes
{
    /**
     * @param list<array{panneau: int, tete: string, score: float}>        $premierTour score = pourcentage des exprimés, arrondi au centième
     * @param array<int, array{tete: ?string, colistiers: list<string>}>   $listesSecondTour par panneau ; tete déjà mise en forme (« Benoît Payan »)
     * @param array<int, list<string>>                                     $colistiersPremierTour noms des colistiers du premier tour, par panneau
     *
     * @return array<int, array{situation: string, role: ?string, tete: ?string}> par panneau du premier tour
     */
    public static function calcule(array $premierTour, array $listesSecondTour, array $colistiersPremierTour): array
    {
        // Chaque colistier du second tour pointe vers sa liste ; un homonyme
        // présent sur deux listes pointe vers la dernière lue, comme sur le site.
        $listeDuColistier = [];
        foreach ($listesSecondTour as $panneau => $liste) {
            foreach ($liste['colistiers'] as $nom) {
                $cle = self::cle($nom);
                if ($cle !== '') {
                    $listeDuColistier[$cle] = $panneau;
                }
            }
        }

        $donneuses = [];
        $recues = [];

        foreach ($premierTour as $liste) {
            $cle = self::cle($liste['tete']);

            if ($liste['score'] <= 5 || isset($listesSecondTour[$liste['panneau']]) || $cle === '') {
                continue;
            }

            $receveuse = $listeDuColistier[$cle] ?? null;

            if ($receveuse === null) {
                foreach ($colistiersPremierTour[$liste['panneau']] ?? [] as $nom) {
                    $autre = self::cle($nom);
                    if ($autre !== '' && isset($listeDuColistier[$autre])) {
                        $receveuse = $listeDuColistier[$autre];
                        break;
                    }
                }
            }

            if ($receveuse === null || $receveuse === $liste['panneau']) {
                continue;
            }

            $donneuses[$liste['panneau']] = [
                'situation' => 'fusion',
                'role' => 'donneuse',
                'tete' => self::tete($listesSecondTour[$receveuse]),
            ];
            $recues[$receveuse][] = trim($liste['tete']);
        }

        $situations = [];

        foreach ($premierTour as $liste) {
            $panneau = $liste['panneau'];

            if (isset($donneuses[$panneau])) {
                $situations[$panneau] = $donneuses[$panneau];

                continue;
            }

            if ($liste['score'] <= 10) {
                continue;
            }

            if (!isset($listesSecondTour[$panneau])) {
                $situations[$panneau] = ['situation' => 'desistement', 'role' => null, 'tete' => null];

                continue;
            }

            $situations[$panneau] = isset($recues[$panneau])
                // Le site ne garde que la première donneuse pour la phrase.
                ? ['situation' => 'fusion', 'role' => 'receveuse', 'tete' => $recues[$panneau][0]]
                : ['situation' => 'maintien', 'role' => null, 'tete' => null];
        }

        return $situations;
    }

    /** Clé de comparaison des noms : espaces réduits, minuscules. */
    public static function cle(string $nom): string
    {
        return mb_strtolower((string) preg_replace('/\s+/u', ' ', trim($nom)));
    }

    /** @param array{tete: ?string, colistiers: list<string>} $liste */
    private static function tete(array $liste): string
    {
        $tete = trim((string) $liste['tete']);

        return $tete !== '' ? $tete : trim($liste['colistiers'][0] ?? '');
    }
}
