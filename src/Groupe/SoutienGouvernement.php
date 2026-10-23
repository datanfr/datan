<?php

namespace App\Groupe;

use App\Entity\Dossier;
use App\FamilleGroupe;
use App\NatureVote;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Décompte, groupe par groupe, des textes du Gouvernement votés « pour » —
 * la matière du graphique « Quels groupes soutiennent le gouvernement ? » de
 * l'accueil et du comparatif de la fiche de groupe.
 *
 * Ne comptent que les scrutins de nature finale portant sur un dossier de
 * procédure 1 ou 21 (projets de loi ordinaires et de finances rectificatives),
 * comme `daily.php` — cf. `scrutin.nature_vote` et `dossier.procedure_code`.
 *
 * La liste des groupes de la majorité est ÉDITORIALE (`groupes_majo()` du
 * legacy) : depuis la dissolution de 2024 l'Assemblée ne déclare plus de
 * `positionPolitique` (cf. CLAUDE.md), et c'est la rédaction qui trace la
 * frontière majorité/opposition de ce graphique.
 */
class SoutienGouvernement
{
    private const NON_INSCRITS = 'NI';

    private const POSITION_POUR = 'pour';

    /** Groupes du bloc gouvernemental, liste éditoriale reprise de `groupes_majo()`. */
    public const MAJORITE = ['EPR', 'DEM', 'HOR', 'DR'];

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Le décompte pour tous les groupes de la législature, trié par soutiens
     * puis effectif décroissants. Les non-inscrits en sont exclus : ils ne
     * forment pas un groupe et leur position majoritaire n'engage personne.
     *
     * Les groupes d'une même famille sont fusionnés sous celui qui est encore
     * en exercice, faute de quoi une scission ferait disparaître les votes du
     * groupe dissous.
     *
     * @return list<array<string, mixed>>
     */
    public function tousLesGroupes(int $legislature): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT g.id, g.uid, g.libelle, g.libelle_abrev, g.couleur, g.date_fin,
                    COUNT(*) AS votes,
                    SUM(vg.position_majoritaire = :pour) AS soutiens,
                    (SELECT COUNT(*) FROM depute dep WHERE dep.groupe_id = g.id) AS effectif
             FROM groupe g
             JOIN vote_groupe vg ON vg.groupe_id = g.id
             JOIN scrutin s ON s.id = vg.scrutin_id
             JOIN dossier d ON d.id = s.dossier_id
             WHERE g.legislature = :legislature AND g.libelle_abrev <> :ni
               AND s.nature_vote = :finale AND d.procedure_code IN (:procedures)
             GROUP BY g.id, g.uid, g.libelle, g.libelle_abrev, g.couleur, g.date_fin',
            [
                'legislature' => $legislature,
                'ni' => self::NON_INSCRITS,
                'pour' => self::POSITION_POUR,
                'finale' => NatureVote::FINALE,
                'procedures' => Dossier::PROCEDURES_GOUVERNEMENT,
            ],
            ['procedures' => ArrayParameterType::INTEGER],
        );

        foreach ($lignes as $i => $ligne) {
            foreach (['votes', 'soutiens', 'effectif'] as $compteur) {
                $lignes[$i][$compteur] = (int) $ligne[$compteur];
            }
        }

        $parUid = array_column($lignes, null, 'uid');

        foreach ($lignes as $ligne) {
            $famille = array_intersect(FamilleGroupe::pour($ligne['uid']), array_keys($parUid));
            if (\count($famille) < 2) {
                continue;
            }

            // Le représentant est le groupe encore en exercice ; à défaut, le premier trouvé.
            $representant = null;
            foreach ($famille as $uid) {
                if ($parUid[$uid]['date_fin'] === null) {
                    $representant = $uid;
                    break;
                }
            }
            $representant ??= reset($famille);

            foreach ($famille as $uid) {
                if ($uid !== $representant) {
                    $parUid[$representant]['soutiens'] += $parUid[$uid]['soutiens'];
                    $parUid[$representant]['votes'] += $parUid[$uid]['votes'];
                    unset($parUid[$uid]);
                }
            }
        }

        $groupes = array_values($parUid);
        usort($groupes, static fn (array $a, array $b) => [$b['soutiens'], $b['effectif']] <=> [$a['soutiens'], $a['effectif']]);

        return $groupes;
    }
}
