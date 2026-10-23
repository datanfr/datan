<?php

namespace App\Repository;

use App\Entity\ResultatCirconscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Résultat électoral d'une circonscription, pour le bloc « Son élection » de la
 * fiche d'un député. DBAL et tableaux associatifs, aucun ORM.
 *
 * Un seul dépôt pour trois tables — résultats généraux, participation et
 * partielles — parce que la règle qui les combine est une seule et même règle,
 * portée telle quelle du legacy (`Deputes_model` + `Depute_service`) :
 *
 * 1. On cherche d'abord une **élection partielle** gagnée par ce député dans la
 *    fenêtre de sa législature. Si elle existe, elle **remplace** l'élection
 *    générale : ni participation, ni résultat général ne sont lus.
 * 2. Sinon, on lit l'élection **générale** — et seulement si le député en est
 *    bien l'élu ; un suppléant qui a pris le relais sans partielle n'a pas de
 *    bloc, comme sur datan.fr.
 * 3. Élu au premier tour, seuls les deux premiers concurrents sont détaillés,
 *    le reste fondu dans « Autres candidats ».
 *
 * @extends ServiceEntityRepository<ResultatCirconscription>
 */
class ResultatCirconscriptionRepository extends ServiceEntityRepository
{
    /** Législature → année de l'élection générale correspondante. */
    private const ANNEE = [15 => 2017, 16 => 2022, 17 => 2024];

    /**
     * Fenêtres de législature, pour rattacher une partielle à la bonne.
     *
     * Borne basse = second tour de l'élection générale ; borne haute = veille du
     * scrutin suivant, `null` pour la législature en cours (→ aujourd'hui). Une
     * même circonscription peut connaître une partielle à chaque législature :
     * c'est la date qui tranche, pas l'année seule.
     */
    private const DEBUT = [15 => '2017-06-18', 16 => '2022-06-19', 17 => '2024-07-07'];
    private const FIN = [15 => '2022-06-18', 16 => '2024-07-06', 17 => null];

    /**
     * Participation nationale par tour, en points, codée en dur comme dans le
     * legacy — la source ne la porte pas par circonscription.
     */
    private const PARTICIPATION_NATIONALE = [
        17 => [1 => 68, 2 => 67],
        16 => [1 => 48, 2 => 46],
        15 => [1 => 49, 2 => 43],
    ];

