<?php

namespace App\Election;

use Doctrine\DBAL\Connection;

/**
 * Bloc « Résultats aux municipales 2026 » de la page de résultats d'une commune
 * (`Elections::results_city()` et `elections/results_city.php` de
 * l'application d'origine).
 *
 * Toute la mise en scène découle d'une seule décision, prise par le site sur
 * les chiffres du premier tour et non sur l'indicateur du ministère
 * ({@see Municipales::pourvuAuPremierTour()}) :
 *
 * - commune pourvue au premier tour : un seul tour, la phrase « La liste
 *   dirigée par … a remporté », et les sièges de chaque liste ;
 * - sinon : deux onglets, le second ouvert par défaut, la phrase « Au second
 *   tour, la liste dirigée par … est arrivée en tête », les sièges au second
 *   tour et, au premier, le devenir de chaque liste (maintien, fusion,
 *   désistement).
 */
final class ResultatsMunicipales
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<string, mixed>|null null si le ministère ne publie rien
     *                                   pour cette commune : la page garde
     *                                   alors sa forme sans municipales
     */
    public function pourCommune(string $codeInsee): ?array
    {
        // Le ministère écrit la Corse en capitales (`2A004`), le référentiel
        // en minuscules : la collation de la base les confond, et c'est ici
        // voulu.
        //
        // Ordre du site — voix décroissantes, puis panneau —, les sièges
        // départageant une égalité de voix : à Merry-la-Vallée, deux listes à
        // 109 voix, c'est celle qui a reçu la prime majoritaire (9 sièges sur
        // 11) qui « a remporté » l'élection, pas celle du premier panneau.
        // Hors égalité, les sièges suivent les voix et l'ordre est inchangé.
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT tour, numero_panneau AS panneau, voix, rapport_exprimes, code_nuance,
                    nom, prenom, sieges, situation, fusion_role, fusion_tete
             FROM municipale_resultat
             WHERE code_commune = ?
             ORDER BY tour, voix DESC, sieges DESC, numero_panneau',
            [$codeInsee],
        );
        $participations = $this->connection->fetchAllAssociativeIndexed(
            'SELECT tour, inscrits, abstentions, votants, blancs, nuls, pct_saisis, pourvu
             FROM municipale_participation
             WHERE code_commune = ?',
            [$codeInsee],
        );

        $parTour = [1 => [], 2 => []];
        foreach ($lignes as $ligne) {
            $parTour[(int) $ligne['tour']][] = $ligne;
        }

        if ($parTour[1] === [] && !isset($participations[1])) {
            return null;
        }

        $pourvu = Municipales::pourvuAuPremierTour(
            array_map(static fn (array $l) => $l['voix'] === null ? null : (int) $l['voix'], $parTour[1]),
            $participations[1]['pourvu'] ?? null,
            $parTour[2] !== [],
        );

        $tours = ['t1' => $this->tour('t1', $parTour[1], $participations[1] ?? null, $pourvu)];
        if (!$pourvu) {
            $tours['t2'] = $this->tour('t2', $parTour[2], $participations[2] ?? null, false);
        }

        $second = $tours['t2'] ?? null;
        // Second tour « officiel » : dépouillement complet. Les chiffres
        // partiels d'un soir d'élection ont leur propre mention.
        $secondPartiel = $second !== null && $second['resultats'] !== [] && $second['pct_saisis'] !== null && $second['pct_saisis'] < 100.0;
        $secondOfficiel = $second !== null && $second['resultats'] !== [] && $second['pct_saisis'] !== null && $second['pct_saisis'] === 100.0;

        $synthese = $secondPartiel || $secondOfficiel ? $tours['t2'] : $tours['t1'];

        return [
            'tours' => $tours,
            'tour_par_defaut' => $second !== null && ($second['resultats'] !== [] || $second['participation'] !== null) ? 't2' : 't1',
            'onglets' => !$pourvu,
            'pourvu' => $pourvu,
            'synthese' => $synthese,
            'en_attente' => $second !== null && !$secondOfficiel && !$secondPartiel,
            // Le tableau des situations n'apparaît qu'entre les deux tours : une
            // fois le second dépouillé, ses résultats parlent d'eux-mêmes.
            'situations' => $second !== null && $second['resultats'] === [] ? $this->tableauSituations($tours['t1']['resultats']) : [],
        ];
    }

    /**
     * Députés candidats dans la commune (`Elections_model::get_candidates_by_city()`).
     *
     * Le seul filtre est la visibilité, comme sur le site : une candidature
     * publiée vaut candidature, quel qu'en soit le sort. L'ordre est celui de
     * la table d'origine (`ordre_saisie`, cf. ImportElectionsCommand).
     *
     * @return list<array<string, mixed>>
     */
    public function deputesCandidats(string $codeInsee): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite
             FROM candidature c
             JOIN election e ON e.id = c.election_id
             JOIN depute d ON d.id = c.depute_id
             WHERE e.slug = 'municipales-2026' AND c.visible = 1 AND c.district = ?
             ORDER BY c.ordre_saisie, c.id",
            [$codeInsee],
        );
    }

    /**
     * @param list<array<string, mixed>>  $lignes
     * @param array<string, mixed>|null   $participation
     *
     * @return array<string, mixed>
     */
    private function tour(string $cle, array $lignes, ?array $participation, bool $pourvu): array
    {
        $total = array_sum(array_map(static fn (array $l) => (int) $l['voix'], $lignes));

        $resultats = array_map(static function (array $l) use ($total) {
            $score = $l['rapport_exprimes'] !== null
                ? round((float) $l['rapport_exprimes'], 2)
                : ($total > 0 ? round((int) $l['voix'] / $total * 100, 2) : 0.0);

            return [
                'nom_complet' => trim($l['prenom'] . ' ' . $l['nom']),
                'code_nuance' => $l['code_nuance'],
                'nuance' => NuanceMunicipale::libelle($l['code_nuance']),
                'couleur' => NuanceMunicipale::couleur($l['code_nuance']),
                'score' => $score,
                'barre' => max(0.0, min(100.0, $score)),
                'voix' => (int) $l['voix'],
                'sieges' => $l['sieges'] === null ? 0 : (int) $l['sieges'],
                'situation' => $l['situation'],
                'fusion_role' => $l['fusion_role'],
                'fusion_tete' => $l['fusion_tete'],
            ];
        }, $lignes);

        $infos = null;
        if ($participation !== null) {
            $inscrits = $participation['inscrits'] === null ? null : (int) $participation['inscrits'];
            $infos = [
                'inscrits' => (int) $inscrits,
                'votants' => (int) $participation['votants'],
                'abstention' => $inscrits > 0 ? round((int) $participation['abstentions'] / $inscrits * 100, 2) : 0.0,
                'blancs_nuls' => (int) $participation['blancs'] + (int) $participation['nuls'],
            ];
        }

        $pctSaisis = $participation === null || $participation['pct_saisis'] === null ? null : (float) $participation['pct_saisis'];

        return [
            'cle' => $cle,
            'titre' => $cle === 't1' ? 'Premier tour' : 'Second tour',
            // Le site bascule le second tour en « listes candidates » tant que
            // l'index n'a pas d'état de dépouillement pour la commune ; les
            // listes du second tour ne sont pas importées, la carte dit alors
            // qu'aucune n'est disponible — ce que fait le site faute de liste.
            'mode' => $cle === 't2' && ($resultats === [] || $pctSaisis === null) ? 'listes' : 'resultats',
            'resultats' => $resultats,
            'participation' => $infos,
            'pct_saisis' => $pctSaisis,
            'pourvu' => $pourvu,
        ];
    }

    /**
     * Lignes du tableau « La situation des listes entre les deux tours » : les
     * listes ayant une situation, chaque liste fusionnée rangée sous celle qui
     * l'a accueillie — reconnue au nom de sa tête, sans égard à la casse —, et
     * les orphelines en fin de tableau.
     *
     * @param list<array<string, mixed>> $resultats
     *
     * @return list<array<string, mixed>>
     */
    private function tableauSituations(array $resultats): array
    {
        $avecSituation = array_values(array_filter($resultats, static fn (array $r) => $r['situation'] !== null));

        $donneusesPar = [];
        foreach ($avecSituation as $r) {
            if ($r['fusion_role'] === 'donneuse' && trim((string) $r['fusion_tete']) !== '') {
                $donneusesPar[mb_strtolower(trim((string) $r['fusion_tete']))][] = $r + ['enfant' => true];
            }
        }

        $lignes = [];
        $placees = [];
        foreach ($avecSituation as $r) {
            if ($r['fusion_role'] === 'donneuse') {
                continue;
            }
            $lignes[] = $r + ['enfant' => false];

            foreach ($donneusesPar[mb_strtolower($r['nom_complet'])] ?? [] as $donneuse) {
                $lignes[] = $donneuse;
                $placees[mb_strtolower($donneuse['nom_complet'])] = true;
            }
        }

        foreach ($donneusesPar as $donneuses) {
            foreach ($donneuses as $donneuse) {
                if (!isset($placees[mb_strtolower($donneuse['nom_complet'])])) {
                    $lignes[] = $donneuse;
                }
            }
        }

        return $lignes;
    }
}
