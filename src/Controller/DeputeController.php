<?php

namespace App\Controller;

use App\Depute\ComportementDepute;
use App\Depute\GroupeAuScrutin;
use App\Http\RechauffeurCache;
use App\Legislature;
use App\Region;
use App\Referencement\OpenGraph;
use App\Repository\ResultatCirconscriptionRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages publiques des députés, portées depuis le contrôleur Deputes de
 * l'application CodeIgniter d'origine.
 */
class DeputeController extends AbstractController
{
    private const OFFICIAL = 'decompteNominatif';

    /** Code des scrutins publics solennels, sur lesquels se calcule la participation. */
    private const SOLENNEL = 'SPS';

    /** Les données d'un député ne bougent qu'au rythme des scrutins, et la synchronisation purge le cache. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** Rang du mandat en toutes lettres (`Depute_edito::get_nbr_lettre`) ; au-delà de 5, le chiffre. */
    private const ORDINAUX = [1 => 'premier', 2 => 'deuxième', 3 => 'troisième', 4 => 'quatrième', 5 => 'cinquième'];

    /**
     * Mise en forme du nom du candidat parrainé (`Parrainages_model::change_candidate_name`) :
     * la table `parrainage` stocke le nom en capitales façon état civil
     * (« MACRON Emmanuel »), le site l'affiche en casse de lecture. Reprise à
     * l'identique — « Gaspar Koenig » compris, orthographe de la source.
     */
    private const PARRAINAGE_CANDIDATS = [
        'ARTHAUD Nathalie' => 'Nathalie Arthaud',
        'ASSELINEAU François' => 'François Asselineau',
        'DUPONT-AIGNAN Nicolas' => 'Nicolas Dupont-Aignan',
        'HIDALGO Anne' => 'Anne Hidalgo',
        'JADOT Yannick' => 'Yannick Jadot',
        'KAZIB Anasse' => 'Anasse Kazib',
        'KUZMANOVIC Georges' => 'Georges Kuzmanovic',
        'LASSALLE Jean' => 'Jean Lassalle',
        'LE PEN Marine' => 'Marine Le Pen',
        'MACRON Emmanuel' => 'Emmanuel Macron',
        'MÉLENCHON Jean-Luc' => 'Jean-Luc Mélenchon',
        'PÉCRESSE Valérie' => 'Valérie Pécresse',
        'POUTOU Philippe' => 'Philippe Poutou',
        'ROUSSEL Fabien' => 'Fabien Roussel',
        'THOUY Hélène' => 'Hélène Thouy',
        'ZEMMOUR Éric' => 'Éric Zemmour',
        'TAUBIRA Christiane' => 'Christiane Taubira',
        'KOENIG Gaspard' => 'Gaspar Koenig',
        'HERROU Cédric' => 'Cédric Herrou',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly ResultatCirconscriptionRepository $resultatsCirconscription,
        private readonly OpenGraph $openGraph,
        private readonly ComportementDepute $comportement,
    ) {
    }

