<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\Http\RechauffeurCache;
use App\Legislature;
use App\Repository\CandidatureRepository;
use App\Repository\ElectionRepository;
use App\Repository\ResultatElectoralRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Élections : catalogue, candidatures des députés, et résultats par département
 * et par commune (contrôleur Elections de l'application CodeIgniter d'origine,
 * vues application/views/elections/).
 *
 * **Les municipales de 2026 sont hors de ce portage.** L'application d'origine
 * les traite sous une élection `id = 7` absente de son propre catalogue
 * `elect_libelle`, alimentée par des tables de dépouillement que nous
 * n'importons pas : c'est son chantier en cours, pas une donnée à reprendre.
 * Trois blocs du site en dépendent entièrement et n'ont donc pas d'équivalent
 * ici — l'annonce du prochain scrutin sur `/elections`, et sur la fiche de ville
 * la candidature des députés puis le dépouillement commune par commune. Les
 * endroits concernés le disent.
 */
class ElectionController extends AbstractController
{
    /**
     * Un scrutin passé ne bouge plus, et les six du catalogue le sont tous. La
     * seule chose qui vieillisse est l'état déduit des dates, qui ne changera
     * plus avant la prochaine élection.
     */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Seuil de population au-delà duquel le site écrit le lien vers la commune
     * en clair (`url_obf_cities_election()`). Il vaut 500 ici, contre 4 000 pour
     * les fiches de ville de `/deputes` ({@see DepartementController}) : les
     * pages d'élections descendent bien plus bas dans la carte des communes.
     * En deçà, la commune reste cliquable mais son adresse est masquée aux
     * robots ({@see \App\Twig\DatanExtension::urlObf()}) — et les plans de site
     * suivent le même critère.
     */
    public const POPULATION_MINIMALE = 500;

    /** Communes proposées en pied de `/elections`, de la plus peuplée à la plus petite. */
    private const COMMUNES_VEDETTES = 30;

    /** Communes mises en avant en tête d'une page de département. */
    private const COMMUNES_PHARES = 15;

    /**
     * Départements sans liste de communes en pied de page de commune. Aux deux
     * collectivités que {@see DepartementController::SANS_COMMUNES} écarte
     * s'ajoute Paris, qui est à lui seul son unique commune.
     */
    private const SANS_COMMUNES = [...DepartementController::SANS_COMMUNES, '75'];

    /**
     * La seule législative dont la fiche de ville détaille les résultats.
     *
     * L'application d'origine ne construit qu'un bloc, « Législatives 2024 »
     * (`City_model::get_results_elections_full()`), alors que sa table en porte
     * quatre — 2017, 2022 et les deux tours de 2024. C'est un choix éditorial :
     * la page montre la dernière élection, pas une archive. Les scrutins
     * antérieurs sont affichés ailleurs, sur la fiche de ville de `/deputes`.
     */
    private const ANNEE_RESULTATS = 2024;

    /**
     * Les dix-huit régions, du référentiel INSEE en vigueur depuis 2016.
     *
     * L'application d'origine tient une table `regions` que nous n'importons pas
     * : elle ne sert qu'ici, pour nommer la circonscription d'un candidat aux
     * régionales de 2021, et le découpage n'a pas bougé depuis. Les deux
     * collectivités à statut particulier (Corse) et les cinq d'outre-mer y
     * figurent, l'élection s'y étant tenue.
     */
    private const REGIONS = [
        '1' => 'Guadeloupe',
        '2' => 'Martinique',
        '3' => 'Guyane',
        '4' => 'La Réunion',
        '6' => 'Mayotte',
        '11' => 'Île-de-France',
        '24' => 'Centre-Val de Loire',
        '27' => 'Bourgogne-Franche-Comté',
        '28' => 'Normandie',
        '32' => 'Hauts-de-France',
        '44' => 'Grand Est',
        '52' => 'Pays de la Loire',
        '53' => 'Bretagne',
        '75' => 'Nouvelle-Aquitaine',
        '76' => 'Occitanie',
        '84' => 'Auvergne-Rhône-Alpes',
        '93' => "Provence-Alpes-Côte d'Azur",
        '94' => 'Corse',
    ];

