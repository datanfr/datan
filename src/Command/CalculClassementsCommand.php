<?php

namespace App\Command;

use App\Enum\TypeClassement;
use App\FamilleSocioPro;
use App\Groupe\ParticipationGroupe;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Remplit la table `classement` : les palmarès affichés sous /statistiques.
 *
 * Équivalent des méthodes `classParticipationSolennels()`, `classLoyaute()`,
 * `classGroups()` et `groupeStats()` de `scripts/daily.php` dans l'application
 * d'origine, qui tournaient chaque nuit. Rien n'est calculé à l'affichage :
 * classer les 577 députés à la participation suppose d'agréger le million de
 * lignes de `vote`, ce qu'aucune page ne peut se permettre.
 *
 * Les définitions métier ne sont pas réinventées : la participation et la
 * loyauté sont celles de {@see \App\Controller\DeputeController}, la cohésion
 * et la féminisation celles de {@see \App\Controller\GroupeController}.
 *
 * À lancer en `APP_ENV=prod` : en dev, le logger Doctrine journalise chaque
 * requête et fait exploser la mémoire sur les agrégats de `vote`.
 */
#[AsCommand(
    name: 'app:calcul:classements',
    description: 'Recalcule les classements des députés et des groupes (/statistiques).',
)]
class CalculClassementsCommand extends Command
{
    private const BATCH_SIZE = 500;

    /** Décompte officiel du scrutin, par opposition aux mises au point postérieures. */
    private const OFFICIEL = 'decompteNominatif';

    /** Scrutins publics solennels — ceux sur lesquels le site juge l'assiduité. */
    private const SOLENNEL = 'SPS';

    /**
     * Motions de censure : exclues des taux de participation « tous scrutins »
     * par l'application d'origine, seuls leurs signataires prenant part au vote.
     */
    private const MOTION_CENSURE = 'MOC';

    /** Positions comptées comme un vote exprimé. */
    private const EXPRIMES = "('pour', 'contre', 'abstention')";

    /**
     * Position d'un député qui n'avait pas le droit de prendre part au scrutin
     * — présidence de séance, membre du Gouvernement. Ce n'est pas une absence.
     */
    private const NON_VOTANT = 'nonVotant';

    /** Borne haute conventionnelle d'un mandat encore ouvert. */
    private const SANS_FIN = '9999-12-31';

    /**
     * Les non-inscrits ne forment pas un groupe : l'application d'origine les
     * écarte des classements qui décrivent une composition (âge, féminisation,
     * représentativité sociale), mais les garde dans ceux qui décrivent un
     * comportement de vote (cohésion, participation).
     */
    private const NON_INSCRITS = 'NI';

