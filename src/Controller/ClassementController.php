<?php

namespace App\Controller;

use App\AgeFrance;
use App\CouleurGroupe;
use App\Enum\TypeClassement;
use App\FamilleSocioPro;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages de statistiques et de classements, portées du contrôleur Stats de
 * l'application CodeIgniter d'origine (routes `statistiques` et
 * `statistiques/(:any)`).
 *
 * Aucun classement n'est calculé ici : tout est lu dans la table `classement`,
 * remplie par {@see \App\Command\CalculClassementsCommand}. Ne restent à la
 * charge des pages que des dénombrements sur les 577 députés en exercice —
 * répartition par famille socio-professionnelle, métiers les plus fréquents —
 * qui ne touchent ni `vote` ni `vote_groupe`.
 */
class ClassementController extends AbstractController
{
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** Sigle des non-inscrits, écartés des comparaisons entre groupes. */
    private const NON_INSCRITS = 'NI';

    /**
     * En deçà de ce nombre de scrutins solennels, la législature est jugée trop
     * jeune pour que le score de participation qui s'y rapporte veuille dire
     * quelque chose : le site met alors en avant tous les scrutins.
     */
    private const SEUIL_SOLENNELS = 30;

    /**
     * Prédicat « ce député siège encore », à substituer de l'alias de `depute`
     * et à accompagner d'un paramètre `:legislature`.
     *
     * `depute.groupe_id` désigne le dernier groupe connu, y compris pour les
     * députés partis en cours de mandat : s'y fier gonflerait les effectifs.
     * Le statut se lit sur les mandats, comme sur la page d'un député.
     */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = %s.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    /** Effectif d'un groupe : ses membres encore en exercice, `g` étant l'alias du groupe. */
    private const EFFECTIF_SQL = '(SELECT COUNT(*) FROM depute dep
                                   WHERE dep.groupe_id = g.id
                                     AND EXISTS (SELECT 1 FROM mandat m
                                                 WHERE m.depute_id = dep.id
                                                   AND m.legislature = g.legislature
                                                   AND m.date_fin IS NULL))';

    /** Part de femmes dans la population française, en pourcentage. */
    private const FEMMES_SOCIETE = 52;

    /** Les neuf classements publiés, et le titre de chacun. */
    public const PAGES = [
        'deputes-age' => 'L\'âge des députés',
        'groupes-age' => 'L\'âge moyen au sein des groupes',
        'groupes-feminisation' => 'Le taux de féminisation des groupes parlementaires',
        'deputes-loyaute' => 'La proximité des députés à leur groupe',
        'groupes-cohesion' => 'La cohésion des groupes parlementaires',
        'deputes-participation' => 'La participation des députés',
        'groupes-participation' => 'La participation des groupes politiques',
        'deputes-origine-sociale' => 'L\'origine sociale des députés',
        'groupes-origine-sociale' => 'La représentativité sociale des groupes politiques',
    ];

    /**
     * Les mêmes pages, sous les libellés abrégés de la colonne de droite.
     *
     * Ce ne sont pas les titres : l'encadré « Nos autres statistiques » écrit
     * « La proximité au groupe » là où la page s'intitule « La proximité des
     * députés à leur groupe » (`views/classements/templates/footer.php`).
     * Cinq des neuf diffèrent — les reprendre du titre allongerait la colonne.
     */
    private const MENU = [
        'deputes-age' => 'L\'âge des députés',
        'groupes-age' => 'L\'âge moyen au sein des groupes',
        'groupes-feminisation' => 'Le taux de féminisation des groupes',
        'deputes-loyaute' => 'La proximité au groupe',
        'groupes-cohesion' => 'La cohésion des groupes',
        'deputes-participation' => 'La participation des députés',
        'groupes-participation' => 'La participation des groupes',
        'deputes-origine-sociale' => 'L\'origine sociale des députés',
        'groupes-origine-sociale' => 'La représentativité des groupes',
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * L'ancienne adresse de la rubrique, indexée depuis des années
     * (routes.php:236 → redirection/redir/statistiques). L'origine y répond
     * 307, le `redirect()` de CodeIgniter ne précisant pas de code — un
     * déplacement définitif se dit en 301 : défaut corrigé, pas choix.
     */
    #[Route('/classements', name: 'classements_ancienne_adresse', methods: ['GET'])]
    public function ancienneAdresse(): Response
    {
        return $this->redirectToRoute('classement_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/statistiques', name: 'classement_index', methods: ['GET'])]
    public function index(): Response
    {
        $ages = $this->classementDeputes(TypeClassement::DeputesAge);
        $loyaute = $this->classementDeputes(TypeClassement::DeputesLoyaute);
        $participation = $this->classementDeputes($this->typeParticipationDeputes());
        $agesGroupes = $this->classementGroupes(TypeClassement::GroupesAge);
        $feminisation = $this->classementGroupes(TypeClassement::GroupesFeminisation);
        $cohesion = $this->sansNonInscrits($this->classementGroupes(TypeClassement::GroupesCohesion));
        // Seule page à ne pas arbitrer entre solennels et scrutins ordinaires :
        // `Stats::index()` appelle `get_groups_participation()` sans consulter
        // le seuil, quand la page dédiée, elle, le consulte. D'où les 12 % à
        // 34 % affichés ici, contre 75 % à 95 % sur /statistiques/groupes-participation.
        $participationGroupes = $this->classementGroupes(TypeClassement::GroupesParticipationTous);
        $rose = $this->classementGroupes(TypeClassement::GroupesOrigineSociale);
        $familles = $this->familles();
        $cadres = $this->partsFamilleParGroupe(FamilleSocioPro::CADRES);

        $response = $this->render('classement/index.html.twig', [
            'title' => 'L\'Assemblée nationale en chiffres',
            'age_moyen' => $this->moyenneAges(),
            'deputes_plus_ages' => \array_slice($ages, 0, 3),
            'deputes_plus_jeunes' => \array_slice($ages, -3),
            'groupes_age' => $this->extremes($agesGroupes, 'Le plus âgé', 'Le plus jeune', fn (array $g) => $g['score_arrondi'] . ' ans'),
            'femmes' => $this->femmes(),
            'femmes_historique' => $this->historiqueFemmes(),
            'femmes_evolution' => $this->evolutionFemmes(),
            'groupes_femmes_plus' => \array_slice($feminisation, 0, 3),
            'groupes_femmes_moins' => \array_slice($feminisation, -3),
            'loyaute_moyenne' => $this->moyenne(TypeClassement::DeputesLoyaute),
            'deputes_plus_loyaux' => \array_slice($loyaute, 0, 3),
            'deputes_moins_loyaux' => \array_slice($loyaute, -3),
            'groupes_cohesion' => $this->extremes($cohesion, 'Le plus divisé', 'Le plus uni', $this->deuxDecimales(...), premierAGauche: false),
            'participation_moyenne' => $this->moyenne($this->typeParticipationDeputes()),
            'deputes_plus_actifs' => \array_slice($participation, 0, 3),
            'deputes_moins_actifs' => \array_slice($participation, -3),
            'groupes_participation' => $this->extremes($participationGroupes, 'Vote le moins', 'Vote le plus', fn (array $g) => $g['pourcentage'] . ' %', premierAGauche: false),
            'familles' => $familles,
            'famille_cadres' => $familles[FamilleSocioPro::CADRES],
            'groupes_cadres' => $this->extremes($cadres, 'Le moins de cadres', 'Le plus de cadres', fn (array $g) => $g['pourcentage'] . ' %', premierAGauche: false),
            'groupes_rose' => $this->extremes($rose, 'Le moins représentatif', 'Le plus représentatif', $this->troisDecimales(...), premierAGauche: false),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Nos statistiques', 'url' => $this->generateUrl('classement_index')],
            ],
        ]);

        return $this->cachee($response);
    }

    #[Route('/statistiques/{page}', name: 'classement_individual', requirements: ['page' => '[a-z\-]+'], methods: ['GET'])]
    public function individual(string $page): Response
    {
        if (!isset(self::PAGES[$page])) {
            throw $this->createNotFoundException('Classement introuvable.');
        }

        $donnees = match ($page) {
            'deputes-age' => $this->deputesAge(),
            'groupes-age' => $this->groupesAge(),
            'groupes-feminisation' => $this->groupesFeminisation(),
            'deputes-loyaute' => $this->deputesLoyaute(),
            'groupes-cohesion' => $this->groupesCohesion(),
            'deputes-participation' => $this->deputesParticipation(),
            'groupes-participation' => $this->groupesParticipation(),
            'deputes-origine-sociale' => $this->deputesOrigineSociale(),
            'groupes-origine-sociale' => $this->groupesOrigineSociale(),
        };

        $response = $this->render('classement/' . $page . '.html.twig', [
            ...$donnees,
            'page' => $page,
            'title' => self::PAGES[$page],
            'pages' => self::MENU,
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Nos statistiques', 'url' => $this->generateUrl('classement_index')],
                ['nom' => self::PAGES[$page], 'url' => $this->generateUrl('classement_individual', ['page' => $page])],
            ],
        ]);

        return $this->cachee($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function deputesAge(): array
    {
        $deputes = $this->classementDeputes(TypeClassement::DeputesAge);
        $moyenne = $this->moyenneAges();
        $ecart = (int) round($moyenne - AgeFrance::ELIGIBLES);

        return [
            'deputes' => $deputes,
            'age_moyen' => (int) round($moyenne),
            'age_moyen_france' => (int) round(AgeFrance::ELIGIBLES),
            'ecart' => $this->ecartEnMots($ecart),
            'plus_age' => $deputes[0] ?? null,
            'plus_jeune' => $deputes !== [] ? end($deputes) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function groupesAge(): array
    {
        $groupes = $this->classementGroupes(TypeClassement::GroupesAge);

        return [
            'groupes' => $groupes,
            'age_moyen_france' => (int) round(AgeFrance::ELIGIBLES),
            'plus_age' => $groupes[0] ?? null,
            'plus_jeune' => $groupes !== [] ? end($groupes) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function groupesFeminisation(): array
    {
        $groupes = $this->classementGroupes(TypeClassement::GroupesFeminisation);
        $femmes = $this->femmes();

        return [
            'groupes' => $groupes,
            'femmes' => $femmes,
            'femmes_societe' => self::FEMMES_SOCIETE,
            'ecart_societe' => abs(self::FEMMES_SOCIETE - $femmes['pourcentage']),
            'plus_feminise' => $groupes[0] ?? null,
            'moins_feminise' => $groupes !== [] ? end($groupes) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function deputesLoyaute(): array
    {
        $deputes = $this->classementDeputes(TypeClassement::DeputesLoyaute);

        return [
            'deputes' => $deputes,
            'plus_loyal' => $deputes[0] ?? null,
            'plus_rebelle' => $deputes !== [] ? end($deputes) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function groupesCohesion(): array
    {
        $groupes = $this->classementGroupes(TypeClassement::GroupesCohesion);

        // Les non-inscrits figurent au tableau — l'application d'origine les y
        // laisse — mais pas dans les cartes, qui opposent deux groupes.
        $comparables = $this->sansNonInscrits($groupes);

        return [
            'groupes' => $groupes,
            'cohesion_moyenne' => $this->moyenne(TypeClassement::GroupesCohesion),
            'plus_uni' => $comparables[0] ?? null,
            'plus_divise' => $comparables !== [] ? end($comparables) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function deputesParticipation(): array
    {
        $solennels = $this->classementDeputes(TypeClassement::DeputesParticipation);
        $commission = $this->classementDeputesCommission();
        $tous = $this->classementDeputes(TypeClassement::DeputesParticipationTous);
        $miseEnAvant = $this->assezDeSolennels() ? $solennels : $tous;

        return [
            'solennels' => $solennels,
            'commission' => $commission,
            'tous' => $tous,
            'moyenne_solennels' => $this->moyenne(TypeClassement::DeputesParticipation),
            // La moyenne de ce que le tableau montre : les députés en exercice
            // membres d'une commission. Le site moyenne toute sa table, anciens
            // députés et sans-commission compris — même divergence assumée que
            // les deux autres moyennes de participation (« nous moyennons ce
            // que nous montrons », cf. TODO.md).
            'moyenne_commission' => $commission === []
                ? 0.0
                : array_sum(array_column($commission, 'score')) / \count($commission),
            'moyenne_tous' => $this->moyenne(TypeClassement::DeputesParticipationTous),
            'nombre_solennels' => $this->nombreSolennels(),
            'seuil_solennels' => self::SEUIL_SOLENNELS,
            'assez_de_solennels' => $this->assezDeSolennels(),
            'nombre_scrutins' => $this->nombreScrutins(),
            'plus_actif' => $miseEnAvant[0] ?? null,
            'moins_actif' => $miseEnAvant !== [] ? end($miseEnAvant) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function groupesParticipation(): array
    {
        $solennels = $this->classementGroupes(TypeClassement::GroupesParticipation);
        $commission = $this->classementGroupes(TypeClassement::GroupesParticipationCommission);
        $tous = $this->classementGroupes(TypeClassement::GroupesParticipationTous);

        // Contrairement à la cohésion, cette page garde les non-inscrits dans
        // ses deux cartes : `Stats::individual()` prend les bouts de la liste
        // entière. Ils y arrivent derniers (75 %), et c'est bien eux que le
        // site désigne comme participant le moins — les écarter ferait
        // remonter GDR à leur place.
        $miseEnAvant = $this->assezDeSolennels() ? $solennels : $tous;

        return [
            'solennels' => $solennels,
            'commission' => $commission,
            'tous' => $tous,
            'moyenne_solennels' => $this->moyenne(TypeClassement::GroupesParticipation),
            'moyenne_commission' => $this->moyenne(TypeClassement::GroupesParticipationCommission),
            'moyenne_tous' => $this->moyenne(TypeClassement::GroupesParticipationTous),
            'nombre_solennels' => $this->nombreSolennels(),
            'seuil_solennels' => self::SEUIL_SOLENNELS,
            'assez_de_solennels' => $this->assezDeSolennels(),
            'nombre_scrutins' => $this->nombreScrutins(),
            'plus_actif' => $miseEnAvant[0] ?? null,
            'moins_actif' => $miseEnAvant !== [] ? end($miseEnAvant) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function deputesOrigineSociale(): array
    {
        $familles = $this->familles();

        return [
            'familles' => $familles,
            'famille_cadres' => $familles[FamilleSocioPro::CADRES],
            'metiers' => $this->metiers(10),
            'deputes' => $this->professionsDeputes(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function groupesOrigineSociale(): array
    {
        $groupes = $this->classementGroupes(TypeClassement::GroupesOrigineSociale);
        $familles = $this->familles();
        $croise = $this->partsParGroupe();

        return [
            'groupes' => $groupes,
            'plus_representatif' => $groupes[0] ?? null,
            'famille_cadres' => $familles[FamilleSocioPro::CADRES],
            'parts' => $croise['parts'],
            'noms_familles' => $croise['familles'],
        ];
    }

    /**
     * Une ligne de classement par député, dans l'ordre du palmarès.
     *
     * `rang` comporte des ex æquo et donc des trous : il ne suffit pas à
     * ordonner. Les lignes ayant été écrites dans l'ordre du classement,
     * l'identifiant les départage exactement comme le calcul l'a fait.
     *
     * @return list<array<string, mixed>>
     */
    private function classementDeputes(TypeClassement $type): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT c.rang, c.score, c.numerateur, c.denominateur,
                    d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    d.departement_nom, d.departement_code,
                    g.legislature, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur
             FROM classement c
             JOIN depute d ON d.id = c.depute_id
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE c.type = :type AND c.legislature = :legislature
             ORDER BY c.rang, c.id',
            ['type' => $type->value, 'legislature' => Legislature::COURANTE],
        );

        $deputes = array_map($this->decore(...), $lignes);

        return $type === TypeClassement::DeputesAge
            ? $this->numerote($deputes)
            : $this->classeAuScoreExact($deputes, $type->decimalesDuScore());
    }

    /**
     * Le classement « Votes par spécialisation », avec la commission actuelle de
     * chaque député — la colonne que l'onglet affiche en plus des autres.
     *
     * La jointure sur l'adhésion encore ouverte n'apporte pas que le libellé :
     * elle filtre. Le site ne montre que les députés membres d'une commission
     * au moment du rendu (`get_mps_participation_commission` exige un
     * `mandat_secondaire` COMPER « Membre » sans date de fin), et son RANK()
     * porte sur cette population filtrée — le nôtre aussi, le rang se refaisant
     * ici sur les entiers.
     *
     * @return list<array<string, mixed>>
     */
    private function classementDeputesCommission(): array
    {
        $type = TypeClassement::DeputesParticipationCommission;

        $lignes = $this->connection->fetchAllAssociative(
            'SELECT c.rang, c.score, c.numerateur, c.denominateur,
                    d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    d.departement_nom, d.departement_code,
                    g.legislature, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur,
                    com.libelle_abrege AS commission
             FROM classement c
             JOIN depute d ON d.id = c.depute_id
             LEFT JOIN groupe g ON g.id = d.groupe_id
             JOIN (SELECT depute_id, commission_id,
                          ROW_NUMBER() OVER (PARTITION BY depute_id
                                             ORDER BY date_debut DESC, id DESC) AS rang
                   FROM fonction_commission
                   WHERE legislature = :legislature AND code_qualite = :membre
                     AND date_fin IS NULL) fc
               ON fc.depute_id = d.id AND fc.rang = 1
             JOIN commission com ON com.id = fc.commission_id
             WHERE c.type = :type AND c.legislature = :legislature
             ORDER BY c.rang, c.id',
            [
                'type' => $type->value,
                'legislature' => Legislature::COURANTE,
                'membre' => 'Membre',
            ],
        );

        return $this->classeAuScoreExact(array_map($this->decore(...), $lignes), $type->decimalesDuScore());
    }

    /**
     * Réordonne et renumérote un classement de députés, comme le
     * `RANK() OVER (ORDER BY score DESC, votesN DESC)` de l'application
     * d'origine.
     *
     * Deux choses s'y jouent, et elles tirent en sens contraire.
     *
     * Le rang écrit par la commande de calcul souffre du DECIMAL(8,3) : le tri
     * s'y fait sur un score déjà arrondi, et le nombre de votes — qui départage
     * les égalités — n'y entre pas. On refait donc les deux ici, où
     * `numerateur` et `denominateur` sont encore des entiers.
     *
     * Mais l'égalité ne se juge pas sur la fraction exacte pour autant : elle se
     * juge à la précision où l'application d'origine range le score, deux
     * décimales pour la participation (`class_participation*`), trois pour la
     * loyauté (`class_loyaute`). C'est ce qui fait les blocs d'ex æquo du site :
     * six députés partagent le rang 1 de la participation aux solennels avec
     * 72 votes sur 72, le suivant porte le 7 avec 68 sur 68 ; et deux
     * non-inscrits partagent le rang 574 de la participation générale à 2 %,
     * alors que leurs fractions, sur 8 402 scrutins, ne sont pas les mêmes.
     * Départager à la fraction exacte les renumérotait un par un.
     *
     * @param list<array<string, mixed>> $classement
     *
     * @return list<array<string, mixed>>
     */
    private function classeAuScoreExact(array $classement, int $decimales): array
    {
        if ($classement === [] || $classement[0]['numerateur'] === null || $classement[0]['denominateur'] === null) {
            return $classement;
        }

        foreach ($classement as $position => $ligne) {
            $denominateur = (int) $ligne['denominateur'];

            $classement[$position]['cle'] = [
                $denominateur > 0 ? round((int) $ligne['numerateur'] / $denominateur, $decimales) : 0.0,
                $denominateur,
            ];
        }

        // Score décroissant, puis nombre de votes décroissant : les deux termes
        // du `ORDER BY` d'origine, dans cet ordre.
        usort($classement, static fn (array $a, array $b): int => $b['cle'] <=> $a['cle']);

        $rang = 0;
        $precedent = null;

        foreach ($classement as $position => $ligne) {
            if ($ligne['cle'] !== $precedent) {
                $rang = $position + 1;
                $precedent = $ligne['cle'];
            }

            $classement[$position]['rang'] = $rang;
            unset($classement[$position]['cle']);
        }

        return $classement;
    }

    /**
     * Renumérote un classement de 1 à N, sans égalités.
     *
     * L'âge est le seul des neuf classements que l'application d'origine ne
     * passe pas par `RANK()` : `get_ranking_age()` numérote les lignes au fil
     * de l'eau (`stats_model.php:29`). Deux députés nés la même année s'y
     * suivent donc en 2 et 3, et le dernier porte le 577 — quand un rang avec
     * ex æquo s'arrêterait à 573. Le site le montre ainsi depuis toujours :
     * c'est sa numérotation, pas une erreur d'arrondi.
     *
     * @param list<array<string, mixed>> $classement
     *
     * @return list<array<string, mixed>>
     */
    private function numerote(array $classement): array
    {
        foreach ($classement as $position => $ligne) {
            $classement[$position]['rang'] = $position + 1;
        }

        return $classement;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function classementGroupes(TypeClassement $type): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT c.rang, c.score, c.numerateur, c.denominateur,
                    g.legislature, g.libelle, g.libelle_abrev,
                    ' . CouleurGroupe::SQL . ' AS couleur,
                    ' . self::EFFECTIF_SQL . ' AS effectif
             FROM classement c
             JOIN groupe g ON g.id = c.groupe_id
             WHERE c.type = :type AND c.legislature = :legislature
             ORDER BY c.rang, c.id',
            ['type' => $type->value, 'legislature' => Legislature::COURANTE],
        );

        return array_map($this->decore(...), $lignes);
    }

    /**
     * Ajoute à une ligne de classement les formes sous lesquelles les vues
     * l'affichent : le score en nombre, arrondi, et en pourcentage.
     *
     * @param array<string, mixed> $ligne
     *
     * @return array<string, mixed>
     */
    private function decore(array $ligne): array
    {
        $score = (float) $ligne['score'];
        $numerateur = $ligne['numerateur'] !== null ? (int) $ligne['numerateur'] : null;
        $denominateur = $ligne['denominateur'] !== null ? (int) $ligne['denominateur'] : null;

        // `classement.score` est un DECIMAL(8,3) : la fraction y perd ses
        // décimales suivantes, et l'arrondi au point de pourcentage se fait
        // alors sur une valeur déjà arrondie. Les 45 femmes d'EPR sur 91 sièges
        // valent 49,45 % ; stockées 0,495 elles ressortent à 50 %. Ce double
        // arrondi déplaçait d'un point 38 taux de loyauté et 32 de
        // participation. Quand le classement garde ses deux termes, le
        // pourcentage se refait sur eux.
        $pourcentage = $numerateur !== null && $denominateur > 0
            ? (int) round($numerateur / $denominateur * 100)
            : (int) round($score * 100);

        return [
            ...$ligne,
            'rang' => (int) $ligne['rang'],
            'score' => $score,
            'score_arrondi' => (int) round($score),
            'pourcentage' => $pourcentage,
            'numerateur' => $numerateur,
            'denominateur' => $denominateur,
        ];
    }

    /**
     * Les deux bouts d'un classement, tels que les affichent les cartes en
     * vis-à-vis : un titre, le groupe, et le score mis en forme.
     *
     * Les titres sont donnés dans l'ordre où les cartes s'affichent, de gauche
     * à droite, et `$premierAGauche` dit lequel des deux bouts occupe la
     * gauche. L'application d'origine ne les présente pas toutes dans le même
     * sens : `Stats::index()` monte `groups_age_edited` avec le premier du
     * classement en `first`, mais cohésion, participation, cadres et
     * représentativité y mettent le **dernier**. Le sens appartient donc à
     * l'appel, pas à cette méthode.
     *
     * @param list<array<string, mixed>> $classement
     * @param callable(array<string, mixed>): string $stat
     *
     * @return array{gauche: array<string, mixed>, droite: array<string, mixed>}|null
     */
    private function extremes(
        array $classement,
        string $titreGauche,
        string $titreDroite,
        callable $stat,
        bool $premierAGauche = true,
    ): ?array {
        if ($classement === []) {
            return null;
        }

        $premier = $classement[0];
        $dernier = end($classement);
        [$gauche, $droite] = $premierAGauche ? [$premier, $dernier] : [$dernier, $premier];

        return [
            'gauche' => ['titre' => $titreGauche, 'groupe' => $gauche, 'stat' => $stat($gauche)],
            'droite' => ['titre' => $titreDroite, 'groupe' => $droite, 'stat' => $stat($droite)],
        ];
    }

    /**
     * Indice de cohésion, à deux décimales.
     *
     * L'application d'origine laisse `round()` écrire « 0.86 » : un séparateur
     * décimal anglais au milieu d'une page française. Faute de typographie,
     * corrigée — le chiffre, lui, est le même.
     *
     * @param array<string, mixed> $groupe
     */
    private function deuxDecimales(array $groupe): string
    {
        return number_format((float) $groupe['score'], 2, ',', ' ');
    }

    /**
     * Indice de Rose, à trois décimales — même correction de séparateur.
     *
     * @param array<string, mixed> $groupe
     */
    private function troisDecimales(array $groupe): string
    {
        return number_format((float) $groupe['score'], 3, ',', ' ');
    }

    /**
     * @param list<array<string, mixed>> $groupes
     *
     * @return list<array<string, mixed>>
     */
    private function sansNonInscrits(array $groupes): array
    {
        return array_values(array_filter(
            $groupes,
            static fn (array $g) => $g['libelle_abrev'] !== self::NON_INSCRITS,
        ));
    }

    /**
     * Moyenne d'un classement — celle de ses lignes affichées.
     *
     * Les deux moyennes de participation des **députés** sortent un point
     * au-dessus de celles du site (90 % contre 89 %, 26 % contre 25 %), et pour
     * la même raison que la cohésion moyenne des groupes : `class_participation`
     * garde une ligne par député ayant voté sous la législature, anciens
     * compris, et `get_mps_participation_mean()` les moyenne tous quand le
     * tableau, lui, n'affiche que les 577 en exercice. Les ministres et les
     * suppléés de passage, qui n'y figurent pas, tirent donc vers le bas une
     * moyenne présentée comme celle du tableau. Nous moyennons ce que nous
     * montrons — divergence assumée, elle corrige. Les quatre moyennes de
     * groupes, elles, tombent juste.
     */
    private function moyenne(TypeClassement $type): float
    {
        return (float) $this->connection->fetchOne(
            'SELECT AVG(score) FROM classement WHERE type = :type AND legislature = :legislature',
            ['type' => $type->value, 'legislature' => Legislature::COURANTE],
        );
    }

    /**
     * L'âge moyen se recalcule sur les dates de naissance plutôt que sur la
     * moyenne des scores du classement, qui sont des années révolues arrondies.
     */
    private function moyenneAges(): float
    {
        return (float) $this->connection->fetchOne(
            'SELECT AVG(TIMESTAMPDIFF(YEAR, ps.date_naissance, CURDATE()))
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND ps.date_naissance IS NOT NULL
               AND ' . sprintf(self::EN_EXERCICE, 'd'),
            ['legislature' => Legislature::COURANTE],
        );
    }

    /**
     * @return array{nombre: int, pourcentage: int, effectif: int}
     */
    private function femmes(): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS effectif, SUM(d.civilite = :mme) AS femmes
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             WHERE g.legislature = :legislature AND ' . sprintf(self::EN_EXERCICE, 'd'),
            ['legislature' => Legislature::COURANTE, 'mme' => 'Mme'],
        ) ?: [];

        $effectif = (int) ($row['effectif'] ?? 0);
        $femmes = (int) ($row['femmes'] ?? 0);

        return [
            'nombre' => $femmes,
            'effectif' => $effectif,
            'pourcentage' => $effectif > 0 ? (int) round($femmes / $effectif * 100) : 0,
        ];
    }

    /**
     * Part de femmes à l'Assemblée au fil des législatures. Les valeurs passées
     * sont celles de l'application d'origine, qui les tient en dur — les
     * effectifs des législatures antérieures à la 14e ne sont pas en base.
     *
     * @return list<array{legislature: int, debut: string, fin: string, pourcentage: int}>
     */
    private function historiqueFemmes(): array
    {
        $historique = [
            ['legislature' => 11, 'debut' => '1997', 'fin' => '2002', 'pourcentage' => 13],
            ['legislature' => 12, 'debut' => '2002', 'fin' => '2007', 'pourcentage' => 13],
            ['legislature' => 13, 'debut' => '2007', 'fin' => '2012', 'pourcentage' => 20],
            ['legislature' => 14, 'debut' => '2012', 'fin' => '2017', 'pourcentage' => 27],
            ['legislature' => 15, 'debut' => '2017', 'fin' => '2022', 'pourcentage' => 39],
            ['legislature' => 16, 'debut' => '2022', 'fin' => '2024', 'pourcentage' => 37],
        ];

        $historique[] = [
            'legislature' => Legislature::COURANTE,
            'debut' => '2024',
            'fin' => '2029',
            'pourcentage' => $this->femmes()['pourcentage'],
        ];

        return \array_slice($historique, -6);
    }

    /**
     * Le sens de l'évolution de la part de femmes depuis la législature
     * précédente, tel que la phrase de la page le formule.
     *
     * L'application d'origine écrit « a légèrement baissé » en dur
     * (`views/classements/index.php:127`) : chez elle la comparaison portait
     * sur 39 % puis 38 %, et son historique s'arrête à une 16e législature
     * qu'elle prolonge jusqu'en 2027. La dissolution de 2024 l'a périmée sans
     * que personne ne la relise — 37 % à la 16e, 38 % aujourd'hui, c'est une
     * hausse. Le verbe se déduit donc des deux dernières valeurs plutôt que de
     * rester écrit.
     */
    private function evolutionFemmes(): string
    {
        $historique = $this->historiqueFemmes();
        $precedente = $historique[\count($historique) - 2]['pourcentage'] ?? null;
        $courante = end($historique)['pourcentage'];

        return match (true) {
            $precedente === null || $courante === $precedente => 'est resté stable',
            $courante < $precedente => 'a légèrement baissé',
            default => 'a légèrement augmenté',
        };
    }

    /**
     * Répartition des députés en exercice dans les huit familles
     * socio-professionnelles de l'INSEE, rapportée à leur poids dans la
     * population. Un dénombrement sur 577 lignes : il n'y a rien à précalculer.
     *
     * @return array<string, array{famille: string, nombre: int, part: float, population: float}>
     */
    private function familles(): array
    {
        $effectif = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM depute d JOIN groupe g ON g.id = d.groupe_id
             WHERE g.legislature = :legislature AND ' . sprintf(self::EN_EXERCICE, 'd'),
            ['legislature' => Legislature::COURANTE],
        );

        $comptes = $this->connection->fetchAllKeyValue(
            'SELECT ps.fam_soc_pro, COUNT(*)
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND ps.fam_soc_pro IS NOT NULL
               AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             GROUP BY ps.fam_soc_pro',
            ['legislature' => Legislature::COURANTE],
        );

        $familles = [];

        foreach (FamilleSocioPro::population() as $famille => $population) {
            $nombre = (int) ($comptes[$famille] ?? 0);

            $familles[$famille] = [
                'famille' => $famille,
                // Les libellés sont longs : le graphique les reçoit découpés en
                // lignes, Chart.js empilant les étiquettes qu'on lui donne en
                // tableau. Même découpe que `word_wrap($famille, 25)` d'origine.
                'lignes' => explode("\n", wordwrap($famille, 25, "\n")),
                'nombre' => $nombre,
                'part' => $effectif > 0 ? round($nombre / $effectif * 100, 2) : 0.0,
                'population' => $population,
            ];
        }

        return $familles;
    }

    /**
     * Part de chaque groupe dans une famille socio-professionnelle donnée,
     * du plus fourni au moins fourni.
     *
     * @return list<array<string, mixed>>
     */
    private function partsFamilleParGroupe(string $famille): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT g.legislature, g.libelle, g.libelle_abrev,
                    ' . CouleurGroupe::SQL . ' AS couleur,
                    COUNT(ps.id) AS nombre,
                    ' . self::EFFECTIF_SQL . ' AS effectif
             FROM groupe g
             JOIN depute d ON d.groupe_id = g.id
             LEFT JOIN profil_social ps ON ps.depute_id = d.id AND ps.fam_soc_pro = :famille
             WHERE g.legislature = :legislature AND g.date_fin IS NULL
               AND g.libelle_abrev <> :ni AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             GROUP BY g.id, g.legislature, g.libelle, g.libelle_abrev, g.couleur
             -- Le sigle départage les ex æquo, que l\'application d\'origine
             -- laisse à MariaDB : UDDPLR et GDR comptent tous deux 9 cadres sur
             -- 17 sièges, et sans second critère la carte « le moins de cadres »
             -- change de groupe d\'un rendu à l\'autre.
             ORDER BY COUNT(ps.id) / ' . self::EFFECTIF_SQL . ' DESC, g.libelle_abrev ASC',
            ['famille' => $famille, 'legislature' => Legislature::COURANTE, 'ni' => self::NON_INSCRITS],
        );

        return array_map(static function (array $ligne): array {
            $effectif = (int) $ligne['effectif'];
            $nombre = (int) $ligne['nombre'];

            return [
                ...$ligne,
                'nombre' => $nombre,
                'effectif' => $effectif,
                'pourcentage' => $effectif > 0 ? (int) round($nombre / $effectif * 100) : 0,
            ];
        }, $lignes);
    }

    /**
     * Part de chaque famille socio-professionnelle dans chaque groupe, en
     * pourcentage : le tableau croisé de la page « représentativité ».
     *
     * Le dénominateur est l'effectif du groupe, pas le nombre de députés
     * classés — c'est le `ge.effectif` de `get_groups_representativite()`. La
     * colonne d'un groupe ne fait donc 100 % que si tous ses membres ont
     * déclaré une profession, d'où la ligne « Sans profession déclarée » qui
     * recueille les autres : sans elle, il manque jusqu'à 12 % d'une colonne
     * sans que rien ne le dise.
     *
     * @return array{parts: array<string, array<string, int>>, familles: list<string>}
     */
    private function partsParGroupe(): array
    {
        // `LEFT JOIN` puis `COALESCE` : un député sans ligne `profil_social`
        // n'est pas dans un autre cas qu'un député dont la famille est nulle —
        // les deux sont des professions non déclarées, et les deux comptent.
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT g.libelle_abrev, COALESCE(ps.fam_soc_pro, :sansProfession) AS famille,
                    COUNT(*) AS nombre,
                    ' . self::EFFECTIF_SQL . ' AS effectif
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             LEFT JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL
               AND g.libelle_abrev <> :ni
               AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             GROUP BY g.id, g.libelle_abrev, famille
             ORDER BY g.libelle_abrev',
            [
                'legislature' => Legislature::COURANTE,
                'ni' => self::NON_INSCRITS,
                'sansProfession' => FamilleSocioPro::SANS_PROFESSION,
            ],
        );

        $parts = [];
        $presentes = [];

        foreach ($lignes as $ligne) {
            $effectif = (int) $ligne['effectif'];
            $presentes[$ligne['famille']] = true;

            $parts[$ligne['libelle_abrev']][$ligne['famille']] = $effectif > 0
                ? (int) round((int) $ligne['nombre'] / $effectif * 100)
                : 0;
        }

        // Les familles de l'INSEE dans leur ordre de référence, puis les
        // non-déclarés en fin de tableau. Celles que personne ne représente —
        // aucun retraité, aucun inactif à la 17e — ne font pas une ligne de
        // zéros : le site ne montre que les familles qu'il a rencontrées.
        $familles = array_values(array_filter(
            [...array_keys(FamilleSocioPro::population()), FamilleSocioPro::SANS_PROFESSION],
            static fn (string $famille): bool => isset($presentes[$famille]),
        ));

        return ['parts' => $parts, 'familles' => $familles];
    }

    /**
     * Les métiers les plus représentés à l'Assemblée. Les libellés déclarés
     * sont regroupés comme dans l'application d'origine — « Avocat » réunit
     * « Avocat », « Avocate », « Avocat associé »… — sans quoi le classement
     * se disperserait en variantes d'un même métier.
     *
     * @return list<array{metier: string, nombre: int}>
     */
    private function metiers(int $limite): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT CASE
                        WHEN ps.metier LIKE :avocat THEN \'Avocat\'
                        WHEN ps.metier LIKE :medecin THEN \'Médecin\'
                        WHEN ps.metier LIKE :cadre THEN \'Cadre\'
                        WHEN ps.metier LIKE :professeur OR ps.metier LIKE :conference THEN \'Professeur\'
                        WHEN ps.metier LIKE :chef THEN \'Chef d\'\'entreprise\'
                        WHEN ps.metier LIKE :agriculteur THEN \'Agriculteur\'
                        WHEN ps.metier LIKE :pharmacien THEN \'Pharmacien\'
                        ELSE ps.metier
                    -- L\'alias ne peut pas s\'appeler `metier` : dans un GROUP BY,
                    -- MariaDB donne la priorité à la colonne du même nom et
                    -- regrouperait sur les libellés bruts, sans les réunir.
                    END AS metier_groupe,
                    COUNT(*) AS nombre
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND ps.metier IS NOT NULL
               AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             GROUP BY metier_groupe
             ORDER BY nombre DESC, metier_groupe
             LIMIT ' . $limite,
            [
                'legislature' => Legislature::COURANTE,
                'avocat' => '%vocat%',
                'medecin' => 'Médecin%',
                'cadre' => '%adre%',
                'professeur' => 'Professeur%',
                'conference' => '%conférence%',
                'chef' => '%hef d\'entreprise%',
                'agriculteur' => '%griculteur%',
                'pharmacien' => '%harmacien%',
            ],
        );

        return array_map(
            static fn (array $l) => ['metier' => $l['metier_groupe'], 'nombre' => (int) $l['nombre']],
            $lignes,
        );
    }

    /**
     * Métier et famille socio-professionnelle de chaque député en exercice.
     *
     * @return list<array<string, mixed>>
     */
    private function professionsDeputes(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname, d.lastname, d.slug, d.dpt_slug,
                    g.libelle_abrev AS groupe_abrev,
                    ps.metier, ps.fam_soc_pro
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             LEFT JOIN profil_social ps ON ps.depute_id = d.id
             WHERE g.legislature = :legislature AND ' . sprintf(self::EN_EXERCICE, 'd') . '
             ORDER BY d.lastname, d.firstname',
            ['legislature' => Legislature::COURANTE],
        );
    }

    private function nombreSolennels(): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM scrutin WHERE legislature = :legislature AND code_type_vote = :sps',
            ['legislature' => Legislature::COURANTE, 'sps' => 'SPS'],
        );
    }

    private function nombreScrutins(): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM scrutin WHERE legislature = :legislature',
            ['legislature' => Legislature::COURANTE],
        );
    }

    private function assezDeSolennels(): bool
    {
        return $this->nombreSolennels() > self::SEUIL_SOLENNELS;
    }

    private function typeParticipationDeputes(): TypeClassement
    {
        return $this->assezDeSolennels()
            ? TypeClassement::DeputesParticipation
            : TypeClassement::DeputesParticipationTous;
    }

    private function typeParticipationGroupes(): TypeClassement
    {
        return $this->assezDeSolennels()
            ? TypeClassement::GroupesParticipation
            : TypeClassement::GroupesParticipationTous;
    }

    /**
     * « deux ans de plus que », « autant que » : l'écart d'âge tel que la page
     * le formule, porté de `Functions_datan::int2str()`.
     */
    private function ecartEnMots(int $annees): string
    {
        if ($annees === 0) {
            return 'autant que';
        }

        $mots = [
            1 => 'un', 2 => 'deux', 3 => 'trois', 4 => 'quatre', 5 => 'cinq',
            6 => 'six', 7 => 'sept', 8 => 'huit', 9 => 'neuf', 10 => 'dix',
            11 => 'onze', 12 => 'douze', 13 => 'treize', 14 => 'quatorze',
            15 => 'quinze', 16 => 'seize', 17 => 'dix-sept', 18 => 'dix-huit',
            19 => 'dix-neuf', 20 => 'vingt',
        ];

        $valeur = abs($annees);

        return sprintf(
            '%s %s de %s que',
            $mots[$valeur] ?? (string) $valeur,
            $valeur === 1 ? 'an' : 'ans',
            $annees > 0 ? 'plus' : 'moins',
        );
    }

    /**
     * Ces pages ne changent qu'au rythme du recalcul nocturne des classements :
     * même politique de cache que les pages de député et de groupe.
     */
    private function cachee(Response $response): Response
    {
        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }
}