    /** Résultats officiels du ministère, par législature. */
    private const ARCHIVES = [
        17 => 'https://www.archives-resultats-elections.interieur.gouv.fr/resultats/legislatives2024/',
        16 => 'https://www.archives-resultats-elections.interieur.gouv.fr/resultats/legislatives-2022/index.php',
        15 => 'https://www.archives-resultats-elections.interieur.gouv.fr/resultats/legislatives-2017/index.php',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultatCirconscription::class);
    }

    /**
     * Le bloc « Son élection » d'un député, ou `null` s'il n'y a rien à montrer
     * (avant la 15e législature, circonscription inconnue, ou député qui n'est
     * pas l'élu de sa circonscription).
     *
     * @return array{
     *     partielle: bool, annee: int, tour: int, tour_libelle: string,
     *     date_partielle: ?string, elu: array{candidat: ?string, voix: int, part: float},
     *     adversaires: list<array{candidat: ?string, voix: int, part: float}>,
     *     participation: ?array{taux: int, nationale: ?int}, lien: ?string
     * }|null
     */
    public function pourDepute(?string $codeDepartement, ?int $circonscription, int $legislature, string $nom): ?array
    {
        if ($codeDepartement === null || $circonscription === null || !isset(self::ANNEE[$legislature])) {
            return null;
        }

        $cx = $this->getEntityManager()->getConnection();
        $annee = self::ANNEE[$legislature];
        $comme = '%' . $nom . '%';

        // 1. Partielle d'abord. Le nom (`nameLast`) confirme que c'est bien ce
        //    député qui a gagné la partielle, et non un autre élu de la même
        //    circonscription à une autre date.
        $partielle = $cx->fetchAssociative(
            'SELECT tour, candidat, voix, part_exprimes, date_scrutin
             FROM partielle_legislative
             WHERE code_departement = :dpt AND circonscription = :circo AND elu = 1
               AND nom LIKE :nom AND date_scrutin BETWEEN :debut AND :fin
             ORDER BY date_scrutin DESC LIMIT 1',
            [
                'dpt' => $codeDepartement,
                'circo' => $circonscription,
                'nom' => $comme,
                'debut' => self::DEBUT[$legislature],
                'fin' => self::FIN[$legislature] ?? date('Y-m-d'),
            ],
        );

        if ($partielle !== false) {
            $tour = (int) $partielle['tour'];

            // Adversaires de CETTE partielle (même date). Le legacy filtre sur la
            // fenêtre entière de la législature ; on resserre sur la date exacte,
            // car mêler les candidats de deux partielles d'une même
            // circonscription serait un défaut, pas un choix.
            $adversaires = $cx->fetchAllAssociative(
                'SELECT candidat, voix, part_exprimes
                 FROM partielle_legislative
                 WHERE code_departement = :dpt AND circonscription = :circo AND tour = :tour
                   AND elu = 0 AND date_scrutin = :date
                 ORDER BY voix DESC',
                ['dpt' => $codeDepartement, 'circo' => $circonscription, 'tour' => $tour, 'date' => $partielle['date_scrutin']],
            );

            return [
                'partielle' => true,
                'annee' => (int) substr((string) $partielle['date_scrutin'], 0, 4),
                'tour' => $tour,
                'tour_libelle' => $tour === 2 ? '2nd' : '1er',
                'date_partielle' => $this->enFrancais((string) $partielle['date_scrutin']),
                'elu' => $this->candidat($partielle),
                'adversaires' => $this->adversaires($adversaires, $tour),
                // Une partielle n'a pas de ligne de participation dans la source :
                // le legacy n'affiche alors aucun taux.
                'participation' => null,
                'lien' => self::ARCHIVES[$legislature] ?? null,
            ];
        }

        // 2. Élection générale. `nom` en 2024 (colonnes éclatées), `candidat` en
        //    2017/2022 (nom complet dans un seul champ) : l'un ou l'autre suffit.
        $elu = $cx->fetchAssociative(
            'SELECT tour, candidat, voix, part_exprimes
             FROM resultat_circonscription
             WHERE code_departement = :dpt AND circonscription = :circo AND annee = :annee AND elu = 1
               AND (nom LIKE :nom OR candidat LIKE :nom)
             ORDER BY tour DESC LIMIT 1',
            ['dpt' => $codeDepartement, 'circo' => $circonscription, 'annee' => $annee, 'nom' => $comme],
        );

        if ($elu === false) {
            return null;
        }

        $tour = (int) $elu['tour'];

        $adversaires = $cx->fetchAllAssociative(
            'SELECT candidat, voix, part_exprimes
             FROM resultat_circonscription
             WHERE code_departement = :dpt AND circonscription = :circo
               AND annee = :annee AND tour = :tour AND elu = 0
             ORDER BY voix DESC',
            ['dpt' => $codeDepartement, 'circo' => $circonscription, 'annee' => $annee, 'tour' => $tour],
        );

        return [
            'partielle' => false,
            'annee' => $annee,
            'tour' => $tour,
            'tour_libelle' => $tour === 2 ? '2nd' : '1er',
            'date_partielle' => null,
            'elu' => $this->candidat($elu),
            'adversaires' => $this->adversaires($adversaires, $tour),
            'participation' => $this->participation($codeDepartement, $circonscription, $annee, $tour, $legislature),
            'lien' => self::ARCHIVES[$legislature] ?? null,
        ];
    }

    /**
     * Taux de participation local, comparé à la moyenne nationale.
     *
     * @return array{taux: int, nationale: ?int}|null
     */
    private function participation(string $codeDepartement, int $circonscription, int $annee, int $tour, int $legislature): ?array
    {
        $infos = $this->getEntityManager()->getConnection()->fetchAssociative(
            'SELECT inscrits, votants
             FROM participation_circonscription
             WHERE code_departement = :dpt AND circonscription = :circo AND annee = :annee AND tour = :tour
             LIMIT 1',
            ['dpt' => $codeDepartement, 'circo' => $circonscription, 'annee' => $annee, 'tour' => $tour],
        );

        if ($infos === false || (int) $infos['inscrits'] === 0) {
            return null;
        }

        return [
            'taux' => (int) round((int) $infos['votants'] * 100 / (int) $infos['inscrits']),
            'nationale' => self::PARTICIPATION_NATIONALE[$legislature][$tour] ?? null,
        ];
    }

    /**
     * Les concurrents de l'élu, triés du plus au moins voté.
     *
     * Élu au premier tour, le legacy ne détaille que les deux premiers et fond le
     * reste dans « Autres candidats », dont les voix et la part sont sommées.
     *
     * @param list<array<string, mixed>> $lignes
     *
     * @return list<array{candidat: ?string, voix: int, part: float}>
     */
    private function adversaires(array $lignes, int $tour): array
    {
        $adversaires = array_map($this->candidat(...), $lignes);

        if ($tour === 1 && \count($adversaires) > 2) {
            $tetes = \array_slice($adversaires, 0, 2);
            $reste = \array_slice($adversaires, 2);

            $tetes[] = [
                'candidat' => 'Autres candidats',
                'voix' => (int) array_sum(array_column($reste, 'voix')),
                'part' => (float) array_sum(array_column($reste, 'part')),
            ];

            return $tetes;
        }

        return $adversaires;
    }

    /**
     * @param array<string, mixed> $ligne
     *
     * @return array{candidat: ?string, voix: int, part: float}
     */
    private function candidat(array $ligne): array
    {
        return [
            'candidat' => $ligne['candidat'],
            'voix' => (int) $ligne['voix'],
            'part' => (float) $ligne['part_exprimes'],
        ];
    }

    /** « 2025-05-25 » → « mai 2025 », pour la phrase d'une élection partielle. */
    private function enFrancais(string $date): string
    {
        return (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMMM y'))
            ->format(new \DateTimeImmutable($date));
    }
}