    /**
     * Prédicat « ce député siège encore », à substituer de l'alias de `depute`
     * et à accompagner d'un paramètre `:legislature`.
     *
     * Le seul rattachement à un groupe de la législature ne suffit pas : la
     * table `depute` garde les groupes des députés partis en cours de mandat,
     * et les compter gonflerait tous les effectifs. `depute.date_fin` ne
     * tranche pas davantage, n'étant pas rafraîchie aux réélections. Le statut
     * se lit sur les mandats, comme sur la page d'un député : un mandat sans
     * date de fin, c'est un siège encore occupé.
     */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = %s.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    /** Indice d'accord d'une ligne de ventilation, identique à celui des pages de groupe. */
    private const COHESION_SQL = '(GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)
            - 0.5 * ((vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions)
                     - GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)))
           / NULLIF(vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions, 0)';

    private \DateTimeImmutable $aujourdhui;

    /** @var array<int, string>|null Sigles des groupes, pour départager les ex æquo. */
    private ?array $sigles = null;

    /** @var array<int, int>|null Effectif courant de chaque groupe. */
    private ?array $effectifs = null;

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
        $this->aujourdhui = new \DateTimeImmutable('today');
    }

    protected function configure(): void
    {
        $this->addOption(
            'legislature',
            null,
            InputOption::VALUE_REQUIRED,
            'Législature à classer',
            (string) Legislature::COURANTE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $legislature = (int) $input->getOption('legislature');

        $io->title(sprintf('Calcul des classements — %de législature', $legislature));

        $depart = microtime(true);
        $lignes = [];

        foreach ($this->participationDeputes($legislature) as $type => $classement) {
            $lignes[$type] = $classement;
        }

        [$commissionDeputes, $commissionGroupes] = $this->participationCommission($legislature);
        $lignes[TypeClassement::DeputesParticipationCommission->value] = $commissionDeputes;
        $lignes[TypeClassement::GroupesParticipationCommission->value] = $commissionGroupes;

        $lignes[TypeClassement::DeputesLoyaute->value] = $this->loyauteDeputes($legislature);
        $lignes[TypeClassement::DeputesAge->value] = $this->ageDeputes($legislature);
        $lignes[TypeClassement::GroupesCohesion->value] = $this->cohesionGroupes($legislature);
        $lignes[TypeClassement::GroupesParticipation->value] = $this->participationGroupes($legislature, true);
        $lignes[TypeClassement::GroupesParticipationTous->value] = $this->participationGroupes($legislature, false);
        $lignes[TypeClassement::GroupesAge->value] = $this->ageGroupes($legislature);
        $lignes[TypeClassement::GroupesFeminisation->value] = $this->feminisationGroupes($legislature);
        $lignes[TypeClassement::GroupesOrigineSociale->value] = $this->origineSocialeGroupes($legislature);

        $this->connection->beginTransaction();

        try {
            foreach ($lignes as $type => $classement) {
                $this->remplace(TypeClassement::from($type), $legislature, $classement);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            $io->error(sprintf('Calcul interrompu, aucun classement modifié : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $io->table(
            ['Classement', 'Lignes'],
            array_map(static fn (string $t, array $c) => [$t, \count($c)], array_keys($lignes), $lignes),
        );

        $io->success(sprintf(
            '%d lignes de classement écrites en %.1f s.',
            array_sum(array_map('count', $lignes)),
            microtime(true) - $depart,
        ));

        return Command::SUCCESS;
    }

    /**
     * Taux de participation des députés, aux scrutins solennels puis à tous les
     * scrutins de la législature.
     *
     * Deux règles, toutes deux reprises de `daily.php:2306-2318`, et toutes deux
     * indispensables au chiffre affiché :
     *
     * 1. Le dénominateur compte TOUS les scrutins tenus pendant que le député
     *    siégeait, et non les seuls scrutins où il a une ligne de vote : une
     *    absence complète ne laisse aucune trace dans `vote`, et l'ignorer
     *    donnerait 100 % à un député pourtant absent. La période est celle du
     *    **mandat** (`mandat.date_prise_fonction` → `date_fin`), et non le
     *    premier et le dernier vote connus : un suppléant entré en cours de
     *    législature prend ses fonctions bien avant son premier vote, et le
     *    borner à ses votes le crédite d'une assiduité qu'il n'a pas eue.
     *
     * 2. Un scrutin où le député est **non-votant** sort du dénominateur au lieu
     *    d'y compter comme une absence. Ce n'en est pas une : présider la séance
     *    ou appartenir au Gouvernement interdit de prendre part au vote. Sans
     *    cette règle la présidente de l'Assemblée, non-votante sur 49 des 72
     *    scrutins solennels, sortait 577e à 32 % quand le site la donne à 100 %,
     *    et tout le bas du classement était décalé d'autant.
     *
     * L'application d'origine double la seconde règle d'une exception nommée —
     * `mpId = "PA721908" AND dateScrutin > "2022-06-22"`, soit Yaël Braun-Pivet
     * depuis son élection à la présidence. Elle n'est pas reprise : c'est un
     * identifiant en dur, qui vieillit le jour où l'Assemblée change de
     * président, et notre source n'en a pas besoin. Les scrutins où la
     * présidente ne vote pas nous arrivent en `nonVotant` — 72 lignes pour 72
     * solennels, aucun trou —, là où la base d'origine n'avait pour eux aucune
     * ligne du tout et devait donc nommer l'intéressée. Le cas général couvre le
     * cas particulier ; si la source cessait de publier ces lignes, le symptôme
     * serait un taux qui s'effondre, pas un silence.
     *
     * Un seul balayage de `vote` sert les deux classements ; les dénominateurs
     * se déduisent ensuite des dates de scrutin, tenues en mémoire.
     *
     * @return array<string, list<array{id: int, score: float, numerateur: int, denominateur: int, tri: array<int|string>}>>
     */
    private function participationDeputes(int $legislature): array
    {
        $dates = [
            TypeClassement::DeputesParticipation->value => $this->datesScrutins($legislature, self::SOLENNEL),
            TypeClassement::DeputesParticipationTous->value => $this->datesScrutins($legislature, null),
        ];

        $comptes = $this->connection->fetchAllAssociative(
            'SELECT v.depute_id,
                    SUM(s.code_type_vote = :solennel
                        AND v.position IN ' . self::EXPRIMES . ') AS exprimes_solennels,
                    SUM(s.code_type_vote = :solennel
                        AND v.position = :non_votant) AS retires_solennels,
                    SUM((s.code_type_vote IS NULL OR s.code_type_vote <> :motion)
                        AND v.position IN ' . self::EXPRIMES . ') AS exprimes_tous,
                    SUM((s.code_type_vote IS NULL OR s.code_type_vote <> :motion)
                        AND v.position = :non_votant) AS retires_tous
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             WHERE v.vote_type = :type AND s.legislature = :legislature
             GROUP BY v.depute_id',
            [
                'solennel' => self::SOLENNEL,
                'motion' => self::MOTION_CENSURE,
                'non_votant' => self::NON_VOTANT,
                'type' => self::OFFICIEL,
                'legislature' => $legislature,
            ],
        );

        $parDepute = [];
        foreach ($comptes as $ligne) {
            $parDepute[(int) $ligne['depute_id']] = $ligne;
        }

        $deputes = $this->deputesEnExercice($legislature);
        $fenetres = $this->fenetresDeSession($legislature, $this->bornesDeVote($legislature));
        $classements = [];

        foreach ([
            TypeClassement::DeputesParticipation->value => 'solennels',
            TypeClassement::DeputesParticipationTous->value => 'tous',
        ] as $type => $suffixe) {
            $classement = [];

            // On part des députés, non des votants : celui qui n'a jamais voté
            // n'a aucune ligne dans `vote` et doit tout de même être classé — à
            // zéro, ce qui est précisément l'information recherchée.
            foreach ($deputes as $deputeId => $nom) {
                $tenus = $this->compteFenetres($dates[$type], $fenetres[$deputeId] ?? []);
                $ligne = $parDepute[$deputeId] ?? null;

                $exprimes = (int) ($ligne['exprimes_' . $suffixe] ?? 0);
                $denominateur = $tenus - (int) ($ligne['retires_' . $suffixe] ?? 0);

                if ($denominateur <= 0) {
                    continue;
                }

                $classement[] = [
                    'id' => $deputeId,
                    'score' => $exprimes / $denominateur,
                    'numerateur' => $exprimes,
                    'denominateur' => $denominateur,
                    'tri' => [-$denominateur, $nom],
                ];
            }

            $classements[$type] = $classement;
        }

        return $classements;
    }

    /**
     * Le score « Votes par spécialisation » : participation d'un député aux
     * scrutins portant sur des textes examinés dans sa commission permanente
     * (`class_participation_commission` du legacy, daily.php:2357 et 2448).
     *
     * Un scrutin compte pour un député si son dossier porte une commission au
     * fond (`dossier.commission_fond`, acte AN1-COM-FOND) et si le député était
     * membre de cette commission ce jour-là — la qualité « Membre » seule, comme
     * le `codeQualite = "Membre"` d'origine : un président ou un rapporteur
     * spécial porte une autre qualité et sort du calcul. Les motions de censure
     * restent écartées, comme du score général.
     *
     * Trois règles héritées du score général, non négociables :
     * - le dénominateur compte les scrutins TENUS, pas les scrutins votés : il
     *   se borne par l'adhésion à la commission croisée avec les périodes de
     *   présence de {@see fenetresDeSession} (fonction_groupe, jamais mandat) ;
     * - un scrutin où le député est non-votant sort du dénominateur ;
     * - le pourcentage affiché se refait sur numérateur et dénominateur entiers,
     *   le score stocké n'étant qu'un DECIMAL(8,3).
     *
     * Le second classement retourné est celui des groupes : la moyenne des
     * scores de leurs membres (`groupeStats`, daily.php:2645), chaque score
     * étant préalablement arrondi à deux décimales — c'est la précision de
     * `class_participation_commission.score`, et l'arrondi entre dans la
     * moyenne. Les membres sont rattachés à leur groupe le plus récent de la
     * législature, députés partis compris : le legacy moyenne `deputes_all`
     * sans filtre d'activité, et les ministres sortis du Palais Bourbon pèsent
     * dans la moyenne de leur ancien groupe.
     *
     * Trois écarts assumés face à la page vivante de datan.fr (04/08/2026,
     * têtes de classement et scores identiques à ±1 point par ailleurs) :
     * - nos « nombre de votes » dépassent les siens de ~3 % : sa table
     *   `votes_participation_commission` est incrémentale et jamais revisitée
     *   (`voteNumero > dernier traité`), un scrutin dont le dossier ou la
     *   commission n'arrive qu'après coup lui échappe pour toujours — notre
     *   recalcul complet le voit ;
     * - son tableau liste 584 lignes pour 577 sièges : neuf réélus y figurent
     *   deux fois, `get_mps_participation_commission` joignant
     *   `class_participation_commission` sans filtrer sa législature. Défaut,
     *   pas choix : corrigé ;
     * - la présidente de l'Assemblée apparaît chez nous (une poignée de votes
     *   à 100 %) et pas chez lui — c'est l'exception nommée `PA721908`,
     *   délibérément non reprise (cf. {@see participationDeputes}).
     *
     * @return array{0: list<array{id: int, score: float, numerateur: int, denominateur: int, tri: array<int|string>}>,
     *               1: list<array{id: int, score: float, numerateur: null, denominateur: int, tri: array<int|string>}>}
     */
    private function participationCommission(int $legislature): array
    {
        // Dates (triées) des scrutins éligibles, par commission au fond.
        $scrutins = $this->connection->fetchAllAssociative(
            'SELECT c.id AS commission_id, DATE(s.date_scrutin) AS jour
             FROM scrutin s
             JOIN dossier dos ON dos.id = s.dossier_id
             JOIN commission c ON c.uid = dos.commission_fond
             WHERE s.legislature = :legislature
               AND (s.code_type_vote IS NULL OR s.code_type_vote <> :motion)
             ORDER BY c.id, s.date_scrutin',
            ['legislature' => $legislature, 'motion' => self::MOTION_CENSURE],
        );

        if ($scrutins === []) {
            return [[], []];
        }

        $datesParCommission = [];
        foreach ($scrutins as $ligne) {
            $datesParCommission[(int) $ligne['commission_id']][] = $ligne['jour'];
        }

        // Adhésions « Membre » aux commissions permanentes, par député puis par
        // commission — un député en cumule plusieurs par législature, l'Assemblée
        // fermant et rouvrant le mandat à chaque remplacement.
        $adhesions = $this->connection->fetchAllAssociative(
            'SELECT fc.depute_id, fc.commission_id, fc.date_debut,
                    COALESCE(fc.date_fin, :sansFin) AS date_fin
             FROM fonction_commission fc
             WHERE fc.legislature = :legislature AND fc.code_qualite = :membre
               AND fc.date_debut IS NOT NULL
             ORDER BY fc.depute_id, fc.commission_id, fc.date_debut',
            [
                'legislature' => $legislature,
                'membre' => 'Membre',
                'sansFin' => self::SANS_FIN,
            ],
        );

        $parDeputeEtCommission = [];
        foreach ($adhesions as $ligne) {
            $parDeputeEtCommission[(int) $ligne['depute_id']][(int) $ligne['commission_id']][] =
                [$ligne['date_debut'], $ligne['date_fin']];
        }

        $fenetres = $this->fenetresDeSession($legislature, $this->bornesDeVote($legislature));

        // Scrutins tenus « dans sa commission » pour chaque député : l'adhésion
        // croisée avec sa présence à l'Assemblée. Un scrutin appartient à une
        // seule commission au fond, les comptes s'additionnent donc sans doublon.
        $tenus = [];
        foreach ($parDeputeEtCommission as $deputeId => $commissions) {
            $total = 0;

            foreach ($commissions as $commissionId => $intervalles) {
                $dates = $datesParCommission[$commissionId] ?? [];
                if ($dates === []) {
                    continue;
                }

                foreach ($this->intersecte($this->fusionne($intervalles), $fenetres[$deputeId] ?? []) as [$debut, $fin]) {
                    $total += $this->borne($dates, $fin, true) - $this->borne($dates, $debut, false);
                }
            }

            if ($total > 0) {
                $tenus[$deputeId] = $total;
            }
        }

        // Votes exprimés et retraits (non-votants) sur ces mêmes scrutins. Le
        // GROUP BY intermédiaire dédouble ce que la jointure sur les adhésions
        // multiplierait si deux mandats de commission se chevauchaient.
        $comptes = $this->connection->fetchAllAssociative(
            'SELECT t.depute_id,
                    SUM(t.exprime) AS exprimes,
                    SUM(t.retire) AS retires
             FROM (SELECT v.depute_id, v.scrutin_id,
                          MAX(v.position IN ' . self::EXPRIMES . ') AS exprime,
                          MAX(v.position = :non_votant) AS retire
                   FROM vote v
                   JOIN scrutin s ON s.id = v.scrutin_id
                   JOIN dossier dos ON dos.id = s.dossier_id
                   JOIN commission c ON c.uid = dos.commission_fond
                   JOIN fonction_commission fc ON fc.commission_id = c.id
                        AND fc.depute_id = v.depute_id
                        AND fc.legislature = :legislature
                        AND fc.code_qualite = :membre
                        AND fc.date_debut IS NOT NULL
                        AND fc.date_debut <= DATE(v.scrutin_date)
                        AND (fc.date_fin IS NULL OR fc.date_fin >= DATE(v.scrutin_date))
                   WHERE v.vote_type = :type AND s.legislature = :legislature
                     AND (s.code_type_vote IS NULL OR s.code_type_vote <> :motion)
                   GROUP BY v.depute_id, v.scrutin_id) t
             GROUP BY t.depute_id',
            [
                'legislature' => $legislature,
                'membre' => 'Membre',
                'type' => self::OFFICIEL,
                'motion' => self::MOTION_CENSURE,
                'non_votant' => self::NON_VOTANT,
            ],
        );

        $parDepute = [];
        foreach ($comptes as $ligne) {
            $parDepute[(int) $ligne['depute_id']] = $ligne;
        }

        // Score de chaque député ayant eu au moins un scrutin à sa portée —
        // y compris les députés partis, dont les groupes ont besoin.
        $scores = [];
        foreach ($tenus as $deputeId => $total) {
            $ligne = $parDepute[$deputeId] ?? null;
            $denominateur = $total - (int) ($ligne['retires'] ?? 0);

            if ($denominateur <= 0) {
                continue;
            }

            $scores[$deputeId] = [
                'numerateur' => (int) ($ligne['exprimes'] ?? 0),
                'denominateur' => $denominateur,
            ];
        }

        $deputes = $this->deputesEnExercice($legislature);
        $classementDeputes = [];

        foreach ($scores as $deputeId => $score) {
            if (!isset($deputes[$deputeId])) {
                continue;
            }

            $classementDeputes[] = [
                'id' => $deputeId,
                'score' => $score['numerateur'] / $score['denominateur'],
                'numerateur' => $score['numerateur'],
                'denominateur' => $score['denominateur'],
                'tri' => [-$score['denominateur'], $deputes[$deputeId]],
            ];
        }

        return [$classementDeputes, $this->groupesParticipationCommission($legislature, $scores)];
    }

    /**
     * Moyenne par groupe des scores « Votes par spécialisation » de ses membres,
     * rattachés par leur fonction de groupe la plus récente de la législature —
     * la règle du site (encore ouvert d'abord, puis date de fin la plus tardive),
     * qui garde les députés partis dans la moyenne de leur dernier groupe.
     *
     * @param array<int, array{numerateur: int, denominateur: int}> $scores
     *
     * @return list<array{id: int, score: float, numerateur: null, denominateur: int, tri: array<int|string>}>
     */
    private function groupesParticipationCommission(int $legislature, array $scores): array
    {
        if ($scores === []) {
            return [];
        }

        $rattachements = $this->connection->fetchAllKeyValue(
            'SELECT depute_id, groupe_id FROM (
                SELECT fg.depute_id, fg.groupe_id,
                       ROW_NUMBER() OVER (
                           PARTITION BY fg.depute_id
                           ORDER BY (fg.date_fin IS NOT NULL), fg.date_fin DESC, fg.date_debut DESC
                       ) AS rang
                FROM fonction_groupe fg
                JOIN groupe g ON g.id = fg.groupe_id AND g.legislature = :legislature
                WHERE fg.nomin_principale = 1
             ) classes WHERE rang = 1',
            ['legislature' => $legislature],
        );

        $sommes = [];
        foreach ($scores as $deputeId => $score) {
            $groupeId = $rattachements[$deputeId] ?? null;
            if ($groupeId === null) {
                continue;
            }

            $sommes[(int) $groupeId] ??= ['scores' => 0.0, 'votes' => 0, 'n' => 0];
            // L'arrondi à deux décimales AVANT la moyenne n'est pas cosmétique :
            // c'est la précision du score stocké par le legacy, et elle entre
            // dans la moyenne du groupe.
            $sommes[(int) $groupeId]['scores'] += round($score['numerateur'] / $score['denominateur'], 2);
            $sommes[(int) $groupeId]['votes'] += $score['denominateur'];
            ++$sommes[(int) $groupeId]['n'];
        }

        // Seuls les groupes encore constitués font une ligne, comme les autres
        // classements de groupes (le site calcule pour tous mais n'affiche que
        // `active = 1`). Un député parti dont le dernier groupe est dissous —
        // UDR avant son renommage en UDDPLR — pèse chez le site dans la ligne
        // du groupe dissous, jamais affichée : l'écarter revient au même.
        $actifs = array_flip(array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT id FROM groupe WHERE legislature = :legislature AND date_fin IS NULL',
            ['legislature' => $legislature],
        )));

        $agreges = [];
        foreach ($sommes as $groupeId => $somme) {
            if (!isset($actifs[$groupeId])) {
                continue;
            }

            $agreges[] = [
                'id' => $groupeId,
                'moyenne' => $somme['scores'] / $somme['n'],
                // Le « nombre de votes » du groupe est la moyenne de ceux de ses
                // membres, arrondie — le `round(avg(votesN))` d'origine.
                'votes' => (int) round($somme['votes'] / $somme['n']),
            ];
        }

        return $this->classementGroupes($agreges, static fn (array $l) => [
            'score' => (float) $l['moyenne'],
            'numerateur' => null,
            'denominateur' => (int) $l['votes'],
        ]);
    }

    /**
     * Croise deux listes d'intervalles de dates : les périodes couvertes par
     * l'une ET par l'autre. Sert à borner une adhésion de commission par les
     * périodes de présence du député — être membre d'une commission pendant une
     * charge ministérielle ne fait pas assister aux scrutins.
     *
     * @param list<array{0: string, 1: string}> $a triés par début
     * @param list<array{0: string, 1: string}> $b triés par début
     *
     * @return list<array{0: string, 1: string}>
     */
    private function intersecte(array $a, array $b): array
    {
        $croises = [];

        foreach ($a as [$debutA, $finA]) {
            foreach ($b as [$debutB, $finB]) {
                $debut = max($debutA, $debutB);
                $fin = min($finA, $finB);

                if ($debut <= $fin) {
                    $croises[] = [$debut, $fin];
                }
            }
        }

        return $croises;
    }

    /**
     * Part des votes exprimés conformes à la position majoritaire du groupe,
     * relevée scrutin par scrutin dans la ventilation historique `vote_groupe`.
     *
     * Seuls comptent les votes émis depuis que le député siège dans le groupe
     * où il se trouve aujourd'hui. La page d'un député, faute d'historique des
     * rattachements au moment où elle a été écrite, confronte encore tous ses
     * votes à son groupe actuel ; `fonction_groupe` permet désormais de dater
     * son arrivée, et c'est ainsi que procédait l'application d'origine, qui
     * tenait un score par mandat de groupe. Sans cette borne, un député passé
     * du RN aux non-inscrits voit ses années de votes RN comparées à la ligne
     * des non-inscrits, et son taux s'effondre sans raison.
     *
     * @return list<array{id: int, score: float, numerateur: int, denominateur: int, tri: array<int|string>}>
     */
    private function loyauteDeputes(int $legislature): array
    {
        $deputes = $this->deputesEnExercice($legislature);

        $rows = $this->connection->fetchAllAssociative(
            'SELECT v.depute_id,
                    COUNT(*) AS total,
                    SUM(v.position = vg.position_majoritaire) AS conformes
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             JOIN depute d ON d.id = v.depute_id
             JOIN vote_groupe vg ON vg.scrutin_id = v.scrutin_id AND vg.groupe_id = d.groupe_id
             JOIN (SELECT depute_id, groupe_id, MAX(date_debut) AS depuis
                   FROM fonction_groupe
                   WHERE date_fin IS NULL
                   GROUP BY depute_id, groupe_id) f
               ON f.depute_id = v.depute_id AND f.groupe_id = d.groupe_id
             WHERE v.vote_type = :type AND s.legislature = :legislature
               AND v.position IN ' . self::EXPRIMES . '
               AND v.scrutin_date >= f.depuis
             GROUP BY v.depute_id',
            ['type' => self::OFFICIEL, 'legislature' => $legislature],
        );

        $classement = [];

        foreach ($rows as $ligne) {
            $deputeId = (int) $ligne['depute_id'];
            $total = (int) $ligne['total'];

            if (!isset($deputes[$deputeId]) || $total === 0) {
                continue;
            }

            $conformes = (int) $ligne['conformes'];

            $classement[] = [
                'id' => $deputeId,
                'score' => $conformes / $total,
                'numerateur' => $conformes,
                'denominateur' => $total,
                'tri' => [-$total, $deputes[$deputeId]],
            ];
        }

        return $classement;
    }

    /**
     * Âge des députés en années révolues, du plus âgé au plus jeune.
     *
     * L'âge se recalcule à partir de la date de naissance plutôt que de lire
     * `depute.age`, figé à la date de son import : deux députés du même âge se
     * départagent ainsi à la date près, comme sur le site d'origine.
     *
     * @return list<array{id: int, score: float, numerateur: null, denominateur: null, tri: array<int|string>}>
     */
    private function ageDeputes(int $legislature): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT d.id, ps.date_naissance
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND ps.date_naissance IS NOT NULL
               AND ' . sprintf(self::EN_EXERCICE, 'd'),
            ['legislature' => $legislature],
        );

        $classement = [];

        foreach ($rows as $ligne) {
            $naissance = new \DateTimeImmutable($ligne['date_naissance']);

            $classement[] = [
                'id' => (int) $ligne['id'],
                'score' => (float) $naissance->diff($this->aujourdhui)->y,
                'numerateur' => null,
                'denominateur' => null,
                // À âge égal, l'aîné passe devant : le tri secondaire est la date de naissance.
                'tri' => [$naissance->format('Y-m-d')],
            ];
        }

        return $classement;
    }

    /**
     * Cohésion moyenne des groupes : l'indice d'accord de leurs ventilations de
     * scrutin, moyenné sur toute la législature.
     *
     * @return list<array{id: int, score: float, numerateur: null, denominateur: int, tri: array<int|string>}>
     */
    private function cohesionGroupes(int $legislature): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT g.id,
                    AVG(' . self::COHESION_SQL . ') AS cohesion,
                    COUNT(*) AS scrutins
             FROM vote_groupe vg
             JOIN groupe g ON g.id = vg.groupe_id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL
             GROUP BY g.id
             HAVING cohesion IS NOT NULL',
            ['legislature' => $legislature],
        );

        return $this->classementGroupes($rows, static fn (array $l) => [
            'score' => (float) $l['cohesion'],
            'numerateur' => null,
            'denominateur' => (int) $l['scrutins'],
        ]);
    }

    /**
     * Taux de participation moyen des groupes : par scrutin, la part des membres
     * qui se sont exprimés. Les non-votants — présidence de séance, membres du
     * Gouvernement — sortent du dénominateur, n'ayant pas le droit de voter
     * (cf. ParticipationGroupe).
     *
     * @return list<array{id: int, score: float, numerateur: null, denominateur: int, tri: array<int|string>}>
     */
    private function participationGroupes(int $legislature, bool $solennelsSeuls): array
    {
        $filtre = $solennelsSeuls
            ? 's.code_type_vote = :solennel'
            : '(s.code_type_vote IS NULL OR s.code_type_vote <> :motion)';

        $rows = $this->connection->fetchAllAssociative(
            'SELECT g.id,
                    AVG(' . ParticipationGroupe::SQL . ') AS taux,
                    COUNT(*) AS scrutins
             FROM vote_groupe vg
             JOIN groupe g ON g.id = vg.groupe_id
             JOIN scrutin s ON s.id = vg.scrutin_id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL AND ' . $filtre . '
             GROUP BY g.id
             HAVING taux IS NOT NULL',
            $solennelsSeuls
                ? ['legislature' => $legislature, 'solennel' => self::SOLENNEL]
                : ['legislature' => $legislature, 'motion' => self::MOTION_CENSURE],
        );

        return $this->classementGroupes($rows, static fn (array $l) => [
            'score' => (float) $l['taux'],
            'numerateur' => null,
            'denominateur' => (int) $l['scrutins'],
        ]);
    }

    /**
     * @return list<array{id: int, score: float, numerateur: null, denominateur: int, tri: array<int|string>}>
     */
    private function ageGroupes(int $legislature): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT g.id,
                    AVG(TIMESTAMPDIFF(YEAR, ps.date_naissance, :aujourdhui)) AS age,
                    COUNT(*) AS effectif
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL AND g.libelle_abrev <> :ni
               AND ps.date_naissance IS NOT NULL AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             GROUP BY g.id',
            [
                'legislature' => $legislature,
                'ni' => self::NON_INSCRITS,
                'aujourdhui' => $this->aujourdhui->format('Y-m-d'),
            ],
        );

        return $this->classementGroupes($rows, static fn (array $l) => [
            'score' => (float) $l['age'],
            'numerateur' => null,
            'denominateur' => (int) $l['effectif'],
        ]);
    }

    /**
     * @return list<array{id: int, score: float, numerateur: int, denominateur: int, tri: array<int|string>}>
     */
    private function feminisationGroupes(int $legislature): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT g.id,
                    COUNT(*) AS effectif,
                    SUM(d.civilite = :mme) AS femmes
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL AND g.libelle_abrev <> :ni
               AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             GROUP BY g.id',
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS, 'mme' => 'Mme'],
        );

        return $this->classementGroupes($rows, static fn (array $l) => [
            'score' => (int) $l['femmes'] / (int) $l['effectif'],
            'numerateur' => (int) $l['femmes'],
            'denominateur' => (int) $l['effectif'],
        ]);
    }

    /**
     * Indice de Rose : à quel point la composition sociale d'un groupe reflète
     * celle du pays. Un groupe parfaitement représentatif vaut 1, un groupe qui
     * n'a aucun point commun avec la population vaut 0.
     *
     * Formule de l'application d'origine : 1 − ½ Σ |part dans la population −
     * part dans le groupe|, sur les huit familles socio-professionnelles de
     * l'INSEE. Les députés dont la profession n'est pas déclarée sortent du
     * calcul faute de pouvoir être rangés.
     *
     * Les deux parts sont arrondies au millième **avant** d'être soustraites,
     * comme les deux `round(…, 3)` de `daily.php:1091-1109`. L'arrondi n'est pas
     * un détail de présentation : il entre dans la somme, et c'est lui qui fait
     * tomber GDR sur le 0,387 du site plutôt que sur 0,386.
     *
     * Nos scores restent au-dessus de ceux de datan.fr d'un à quatre centièmes,
     * et c'est **voulu**. L'application d'origine apparie la profession du
     * député à sa table `famsocpro` par égalité stricte ; or l'open data écrit
     * « Artisans, commerçants, chefs d'entreprises » quand la table de référence
     * dit « Artisans, commerçants et chefs d'entreprise ». Les 41 artisans de la
     * 17e n'y trouvent donc aucune correspondance et disparaissent du calcul —
     * ni au numérateur, ni au dénominateur —, alors même que le tableau croisé
     * de la page, lui, les affiche sous leur graphie brute. Retirer nos artisans
     * du calcul reproduit le site au millième près (LFI 0,432, RN 0,403, GDR
     * 0,387, SOC et DEM 0,239, ECOS 0,205, UDDPLR 0,114) : la démonstration est
     * faite, l'écart n'a pas d'autre cause. C'est la coquille de casse de
     * `CLAUDE.md` sous un autre jour, et on ne la reproduit pas — 43 députés
     * classés valent mieux qu'un score comparable au chiffre près.
     *
     * @return list<array{id: int, score: float, numerateur: null, denominateur: int, tri: array<int|string>}>
     */
    private function origineSocialeGroupes(int $legislature): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT g.id, ps.fam_soc_pro, COUNT(*) AS n
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL AND g.libelle_abrev <> :ni
               AND ps.fam_soc_pro IS NOT NULL AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             GROUP BY g.id, ps.fam_soc_pro',
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS],
        );

        /** @var array<int, array<string, int>> $parGroupe */
        $parGroupe = [];
        foreach ($rows as $ligne) {
            $parGroupe[(int) $ligne['id']][$ligne['fam_soc_pro']] = (int) $ligne['n'];
        }

        $population = FamilleSocioPro::population();
        $agreges = [];

        foreach ($parGroupe as $groupeId => $effectifs) {
            $classes = array_sum($effectifs);
            $ecart = 0.0;

            foreach ($population as $famille => $part) {
                $ecart += abs(round($part / 100, 3) - round(($effectifs[$famille] ?? 0) / $classes, 3));
            }

            $agreges[] = ['id' => $groupeId, 'rose' => 1 - 0.5 * $ecart, 'classes' => $classes];
        }

        return $this->classementGroupes($agreges, static fn (array $l) => [
            'score' => (float) $l['rose'],
            'numerateur' => null,
            'denominateur' => (int) $l['classes'],
        ]);
    }

    /**
     * Complète des agrégats par groupe de leur clé de tri secondaire : à score
     * égal, le groupe le plus nombreux passe devant, puis l'ordre alphabétique.
     * L'application d'origine tranchait au hasard, ce qui faisait bouger le
     * classement d'un affichage à l'autre.
     *
     * @param list<array<string, mixed>> $rows
     * @param callable(array<string, mixed>): array{score: float, numerateur: int|null, denominateur: int|null} $valeurs
     *
     * @return list<array{id: int, score: float, numerateur: int|null, denominateur: int|null, tri: array<int|string>}>
     */
    private function classementGroupes(array $rows, callable $valeurs): array
    {
        $this->sigles ??= $this->connection->fetchAllKeyValue('SELECT id, libelle_abrev FROM groupe');
        $this->effectifs ??= $this->connection->fetchAllKeyValue(
            'SELECT d.groupe_id, COUNT(*)
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             WHERE EXISTS (SELECT 1 FROM mandat m
                           WHERE m.depute_id = d.id AND m.legislature = g.legislature
                             AND m.date_fin IS NULL)
             GROUP BY d.groupe_id',
        );

        $classement = [];

        foreach ($rows as $ligne) {
            $groupeId = (int) $ligne['id'];

            $classement[] = $valeurs($ligne) + [
                'id' => $groupeId,
                'tri' => [-(int) ($this->effectifs[$groupeId] ?? 0), $this->sigles[$groupeId] ?? ''],
            ];
        }

        return $classement;
    }

    /**
     * Trie un classement par score décroissant, attribue les rangs et réécrit la
     * table pour ce type et cette législature. Deux scores égaux partagent le
     * même rang et décalent le suivant d'autant, comme le `RANK()` d'origine.
     *
     * L'égalité se juge sur le score **tel qu'il sera stocké**, à trois
     * décimales, et non sur le flottant qui l'a produit. C'est la précision de
     * `class_groups.value` (un `decimal(6,3)`) sur lequel porte le `RANK()` de
     * l'application d'origine, et c'est elle qui fait les ex æquo du site :
     * UDDPLR et GDR partagent le rang 3 de la cohésion à 0,964, EPR et DEM le
     * rang 6 de la participation à 0,899. Comparer les flottants bruts les
     * sépare sur une décimale que personne ne voit, et le classement affiche
     * 3 puis 4 là où le site affiche 3 et 3.
     *
     * @param list<array{id: int, score: float, numerateur: int|null, denominateur: int|null, tri: array<int|string>}> $classement
     */
    private function remplace(TypeClassement $type, int $legislature, array $classement): void
    {
        // Score décroissant ; à égalité, la clé de tri secondaire, construite
        // pour être croissante (d'où les compteurs comptés en négatif).
        usort(
            $classement,
            static fn (array $a, array $b) => ($b['score'] <=> $a['score']) ?: ($a['tri'] <=> $b['tri']),
        );

        $this->connection->executeStatement(
            'DELETE FROM classement WHERE type = :type AND legislature = :legislature',
            ['type' => $type->value, 'legislature' => $legislature],
        );

        $colonne = $type->porteSurUnGroupe() ? 'groupe_id' : 'depute_id';
        $calculeLe = $this->aujourdhui->format('Y-m-d');

        $rang = 0;
        $precedent = null;
        $batch = [];

        foreach ($classement as $position => $ligne) {
            $stocke = round($ligne['score'], 3);

            if ($precedent === null || $stocke !== $precedent) {
                $rang = $position + 1;
                $precedent = $stocke;
            }

            $batch[] = [
                $type->value,
                $legislature,
                $rang,
                $ligne['id'],
                $stocke,
                $ligne['numerateur'],
                $ligne['denominateur'],
                $calculeLe,
            ];

            if (\count($batch) >= self::BATCH_SIZE) {
                $this->insere($colonne, $batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->insere($colonne, $batch);
        }
    }

    /**
     * @param list<list<mixed>> $batch
     */
    private function insere(string $colonne, array $batch): void
    {
        $placeholders = implode(', ', array_fill(0, \count($batch), '(?, ?, ?, ?, ?, ?, ?, ?)'));

        $this->connection->executeStatement(
            'INSERT INTO classement (type, legislature, rang, ' . $colonne . ',
                                     score, numerateur, denominateur, calcule_le)
             VALUES ' . $placeholders,
            array_merge(...$batch)
        );
    }

    /**
     * Dates de tous les scrutins de la législature, triées, éventuellement
     * restreintes à un type de scrutin. Les motions de censure sont écartées
     * quand on regarde l'ensemble des scrutins.
     *
     * `DATE()` n'est pas cosmétique : `scrutin.date_scrutin` est un DATETIME et
     * les bornes de mandat sont des DATE. Comparées telles quelles, en chaînes,
     * « 2025-02-13 15:00:00 » passe pour postérieur à « 2025-02-13 » — et le
     * scrutin du dernier jour de mandat sortait de la fenêtre.
     *
     * @return list<string>
     */
    private function datesScrutins(int $legislature, ?string $codeTypeVote): array
    {
        if ($codeTypeVote !== null) {
            return $this->connection->fetchFirstColumn(
                'SELECT DATE(date_scrutin) FROM scrutin
                 WHERE legislature = :legislature AND code_type_vote = :code
                 ORDER BY date_scrutin',
                ['legislature' => $legislature, 'code' => $codeTypeVote],
            );
        }

        return $this->connection->fetchFirstColumn(
            'SELECT DATE(date_scrutin) FROM scrutin
             WHERE legislature = :legislature
               AND (code_type_vote IS NULL OR code_type_vote <> :motion)
             ORDER BY date_scrutin',
            ['legislature' => $legislature, 'motion' => self::MOTION_CENSURE],
        );
    }

    /**
     * Périodes pendant lesquelles chaque député a effectivement siégé, une liste
     * d'intervalles par député.
     *
     * Elles se lisent dans `fonction_groupe`, et **non dans `mandat`**, alors
     * même que `daily.php` interroge son `mandat_principal` : la table de
     * l'Assemblée ne garde qu'un mandat par siège et **remplace** le précédent
     * au lieu de l'archiver, quand le legacy accumule le sien moisson après
     * moisson. Les interruptions y ont donc disparu — Charlotte
     * Parmentier-Lecocq n'a plus qu'un mandat ouvert le 27 mars 2026, et
     * Patrick Hetzel un seul couvrant toute la législature, alors que l'un et
     * l'autre ont quitté l'hémicycle pour le Gouvernement entre-temps. Vingt-six
     * députés de la 17e ont ainsi des votes **hors** du mandat qu'on leur
     * déclare, et six sortaient à 179 %, 113 %, 107 % de participation.
     *
     * Le rattachement à un groupe, lui, se referme et se rouvre à chaque
     * aller-retour : Hetzel est DR jusqu'au 21 octobre 2024, rien pendant sa
     * charge ministérielle, DR à nouveau depuis le 25 janvier 2025. C'est la
     * seule trace fidèle des périodes de présence dont nous disposions, et elle
     * rend au site ses quatre scrutins d'écart — 68 solennels sur 68, non 68
     * sur 72.
     *
     * Restent deux garde-fous : `mandat` prend le relais pour qui n'a aucun
     * rattachement, et les bornes observées des votes élargissent la fenêtre si
     * elles la débordent encore. Un vote vaut preuve de présence — il ne peut
     * pas tomber hors de la fenêtre qui le compte.
     *
     * @param array<int, array{0: string, 1: string}> $bornes premier et dernier vote connus
     *
     * @return array<int, list<array{0: string, 1: string}>>
     */
    private function fenetresDeSession(int $legislature, array $bornes): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            // Sans filtre sur `nomin_principale`, contrairement à tout ce qui
            // désigne LE groupe d'un député : ici on ne choisit pas un
            // rattachement, on réunit des présences, et n'importe lequel en est
            // la preuve. Chez les présidents de groupe, la ligne « Membre » et
            // la ligne « Président » ne portent pas le même drapeau — Stéphane
            // Peu et Christophe Naegelen n'avaient plus, filtrés, que leur
            // parenthèse de non-inscrit de juillet 2024, et sortaient à 108 % et
            // 171 % de participation.
            'SELECT fg.depute_id, fg.date_debut AS debut, fg.date_fin AS fin
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id AND g.legislature = :legislature
             WHERE fg.date_debut IS NOT NULL
             ORDER BY fg.depute_id, fg.date_debut',
            ['legislature' => $legislature],
        );

        $brutes = [];

        foreach ($lignes as $ligne) {
            $brutes[(int) $ligne['depute_id']][] = [$ligne['debut'], $ligne['fin'] ?? self::SANS_FIN];
        }

        // Un député sans aucun rattachement de groupe garde son mandat pour
        // seule trace : c'est mieux que rien, et cela ne concerne personne à la
        // 17e — la garde existe pour les législatures où `fonction_groupe` est
        // moins bien renseignée.
        $mandats = $this->connection->fetchAllAssociative(
            'SELECT depute_id, date_prise_fonction AS debut, date_fin AS fin
             FROM mandat
             WHERE legislature = :legislature AND date_prise_fonction IS NOT NULL
             ORDER BY depute_id, date_prise_fonction',
            ['legislature' => $legislature],
        );

        foreach ($mandats as $ligne) {
            $deputeId = (int) $ligne['depute_id'];

            if (!isset($brutes[$deputeId])) {
                $brutes[$deputeId][] = [$ligne['debut'], $ligne['fin'] ?? self::SANS_FIN];
            }
        }

        $fenetres = [];

        foreach ($brutes as $deputeId => $intervalles) {
            $fenetres[$deputeId] = $this->fusionne($intervalles);
        }

        foreach ($bornes as $deputeId => [$premier, $dernier]) {
            if (!isset($fenetres[$deputeId])) {
                $fenetres[$deputeId] = [[$premier, $dernier]];

                continue;
            }

            // Seules les bornes extérieures s'écartent : un vote tombé dans
            // l'interruption entre deux rattachements ne rouvre pas la
            // parenthèse — c'est justement elle qu'on cherche à préserver.
            $derniere = \count($fenetres[$deputeId]) - 1;
            $fenetres[$deputeId][0][0] = min($fenetres[$deputeId][0][0], $premier);
            $fenetres[$deputeId][$derniere][1] = max($fenetres[$deputeId][$derniere][1], $dernier);
        }

        return $fenetres;
    }

    /**
     * Réunit les intervalles qui se chevauchent ou se touchent. Les
     * rattachements se succèdent au jour le jour — non-inscrit du 8 au 18
     * juillet, puis membre du groupe à partir du 19 — et il ne faut ni compter
     * deux fois un scrutin à la charnière, ni y voir une interruption.
     *
     * @param list<array{0: string, 1: string}> $intervalles déjà triés par date de début
     *
     * @return list<array{0: string, 1: string}>
     */
    private function fusionne(array $intervalles): array
    {
        $fusionnes = [];

        foreach ($intervalles as [$debut, $fin]) {
            $precedent = \count($fusionnes) - 1;

            // Un jour d'écart suffit à rattacher : deux mandats de groupe
            // consécutifs ne laissent pas de trou, une charge ministérielle si.
            if ($precedent >= 0 && $debut <= $this->lendemain($fusionnes[$precedent][1])) {
                $fusionnes[$precedent][1] = max($fusionnes[$precedent][1], $fin);

                continue;
            }

            $fusionnes[] = [$debut, $fin];
        }

        return $fusionnes;
    }

    /**
     * Le jour suivant une date, la borne conventionnelle mise à part.
     *
     * `strtotime('9999-12-31 +1 day')` déborde et rend `false`, que `date()`
     * traduit en 1970 : la comparaison s'inversait alors et deux rattachements
     * pourtant contigus restaient séparés. Stéphane Lenormand, membre et
     * président de LIOT le même jour, se voyait ainsi compter deux fois chaque
     * scrutin — 64 votes sur 144 solennels, quand la législature n'en compte
     * que 72.
     */
    private function lendemain(string $date): string
    {
        return $date === self::SANS_FIN
            ? self::SANS_FIN
            : (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
    }

    /**
     * Premier et dernier scrutin où chaque député apparaît, non-votants compris :
     * figurer dans la ventilation d'un scrutin, fût-ce comme non-votant, prouve
     * qu'on siégeait ce jour-là.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function bornesDeVote(int $legislature): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT v.depute_id,
                    MIN(DATE(v.scrutin_date)) AS premier,
                    MAX(DATE(v.scrutin_date)) AS dernier
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             WHERE s.legislature = :legislature
             GROUP BY v.depute_id',
            ['legislature' => $legislature],
        );

        $bornes = [];

        foreach ($lignes as $ligne) {
            $bornes[(int) $ligne['depute_id']] = [$ligne['premier'], $ligne['dernier']];
        }

        return $bornes;
    }

    /**
     * Nombre de scrutins tenus pendant qu'un député siégeait. Les intervalles
     * d'un même député sont disjoints — un mandat se referme avant que le
     * suivant s'ouvre —, leurs comptes s'additionnent donc sans risque de
     * double compte.
     *
     * @param list<string>                        $dates
     * @param list<array{0: string, 1: string}>   $fenetres
     */
    private function compteFenetres(array $dates, array $fenetres): int
    {
        $total = 0;

        foreach ($fenetres as [$debut, $fin]) {
            $total += $this->borne($dates, $fin, true) - $this->borne($dates, $debut, false);
        }

        return $total;
    }

    /**
     * Position d'insertion de `$valeur` dans `$dates` : après ses occurrences
     * si `$inclusive`, avant sinon.
     *
     * @param list<string> $dates
     */
    private function borne(array $dates, string $valeur, bool $inclusive): int
    {
        $bas = 0;
        $haut = \count($dates);

        while ($bas < $haut) {
            $milieu = intdiv($bas + $haut, 2);

            if ($inclusive ? $dates[$milieu] <= $valeur : $dates[$milieu] < $valeur) {
                $bas = $milieu + 1;
            } else {
                $haut = $milieu;
            }
        }

        return $bas;
    }

    /**
     * Députés en exercice, associés à leur nom : ceux rattachés à un groupe de
     * la législature demandée. Le nom sert de dernier départage au tri.
     *
     * @return array<int, string>
     */
    private function deputesEnExercice(int $legislature): array
    {
        return $this->connection->fetchAllKeyValue(
            'SELECT d.id, CONCAT(d.lastname, " ", d.firstname)
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             WHERE g.legislature = :legislature AND ' . sprintf(self::EN_EXERCICE, 'd'),
            ['legislature' => $legislature],
        );
    }
}
