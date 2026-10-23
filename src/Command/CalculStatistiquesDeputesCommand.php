<?php

namespace App\Command;

use App\Legislature;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Précalcule les statistiques de comportement des députés qui alimentent les
 * cartes « Son comportement politique » de la fiche : participation aux scrutins
 * solennels, proximité (loyauté) avec le groupe, proximité avec la majorité
 * gouvernementale, et proximité avec chaque groupe.
 *
 * L'application d'origine tient ces tables (`class_participation_solennels`,
 * `class_loyaute`, `class_majorite`, `deputes_accord_cleaned`) à jour chaque nuit
 * dans `daily.php`,
 * parce que ces cartes comparent le député à la MOYENNE de tous les députés et de
 * son groupe — moyennes qu'on ne peut pas rejouer sur 1,27 M de votes à chaque
 * affichage. On fait de même : cette commande remplit `statistique_depute` et
 * `accord_groupe`, la fiche s'y lit en DBAL.
 *
 * **Hors `app:sync:quotidien`, mais à relancer après chaque import de votes** :
 * un nouveau scrutin décale participation, loyauté et proximité. Comme les autres
 * précalculs, on la lance sciemment (l'agrégation d'accord prend ~15 s).
 *
 * La commande traite une législature à la fois (`--legislature`, la courante par
 * défaut) et ne réécrit que ses lignes : les votes nominatifs des législatures
 * 14 à 16 (dépôts `Scrutins_XIV/XV/XVI_nettoye`) s'importent une fois pour
 * toutes, leurs statistiques se calculent de même — seule la 17e bouge encore.
 */
#[AsCommand(
    name: 'app:calcul:statistiques-deputes',
    description: 'Précalcule participation, loyauté et proximité par groupe pour la fiche député.',
)]
class CalculStatistiquesDeputesCommand extends Command
{
    /** Code des scrutins publics solennels, sur lesquels se mesure la participation. */
    private const SOLENNEL = 'SPS';

    /** Seul type de vote qui compte une position (les autres sont des mises au point). */
    private const OFFICIEL = 'decompteNominatif';

    /** Position d'un député qui n'avait pas le droit de voter : ce n'est pas une absence. */
    private const NON_VOTANT = 'nonVotant';

    /** Qualification d'un groupe soutenant le Gouvernement (`organes.positionPolitique`). */
    private const MAJORITAIRE = 'Majoritaire';

    /** Borne haute conventionnelle d'un rattachement ou d'un mandat encore ouvert. */
    private const SANS_FIN = '9999-12-31';