    /**
     * Issue d'une candidature, traduite dans les noms de classes du site.
     *
     * Ces trois mots ne sont pas de l'affichage : ils sont écrits en dur dans
     * les valeurs des boutons radio du gabarit et lus par `sorting_select.js`
     * pour filtrer la grille. Les traduire casserait le filtre.
     */
    private const CLASSES_ETAT = [
        CandidatureRepository::ELU => 'elected',
        CandidatureRepository::BATTU => 'lost',
        CandidatureRepository::QUALIFIE => 'second',
    ];

    /**
     * Motif d'un slug de commune.
     *
     * Les parenthèses ne sont pas une coquetterie : vingt-quatre communes en
     * portent, le référentiel inversant leur article — « Assions (Les) » donne
     * `assions-(les)`, « Étoile (L') » donne `etoile-(l)`. Ce sont les slugs de
     * l'application d'origine, dont le routeur accepte n'importe quoi.
     *
     * datan.fr renvoie pourtant 400 sur ces adresses : les parenthèses brutes y
     * sont rejetées en amont du routeur (serveur web ou pare-feu applicatif), si
     * bien que les pages existent mais restent inatteignables. Nous faisons le
     * choix de les servir — le motif accepte les parenthèses, la donnée est là —
     * plutôt que de reproduire ce rejet d'infrastructure. Elles restent en
     * revanche hors des plans de site ({@see SitemapController::electionsCommunes()}) :
     * on n'annonce pas une adresse que la référence ne sert pas.
     *
     * Les majuscules sont acceptées puis redirigées, pour la même raison que
     * {@see DepartementController::SLUG} : le site sert `ville_Ajaccio` en 200,
     * et un segment de commune insensible à la casse serait sans effet si celui
     * du département l'était seul — les deux se suivent dans la même adresse.
     */
    public const SLUG_COMMUNE = '[a-zA-Z0-9()\-]+';

    /** Un député ne représente sa commune que s'il siège encore. */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = d.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    public function __construct(
        private readonly Connection $connection,
        private readonly ElectionRepository $elections,
        private readonly CandidatureRepository $candidatures,
        private readonly ResultatElectoralRepository $resultats,
    ) {
    }