    /**
     * L'URL reprend celle de l'application d'origine (/deputes/nord-59/depute_ugo-bernalicis)
     * pour ne pas casser les liens existants ni le référencement.
     */
    #[Route('/deputes/{dptSlug}/depute_{slug}', name: 'depute_individual', requirements: ['dptSlug' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function individual(string $dptSlug, string $slug): Response
    {
        // Un slug n'est pas unique : le député Jean-Louis Masson (Var) partage
        // le sien avec un sénateur homonyme du dépôt d'acteurs, sans page. À
        // slug égal, la ligne qui a un dpt_slug — donc une fiche — gagne, sans
        // quoi le LIMIT 1 peut tirer l'homonyme et rendre un 404 que le plan
        // des députés annonce pourtant en 200. Même parade dans depute().
        $depute = $this->connection->fetchAssociative(
            'SELECT d.id, d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite, d.age,
                    d.date_naissance, d.ville_naissance,
                    d.date_fin, d.cause_fin, d.departement_nom, d.departement_code,
                    d.circonscription, d.region, d.place_hemicycle, d.profession, d.commission,
                    g.id AS groupe_id, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    g.couleur AS groupe_couleur, g.position_politique,
                    p.libelle AS parti_libelle, p.libelle_abrev AS parti_abrev,
                    dep.libelle_de
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             LEFT JOIN parti p ON p.id = d.parti_id
             LEFT JOIN departement dep ON dep.code = d.departement_code
             WHERE d.slug = :slug
             ORDER BY (d.dpt_slug IS NULL), d.id
             LIMIT 1',
            ['slug' => $slug],
        );

        if ($depute === false) {
            throw $this->createNotFoundException('Député introuvable.');
        }

        // Sans dpt_slug, pas de page : ces lignes viennent du dépôt d'acteurs des
        // Tricoteuses, qui couvre plus que l'Assemblée — sénateurs, membres du
        // gouvernement — et aucune n'a de mandat de député chez nous. L'application
        // d'origine ne publie que les acteurs de sa table `deputes_last` : servir
        // ceux-ci, à n'importe quelle adresse de département faute de forme
        // canonique, fabriquerait du contenu dupliqué qu'elle n'a jamais eu.
        if ($depute['dpt_slug'] === null) {
            throw $this->createNotFoundException('Acteur sans mandat de député : pas de fiche publiée.');
        }

        // URL canonique : on redirige si le département de l'URL n'est pas le bon.
        if ($depute['dpt_slug'] !== $dptSlug) {
            return $this->redirectToRoute('depute_individual', [
                'dptSlug' => $depute['dpt_slug'],
                'slug' => $depute['slug'],
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        $deputeId = (int) $depute['id'];

        // `depute.groupe_id` ne porte que l'appartenance COURANTE : un ancien
        // député n'en a aucune (cf. CLAUDE.md), et sa fiche perdait alors son
        // liseré, le groupe de sa carte de profil, la phrase « a siégé avec le
        // groupe … » et les moyennes de groupe de son comportement. Le site,
        // lui, garde le dernier groupe connu dans `deputes_last.groupeId`. On
        // le reconstitue par le rattachement principal le plus récent — même
        // règle que {@see ElectionController::candidats}, et que le legacy
        // (daily.php:914) : encore ouvert d'abord, puis fin la plus tardive.
        if ($depute['groupe_id'] === null) {
            $depute = $this->dernierGroupe($deputeId) + $depute;
        }
        $groupeId = $depute['groupe_id'] !== null ? (int) $depute['groupe_id'] : null;

        $mandats = $this->mandats($deputeId);

        // Le statut se déduit des mandats, pas de depute.date_fin : cette colonne
        // vient de la migration initiale et n'est pas rafraîchie lors des réélections.
        // Un mandat sans date de fin signifie que le député est en exercice.
        $actif = $mandats !== [] && $mandats[0]['date_fin'] === null;

        // Bloc « Son élection » : le résultat de la circonscription pour le mandat
        // le plus récent — sa législature en fixe l'année, son département et sa
        // circonscription la clé. Le nom lève l'ambiguïté quand la circonscription
        // a connu une élection partielle.
        // La fiche décrit la DERNIÈRE législature du député, comme le legacy
        // (`deputes_last.legislature`, statistics/_index.php) : un ancien député
        // de la 14e garde son comportement politique de la 14e, titre suffixé et
        // phrases au passé. Figée sur la législature courante, la fiche de tout
        // député sorti avant 2024 perdait le bloc entier. La page
        // /legislature-N renvoie d'ailleurs ici pour cette législature-là.
        $legislatureFiche = $mandats !== [] ? (int) $mandats[0]['legislature'] : Legislature::COURANTE;

        $election = $mandats !== []
            ? $this->resultatsCirconscription->pourDepute(
                $mandats[0]['departement_code'],
                $mandats[0]['circonscription'] !== null ? (int) $mandats[0]['circonscription'] : null,
                (int) $mandats[0]['legislature'],
                (string) $depute['lastname'],
            )
            : null;

        $response = $this->render('depute/individual.html.twig', [
            'depute' => $depute,
            'actif' => $actif,
            // Positionnement gauche/centre/droite du groupe courant sur l'échiquier,
            // pour la phrase « … un groupe classé à gauche de l'échiquier politique »
            // du bloc biographique. Même table éditoriale que la carte de proximité.
            'echiquier' => $depute['groupe_abrev'] !== null ? (ComportementDepute::ECHIQUIER[$depute['groupe_abrev']] ?? null) : null,
            // Le bloc « Son comportement politique » : participation, loyauté et
            // proximité par groupe, lues sur les tables précalculées (les moyennes
            // comparatives interdisent tout calcul à la requête), pour la dernière
            // législature du député. Nul sans votes nominatifs (avant la 14e).
            // Lecture partagée avec l'iframe via le service ComportementDepute.
            'statistiques' => $this->comportement->statistiques($deputeId, $groupeId, $legislatureFiche),
            // Hors législature courante, le bloc passe en mode historique, comme
            // sur /legislature-N : voir depute/_statistiques.html.twig.
            'legislature_stats' => $legislatureFiche !== Legislature::COURANTE ? $legislatureFiche : null,
            // Le carrousel « Ses derniers votes » de la fiche montre les cinq
            // derniers scrutins DÉCRYPTÉS où le député s'est exprimé (avec sa
            // position et son éventuelle explication), comme datan.fr — et non
            // les dix derniers votes bruts. La liste complète reste sur /votes.
            'carrousel_votes' => $this->comportement->votesDecryptes($deputeId, 5),
            // Sélection éditoriale figée de scrutins marquants (« Ses positions
            // importantes »), et non les votes décryptés génériques : cf.
            // ComportementDepute::positionsImportantes.
            'positions_importantes' => $this->comportement->positionsImportantes($deputeId),
            // « né le 25 septembre 1989 à Arras » du paragraphe d'ouverture — jour
            // sur deux chiffres, comme le strftime('%d %B %Y') du legacy.
            'naissance' => $this->naissance($depute),
            // « Il a quitté l'Assemblée nationale le 9 avril 2026 … » : date de
            // fin du dernier mandat et motif en toutes lettres, pour une fiche
            // d'ancien député (`_bio.php:45-52`).
            'fin_mandat' => $actif || $mandats === [] || $mandats[0]['date_fin'] === null ? null : [
                // Jour sur deux chiffres (« 09 avril 2026 »), comme le
                // `date_format(…, '%d %M %Y')` du legacy et la date de
                // naissance juste au-dessus — et non le « 9 avril » de
                // `date_fr`, qui sert les dates de scrutin.
                'date' => (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'dd MMMM y'))
                    ->format(new \DateTimeImmutable((string) $mandats[0]['date_fin'])),
                'motif' => $this->motifDeFin((string) $depute['cause_fin'], $depute),
            ],
            // Encart « Municipales 2026 » en tête de fiche (`electionFeature.php`) :
            // la candidature du député, quel que soit son sort — y compris le
            // « n'est pas candidat » vérifié par la rédaction.
            'election_feature' => $this->electionMunicipales($deputeId),
            // « Ses participations électorales » et « Ses professions de foi »
            // (`_elections_participation.php`, `_manifesto.php`).
            'participations' => $this->participationsElectorales($deputeId),
            'professions_foi' => $this->professionsFoi((string) $depute['mp_id']),
            // « Sa dernière explication de vote » (`_explanation.php`).
            'derniere_explication' => $this->derniereExplication($deputeId),
            // Encart « dernier vote important » en tête de fiche (`voteFeature.php`) :
            // la position du député sur le scrutin phare figé par le legacy.
            'vote_feature' => $this->voteFeature($deputeId),
            // Deux phrases du bloc biographique (`_bio.php`) : la commission
            // parlementaire du député, et son entrée en fonction / ancienneté.
            'commission' => $this->commission($deputeId, $depute['commission']),
            'anciennete' => $this->anciennete($deputeId),
            'mandats' => $mandats,
            'election' => $election,
            // Le parrainage présidentiel 2022 donné par le député, quand il siégeait
            // (bloc « Ses parrainages présidentiels »). Nul pour la plupart.
            'parrainage' => $this->parrainage((string) $depute['mp_id']),
            // Pied de fiche « Les autres députés … » : ceux du groupe (député en
            // exercice), de la législature (fiche d'un ancien) ou plus en activité
            // (sortant), et ceux du département — comme `_other_mps.php`.
            'autres_deputes' => $this->autresDeputes($depute, $mandats, $actif),
            'contact' => $this->contact($deputeId),
            'ogp' => $this->openGraph->pourDepute($depute, $actif),
            'fil_ariane' => $this->filAriane($depute),
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Tous les votes d'un député : les décryptés en cartes, puis l'intégralité
     * des scrutins où il s'est exprimé.
     */
    #[Route('/deputes/{dptSlug}/depute_{slug}/votes', name: 'depute_votes', requirements: ['dptSlug' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function votes(string $dptSlug, string $slug): Response
    {
        $depute = $this->depute($slug);

        if ($depute['dpt_slug'] !== $dptSlug) {
            return $this->redirectToRoute('depute_votes', [
                'dptSlug' => $depute['dpt_slug'],
                'slug' => $depute['slug'],
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        $deputeId = (int) $depute['id'];
        $decryptes = $this->comportement->votesDecryptes($deputeId);

        $categories = [];
        foreach ($decryptes as $vote) {
            if ($vote['categorie_slug'] !== null) {
                $categories[$vote['categorie_slug']] = $vote['categorie_name'];
            }
        }
        asort($categories);

        $mandats = $this->mandats($deputeId);
        $actif = $mandats !== [] && $mandats[0]['date_fin'] === null;

        $response = $this->render('depute/votes.html.twig', [
            'depute' => $depute,
            'actif' => $actif,
            'mandats' => $mandats,
            'decryptes' => $decryptes,
            'categories' => $categories,
            'tous_les_votes' => $this->tousLesVotes($deputeId),
            // Même carte de profil que la fiche (card_individual, titre en span)
            // et mêmes listes de pied de page — `deputes/votes.php` les rend aussi.
            'autres_deputes' => $this->autresDeputes($depute, $mandats, $actif),
            'fil_ariane' => [...$this->filAriane($depute),
                ['nom' => 'Votes', 'url' => $this->generateUrl('depute_votes', ['dptSlug' => $depute['dpt_slug'], 'slug' => $depute['slug']])],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Le député tel qu'il siégeait à une législature passée.
     *
     * Sa circonscription, son département et son groupe ont pu changer d'une
     * législature à l'autre : c'est le mandat de l'époque qui fait foi, pas la
     * fiche d'aujourd'hui. L'adresse de la dernière législature redirige vers la
     * fiche principale, qui dit déjà la même chose.
     */
    #[Route('/deputes/{dptSlug}/depute_{slug}/legislature-{legislature}', name: 'depute_legislature', requirements: ['dptSlug' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+', 'legislature' => '\d+'], methods: ['GET'])]
    public function legislature(string $dptSlug, string $slug, int $legislature): Response
    {
        // Le site ne publie rien avant la 14e : en deçà, l'application d'origine
        // répond 404 même si nos mandats remontent plus loin.
        if ($legislature < Legislature::PREMIERE) {
            throw $this->createNotFoundException('Législature inconnue.');
        }

        $depute = $this->depute($slug);
        $deputeId = (int) $depute['id'];
        $mandats = $this->mandats($deputeId);

        $mandat = null;
        foreach ($mandats as $ligne) {
            if ((int) $ligne['legislature'] === $legislature) {
                $mandat = $ligne;
                break;
            }
        }

        if ($mandat === null) {
            throw $this->createNotFoundException(sprintf('Ce député n\'a pas siégé en %de législature.', $legislature));
        }

        if ($mandats !== [] && (int) $mandats[0]['legislature'] === $legislature) {
            return $this->redirectToRoute('depute_individual', [
                'dptSlug' => $depute['dpt_slug'],
                'slug' => $depute['slug'],
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        $groupe = $this->groupeALegislature($deputeId, $legislature);
        $actif = $mandats !== [] && $mandats[0]['date_fin'] === null;

        // L'élection du mandat consulté (celle de 2022 pour une page de la 16e).
        $election = $this->resultatsCirconscription->pourDepute(
            $mandat['departement_code'],
            $mandat['circonscription'] !== null ? (int) $mandat['circonscription'] : null,
            $legislature,
            (string) $depute['lastname'],
        );

        $response = $this->render('depute/legislature.html.twig', [
            'depute' => $depute,
            'actif' => $actif,
            'mandat' => $mandat,
            'mandats' => $mandats,
            'legislature' => $legislature,
            'groupe' => $groupe,
            'election' => $election,
            // Le bloc « Son comportement politique » de la législature
            // consultée : servi depuis l'import des votes nominatifs 14-16
            // (4 août 2026) sur les lignes `statistique_depute` de cette
            // législature, moyennes de groupe comprises — le groupe est celui
            // de l'époque. Nul (bloc absent) si le député n'y a pas de ligne.
            'statistiques' => $this->comportement->statistiques(
                $deputeId,
                $groupe !== null ? (int) $groupe['id'] : null,
                $legislature,
            ),
            'naissance' => $this->naissance($depute),
            'anciennete' => $this->anciennete($deputeId),
            'contact' => $this->contact($deputeId),
            // La carte porte le groupe de l'époque, celui que la page affiche —
            // l'origine y met le dernier groupe connu, mais `depute.groupe_id`
            // ne porte que l'appartenance courante, vide pour un ancien député.
            'ogp' => $this->openGraph->pourDepute(
                ['groupe_libelle' => $groupe['libelle'] ?? null, 'groupe_couleur' => $groupe['couleur'] ?? null] + $depute,
                $mandats !== [] && $mandats[0]['date_fin'] === null,
            ),
            // « législature » : l'origine affiche « Historique 16e legislature »,
            // sans accent — coquille, pas choix.
            'fil_ariane' => [...$this->filAriane($depute),
                ['nom' => \sprintf('Historique %de législature', $legislature), 'url' => $this->generateUrl('depute_legislature', ['dptSlug' => $depute['dpt_slug'], 'slug' => $depute['slug'], 'legislature' => $legislature])],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Motif de fin de mandat en toutes lettres (`Depute_edito::get_end_mandate`),
     * accolé à la phrase « a quitté l'Assemblée nationale le … ».
     *
     * **Deux branches du legacy oublient leur `return`** — démission et
     * annulation de l'élection : sa fonction rend `null`, et le site publie
     * « … le 09 avril 2026 . », motif manquant et espace avant le point. Ce
     * sont les deux motifs les plus fréquents après la fin de législature (56 +
     * 43 + 14 fiches). Le `return` est rétabli ici : c'est un oubli d'écriture,
     * pas une intention éditoriale — sans quoi les deux chaînes ne seraient pas
     * dans le code.
     *
     * L'ordre des tests est celui de l'origine : « Démission d'office sur
     * décision du Conseil constitutionnel » avant le « Démission » générique,
     * qui l'attraperait sinon.
     *
     * @param array<string, mixed> $depute
     */
    private function motifDeFin(string $cause, array $depute): ?string
    {
        return match (true) {
            str_contains($cause, 'Nomination comme membre du Gouvernement') => 'pour cause de nomination au Gouvernement',
            str_contains($cause, 'Décès') => 'pour cause de décès',
            str_contains($cause, "Démission d'office sur décision du Conseil constitutionnel") => 'pour cause de démission sur décision du Conseil constitutionnel',
            str_contains($cause, 'Démission') => 'pour cause de démission',
            str_contains($cause, "Annulation de l'élection sur décision du Conseil constitutionnel") => "pour cause d'annulation de l'élection sur décision du Conseil constitutionnel",
            str_contains($cause, "Reprise de l'exercice du mandat d'un ancien membre du Gouvernement") => sprintf(
                ". Remplaçant un député nommé au Gouvernement, %s %s a quitté l'Assemblée lorsque celui-ci est redevenu député",
                $depute['firstname'],
                $depute['lastname'],
            ),
            default => null,
        };
    }

    /**
     * Le dernier groupe connu d'un député qui n'a plus de rattachement ouvert,
     * dans la forme des colonnes `groupe_*` de la requête de fiche.
     *
     * Le rattachement le plus récent et PRINCIPAL : onze députés de la 17e en
     * portent deux ouverts à la fois, et sans `nomin_principale` le tri
     * choisirait au hasard entre les deux (cf. CLAUDE.md).
     *
     * @return array<string, mixed>
     */
    private function dernierGroupe(int $deputeId): array
    {
        $groupe = $this->connection->fetchAssociative(
            'SELECT g.id AS groupe_id, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    g.couleur AS groupe_couleur, g.position_politique
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id
             WHERE fg.depute_id = :depute AND fg.nomin_principale = 1
             ORDER BY fg.date_fin IS NULL DESC, fg.date_fin DESC, fg.date_debut DESC
             LIMIT 1',
            ['depute' => $deputeId],
        );

        return $groupe === false ? [] : $groupe;
    }

    /**
     * Le groupe du député pendant une législature donnée, lu sur
     * `fonction_groupe` : `depute.groupe_id` ne porte que l'appartenance
     * courante et donnerait le groupe d'aujourd'hui, voire aucun.
     *
     * @return array<string, mixed>|null
     */
    private function groupeALegislature(int $deputeId, int $legislature): ?array
    {
        $groupe = $this->connection->fetchAssociative(
            'SELECT g.id, g.libelle, g.libelle_abrev, g.couleur, g.legislature
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id
             WHERE fg.depute_id = :depute AND g.legislature = :legislature AND fg.nomin_principale = 1
             ORDER BY fg.date_fin IS NULL DESC, fg.date_fin DESC, fg.date_debut DESC
             LIMIT 1',
            ['depute' => $deputeId, 'legislature' => $legislature],
        );

        return $groupe === false ? null : $groupe;
    }

    /**
     * Tronc commun du fil d'Ariane des pages d'un député : Datan, Députés, le
     * département, puis l'initiale et le nom (« U. Bernalicis »), comme le
     * `title_breadcrumb` de l'origine. Les sous-pages y accolent leur maillon.
     *
     * @param array<string, mixed> $depute
     *
     * @return list<array{nom: string, url: string}>
     */
    private function filAriane(array $depute): array
    {
        return [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => 'Députés', 'url' => $this->generateUrl('deputes_index')],
            [
                'nom' => $depute['departement_nom'] . ' (' . $depute['departement_code'] . ')',
                'url' => $this->generateUrl('departement_individual', ['departement' => $depute['dpt_slug']]),
            ],
            [
                'nom' => mb_substr((string) $depute['firstname'], 0, 1) . '. ' . $depute['lastname'],
                'url' => $this->generateUrl('depute_individual', ['dptSlug' => $depute['dpt_slug'], 'slug' => $depute['slug']]),
            ],
        ];
    }

    /**
     * La plus récente explication de vote publiée par le député
     * (`Deputes_model::get_last_explication`) : jointe au décryptage pour le
     * titre, au scrutin pour la date et le lien, et au vote du député pour le
     * badge de position — une explication sans ligne de vote se dit « absent »,
     * comme le legacy.
     *
     * @return array<string, mixed>|null
     */
    private function derniereExplication(int $deputeId): ?array
    {
        $ligne = $this->connection->fetchAssociative(
            "SELECT e.texte, dcr.title, dcr.legislature, dcr.vote_numero,
                    s.date_scrutin, v.position
             FROM explication e
             JOIN scrutin s ON s.id = e.scrutin_id
             JOIN decryptage dcr ON dcr.scrutin_id = e.scrutin_id
             LEFT JOIN vote v ON v.scrutin_id = e.scrutin_id AND v.depute_id = e.depute_id
               AND v.vote_type = :type
             WHERE e.depute_id = :depute AND e.publiee = 1
             ORDER BY e.id DESC
             LIMIT 1",
            ['depute' => $deputeId, 'type' => self::OFFICIAL],
        );

        if ($ligne === false) {
            return null;
        }

        // « Scrutin du 05 décembre 2025 » : jour sur deux chiffres, comme le
        // date_format(%d %M %Y) de l'origine.
        $ligne['date_fr'] = (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'dd MMMM y'))
            ->format(new \DateTimeImmutable((string) $ligne['date_scrutin']));

        return $ligne;
    }

    /**
     * Date et ville de naissance pour le paragraphe d'ouverture de la bio
     * (`_bio.php` : « né le 25 septembre 1989 à Arras »). Le legacy formate en
     * `strftime('%d %B %Y')` : jour sur deux chiffres, mois en toutes lettres.
     *
     * @param array<string, mixed> $depute
     *
     * @return array{date: string, ville: ?string}|null
     */
    private function naissance(array $depute): ?array
    {
        if (empty($depute['date_naissance'])) {
            return null;
        }

        $date = (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'dd MMMM y'))
            ->format(new \DateTimeImmutable((string) $depute['date_naissance']));

        return ['date' => $date, 'ville' => $depute['ville_naissance'] ?? null];
    }

    /**
     * Les candidatures du député à des élections pendant son mandat, pour le bloc
     * « Ses participations électorales » (`Elections_model::get_candidate_elections`
     * avec `visible = 1` et `candidature = 1`, tri par année décroissante).
     *
     * Le libellé de circonscription suit `get_district`, qui branche sur la
     * sorte d'élection — la colonne est polymorphe (cf. {@see \App\Entity\Candidature}) :
     * le nom du département pour des législatives, celui de la commune pour des
     * municipales, celui de la région pour des régionales ; à défaut, le district
     * brut. Les régionales passaient jusqu'ici par ce repli et affichaient le
     * code INSEE de la région (« 32 » pour les Hauts-de-France).
     *
     * @return list<array<string, mixed>>
     */
    private function participationsElectorales(int $deputeId): array
    {
        $participations = $this->connection->fetchAllAssociative(
            "SELECT e.annee, e.libelle, e.libelle_abrege, c.elu,
                    COALESCE(dep.nom, co.nom, c.district) AS district
             FROM candidature c
             JOIN election e ON e.id = c.election_id
             LEFT JOIN departement dep ON e.libelle_abrege = 'Législatives' AND dep.code = c.district
             LEFT JOIN commune co ON e.libelle_abrege = 'Municipales' AND co.code_insee = c.district
             WHERE c.depute_id = :depute AND c.visible = 1 AND c.candidat = 1
             ORDER BY e.annee DESC",
            ['depute' => $deputeId],
        );

        foreach ($participations as &$participation) {
            if ($participation['libelle_abrege'] === Region::ELECTION) {
                $participation['district'] = Region::NOMS[$participation['district']] ?? $participation['district'];
            }
        }

        return $participations;
    }

    /**
     * L'encart « Municipales 2026 » en tête de fiche
     * (`Elections_model::get_candidate_election($mp, 7, TRUE, FALSE)` +
     * `City_model::get_city_by_insee`, vue `deputes/partials/electionFeature.php`).
     *
     * Le seul critère est la visibilité : une candidature vérifiée s'affiche
     * quel que soit son sort, y compris négative (« n'est pas candidat ») et
     * y compris pour un ancien député — le site sert l'encart sur la fiche
     * d'Emmanuel Grégoire, qui ne siège plus. La couleur de la carte reprend la
     * cascade du modèle d'origine : sort connu d'abord, second tour ensuite,
     * candidature enfin.
     *
     * @return array<string, mixed>|null
     */
    private function electionMunicipales(int $deputeId): ?array
    {
        $candidature = $this->connection->fetchAssociative(
            "SELECT c.position, c.candidat, c.second_tour, c.elu, c.lien,
                    co.nom AS commune_nom, co.slug AS commune_slug, co.population,
                    dep.slug AS commune_dpt_slug
             FROM candidature c
             JOIN election e ON e.id = c.election_id
             LEFT JOIN commune co ON co.code_insee = c.district
             LEFT JOIN departement dep ON dep.id = co.departement_id
             WHERE c.depute_id = :depute AND e.slug = 'municipales-2026' AND c.visible = 1
             LIMIT 1",
            ['depute' => $deputeId],
        );

        if ($candidature === false) {
            return null;
        }

        $elu = $candidature['elu'] === null ? null : (bool) $candidature['elu'];
        $secondTour = $candidature['second_tour'] === null ? null : (bool) $candidature['second_tour'];
        $candidat = $candidature['candidat'] === null ? null : (bool) $candidature['candidat'];

        return [
            'elu' => $elu,
            'second_tour' => $secondTour,
            'candidat' => $candidat,
            'tete_de_liste' => $candidature['position'] === 'Tête de liste',
            'lien' => $candidature['lien'],
            'couleur' => match (true) {
                $elu === true => 'results-success',
                $elu === false, $secondTour === false => 'results-fail',
                $secondTour === true, $candidat === true => 'information-success',
                $candidat === false => 'information-fail',
                default => 'information-success',
            },
            // La commune peut manquer : une candidature négative n'a pas
            // toujours de district. L'encart tient alors en une phrase.
            'commune' => $candidature['commune_nom'] === null ? null : [
                'nom' => $candidature['commune_nom'],
                'nom_de' => $this->communeAvecDe((string) $candidature['commune_nom']),
                'nom_a' => $this->communeAvecA((string) $candidature['commune_nom']),
                'slug' => $candidature['commune_slug'],
                'dpt_slug' => $candidature['commune_dpt_slug'],
                // Sous ce seuil, le lien vers les résultats passe par url_obf,
                // comme toutes les adresses d'élections des petites communes.
                'lien_clair' => (int) $candidature['population'] > ElectionController::POPULATION_MINIMALE,
            ],
        ];
    }

    /**
     * « de Brest », « du Havre », « des Abymes », « de la Rochelle »,
     * « d'Aix-en-Provence » : la colonne `nom_de` des `cities` du legacy,
     * recomposée — notre référentiel des communes ne la porte pas. L'article du
     * nom se contracte ou s'abaisse ; l'élision joue sur toute initiale
     * vocalique, trait d'union compris (« d'Évry »), là où la fiche de ville
     * suit une règle plus fruste ({@see CommuneController::elision}) — les deux
     * reproduisent chacune leur colonne d'origine.
     */
    private function communeAvecDe(string $nom): string
    {
        if (str_starts_with($nom, 'Le ')) {
            return 'du ' . substr($nom, 3);
        }
        if (str_starts_with($nom, 'Les ')) {
            return 'des ' . substr($nom, 4);
        }
        if (str_starts_with($nom, 'La ')) {
            return 'de la ' . substr($nom, 3);
        }
        if (str_starts_with($nom, "L'")) {
            return "de l'" . substr($nom, 2);
        }

        return \in_array(mb_substr($nom, 0, 1), ['A', 'E', 'I', 'O', 'U', 'Y', 'É', 'È', 'Î', 'Ô'], true)
            ? "d'" . $nom
            : 'de ' . $nom;
    }

    /**
     * « à Brest », « au Havre », « aux Abymes » — et « à La Rochelle », où la
     * colonne `nom_a` du legacy perd l'article (« Résultats des élections à
     * Rochelle », « à Aigle ») : même famille de défaut que « à la La Réunion »,
     * déjà corrigée ailleurs — l'article fait partie du nom, il reste.
     */
    private function communeAvecA(string $nom): string
    {
        if (str_starts_with($nom, 'Le ')) {
            return 'au ' . substr($nom, 3);
        }
        if (str_starts_with($nom, 'Les ')) {
            return 'aux ' . substr($nom, 4);
        }

        return 'à ' . $nom;
    }

    /**
     * Les professions de foi du député (`Deputes_model::get_professions`) : une
     * ligne par élection, un chemin d'asset par tour — les PDF vivent sous
     * `assets/data/professions/election_<id>/`, comme sur datan.fr.
     *
     * La table est vide tant que `app:import:professions-foi` n'a pas été rejouée
     * contre la vraie base de production (le backup public la vide) : le bloc de
     * la fiche reste alors masqué.
     *
     * @return list<array{election: string, tour1: ?string, tour2: ?string}>
     */
    private function professionsFoi(string $mpId): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT pf.election_id, pf.fichier, pf.tour, e.libelle, e.annee
             FROM profession_foi pf
             JOIN election e ON e.id = pf.election_id
             WHERE pf.mp_id = :mp
             ORDER BY e.annee DESC, pf.tour',
            ['mp' => $mpId],
        );

        $parElection = [];
        foreach ($lignes as $ligne) {
            $id = (int) $ligne['election_id'];
            $parElection[$id] ??= ['election' => $ligne['libelle'] . ' ' . $ligne['annee'], 'tour1' => null, 'tour2' => null];
            $chemin = 'assets/data/professions/election_' . $id . '/' . $ligne['fichier'];
            if ((int) $ligne['tour'] === 1) {
                $parElection[$id]['tour1'] = $chemin;
            } else {
                $parElection[$id]['tour2'] = $chemin;
            }
        }

        return array_values($parElection);
    }

    /**
     * Parrainage présidentiel 2022 donné par le député pendant son mandat
     * (`Parrainages_model::get_mp_parrainage($mp, 2022)`). Un député n'en a qu'un
     * au plus — la table ne porte que 2022 —, d'où le `LIMIT 1` de l'origine.
     * Le nom du candidat est remis en casse de lecture.
     *
     * @return array{candidat: string, annee: int}|null
     */
    private function parrainage(string $mpId): ?array
    {
        $ligne = $this->connection->fetchAssociative(
            'SELECT candidat, annee FROM parrainage
             WHERE mp_id = :mp AND annee = 2022 LIMIT 1',
            ['mp' => $mpId],
        );

        if ($ligne === false) {
            return null;
        }

        return [
            'candidat' => self::PARRAINAGE_CANDIDATS[$ligne['candidat']] ?? $ligne['candidat'],
            'annee' => (int) $ligne['annee'],
        ];
    }

    /**
     * Listes du pied de fiche « Les autres députés … » (`_other_mps.php`,
     * `Depute_service::get_other_mps`), en trois volets selon le contexte, plus
     * les députés du même département.
     *
     * Le premier volet dépend de la fiche affichée :
     * - fiche d'une législature passée → les députés de CETTE législature ;
     * - député en exercice → les autres membres de son groupe, au rattachement
     *   le plus récent de la législature (`deputes_all.groupeId`) — partis de
     *   l'Assemblée compris, comme sur le site ;
     * - sortant (dernier mandat clos) → les députés ayant siégé à la 15e, quirk
     *   du legacy (`get_other_deputes` else : `dateFin IS NOT NULL AND legislature = 15`).
     *
     * Le tri reproduit celui de l'origine : les noms à partir de l'initiale du
     * député d'abord (`nameLast < LEFT(nom, 1)`), puis alphabétique. On n'affiche
     * que les députés à fiche (`dpt_slug IS NOT NULL`), pour ne jamais lier un 404.
     *
     * @param array<string, mixed>            $depute
     * @param list<array<string, mixed>>      $mandats
     *
     * @return array{autres: list<array<string, mixed>>, contexte: string, legislature: int, departement: list<array<string, mixed>>}
     */
    private function autresDeputes(array $depute, array $mandats, bool $actif): array
    {
        $id = (int) $depute['id'];
        $nom = (string) $depute['lastname'];
        $legislatureFiche = $mandats !== [] ? (int) $mandats[0]['legislature'] : Legislature::COURANTE;

        $colonnes = 'd.firstname, d.lastname, d.slug, d.dpt_slug';
        $tri = 'ORDER BY d.lastname < LEFT(:nom, 1), d.lastname LIMIT 15';

        if ($legislatureFiche === Legislature::COURANTE && $actif) {
            $contexte = 'groupe';
            // Les membres du groupe au sens de `deputes_all` : le rattachement
            // le plus récent de la législature — encore ouvert d'abord, puis
            // date de fin la plus tardive (`daily.php:914`) — que le député
            // siège encore ou non. Le site liste ainsi Barthès et Bordes parmi
            // « Les autres députés RN » après leur départ de l'Assemblée ;
            // filtrer sur `depute.groupe_id` (appartenance courante) les
            // faisait disparaître et décalait toute la liste.
            $autres = $depute['groupe_id'] === null ? [] : $this->connection->fetchAllAssociative(
                "SELECT $colonnes FROM depute d
                 JOIN (SELECT fg.depute_id,
                              SUBSTRING_INDEX(GROUP_CONCAT(fg.groupe_id
                                  ORDER BY COALESCE(fg.date_fin, '9999-12-31') DESC, fg.date_debut DESC), ',', 1) AS groupe_recent
                       FROM fonction_groupe fg
                       JOIN groupe g ON g.id = fg.groupe_id AND g.legislature = :leg
                       WHERE fg.nomin_principale = 1
                       GROUP BY fg.depute_id) r ON r.depute_id = d.id AND r.groupe_recent = :gid
                 WHERE d.id <> :id AND d.dpt_slug IS NOT NULL
                 $tri",
                ['gid' => (string) $depute['groupe_id'], 'id' => $id, 'nom' => $nom, 'leg' => Legislature::COURANTE],
            );
        } elseif ($legislatureFiche === Legislature::COURANTE) {
            $contexte = 'inactifs';
            $autres = $this->connection->fetchAllAssociative(
                "SELECT $colonnes FROM depute d
                 WHERE d.id <> :id AND d.dpt_slug IS NOT NULL
                   AND EXISTS (SELECT 1 FROM mandat m WHERE m.depute_id = d.id AND m.legislature = 15)
                 $tri",
                ['id' => $id, 'nom' => $nom],
            );
        } else {
            $contexte = 'legislature';
            // Legacy `get_other_deputes_legislature` : `deputes_last.legislature = :leg`,
            // c.-à-d. les députés dont la DERNIÈRE législature est celle-ci (partis
            // après elle), pas tous ceux qui y ont siégé — un député encore en
            // exercice appartient à la 17e, jamais à cette liste. D'où le MAX.
            $autres = $this->connection->fetchAllAssociative(
                "SELECT $colonnes FROM depute d
                 WHERE d.id <> :id AND d.dpt_slug IS NOT NULL
                   AND (SELECT MAX(m.legislature) FROM mandat m WHERE m.depute_id = d.id) = :leg
                 $tri",
                ['leg' => $legislatureFiche, 'id' => $id, 'nom' => $nom],
            );
        }

        // Députés en activité du département — le legacy n'en exclut pas le député
        // courant (`get_deputes_all` sans `mpId !=`), reproduit tel quel.
        $departement = $this->connection->fetchAllAssociative(
            "SELECT $colonnes FROM depute d
             WHERE d.dpt_slug = :dpt AND d.dpt_slug IS NOT NULL
               AND EXISTS (SELECT 1 FROM mandat m WHERE m.depute_id = d.id AND m.date_fin IS NULL)
             ORDER BY d.lastname, d.firstname",
            ['dpt' => $depute['dpt_slug']],
        );

        return ['autres' => $autres, 'contexte' => $contexte, 'legislature' => $legislatureFiche, 'departement' => $departement];
    }

    /**
     * Coordonnées et réseaux sociaux du député (`contact_depute`, alimentée par
     * le chantier réseaux sociaux). Lue en DBAL, comme tout le reste de la
     * fiche — pas d'hydratation d'entité sur une page de lecture.
     *
     * @return array<string, mixed>|null
     */
    private function contact(int $deputeId): ?array
    {
        $contact = $this->connection->fetchAssociative(
            'SELECT site_web, mail_an, twitter, facebook, bluesky
             FROM contact_depute WHERE depute_id = :depute',
            ['depute' => $deputeId],
        );

        return $contact === false ? null : $contact;
    }

    /**
     * @return array<string, mixed>
     */
    private function depute(string $slug): array
    {
        $depute = $this->connection->fetchAssociative(
            'SELECT d.id, d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    d.age, d.commission, d.date_naissance, d.ville_naissance,
                    d.departement_nom, d.departement_code, d.circonscription,
                    g.id AS groupe_id, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    g.couleur AS groupe_couleur, g.legislature AS groupe_legislature,
                    dep.libelle_de
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             LEFT JOIN departement dep ON dep.code = d.departement_code
             WHERE d.slug = :slug
             ORDER BY (d.dpt_slug IS NULL), d.id
             LIMIT 1',
            ['slug' => $slug],
        );

        if ($depute === false) {
            throw $this->createNotFoundException('Député introuvable.');
        }

        // Même garde que sur la fiche principale : un acteur sans dpt_slug n'a
        // jamais siégé comme député et n'a pas de page — voir individual().
        if ($depute['dpt_slug'] === null) {
            throw $this->createNotFoundException('Acteur sans mandat de député : pas de fiche publiée.');
        }

        return $depute;
    }

    /**
     * L'intégralité des scrutins où le député s'est exprimé, avec sa loyauté
     * envers son groupe.
     *
     * Loyauté et position sont confrontées à la position majoritaire du groupe
     * auquel il appartenait **le jour du scrutin** ({@see GroupeAuScrutin}), pas
     * à celle de son groupe actuel : un député qui change de groupe ne devient
     * pas rétroactivement rebelle, et ses législatures passées gardent leur
     * colonne de loyauté. Mise en forme faite par la base — la table compte des
     * milliers de lignes, et chaque filtre Twig s'y paierait autant de fois.
     *
     * @return list<array<string, mixed>>
     */
    private function tousLesVotes(int $deputeId): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT s.legislature,
                    CASE WHEN s.uid LIKE 'VTCGR%' THEN -s.numero ELSE s.numero END AS numero,
                    CONCAT(UPPER(LEFT(s.titre, 1)), SUBSTRING(s.titre, 2)) AS titre,
                    DATE_FORMAT(s.date_scrutin, '%d-%m-%Y') AS date_scrutin,
                    v.position,
                    UPPER(dcr.title) AS decryptage_title,
                    CASE
                        -- Sans position majoritaire (`nv` : égalité, ou personne
                        -- n'a voté), le groupe ne donne rien à suivre.
                        WHEN vg.position_majoritaire IS NULL
                          OR vg.position_majoritaire NOT IN ('pour', 'contre', 'abstention') THEN NULL
                        WHEN v.position = vg.position_majoritaire THEN 'loyal'
                        ELSE 'rebelle'
                    END AS loyaute
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             LEFT JOIN " . GroupeAuScrutin::periodes(':depute') . ' fg ON ' . GroupeAuScrutin::conditionPeriode('fg', 's.date_scrutin') . "
             LEFT JOIN vote_groupe vg ON vg.scrutin_id = s.id AND vg.groupe_id = fg.groupe_id
             LEFT JOIN decryptage dcr ON dcr.scrutin_id = s.id AND dcr.state = :published
             WHERE v.depute_id = :depute AND v.vote_type = :type
               AND v.position IN ('pour', 'contre', 'abstention')
             ORDER BY s.date_scrutin DESC, s.numero DESC",
            ['depute' => $deputeId, 'type' => self::OFFICIAL, 'published' => 'published'],
        );
    }

    /**
     * Taux de participation aux scrutins publics solennels — ceux que
     * l'application d'origine retient, les votes ordinaires étant très nombreux
     * et peu représentatifs de l'assiduité.
     *
     * Le dénominateur compte TOUS les scrutins solennels tenus pendant la période
     * d'activité du député, et non les seuls scrutins où il a une ligne de vote :
     * une absence complète ne laisse aucune trace dans `vote`, et l'ignorer
     * afficherait 100 % pour un député pourtant absent plusieurs fois.
     * La période d'activité est bornée par son premier et son dernier vote connus,
     * faute d'historique des dates de mandat.
     *
     * Restreinte à une législature, la fenêtre se resserre sur les seuls
     * scrutins de celle-ci : comparer les votes d'un mandat au total de quatre
     * législatures donnerait un taux absurde.
     *
     * @return array{exprimes: int, total: int, taux: int|null}
     */
    private function participation(int $deputeId, ?int $legislature = null): array
    {
        $filtreLegislature = $legislature !== null ? ' AND s.legislature = :legislature' : '';
        $parametres = $legislature !== null ? ['legislature' => $legislature] : [];

        $fenetre = $this->connection->fetchAssociative(
            'SELECT MIN(v.scrutin_date) AS debut, MAX(v.scrutin_date) AS fin
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             WHERE v.depute_id = :depute' . $filtreLegislature,
            ['depute' => $deputeId] + $parametres,
        ) ?: [];

        if (empty($fenetre['debut']) || empty($fenetre['fin'])) {
            return ['exprimes' => 0, 'total' => 0, 'taux' => null];
        }

        $exprimes = (int) $this->connection->fetchOne(
            'SELECT COUNT(*)
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             WHERE v.depute_id = :depute AND v.vote_type = :type
               AND s.code_type_vote = :solennel
               AND v.position IN (\'pour\', \'contre\', \'abstention\')' . $filtreLegislature,
            ['depute' => $deputeId, 'type' => self::OFFICIAL, 'solennel' => self::SOLENNEL] + $parametres,
        );

        $total = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM scrutin s
             WHERE s.code_type_vote = :solennel AND s.date_scrutin BETWEEN :debut AND :fin' . $filtreLegislature,
            ['solennel' => self::SOLENNEL, 'debut' => $fenetre['debut'], 'fin' => $fenetre['fin']] + $parametres,
        );

        return [
            'exprimes' => $exprimes,
            'total' => $total,
            'taux' => $total > 0 ? (int) round($exprimes / $total * 100) : null,
        ];
    }

    /**
     * Taux de loyauté : part des votes exprimés conformes à la position
     * majoritaire du groupe, comparée scrutin par scrutin via la ventilation
     * historique ({@see \App\Entity\VoteGroupe}).
     *
     * Réserve : le rapprochement se fait avec le groupe actuel du député, faute
     * d'historique de ses rattachements successifs.
     *
     * @return array{conformes: int, total: int, taux: int|null}
     */
    private function loyaute(int $deputeId, int $groupeId, ?int $legislature = null): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS total,
                    SUM(v.position = vg.position_majoritaire) AS conformes
             FROM vote v
             JOIN vote_groupe vg ON vg.scrutin_id = v.scrutin_id AND vg.groupe_id = :groupe
             JOIN scrutin s ON s.id = v.scrutin_id
             WHERE v.depute_id = :depute AND v.vote_type = :type
               AND v.position IN (\'pour\', \'contre\', \'abstention\')'
             . ($legislature !== null ? ' AND s.legislature = :legislature' : ''),
            ['depute' => $deputeId, 'groupe' => $groupeId, 'type' => self::OFFICIAL]
                + ($legislature !== null ? ['legislature' => $legislature] : []),
        ) ?: [];

        $total = (int) ($row['total'] ?? 0);
        $conformes = (int) ($row['conformes'] ?? 0);

        return [
            'conformes' => $conformes,
            'total' => $total,
            'taux' => $total > 0 ? (int) round($conformes / $total * 100) : null,
        ];
    }

    /**
     * Derniers votes du député. S'appuie sur l'index (depute_id, scrutin_date)
     * pour éviter tout tri en mémoire.
     *
     * @return list<array<string, mixed>>
     */
    private function derniersVotes(int $deputeId, ?int $legislature = null): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT s.legislature, s.numero, s.titre, s.sort_code, v.position, v.scrutin_date,
                    dcr.title AS decryptage_title
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             LEFT JOIN decryptage dcr ON dcr.scrutin_id = s.id AND dcr.state = :published
             WHERE v.depute_id = :depute AND v.vote_type = :type'
             . ($legislature !== null ? ' AND s.legislature = :legislature' : '') . '
             ORDER BY v.scrutin_date DESC
             LIMIT 10',
            ['depute' => $deputeId, 'type' => self::OFFICIAL, 'published' => 'published']
                + ($legislature !== null ? ['legislature' => $legislature] : []),
        );
    }

    /**
     * Historique des mandats parlementaires, du plus récent au plus ancien.
     *
     * @return list<array<string, mixed>>
     */
    private function mandats(int $deputeId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT legislature, date_debut, date_fin, departement_nom, departement_code,
                    circonscription, cause_mandat
             FROM mandat
             WHERE depute_id = :depute
             ORDER BY legislature DESC, date_debut DESC',
            ['depute' => $deputeId],
        );
    }

    /**
     * Le vote mis en avant en tête de fiche (`voteFeature.php`) : la position du
     * député sur le scrutin phare du moment, codé en dur dans l'application
     * d'origine (`Deputes::index` : 17e législature, scrutin n° 3684 — la
     * suspension de la réforme des retraites). Seule cette position est
     * dynamique ; le reste de l'encart est un texte éditorial figé.
     *
     * Nul si le député n'a pas de ligne de vote sur ce scrutin (ancien, absent) :
     * l'encart disparaît alors, comme le legacy.
     *
     * @return array{legislature: int, numero: int, position: string}|null
     */
    private function voteFeature(int $deputeId): ?array
    {
        $position = $this->connection->fetchOne(
            'SELECT v.position FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             WHERE v.depute_id = :depute AND s.legislature = 17 AND s.numero = 3684',
            ['depute' => $deputeId],
        );

        return $position === false
            ? null
            : ['legislature' => 17, 'numero' => 3684, 'position' => (string) $position];
    }

    /**
     * Commission parlementaire du député pour la phrase « … est membre de la
     * Commission des affaires étrangères. » du bloc bio (`Deputes_model::get_commission_parlementaire`).
     *
     * On prend la commission en cours (`fonction_commission.date_fin IS NULL`)
     * dont le libellé abrégé est celui déjà retenu par l'import pour la carte de
     * profil (`depute.commission`) : la phrase et la carte désignent ainsi la
     * même commission. Le libellé complet (« Commission des affaires étrangères »)
     * et la qualité (« Membre », « Président »…) viennent de la jointure.
     *
     * @return array{libelle: string, role: string}|null
     */
    private function commission(int $deputeId, ?string $abrege): ?array
    {
        if ($abrege === null) {
            return null;
        }

        $ligne = $this->connection->fetchAssociative(
            'SELECT c.libelle, fc.code_qualite AS role
             FROM fonction_commission fc
             JOIN commission c ON c.id = fc.commission_id
             WHERE fc.depute_id = :depute AND fc.date_fin IS NULL
               AND fc.code_qualite IS NOT NULL AND c.libelle_abrege = :abrege
             ORDER BY fc.legislature DESC
             LIMIT 1',
            ['depute' => $deputeId, 'abrege' => $abrege],
        );

        return $ligne === false ? null : $ligne;
    }

    /**
     * Entrée en fonction et ancienneté du député, pour le paragraphe
     * « … est entré en fonction en juillet 2024 et en est à son deuxième mandat.
     * Au total, … a passé 4 ans sur les bancs… » (`daily.php:3960-3999`).
     *
     * L'ancienneté est la somme des durées de mandat (mandat ouvert → aujourd'hui)
     * comptée depuis la prise de fonction (à défaut la date de début) ; la moyenne
     * est celle de tous les députés, calculée pareil. Le mois affiché est la prise
     * de fonction du mandat le plus récent.
     *
     * @return array{ordinal: string, length_edited: string, moyenne: int, edito: string, debut: \DateTimeImmutable}|null
     */
    private function anciennete(int $deputeId): ?array
    {
        $mandatsN = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM mandat WHERE depute_id = :depute',
            ['depute' => $deputeId],
        );

        $debut = $this->connection->fetchOne(
            'SELECT COALESCE(date_prise_fonction, date_debut) FROM mandat
             WHERE depute_id = :depute ORDER BY legislature DESC, date_debut DESC LIMIT 1',
            ['depute' => $deputeId],
        );

        if ($mandatsN === 0 || $debut === false || $debut === null) {
            return null;
        }

        // Jours d'ancienneté du député et jours moyens de tous les députés, en une
        // requête : COALESCE(prise de fonction, début) borne chaque mandat, un
        // mandat encore ouvert court jusqu'à aujourd'hui.
        $duree = 'DATEDIFF(COALESCE(date_fin, CURDATE()), COALESCE(date_prise_fonction, date_debut))';
        $ligne = $this->connection->fetchAssociative(
            "SELECT
                (SELECT SUM($duree) FROM mandat WHERE depute_id = :depute) AS jours,
                (SELECT AVG(t.jours) FROM
                   (SELECT SUM($duree) AS jours FROM mandat GROUP BY depute_id) t) AS jours_moyens",
            ['depute' => $deputeId],
        );

        $annees = (int) round(((int) $ligne['jours']) / 365);
        $moyenne = (int) round(((float) $ligne['jours_moyens']) / 365);

        // Mois de prise de fonction en toutes lettres (« juillet 2024 »), comme le
        // legacy (`strftime('%B %Y')`). ICU en français rend le mois en minuscules.
        $mois = (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMMM y'))
            ->format(new \DateTimeImmutable((string) $debut));

        return [
            'ordinal' => self::ORDINAUX[$mandatsN] ?? (string) $mandatsN,
            'mois' => $mois,
            'length_edited' => $this->ancienneteEnLettres((int) $ligne['jours']),
            'moyenne' => $moyenne,
            'edito' => $annees < $moyenne ? 'moins' : ($annees > $moyenne ? 'plus' : 'autant'),
        ];
    }

    /**
     * Ancienneté en toutes lettres, comme le legacy (`daily.php:3994`) : en années
     * dès un an, sinon en mois, sinon en jours.
     */
    private function ancienneteEnLettres(int $jours): string
    {
        $annees = (int) round($jours / 365);
        if ($annees >= 1) {
            return $annees . ($annees > 1 ? ' ans' : ' an');
        }

        $mois = (int) round($jours / 30);

        return $mois !== 0 ? $mois . ' mois' : $jours . ' jours';
    }
}