    /**
     * Le rattachement à un groupe qui fait foi pour un député : le plus récent —
     * encore ouvert d'abord, puis la date de fin la plus tardive
     * (`daily.php:914`) —, et principal, car onze députés de la 17e en portent
     * deux ouverts à la fois.
     *
     * Passer par cette table plutôt que par `depute.groupe_id` n'est pas un
     * raffinement : `groupe_id` ne porte que l'appartenance **courante**, et un
     * député sorti de l'Assemblée n'en a plus aucune. Soixante-huit des
     * 645 votants de la 17e — ministres nommés, démissionnaires, suppléés —
     * perdaient ainsi toute leur loyauté, et leur fiche la carte « Proximité
     * avec son groupe » que datan.fr leur affiche.
     */
    private const RATTACHEMENT = "(SELECT depute_id, groupe_id, date_debut, date_fin
                                   FROM (SELECT fg.depute_id, fg.groupe_id, fg.date_debut,
                                                COALESCE(fg.date_fin, '" . self::SANS_FIN . "') AS date_fin,
                                                ROW_NUMBER() OVER (
                                                    PARTITION BY fg.depute_id
                                                    ORDER BY COALESCE(fg.date_fin, '" . self::SANS_FIN . "') DESC,
                                                             fg.date_debut DESC,
                                                             fg.groupe_id ASC) AS n
                                         FROM fonction_groupe fg
                                         JOIN groupe g ON g.id = fg.groupe_id AND g.legislature = :leg
                                         WHERE fg.nomin_principale = 1) classes
                                   WHERE n = 1)";

    private const LOT = 500;

    /** @var array<int, list<int>> Groupes majoritaires par législature, résolus une fois. */
    private array $majoritaires = [];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'legislature',
            null,
            InputOption::VALUE_REQUIRED,
            'Législature à calculer',
            (string) Legislature::COURANTE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $legislature = (int) $input->getOption('legislature');
        $io->title(sprintf('Précalcul des statistiques de comportement des députés — %de législature', $legislature));

        // Seules les lignes de la législature calculée se réécrivent : celles
        // des législatures closes, calculées une fois pour toutes, survivent au
        // recalcul quotidien de la courante.
        $this->connection->executeStatement(
            'DELETE FROM statistique_depute WHERE legislature = :leg',
            ['leg' => $legislature],
        );
        $this->connection->executeStatement(
            'DELETE FROM accord_groupe WHERE legislature = :leg',
            ['leg' => $legislature],
        );

        $io->text('Participation, loyauté et proximité avec la majorité…');
        $lignes = $this->statistiquesDeputes($legislature);
        $this->inserer(
            'statistique_depute (depute_id, legislature, participation_score, participation_votes, loyaute_score, loyaute_votes, majorite_score, majorite_votes, actif, groupe_id)',
            '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $lignes,
        );
        $io->text(sprintf('  %d députés.', \count($lignes)));

        if ($this->groupesMajoritaires($legislature) === []) {
            // Dit à voix haute, sinon une colonne entièrement nulle passe pour
            // un import raté : c'est la 17e qui n'a pas de majorité déclarée.
            $io->text('  Aucun groupe majoritaire déclaré : proximité avec la majorité laissée vide.');
        }

        $io->text('Proximité par groupe (agrégation sur tous les votes)…');
        $accords = $this->accordsParGroupe($legislature);
        $this->inserer(
            'accord_groupe (depute_id, groupe_id, legislature, accord, votes_n)',
            '(?, ?, ?, ?, ?)',
            $accords,
        );
        $io->text(sprintf('  %d couples député × groupe.', \count($accords)));

        $io->success('Statistiques précalculées.');

        return Command::SUCCESS;
    }

    /**
     * Une ligne `statistique_depute` par député ayant voté sous la législature.
     *
     * Participation et loyauté suivent les mêmes règles que
     * {@see \App\Command\CalculClassementsCommand} — sans quoi la fiche d'un
     * député annoncerait un taux et le classement des députés un autre :
     *
     * - la participation rapporte les votes exprimés aux scrutins solennels
     *   tenus pendant le **mandat** du député, non pendant l'intervalle de ses
     *   votes connus, et retire du dénominateur les scrutins où il était
     *   non-votant (présidence de séance, Gouvernement), qui ne sont pas des
     *   absences ;
     * - la loyauté se mesure contre le groupe où le député siégeait, lu dans
     *   `fonction_groupe` et borné aux dates de ce rattachement.
     *
     * S'y ajoute la proximité avec la majorité gouvernementale (`class_majorite`,
     * daily.php:2534), qui ne dépend d'aucun rattachement : c'est le taux d'accord
     * du député avec le groupe majoritaire de la législature.
     *
     * @return list<list<int|null>>
     */
    private function statistiquesDeputes(int $legislature): array
    {
        // Exprimés et non-votants solennels par député. Le filtre de législature
        // n'est pas décoratif : `vote` porte aussi les nominatifs de deux scrutins
        // de la 16e (les « positions importantes », importés ponctuellement) — sans
        // lui, un réélu compterait les solennels d'une autre législature.
        $comptes = $this->connection->fetchAllAssociative(
            "SELECT v.depute_id,
                    SUM(s.code_type_vote = :sps AND v.vote_type = :officiel
                        AND v.position IN ('pour','contre','abstention')) AS exprimes,
                    SUM(s.code_type_vote = :sps AND v.vote_type = :officiel
                        AND v.position = :nonvotant) AS retires
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id AND s.legislature = :leg
             GROUP BY v.depute_id",
            [
                'sps' => self::SOLENNEL,
                'officiel' => self::OFFICIEL,
                'nonvotant' => self::NON_VOTANT,
                'leg' => $legislature,
            ],
        );

        // Dénominateur : scrutins solennels tenus pendant que le député siégeait.
        // Les bornes de ses **rattachements de groupe**, et non l'intervalle de
        // ses votes : l'absent d'un bout à l'autre doit compter comme absent, or
        // une absence ne laisse aucune ligne dans `vote`. Et non plus les bornes
        // de son mandat, dont l'Assemblée ne garde que la dernière version —
        // {@see \App\Command\CalculClassementsCommand::fenetresDeSession()}
        // détaille pourquoi, et les deux commandes doivent compter pareil : la
        // fiche et le classement annoncent le même taux.
        $totaux = $this->compteSolennels($legislature);

        $loyautes = $this->connection->fetchAllAssociative(
            "SELECT v.depute_id,
                    COUNT(*) AS total,
                    SUM(v.position = vg.position_majoritaire) AS conformes
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id AND s.legislature = :leg
             JOIN " . self::RATTACHEMENT . " r ON r.depute_id = v.depute_id
             JOIN vote_groupe vg ON vg.scrutin_id = v.scrutin_id AND vg.groupe_id = r.groupe_id
             WHERE v.vote_type = :officiel AND v.position IN ('pour','contre','abstention')
               AND DATE(v.scrutin_date) BETWEEN r.date_debut AND r.date_fin
             GROUP BY v.depute_id",
            ['officiel' => self::OFFICIEL, 'leg' => $legislature],
        );
        $loyauteParDepute = [];
        foreach ($loyautes as $l) {
            $loyauteParDepute[(int) $l['depute_id']] = $l;
        }

        $majoriteParDepute = $this->proximiteMajorite($legislature);

        $actifs = array_flip(array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT DISTINCT depute_id FROM mandat WHERE legislature = :leg AND date_fin IS NULL',
            ['leg' => $legislature],
        )));

        // Le groupe du député pour CETTE législature, posé sur la ligne : la
        // moyenne de groupe d'une fiche ne peut pas se lire sur
        // `depute.groupe_id`, qui ne porte que l'appartenance courante — sur
        // une législature close, tous les groupes y sont éteints et la moyenne
        // sortait vide.
        $rattachements = $this->connection->fetchAllKeyValue(
            'SELECT depute_id, groupe_id FROM ' . self::RATTACHEMENT . ' r',
            ['leg' => $legislature],
        );

        $lignes = [];
        foreach ($comptes as $f) {
            $deputeId = (int) $f['depute_id'];
            $total = (int) ($totaux[$deputeId] ?? 0) - (int) $f['retires'];
            $exprimes = (int) $f['exprimes'];
            $participationScore = $total > 0 ? (int) round($exprimes / $total * 100) : null;

            $loyauteTotal = 0;
            $loyauteScore = null;
            if (isset($loyauteParDepute[$deputeId])) {
                $loyauteTotal = (int) $loyauteParDepute[$deputeId]['total'];
                $loyauteScore = $loyauteTotal > 0
                    ? (int) round((int) $loyauteParDepute[$deputeId]['conformes'] / $loyauteTotal * 100)
                    : null;
            }

            $majorite = $majoriteParDepute[$deputeId] ?? ['score' => null, 'votes' => 0];

            $lignes[] = [
                $deputeId,
                $legislature,
                $participationScore,
                $total,
                $loyauteScore,
                $loyauteTotal,
                $majorite['score'],
                $majorite['votes'],
                isset($actifs[$deputeId]) ? 1 : 0,
                isset($rattachements[$deputeId]) ? (int) $rattachements[$deputeId] : null,
            ];
        }

        return $lignes;
    }

    /**
     * Proximité de chaque député avec la majorité gouvernementale : part de ses
     * votes exprimés où il rejoint la position majoritaire du groupe majoritaire
     * (`class_majorite`, sur le `scoreGvt` de `votes_scores`, daily.php:1931).
     *
     * Deux traits de ce calcul le distinguent de la proximité par groupe, et il
     * faut les garder pour retomber sur les chiffres du site :
     *
     * - le dénominateur est l'ensemble des votes exprimés du député sous la
     *   législature, y compris les scrutins où la majorité n'a pas de position.
     *   Le `CASE … WHEN vote = gvtPosition THEN 1 ELSE 0` de l'origine bascule
     *   sur son `ELSE` quand la comparaison vaut NULL : un scrutin sans position
     *   de la majorité compte comme un désaccord, pas comme une non-comparaison.
     *   La jointure reste donc un `LEFT JOIN`, et `SUM(…)` — qui ignore les NULL
     *   — les compte à zéro sans les retirer du `COUNT(*)`. (En pratique, chacune
     *   des trois législatures concernées a une ventilation de sa majorité sur
     *   chacun de ses scrutins ; la garde vaut pour ce qui viendra.)
     * - la majorité n'est pas un groupe fixe sur toute la législature : la 14e en
     *   déclare deux qui se succèdent (SRC jusqu'au 24 mai 2016, puis SER). D'où
     *   le `IN` sur tous les groupes majoritaires de la législature, comme le
     *   `gvt.organeRef IN (…)` de l'origine — leurs mandats ne se chevauchant
     *   pas, chaque scrutin n'en trouve qu'un.
     *
     * @return array<int, array{score: int|null, votes: int}>
     */
    private function proximiteMajorite(int $legislature): array
    {
        $majoritaires = $this->groupesMajoritaires($legislature);

        if ($majoritaires === []) {
            return [];
        }

        $lignes = $this->connection->fetchAllAssociative(
            "SELECT v.depute_id,
                    COUNT(*) AS total,
                    SUM(v.position = vg.position_majoritaire) AS conformes
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id AND s.legislature = :leg
             LEFT JOIN vote_groupe vg ON vg.scrutin_id = v.scrutin_id AND vg.groupe_id IN (:majoritaires)
             WHERE v.vote_type = :officiel AND v.position IN ('pour','contre','abstention')
             GROUP BY v.depute_id",
            ['leg' => $legislature, 'officiel' => self::OFFICIEL, 'majoritaires' => $majoritaires],
            ['majoritaires' => ArrayParameterType::INTEGER],
        );

        $parDepute = [];
        foreach ($lignes as $ligne) {
            $total = (int) $ligne['total'];
            $parDepute[(int) $ligne['depute_id']] = [
                'score' => $total > 0 ? (int) round((int) $ligne['conformes'] / $total * 100) : null,
                'votes' => $total,
            ];
        }

        return $parDepute;
    }

    /**
     * Groupes que la législature déclare majoritaires (`positionPolitique`).
     *
     * Vide pour la 17e : depuis la dissolution de 2024, l'Assemblée ne publie
     * plus cette qualification, et rien ne la remplace — surtout pas un groupe
     * choisi au jugé (cf. CLAUDE.md). La carte de la fiche disparaît alors,
     * comme sur datan.fr, qui l'écarte explicitement pour cette législature.
     *
     * @return list<int>
     */
    private function groupesMajoritaires(int $legislature): array
    {
        return $this->majoritaires[$legislature] ??= array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT id FROM groupe WHERE legislature = :leg AND position_politique = :majoritaire',
            ['leg' => $legislature, 'majoritaire' => self::MAJORITAIRE],
        ));
    }

    /**
     * Nombre de scrutins solennels tenus pendant que chaque député siégeait.
     *
     * Une seule requête ramène les périodes de présence et les dates de
     * scrutin ; le croisement se fait en mémoire, sur des listes triées, plutôt
     * qu'en SQL — 577 députés contre 72 dates, la jointure ne vaut pas le
     * détour.
     *
     * @return array<int, int>
     */
    private function compteSolennels(int $legislature): array
    {
        $dates = $this->connection->fetchFirstColumn(
            'SELECT DATE(date_scrutin) FROM scrutin
             WHERE legislature = :leg AND code_type_vote = :sps
             ORDER BY date_scrutin',
            ['leg' => $legislature, 'sps' => self::SOLENNEL],
        );

        // Tous les rattachements, principaux ou non : on réunit des présences,
        // on ne choisit pas un groupe. Ceux d'un président de groupe ne portent
        // pas le même drapeau que ceux d'un simple membre.
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT fg.depute_id, fg.date_debut AS debut,
                    COALESCE(fg.date_fin, :sansFin) AS fin
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id AND g.legislature = :leg
             WHERE fg.date_debut IS NOT NULL
             ORDER BY fg.depute_id, fg.date_debut',
            ['leg' => $legislature, 'sansFin' => self::SANS_FIN],
        );

        $totaux = [];

        foreach ($lignes as $ligne) {
            $deputeId = (int) $ligne['depute_id'];
            $totaux[$deputeId] ??= [];

            foreach ($dates as $rang => $date) {
                if ($date >= $ligne['debut'] && $date <= $ligne['fin']) {
                    // Indexé par le rang du scrutin, jamais par sa date : deux
                    // rattachements consécutifs se touchent et le scrutin de la
                    // charnière ne doit compter qu'une fois, mais **plusieurs
                    // scrutins solennels se tiennent le même jour** — les 72 de
                    // la 17e ne couvrent que 50 journées. Dédupliquer sur la
                    // date en effaçait 22 et donnait 144 % à qui avait tout voté.
                    $totaux[$deputeId][$rang] = true;
                }
            }
        }

        return array_map('\count', $totaux);
    }

    /**
     * Proximité de chaque député avec chaque groupe : part des scrutins où sa
     * position rejoint la position majoritaire du groupe (`deputes_accord`).
     * `votes_n` compte les scrutins comparables ; la fiche écartera les couples
     * sous onze votes.
     *
     * @return list<list<int>>
     */
    private function accordsParGroupe(int $legislature): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            "SELECT v.depute_id, vg.groupe_id,
                    COUNT(*) AS votes_n,
                    ROUND(AVG(v.position = vg.position_majoritaire) * 100) AS accord
             FROM vote v
             JOIN vote_groupe vg ON vg.scrutin_id = v.scrutin_id
             JOIN scrutin s ON s.id = v.scrutin_id AND s.legislature = :leg
             WHERE v.vote_type = :officiel AND v.position IN ('pour','contre','abstention')
               AND vg.position_majoritaire IS NOT NULL
             GROUP BY v.depute_id, vg.groupe_id",
            ['leg' => $legislature, 'officiel' => self::OFFICIEL],
        );

        return array_map(
            fn (array $l) => [(int) $l['depute_id'], (int) $l['groupe_id'], $legislature, (int) $l['accord'], (int) $l['votes_n']],
            $lignes,
        );
    }

    /**
     * Insertion par lots : un seul INSERT multi-lignes tous les 500 tuples.
     *
     * @param list<list<int|null>> $lignes
     */
    private function inserer(string $cible, string $gabarit, array $lignes): void
    {
        foreach (array_chunk($lignes, self::LOT) as $lot) {
            $this->connection->executeStatement(
                'INSERT INTO ' . $cible . ' VALUES ' . implode(', ', array_fill(0, \count($lot), $gabarit)),
                array_merge(...$lot),
            );
        }
    }
}