    #[Route('/elections', name: 'elections_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->cache($this->render('election/index.html.twig', [
            'elections' => array_map($this->datee(...), $this->elections->toutes()),
            'departements' => $this->departements(),
            'communes' => $this->communesVedettes(),
            // Fil d'Ariane repris maillon par maillon de `Elections::index()` :
            // « Datan » (lien) puis « Élections » (maillon courant, actif).
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Élections', 'url' => $this->generateUrl('elections_index')],
            ],
        ]));
    }

    /**
     * Les deux routes de résultats sont déclarées avant celle d'un scrutin pour
     * que la lecture du fichier dise laquelle prime. Elles ne se disputent en
     * réalité rien : `{slug}` ne franchit pas de barre oblique, là où le
     * `(:any)` de `routes.php` avalait `resultats/paris-75` entier — c'est cette
     * différence, et non l'ordre, qui règle la collision que l'application
     * d'origine devait arbitrer à la main.
     */
    #[Route(
        '/elections/resultats/{departement}',
        name: 'elections_resultats_departement',
        requirements: ['departement' => DepartementController::SLUG],
        methods: ['GET'],
    )]
    public function resultatsDepartement(string $departement): Response
    {
        $ligne = $this->departement($departement);

        if ($ligne === null) {
            throw $this->createNotFoundException('Département sans page de résultats.');
        }

        // Avant le cas de Paris, pour que `Paris-75` rejoigne d'abord son
        // orthographe canonique et n'ait qu'une façon de rendre son 404
        // ({@see DepartementController::SLUG}).
        if ($departement !== $ligne['slug']) {
            return $this->redirectToRoute(
                'elections_resultats_departement',
                ['departement' => $ligne['slug']],
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        // Paris est sa propre commune : le site rend un 404 sur son département
        // et ne mène jamais qu'à `…/paris-75/ville_paris`.
        if ($departement === 'paris-75') {
            throw $this->createNotFoundException('Département sans page de résultats.');
        }

        $communes = $this->connection->fetchAllAssociative(
            // Regroupées par nom, comme le site : les deux Château-Chinon de la
            // Nièvre — Ville et Campagne — portent le même nom et le même slug,
            // et n'ont donc qu'une adresse à elles deux. Sans ce regroupement,
            // l'index alphabétique afficherait deux fois le même lien. La
            // population — qui décide d'un lien en clair ou masqué — est celle
            // de la plus peuplée du groupe.
            'SELECT nom, MIN(slug) AS slug, MAX(population) AS population FROM commune
             WHERE departement_id = :departement
             GROUP BY nom
             ORDER BY nom',
            ['departement' => $ligne['id']],
        );

        $phares = $this->connection->fetchAllAssociative(
            'SELECT nom, slug FROM commune
             WHERE departement_id = :departement AND population > :seuil
             ORDER BY population DESC, nom
             LIMIT ' . self::COMMUNES_PHARES,
            ['departement' => $ligne['id'], 'seuil' => self::POPULATION_MINIMALE],
        );

        return $this->cache($this->render('election/resultats_departement.html.twig', [
            'departement' => $ligne,
            'phares' => $phares,
            'lettres' => $this->parLettre($communes),
            'seuil' => self::POPULATION_MINIMALE,
            // Fil d'Ariane de `Elections::results_dpt()` : « Datan » › « Élections »
            // › « Nom (code) » du département, maillon courant.
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Élections', 'url' => $this->generateUrl('elections_index')],
                [
                    'nom' => $ligne['nom'] . ' (' . $ligne['code'] . ')',
                    'url' => $this->generateUrl('elections_resultats_departement', ['departement' => $departement]),
                ],
            ],
        ]));
    }

    #[Route(
        '/elections/resultats/{departement}/ville_{commune}',
        name: 'elections_resultats_commune',
        requirements: ['departement' => DepartementController::SLUG, 'commune' => self::SLUG_COMMUNE],
        methods: ['GET'],
    )]
    public function resultatsCommune(string $departement, string $commune): Response
    {
        $ville = $this->commune($departement, $commune);

        if ($ville === null) {
            throw $this->createNotFoundException('Commune inconnue.');
        }

        // Même règle qu'ailleurs : la casse trouvée en base fait foi
        // ({@see DepartementController::SLUG}).
        if ($departement !== $ville['dpt_slug'] || $commune !== $ville['slug']) {
            return $this->redirectToRoute(
                'elections_resultats_commune',
                ['departement' => $ville['dpt_slug'], 'commune' => $ville['slug']],
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        $circonscriptions = $this->circonscriptions((int) $ville['id']);

        // Fil d'Ariane de `Elections::results_city()`. Paris n'a pas de page de
        // résultats de département — elle rend 404 (cf. resultatsDepartement) —,
        // aussi le legacy saute alors le maillon du département et enchaîne
        // « Élections » › « Paris ».
        $fil = [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => 'Élections', 'url' => $this->generateUrl('elections_index')],
        ];
        if ($departement !== 'paris-75') {
            $fil[] = [
                'nom' => $ville['dpt_nom'] . ' (' . $ville['dpt_code'] . ')',
                'url' => $this->generateUrl('elections_resultats_departement', ['departement' => $departement]),
            ];
        }
        $fil[] = [
            'nom' => $ville['nom'],
            'url' => $this->generateUrl('elections_resultats_commune', ['departement' => $departement, 'commune' => $commune]),
        ];

        return $this->cache($this->render('election/resultats_commune.html.twig', [
            'ville' => $ville,
            // Divergence assumée : à cette adresse, le legacy lit les tables
            // `elect_bv_*` (grain bureau de vote), vides même en production — sa
            // page est donc creuse, cadrée sur des municipales 2026 hors de notre
            // périmètre. Nous servons à la place les résultats législatifs par
            // circonscription tirés de `resultat_legislative` : des chiffres
            // réels et utiles, que datan.fr n'affiche pas ici.
            'legislatives' => $this->circonscriptionsDeLElection(
                $this->resultats->legislativesParCommune((string) $ville['code_insee'])[self::ANNEE_RESULTATS] ?? [],
            ),
            'annee' => self::ANNEE_RESULTATS,
            'deputes' => $this->deputesDeLaCommune((string) $ville['dpt_code'], $circonscriptions),
            'voisines' => $this->voisines((int) $ville['id']),
            'communes' => \in_array($ville['dpt_code'], self::SANS_COMMUNES, true)
                ? []
                : $this->communesDuDepartement((int) $ville['dpt_id']),
            // Le lien « Voir la page commune » suit le seuil de la fiche de
            // ville de `/deputes`, pas celui des pages d'élections.
            'seuil_page_commune' => DepartementController::POPULATION_MINIMALE,
            'fil_ariane' => $fil,
        ]));
    }

    #[Route(
        '/elections/{slug}',
        name: 'elections_individual',
        requirements: ['slug' => '[a-z0-9\-]+'],
        methods: ['GET'],
    )]
    public function individual(string $slug): Response
    {
        $election = $this->elections->parSlug($slug);

        if ($election === null) {
            throw $this->createNotFoundException('Élection inconnue.');
        }

        $candidats = $this->candidats((int) $election['id'], (string) $election['libelle_abrege']);

        return $this->cache($this->render('election/individual.html.twig', [
            'election' => $this->datee($election),
            'candidats' => $candidats,
            'compteurs' => $this->candidatures->compteursParElection((int) $election['id']),
            'districts' => $this->districts((string) $election['libelle_abrege']),
            'groupes' => $this->groupesDesCandidats($candidats),
            'hemicycle' => $this->hemicycle($slug),
            'departements' => $this->departements(),
            'communes' => $this->communesVedettes(),
            // Fil d'Ariane de `Elections::individual()` : le troisième maillon
            // reprend son intitulé « libellé abrégé + année » (« Législatives
            // 2022 »), non le libellé complet du titre de page.
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Élections', 'url' => $this->generateUrl('elections_index')],
                [
                    'nom' => $election['libelle_abrege'] . ' ' . $election['annee'],
                    'url' => $this->generateUrl('elections_individual', ['slug' => $slug]),
                ],
            ],
        ]));
    }

    /**
     * Candidatures d'un scrutin, prêtes pour les cartes de députés.
     *
     * Le dépôt livre bien `CandidatureRepository::parElection()`, mais sans
     * l'identifiant d'acteur dont dépend la photographie, ni de quoi dire si le
     * député siège encore — les deux choses que la carte affiche. La requête est
     * donc reprise ici, ce que la convention du projet veut de toute façon pour
     * une page de lecture ; seule la règle d'issue reste au dépôt, pour n'être
     * écrite qu'une fois.
     *
     * Le groupe nommé en pied est le rattachement courant : un ancien député n'en
     * a pas et sa carte porte « Ancien député ».
     *
     * **Le liseré, lui, suit le dernier groupe connu.** La vue `candidate_full`
     * du site porte le groupe de la dernière législature du candidat, si bien
     * que ses anciens députés gardent leur couleur — Caroline Abadie reste
     * violette (RE, 16e). S'en tenir à `depute.groupe_id` la laisserait sans
     * liseré : la colonne ne porte que l'appartenance courante, et 66 des 102
     * candidats aux régionales de 2021 ne siègent plus. D'où la seconde
     * jointure, qui prend le rattachement le plus récent — encore ouvert
     * d'abord, puis date de fin la plus tardive — et porte l'alias `g` qu'attend
     * {@see CouleurGroupe::SQL}.
     *
     * @return list<array<string, mixed>>
     */
    private function candidats(int $election, string $type): array
    {
        $candidats = $this->connection->fetchAllAssociative(
            'SELECT c.district, c.position, c.nuance, c.candidat, c.second_tour, c.elu,
                    d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    d.departement_nom, d.departement_code,
                    (SELECT MAX(m.legislature) FROM mandat m WHERE m.depute_id = d.id) AS legislature_last,
                    ' . self::EN_EXERCICE . ' AS actif,
                    gc.libelle AS groupe_libelle, gc.libelle_abrev AS groupe_abrev,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur
             FROM candidature c
             JOIN depute d ON d.id = c.depute_id
             LEFT JOIN groupe gc ON gc.id = d.groupe_id
             LEFT JOIN groupe g ON g.id = (
                 SELECT fg.groupe_id FROM fonction_groupe fg
                 WHERE fg.depute_id = d.id AND fg.nomin_principale = 1
                 ORDER BY fg.date_fin IS NULL DESC, fg.date_fin DESC
                 LIMIT 1
             )
             WHERE c.election_id = :election AND c.visible = 1
             ORDER BY d.lastname, d.firstname',
            ['election' => $election, 'legislature' => Legislature::COURANTE],
        );

        $libelles = $this->libellesDeDistrict($type);

        foreach ($candidats as &$candidat) {
            $district = (string) ($candidat['district'] ?? '');
            // Recherche insensible à la casse : voir libellesDeDistrict(). La
            // Corse écrit son district « 2a »/« 2b » là où la table l'indexe
            // « 2A »/« 2B », et un lookup PHP brut la manquerait.
            $libelle = $libelles[mb_strtolower($district)] ?? null;

            $etat = CandidatureRepository::etat(
                $candidat['second_tour'] === null ? null : (bool) $candidat['second_tour'],
                $candidat['elu'] === null ? null : (bool) $candidat['elu'],
            );
            $candidat['etat'] = $etat === null ? null : self::CLASSES_ETAT[$etat];

            // Le site nomme la circonscription du candidat quand il sait la
            // traduire, et se rabat sinon sur le département où il a été élu.
            $nomme = $candidat['candidat'] && $libelle !== null;
            $candidat['circonscription'] = $nomme ? $libelle : null;
            // En casse canonique : `candidature.district` écrit la Corse
            // « 2a »/« 2b », mais on l'affiche « 2A »/« 2B » — comme le repli non
            // candidat de la carte voisine, tiré de `departement.code`, et comme
            // le nom déjà rendu « Haute-Corse ». Le legacy garde ses minuscules ;
            // l'écart de casse est notre correction, pas une incohérence.
            $candidat['district_id'] = $nomme ? mb_strtoupper($district) : $candidat['departement_code'];
        }

        return $candidats;
    }

    /**
     * Table de correspondance du district vers son libellé, selon la nature du
     * scrutin.
     *
     * `candidature.district` est polymorphe : identifiant de région aux
     * régionales, code de département aux législatives. Aux trois autres
     * scrutins, l'application d'origine ne sait pas le traduire et n'écrit rien
     * — la présidentielle et les européennes se jouent d'ailleurs sur une
     * circonscription unique, et la colonne y est vide.
     *
     * Chargée d'un bloc plutôt qu'interrogée par candidat : les législatives de
     * 2022 en comptent 601, et autant d'allers-retours vers la base pour
     * traduire 107 codes coûteraient bien plus que la table entière.
     *
     * @return array<string, string>
     */
    private function libellesDeDistrict(string $type): array
    {
        return match ($type) {
            'Régionales' => self::REGIONS,
            // Indexé en minuscules : `candidature.district` écrit la Corse
            // « 2a »/« 2b », mais `departement.code` l'écrit « 2A »/« 2B ». La
            // collation de MariaDB masquerait la différence dans une jointure ;
            // le lookup PHP qui suit, lui, est sensible à la casse. Sans cette
            // normalisation, les quatre candidats corses de 2022 tombent sur le
            // repli « nom de département » au lieu de « Candidat·e à Haute-Corse ».
            'Législatives' => $this->connection->fetchAllKeyValue('SELECT LOWER(code), nom FROM departement'),
            default => [],
        };
    }

    /**
     * Entrées du menu déroulant qui filtre la grille des candidats.
     *
     * Il n'existe que là où `candidature.district` se traduit : par région aux
     * régionales, par département aux législatives.
     *
     * @return list<array{id: string, libelle: string}>
     */
    private function districts(string $type): array
    {
        if ($type === 'Régionales') {
            $regions = self::REGIONS;
            asort($regions);

            // PHP retient les clés numériques comme des entiers, d'où le cast :
            // c'est une valeur d'attribut HTML qui est attendue.
            return array_map(
                static fn (int|string $id, string $libelle) => ['id' => (string) $id, 'libelle' => $libelle],
                array_keys($regions),
                $regions,
            );
        }

        if ($type !== 'Législatives') {
            return [];
        }

        return array_map(
            static fn (array $d) => ['id' => (string) $d['code'], 'libelle' => $d['code'] . ' - ' . $d['nom']],
            $this->connection->fetchAllAssociative('SELECT code, nom FROM departement ORDER BY code'),
        );
    }

    /**
     * Groupes représentés parmi les candidats, pour le second menu déroulant.
     *
     * Tirés de la grille elle-même et non de la table des groupes : un groupe
     * dont aucun candidat n'est membre ne filtrerait rien.
     *
     * @param list<array<string, mixed>> $candidats
     *
     * @return list<array<string, string>>
     */
    private function groupesDesCandidats(array $candidats): array
    {
        $groupes = [];

        foreach ($candidats as $candidat) {
            if ($candidat['groupe_abrev'] !== null) {
                $groupes[(string) $candidat['groupe_abrev']] = (string) $candidat['groupe_libelle'];
            }
        }

        asort($groupes);

        return array_map(
            static fn (string $abrev, string $libelle) => ['abrev' => $abrev, 'libelle' => $libelle],
            array_keys($groupes),
            $groupes,
        );
    }

    /**
     * Résultats d'une législative dans une commune, rangés par circonscription
     * puis par tour.
     *
     * Le dépôt les rend par tour puis par circonscription ; la page les présente
     * dans l'autre sens — un onglet par circonscription, deux tours dedans —
     * parce qu'une commande à cheval sur plusieurs circonscriptions n'a pas de
     * résultat global : additionner ses bureaux donnerait un total qui n'a été
     * soumis à personne.
     *
     * @param array<int, array<string, array<string, mixed>>> $tours
     *
     * @return list<array<string, mixed>>
     */
    private function circonscriptionsDeLElection(array $tours): array
    {
        $circonscriptions = [];

        foreach ($tours as $tour => $blocs) {
            foreach ($blocs as $bloc) {
                $numero = (int) $bloc['circonscription'];
                $circonscriptions[$numero]['circonscription'] = $numero;
                $circonscriptions[$numero]['tours'][(int) $tour] = $bloc;
            }
        }

        ksort($circonscriptions, SORT_NUMERIC);

        return array_values($circonscriptions);
    }

    /**
     * Communes rangées par initiale, pour l'index alphabétique d'un département.
     *
     * L'initiale garde son accent : « Ébreuil » se range sous « É », comme sur
     * le site, et non sous « E ».
     *
     * @param list<array<string, mixed>> $communes
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function parLettre(array $communes): array
    {
        $lettres = [];

        foreach ($communes as $commune) {
            $lettres[mb_strtoupper(mb_substr((string) $commune['nom'], 0, 1))][] = $commune;
        }

        ksort($lettres);

        return $lettres;
    }

    /** @return array<string, mixed>|null */
    private function departement(string $slug): ?array
    {
        $ligne = $this->connection->fetchAssociative(
            'SELECT id, code, nom, slug, region, libelle_de, libelle_dans FROM departement WHERE slug = ?',
            [$slug],
        );

        return $ligne === false ? null : $ligne;
    }

    /**
     * Même lecture que {@see CommuneController::commune()} : le doublon de slug
     * de Château-Chinon s'y arbitre par la population, et les deux pages doivent
     * servir la même commune sous la même adresse.
     *
     * @return array<string, mixed>|null
     */
    private function commune(string $departement, string $commune): ?array
    {
        $ligne = $this->connection->fetchAssociative(
            'SELECT c.id, c.code_insee, c.nom, c.slug, c.population,
                    c.maire_prenom, c.maire_nom, c.maire_civilite,
                    d.id AS dpt_id, d.code AS dpt_code, d.nom AS dpt_nom, d.slug AS dpt_slug,
                    d.libelle_de, d.libelle_dans
             FROM commune c
             JOIN departement d ON d.id = c.departement_id
             WHERE c.slug = :commune AND d.slug = :departement
             ORDER BY c.population DESC
             LIMIT 1',
            ['commune' => $commune, 'departement' => $departement],
        );

        return $ligne === false ? null : $ligne;
    }

    /**
     * @return list<int>
     */
    private function circonscriptions(int $commune): array
    {
        return array_map('intval', $this->connection->fetchFirstColumn(
            // Ordre numérique : la colonne de l'application d'origine est du
            // texte, ce qui range les circonscriptions de Paris 1, 10, 11 … 2.
            'SELECT circonscription FROM commune_circonscription WHERE commune_id = ? ORDER BY circonscription',
            [$commune],
        ));
    }

    /**
     * Députés en exercice des circonscriptions que la commune recouvre.
     *
     * @param list<int> $circonscriptions
     *
     * @return list<array<string, mixed>>
     */
    private function deputesDeLaCommune(string $code, array $circonscriptions): array
    {
        if ($circonscriptions === []) {
            return [];
        }

        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname, d.lastname, d.slug, d.dpt_slug, d.circonscription,
                    g.libelle_abrev AS groupe_abrev, ' . CouleurGroupe::SQL . ' AS groupe_couleur
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE d.departement_code = :code
               AND d.circonscription IN (:circonscriptions)
               AND ' . self::EN_EXERCICE . '
             ORDER BY d.circonscription',
            ['code' => $code, 'circonscriptions' => $circonscriptions, 'legislature' => Legislature::COURANTE],
            ['circonscriptions' => ArrayParameterType::INTEGER],
        );
    }

    /**
     * Communes limitrophes, de la plus peuplée à la plus petite.
     *
     * Toutes, sans le plafond de quatre que pose la fiche de ville de
     * `/deputes` : ici elles occupent une colonne entière et non un bandeau.
     *
     * @return list<array<string, mixed>>
     */
    private function voisines(int $commune): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT v.nom, v.slug, d.slug AS dpt_slug
             FROM commune_adjacente a
             JOIN commune v ON v.id = a.adjacente_id
             JOIN departement d ON d.id = v.departement_id
             WHERE a.commune_id = ?
             ORDER BY v.population DESC',
            [$commune],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function communesDuDepartement(int $departement): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT nom, slug FROM commune
             WHERE departement_id = :departement AND population > :seuil
             ORDER BY population DESC, nom
             LIMIT ' . self::COMMUNES_VEDETTES,
            ['departement' => $departement, 'seuil' => self::POPULATION_MINIMALE],
        );
    }

    /**
     * Les plus grandes communes de France, pour les liens de pied de page.
     *
     * Le site regroupe ces communes par nom (`GROUP BY commune_nom`) : plusieurs
     * en partageant un, le groupe est alors classé sur une population
     * indéterminée. « Saint-Denis » — la Réunion (154 765 hab) et la
     * Seine-Saint-Denis (113 942) — se range ainsi sur la moindre et tombe hors
     * des trente premières. Nous classons chaque commune sur sa propre
     * population : Saint-Denis (Réunion) reprend son 20e rang, et
     * Boulogne-Billancourt bascule au 31e. Divergence assumée, la nôtre étant la
     * plus juste — deux homonymes sont deux communes, pas une.
     *
     * @return list<array<string, mixed>>
     */
    private function communesVedettes(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT c.nom, c.slug, d.slug AS dpt_slug
             FROM commune c
             JOIN departement d ON d.id = c.departement_id
             ORDER BY c.population DESC, c.nom
             LIMIT ' . self::COMMUNES_VEDETTES,
        );
    }

    /**
     * Répartition des sièges commentée par la page d'un scrutin, pour les trois
     * élections dont le site publie un graphique.
     *
     * Ces effectifs ne sont pas les nôtres : ils recomposent l'Assemblée en
     * coalitions — « Nouveau Front Populaire », « Ensemble » — que l'Assemblée
     * ne déclare pas. C'est une lecture de presse, créditée comme telle sous le
     * graphique, et elle ne se recalcule pas depuis `groupe`.
     *
     * L'application d'origine va les chercher en HTTP sur son serveur d'actifs à
     * chaque affichage (`file_get_contents(asset_url() . …)`). Le fichier est le
     * même, lu sur le disque.
     *
     * @return list<array<string, mixed>>
     */
    private function hemicycle(string $slug): array
    {
        // Les législatives de 2024 ont deux jeux, publiés au fil du rattachement
        // des élus à un groupe ; le site sert le second.
        $fichier = $this->getParameter('kernel.project_dir')
            . '/public/assets/data_elections/'
            . ($slug === 'legislatives-2024' ? 'legislatives-2024-2' : $slug)
            . '.json';

        if (!is_file($fichier)) {
            return [];
        }

        return json_decode((string) file_get_contents($fichier), true) ?: [];
    }

    /**
     * Dates du scrutin telles que le site les écrit : « 09 juin 2024 » sur la
     * page d'un scrutin, « 09 juin » sur les cartes du catalogue.
     *
     * L'application d'origine les met en forme dans son SQL — `date_format(…,
     * "%d %M %Y")` — et doit pour cela poser `SET lc_time_names='fr_FR'` à
     * chaque requête (`Language_sql`, chargé d'office). Le quantième garde son
     * zéro de tête, ce que fait `%d` et que ne fait pas le filtre `date_fr` du
     * projet ; c'est la seule raison de ne pas s'en servir ici.
     *
     * @param array<string, mixed> $election
     *
     * @return array<string, mixed>
     */
    private function datee(array $election): array
    {
        foreach (['date_tour1', 'date_tour2'] as $tour) {
            $election[$tour . '_fr'] = $this->enFrancais($election[$tour], 'dd MMMM y');
            $election[$tour . '_court'] = $this->enFrancais($election[$tour], 'dd MMMM');
        }

        return $election;
    }

    private function enFrancais(?string $date, string $motif): ?string
    {
        if ($date === null) {
            return null;
        }

        return (new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            pattern: $motif,
        ))->format(new \DateTimeImmutable($date)) ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function departements(): array
    {
        // Tri de l'application d'origine : `ORDER BY departement_code` sur une
        // colonne de texte. « 099 » tombe entre l'Ariège et l'Aube.
        return $this->connection->fetchAllAssociative('SELECT code, nom, slug FROM departement ORDER BY code');
    }

    private function cache(Response $response): Response
    {
        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }
}
