<?php

namespace App\Controller;

use App\BlocPolitique;
use App\CouleurGroupe;
use App\Entity\Dossier;
use App\Depute\ComportementDepute;
use App\Entity\FonctionGroupe;
use App\FamilleGroupe;
use App\FamilleSocioPro;
use App\Groupe\EditoGroupe;
use App\Groupe\MoyennesAssemblee;
use App\Groupe\ParticipationGroupe;
use App\Groupe\ReseauxGroupe;
use App\Groupe\SoutienGouvernement;
use App\Groupe\StatistiquesGroupe;
use App\Http\RechauffeurCache;
use App\Legislature;
use App\NatureVote;
use App\Referencement\OpenGraph;
use App\Twig\DatanExtension;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages publiques des groupes parlementaires, portées depuis le contrôleur
 * Groupes de l'application CodeIgniter d'origine.
 */
class GroupeController extends AbstractController
{
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** Sigle des députés non-inscrits, exclus des comparaisons entre groupes. */
    private const NON_INSCRITS = 'NI';

    /**
     * Position majoritaire favorable. `vote_groupe` a son propre vocabulaire,
     * distinct de {@see \App\Enum\VotePosition} : il note « nv » les non-votants.
     */
    private const POSITION_POUR = 'pour';

    /** Position politique de la majorité présidentielle, telle que l'Assemblée la déclare. */
    private const POSITION_MAJORITAIRE = 'Majoritaire';

    /**
     * Sigles ramenés à leur successeur dans l'affichage des coalitions.
     *
     * Le site ne recolle qu'UNE filiation — UDR → UDDPLR
     * (`clean_libelleAbrev()`, daily.php:4580) — et regroupe ses coalitions
     * sur le libellé ainsi corrigé. Ne pas généraliser aux autres familles :
     * SOC et SOC-A restent distincts en 16e, et les coalitions socialistes se
     * scindent au 19/10/2023, badge SOC sur les lignes d'avant — c'est ce
     * qu'affiche datan.fr. Une version dérivée de {@see FamilleGroupe} les
     * fusionnait et rebaptisait SOC-A des coalitions que le site étiquette SOC.
     */
    private const SIGLES_CANONIQUES = ['UDR' => 'UDDPLR'];

    /**
     * Rangs en toutes lettres des titres de classement (« Le sixième groupe
     * avec les députés les plus âgés »), portés d'`ordinaux()`. La table de
     * l'origine s'arrête à onze, le nombre de groupes que compte l'Assemblée.
     */
    private const ORDINAUX = [
        1 => 'premier', 2 => 'deuxième', 3 => 'troisième', 4 => 'quatrième',
        5 => 'cinquième', 6 => 'sixième', 7 => 'septième', 8 => 'huitième',
        9 => 'neuvième', 10 => 'dixième', 11 => 'onzième',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly OpenGraph $openGraph,
        private readonly DatanExtension $datan,
        private readonly SoutienGouvernement $soutienGouvernement,
        private readonly MoyennesAssemblee $moyennes,
        private readonly StatistiquesGroupe $statistiques,
    ) {
    }

    /**
     * Expression SQL de l'indice d'accord (cohésion) d'une ligne de vote_groupe,
     * identique à celle de l'application d'origine.
     */
    private const COHESION_SQL = '(GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)
            - 0.5 * ((vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions)
                     - GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)))
           / NULLIF(vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions, 0)';

    // Le sigle peut contenir un underscore ou des points : UDI_I en 15e législature, S.R.C. en 13e.
    #[Route('/groupes/legislature-{legislature}/{abrev}', name: 'groupe_individual', requirements: ['legislature' => '\d+', 'abrev' => '[a-z0-9._\-\&]+'], methods: ['GET'])]
    public function individual(int $legislature, string $abrev): Response
    {
        $groupe = $this->groupe($legislature, $abrev);
        $groupeId = (int) $groupe['id'];

        $presidences = $this->presidences($groupeId);
        $soutien = $this->soutien($groupeId);
        $composition = $this->composition($groupe);

        // Le graphique comparatif n'a de sens qu'entre groupes contemporains.
        $comparatif = $soutien['votes'] > 0 && $legislature === Legislature::COURANTE
            ? $this->soutienGouvernement->tousLesGroupes($legislature)
            : [];

        // Le président compte dans l'effectif du groupe, comme à l'Assemblée.
        $membres = array_merge(...array_values($composition));
        $chiffres = $this->chiffres($membres);
        $legislature = (int) $groupe['legislature'];
        $sigle = (string) $groupe['libelle_abrev'];

        // Les proximités servent deux lectures : le classement complet nourrit
        // les graphiques « vote souvent / rarement avec », et la ligne du
        // groupe majoritaire — quand il en existe un — la carte qui lui est
        // consacrée. Les non-inscrits en sont écartés : ils ne forment pas un
        // groupe et ne s'allient pas.
        $proximites = array_map(
            // La phrase qui commente le classement situe chaque voisin deux
            // fois : par son rapport au gouvernement et par son côté de
            // l'échiquier. Les deux sont des lectures de la rédaction, pas des
            // données de l'open data.
            static fn (array $ligne) => $ligne + [
                'maj_pres' => match ($ligne['position_politique']) {
                    'Opposition' => "un groupe d'opposition",
                    self::POSITION_MAJORITAIRE => 'le groupe de la majorité présidentielle, qui est',
                    'Minoritaire' => 'un groupe allié à la majorité présidentielle et',
                    default => 'qui regroupe les députés non affiliés à un groupe parlementaire',
                },
                'echiquier' => ComportementDepute::ECHIQUIER[$ligne['libelle_abrev']] ?? null,
            ],
            array_values(array_filter(
                $this->proximites($groupeId),
                static fn (array $ligne) => $ligne['libelle_abrev'] !== self::NON_INSCRITS,
            )),
        );

        $coalitions = $this->coalitions($groupeId, $legislature);
        $comportement = $this->comportement($groupeId);
        $moyennes = [
            'age' => $this->moyennes->ageMoyen($legislature),
            'feminisation' => $this->moyennes->feminisation($legislature),
        ] + $this->moyennes->comportementMoyen($legislature);
        $origineSociale = $this->origineSociale($groupeId, $chiffres['effectif']);

        // La carte « proximité avec la majorité » ne s'affiche que s'il existe
        // un groupe majoritaire déclaré : la 17e législature n'en a plus aucun.
        $majorite = null;
        foreach ($proximites as $ligne) {
            if ($ligne['position_politique'] === self::POSITION_MAJORITAIRE) {
                $majorite = $ligne;
                break;
            }
        }

        $response = $this->render('groupe/individual.html.twig', [
            'groupe' => $groupe,
            'chiffres' => $chiffres,
            'comportement' => $comportement,
            'legislature_courante' => Legislature::COURANTE,
            'mois_creation' => $this->moisEtAnnee($groupe['date_debut']),
            'majorite' => $majorite,
            'cohesion_edito' => EditoGroupe::cohesion($comportement['cohesion_moyenne'], $moyennes['cohesion']),
            'comparatifs' => [
                'age' => EditoGroupe::comparatif($chiffres['age_moyen'], $moyennes['age']),
                'feminisation' => EditoGroupe::comparatif($chiffres['feminisation'], $moyennes['feminisation']),
                'participation' => EditoGroupe::comparatif($comportement['participation_moyenne'], $moyennes['participation']),
                'origine_sociale' => $origineSociale === null
                    ? 'autant'
                    : EditoGroupe::comparatif((float) $origineSociale['pct'], round($origineSociale['population'])),
            ],
            'derniers_votes' => $this->derniersVotes($groupeId),
            'membres' => $composition['membres'],
            'apparentes' => $composition['apparentes'],
            'president' => $presidences[0] ?? null,
            'presidences' => $presidences,
            'historique' => $this->historique($groupe['uid'], $groupeId),
            'soutien' => $soutien,
            'soutien_groupes' => $comparatif,
            'edito' => [
                'creation' => EditoGroupe::creation($sigle),
                'opposition' => EditoGroupe::opposition($groupe['position_politique']),
            ],
            'echiquier' => ComportementDepute::ECHIQUIER[$sigle] ?? null,
            'liens' => ReseauxGroupe::liens($sigle),
            // Encart « Municipales 2026 » en tête de biographie : le nombre de
            // candidats du groupe, sur la seule législature courante — comme le
            // site (`Groupes::individual`, `legislature == legislature_current()`).
            'election_municipales' => $legislature === Legislature::COURANTE
                ? $this->candidatsMunicipales($groupeId)
                : null,
            'moyennes' => $moyennes,
            'rang' => $this->moyennes->rangParEffectif($groupeId, $legislature),
            'sieges' => MoyennesAssemblee::SIEGES,
            'origine_sociale' => $origineSociale,
            'proximites' => $proximites,
            'coalitions' => $coalitions,
            // La lecture en blocs politiques n'est établie que pour la 17e
            // législature : ailleurs, la coalition s'énonce par ses sigles.
            'coalition_blocs' => $legislature === BlocPolitique::LEGISLATURE && $coalitions !== []
                ? BlocPolitique::repartis($coalitions[0]['sigles'])
                : [],
            'coalitions_couleurs' => $this->couleursParSigle($legislature),
            'groupes_legislature' => $this->groupesDeLaLegislature($legislature),
            'ogp' => $this->openGraph->pourGroupe($groupe, $groupe['date_fin'] === null),
            'fil_ariane' => $this->filAriane($groupe, avecLegislature: true),
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Liste complète des membres du groupe, page à part entière.
     *
     * La page du groupe n'en montre qu'un aperçu ; c'est ici qu'on trouve tout
     * le monde, présidence en tête puis membres et apparentés, comme sur le
     * site d'origine.
     */
    #[Route('/groupes/legislature-{legislature}/{abrev}/membres', name: 'groupe_membres', requirements: ['legislature' => '\d+', 'abrev' => '[a-z0-9._\-\&]+'], methods: ['GET'])]
    public function membres(int $legislature, string $abrev): Response
    {
        $groupe = $this->groupe($legislature, $abrev);
        $composition = $this->composition($groupe);

        // La présidence vient de `composition()` et non de `presidences()` : la
        // carte de député réclame département, couleur de groupe et photo, que
        // l'historique des présidences ne porte pas.
        $response = $this->render('groupe/membres.html.twig', $composition + [
            'groupe' => $groupe,
            'fil_ariane' => [...$this->filAriane($groupe, avecLegislature: true),
                ['nom' => 'Membres', 'url' => $this->generateUrl('groupe_membres', $this->parametresRoute($groupe))],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Positions du groupe sur les votes décryptés, filtrables par thématique.
     */
    #[Route('/groupes/legislature-{legislature}/{abrev}/votes', name: 'groupe_votes', requirements: ['legislature' => '\d+', 'abrev' => '[a-z0-9._\-\&]+'], methods: ['GET'])]
    public function votes(int $legislature, string $abrev): Response
    {
        $groupe = $this->groupe($legislature, $abrev);

        $votes = $this->connection->fetchAllAssociative(
            'SELECT d.title, d.legislature, d.vote_numero,
                    c.name AS categorie_name, c.slug AS categorie_slug,
                    l.name AS lecture_name,
                    s.date_scrutin, vg.position_majoritaire AS position
             FROM decryptage d
             JOIN scrutin s ON s.id = d.scrutin_id
             JOIN vote_groupe vg ON vg.scrutin_id = s.id AND vg.groupe_id = :groupe
             LEFT JOIN categorie c ON c.id = d.categorie_id
             LEFT JOIN lecture l ON l.id = d.lecture_id
             WHERE d.state = :published
             ORDER BY s.date_scrutin DESC',
            ['groupe' => (int) $groupe['id'], 'published' => 'published'],
        );

        // Seules les thématiques sur lesquelles ce groupe s'est prononcé : un
        // filtre qui ne trouverait rien n'a rien à faire dans la colonne.
        $categories = [];
        foreach ($votes as $vote) {
            if ($vote['categorie_slug'] !== null) {
                $categories[$vote['categorie_slug']] = $vote['categorie_name'];
            }
        }

        // Tri par collation française et non par `asort()`, qui compare des
        // octets : « Économie » y passait après « Sports » (le É d'UTF-8
        // commence par 0xC3, au-delà de « z ») et « Affaires sociales » avant
        // « Affaires étrangères ». Le site de référence range les deux à leur
        // place alphabétique.
        (new \Collator('fr_FR'))->asort($categories);

        $response = $this->render('groupe/votes.html.twig', [
            'groupe' => $groupe,
            'votes' => $votes,
            'categories' => $categories,
            'effectif' => $this->effectif($groupe),
            'president' => $this->presidences((int) $groupe['id'])[0] ?? null,
            'fil_ariane' => [...$this->filAriane($groupe, avecLegislature: false),
                ['nom' => 'Votes', 'url' => $this->generateUrl('groupe_votes', $this->parametresRoute($groupe))],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Tous les scrutins auxquels le groupe a participé, avec sa position et sa
     * cohésion — la liste exhaustive, par opposition aux seuls décryptés.
     */
    #[Route('/groupes/legislature-{legislature}/{abrev}/votes/all', name: 'groupe_votes_tous', requirements: ['legislature' => '\d+', 'abrev' => '[a-z0-9._\-\&]+'], methods: ['GET'])]
    public function tousLesVotes(int $legislature, string $abrev): Response
    {
        $groupe = $this->groupe($legislature, $abrev);

        // Le numéro est rendu négatif pour un vote du Congrès, convention que
        // partagent `decryptage.vote_numero` et la fonction Twig `lien_vote` :
        // sans elle, la ligne du Congrès pointerait vers le scrutin de
        // l'Assemblée portant le même numéro. Date et libellé sont mis en forme
        // par la base : sur huit mille lignes, chaque appel de fonction rendu
        // dans Twig se paie huit mille fois.
        $votes = $this->connection->fetchAllAssociative(
            "SELECT s.legislature,
                    CASE WHEN s.uid LIKE 'VTCGR%' THEN -s.numero ELSE s.numero END AS numero,
                    CONCAT(UPPER(LEFT(s.titre, 1)), SUBSTRING(s.titre, 2)) AS titre,
                    DATE_FORMAT(s.date_scrutin, '%d-%m-%Y') AS date_scrutin,
                    vg.position_majoritaire AS position,
                    ROUND(" . self::COHESION_SQL . ", 3) AS cohesion,
                    UPPER(dcr.title) AS decryptage_title
             FROM vote_groupe vg
             JOIN scrutin s ON s.id = vg.scrutin_id
             LEFT JOIN decryptage dcr ON dcr.scrutin_id = s.id AND dcr.state = :published
             WHERE vg.groupe_id = :groupe
             ORDER BY s.date_scrutin DESC, s.numero DESC",
            ['groupe' => (int) $groupe['id'], 'published' => 'published'],
        );

        $response = $this->render('groupe/votes_tous.html.twig', [
            'groupe' => $groupe,
            'votes' => $votes,
            'effectif' => $this->effectif($groupe),
            'president' => $this->presidences((int) $groupe['id'])[0] ?? null,
            'fil_ariane' => [...$this->filAriane($groupe, avecLegislature: false),
                ['nom' => 'Votes', 'url' => $this->generateUrl('groupe_votes', $this->parametresRoute($groupe))],
                ['nom' => 'Tous les votes', 'url' => $this->generateUrl('groupe_votes_tous', $this->parametresRoute($groupe))],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Statistiques détaillées du groupe : cohésion, participation, et proximité
     * avec chacun des autres groupes de sa législature.
     *
     * Les ancres `#cohesion`, `#participation` et `#majorite` sont celles vers
     * lesquelles pointe la page du groupe.
     */
    #[Route('/groupes/legislature-{legislature}/{abrev}/statistiques', name: 'groupe_statistiques', requirements: ['legislature' => '\d+', 'abrev' => '[a-z0-9._\-\&]+'], methods: ['GET'])]
    public function statistiques(int $legislature, string $abrev): Response
    {
        $groupe = $this->groupe($legislature, $abrev);
        $groupeId = (int) $groupe['id'];

        $proximites = $this->proximites($groupeId);

        // La majorité présidentielle sert de point de comparaison. Elle n'existe
        // pas toujours : depuis la dissolution de 2024, l'Assemblée ne déclare
        // plus aucun groupe « Majoritaire », et le bloc disparaît alors — comme
        // sur le site d'origine, qui lit la même donnée.
        $majorite = null;
        foreach ($proximites as $ligne) {
            if ($ligne['position_politique'] === self::POSITION_MAJORITAIRE) {
                $majorite = $ligne;
                break;
            }
        }

        $comportement = $this->comportement($groupeId);
        $composition = $this->composition($groupe);
        $chiffres = $this->chiffres(array_merge(...array_values($composition)));
        $actif = $groupe['date_fin'] === null;

        // Chacun de ces repères coûte une agrégation sur `vote_groupe` ou sur
        // les mandats : ils se calculent une fois, puis servent deux fois — au
        // chiffre affiché et à la comparaison qui le commente.
        $moyennes = $this->moyennes->comportementMoyen($legislature) + [
            'age' => $this->moyennes->ageMoyen($legislature),
            'feminisation' => $this->moyennes->feminisation($legislature),
            'majorite' => $this->statistiques->proximiteMajoriteMoyenne($legislature),
        ];

        // Les classements entre groupes ne valent que pour l'Assemblée du jour :
        // le site les tait dès que le groupe consulté a disparu, faute de
        // pouvoir reconstituer l'état des autres groupes à cette date.
        $classements = $actif && $legislature === Legislature::COURANTE
            ? $this->statistiques->classements($legislature)
            : [];

        $famille = $this->statistiques->famille($groupe['uid']);
        $majoriteParIncarnation = $this->statistiques->proximiteMajorite($famille);

        // Le groupe le plus proche se cherche parmi ceux auxquels il peut
        // encore se comparer : sur la législature en cours, le site écarte les
        // groupes dissous, les non-inscrits et ceux qui n'ont pas vingt
        // scrutins communs — un groupe éphémère à trois votes prendrait sinon
        // la première place. Sur une législature achevée, tout compte.
        $retenus = $actif && $legislature === Legislature::COURANTE
            ? array_values(array_filter($proximites, static fn (array $p) => !$p['dissous']
                && $p['libelle_abrev'] !== self::NON_INSCRITS
                && (int) $p['votes'] > 20))
            : $proximites;

        $response = $this->render('groupe/statistiques.html.twig', [
            'groupe' => $groupe,
            'actif' => $actif,
            'legislature_courante' => Legislature::COURANTE,
            'bornes' => $this->statistiques->bornes($legislature),
            'comportement' => $comportement,
            'chiffres' => $chiffres,
            'moyennes' => $moyennes,
            'edito' => [
                'participation' => EditoGroupe::comparatif($comportement['participation_moyenne'], $moyennes['participation']),
                'majorite' => EditoGroupe::comparatif(
                    isset($majoriteParIncarnation[$groupeId]) ? round($majoriteParIncarnation[$groupeId]['score'] * 100) : null,
                    $moyennes['majorite'],
                    egalite: 'aussi',
                ),
                // La comparaison porte sur les valeurs à trois décimales, non
                // sur les deux qu'affiche la phrase : un groupe à 0,926 est dit
                // « moins soudé » qu'une moyenne de 0,927 tout en affichant le
                // même 0.93 qu'elle. Déroutant, mais c'est le texte du site.
                'cohesion' => EditoGroupe::cohesion($comportement['cohesion_moyenne'], $moyennes['cohesion']),
                'age' => EditoGroupe::comparatif($chiffres['age_moyen'], $moyennes['age']),
                'feminisation' => EditoGroupe::comparatif($chiffres['feminisation'], $moyennes['feminisation']),
            ],
            'histogrammes' => $this->histogrammes($famille, $majoriteParIncarnation, $groupeId),
            'classements' => $this->classementsDuGroupe($classements, $groupeId, $chiffres),
            'mensuel' => $this->statistiques->comportementMensuel($groupeId),
            'majorite_mensuelle' => $this->statistiques->majoriteMensuelle($groupeId, $legislature),
            'majorite_stat' => $majoriteParIncarnation[$groupeId] ?? ['votes' => 0, 'score' => 0.0],
            'effectifs_lies' => $this->statistiques->historiqueEffectifs($famille),
            'famille' => $famille,
            'groupes_lies' => $this->historique($groupe['uid'], $groupeId),
            'proximites' => $proximites,
            'proximite_mensuelle' => $this->proximiteParMois($groupeId),
            'majorite' => $majorite,
            'plus_proche' => $retenus[0] ?? null,
            'plus_eloigne' => $retenus !== [] ? end($retenus) : null,
            'president' => $this->presidences($groupeId)[0] ?? null,
            // Le pied de page de liens que le site déroule sous chacune des
            // pages du groupe, statistiques comprises.
            'membres' => $composition['membres'],
            'apparentes' => $composition['apparentes'],
            'groupes_legislature' => $this->groupesDeLaLegislature($legislature),
            'fil_ariane' => [...$this->filAriane($groupe, avecLegislature: false),
                ['nom' => 'Statistiques', 'url' => $this->generateUrl('groupe_statistiques', $this->parametresRoute($groupe))],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Les trois histogrammes « sur les dernières législatures » : une barre par
     * incarnation du groupe, la sienne mise en avant.
     *
     * Les valeurs sortent en fractions (0,229 et non 23) : c'est l'échelle
     * qu'attend le gabarit d'histogramme, qui décide seul de les rendre en
     * pourcentage ou en score.
     *
     * @param list<array<string, mixed>>                 $famille
     * @param array<int, array{votes: int, score: float}> $majorite
     *
     * @return array{participation: list<array<string, mixed>>, cohesion: list<array<string, mixed>>, majorite: list<array<string, mixed>>}
     */
    private function histogrammes(array $famille, array $majorite, int $groupeId): array
    {
        $ages = $this->statistiques->ageALaFondation($famille);
        $feminisation = $this->statistiques->feminisationParIncarnation($famille);

        $barres = ['participation' => [], 'cohesion' => [], 'majorite' => [], 'age' => [], 'feminisation' => []];

        foreach ($famille as $incarnation) {
            $id = (int) $incarnation['id'];
            $barre = $incarnation + ['courant' => $id === $groupeId];

            if ($incarnation['participation'] !== null) {
                $barres['participation'][] = $barre + ['valeur' => (float) $incarnation['participation']];
            }
            if ($incarnation['cohesion'] !== null) {
                $barres['cohesion'][] = $barre + ['valeur' => (float) $incarnation['cohesion']];
            }

            // Un groupe qui *est* la majorité présidentielle sort de sa propre
            // comparaison : le site retire ces barres avant de tracer, sans quoi
            // LAREM et RE figureraient à 100 % face à elles-mêmes.
            if ($incarnation['position_politique'] !== self::POSITION_MAJORITAIRE) {
                $barres['majorite'][] = $barre + ['valeur' => $majorite[$id]['score'] ?? 0.0];
            }

            if (isset($ages[$id])) {
                $barres['age'][] = $barre + ['valeur' => round($ages[$id])];
            }
            if (isset($feminisation[$id])) {
                $barres['feminisation'][] = $barre + ['valeur' => $feminisation[$id]['pct'] / 100];
            }
        }

        $histogrammes = [];
        foreach ($barres as $mesure => $lignes) {
            $valeurs = array_column($lignes, 'valeur');

            $histogrammes[$mesure] = [
                'barres' => $lignes,
                'evolution' => $this->evolution($valeurs),
                // L'âge est le seul à ne pas tenir dans une échelle de 0 à 1 :
                // il se rapporte au doyen des incarnations, cinq ans de marge
                // en plus pour que les barres ne butent pas sur le bord.
                'maximum' => $mesure === 'age' && $valeurs !== [] ? max($valeurs) + 5 : 1,
            ];
        }

        return $histogrammes;
    }

    /**
     * « en hausse », « en baisse » ou « stable » : la comparaison des deux
     * dernières incarnations, telle que la titre le site
     * (`Groupes_edito::get_evolution_edited()`).
     *
     * Elle porte sur les valeurs arrondies au point de pourcentage, celles
     * qu'affiche l'histogramme : deux barres marquées « 23% » se disent stables
     * même si leurs valeurs exactes diffèrent au millième.
     *
     * @param list<float> $valeurs
     */
    private function evolution(array $valeurs): ?string
    {
        if (\count($valeurs) < 2) {
            return null;
        }

        $derniere = (int) round(end($valeurs) * 100);
        $precedente = (int) round(prev($valeurs) * 100);

        return match (true) {
            $derniere > $precedente => 'en hausse',
            $derniere < $precedente => 'en baisse',
            default => 'stable',
        };
    }

    /**
     * Les trois classements entre groupes de la législature, chacun trié sur sa
     * propre mesure, avec le rang qu'y tient le groupe consulté.
     *
     * Le rang se lit sur la liste triée plutôt que par une requête à part : les
     * deux ne pourraient diverger que dans le mauvais sens — un texte annonçant
     * un rang que le graphique juste au-dessous contredit.
     *
     * @param list<array<string, mixed>> $classements
     * @param array<string, int|null>    $chiffres
     *
     * @return array<string, array{lignes: list<array<string, mixed>>, rang: int|null, dernier: bool, maximum: float}>
     */
    private function classementsDuGroupe(array $classements, int $groupeId, array $chiffres): array
    {
        if ($classements === []) {
            return [];
        }

        // Les effectifs arrivent déjà triés ; l'âge et la féminisation se
        // reclassent sur la valeur non arrondie, ce qui départage deux groupes
        // que l'affichage montre à égalité. Le site tire ces ex æquo au sort
        // (`ORDER BY … RAND()`) et change d'ordre à chaque rendu.
        $mesures = [
            'effectif' => $classements,
            'age' => $this->trier($classements, 'age'),
            'feminisation' => $this->trier($classements, 'feminisation'),
        ];

        $resultat = [];
        foreach ($mesures as $mesure => $lignes) {
            $rang = null;
            foreach ($lignes as $position => $ligne) {
                if ((int) $ligne['id'] === $groupeId) {
                    $rang = $position + 1;
                }
            }

            // L'âge s'affiche en années pleines — le site le pré-arrondit dans
            // sa requête. Le tri, lui, reste sur la valeur exacte : sans quoi
            // deux groupes montrés à « 55 » se départageraient au hasard.
            $arrondir = $mesure === 'age';

            $resultat[$mesure] = [
                'lignes' => array_map(static fn (array $l) => $l + ['valeur' => $arrondir ? round((float) $l[$mesure]) : (float) $l[$mesure]], $lignes),
                'rang' => $rang,
                'ordinal' => self::ORDINAUX[$rang] ?? null,
                'dernier' => $rang !== null && $rang === \count($lignes),
                'maximum' => $arrondir ? round((float) $lignes[0][$mesure]) : (float) $lignes[0][$mesure],
            ];
        }

        // L'échelle de l'âge se desserre de cinq ans au-delà du doyen : sans
        // cette marge, la barre de tête occuperait toute la largeur et les
        // écarts entre groupes — deux ou trois ans — deviendraient illisibles.
        $resultat['age']['maximum'] += 5;

        return $resultat;
    }

    /**
     * @param list<array<string, mixed>> $lignes
     *
     * @return list<array<string, mixed>>
     */
    private function trier(array $lignes, string $mesure): array
    {
        usort($lignes, static fn (array $a, array $b) => [(float) $b[$mesure], $a['libelle']] <=> [(float) $a[$mesure], $b['libelle']]);

        return $lignes;
    }

    /**
     * Évolution mois par mois de la proximité du groupe avec chacun des autres.
     *
     * Même règle d'accord que {@see proximites()}, resserrée sur le mois du
     * scrutin : c'est la lecture qui montre les recompositions, un groupe
     * pouvant s'éloigner d'un autre au fil d'une législature sans que la
     * moyenne d'ensemble le laisse voir. Les non-inscrits en sont écartés,
     * comme des coalitions.
     *
     * @return array{mois: list<string>, series: list<array{groupe: string, couleur: string, scores: list<int|null>}>}
     */
    private function proximiteParMois(int $groupeId): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            "SELECT DATE_FORMAT(s.date_scrutin, '%Y-%m') AS mois,
                    g.libelle_abrev,
                    " . CouleurGroupe::SQL . " AS couleur,
                    ROUND(AVG(nous.position_majoritaire = autre.position_majoritaire
                              AND nous.position_majoritaire IN ('pour', 'contre')) * 100) AS score
             FROM vote_groupe nous
             JOIN vote_groupe autre ON autre.scrutin_id = nous.scrutin_id AND autre.groupe_id <> nous.groupe_id
             JOIN groupe g ON g.id = autre.groupe_id
             JOIN scrutin s ON s.id = nous.scrutin_id
             WHERE nous.groupe_id = :groupe AND g.libelle_abrev <> :non_inscrits
             GROUP BY mois, g.id
             ORDER BY mois",
            ['groupe' => $groupeId, 'non_inscrits' => self::NON_INSCRITS],
        );

        if ($lignes === []) {
            return ['mois' => [], 'series' => []];
        }

        $mois = array_values(array_unique(array_column($lignes, 'mois')));
        $rangs = array_flip($mois);

        // Une courbe par groupe, trouée aux mois où il n'existait pas encore ou
        // n'a rien voté : Chart.js interrompt le trait plutôt que de le faire
        // plonger à zéro, ce qui serait un contresens.
        $series = [];
        foreach ($lignes as $ligne) {
            $sigle = (string) $ligne['libelle_abrev'];
            $series[$sigle] ??= [
                'groupe' => $sigle,
                'couleur' => (string) ($ligne['couleur'] ?? '#6c757d'),
                'scores' => array_fill(0, \count($mois), null),
            ];
            $series[$sigle]['scores'][$rangs[$ligne['mois']]] = (int) $ligne['score'];
        }

        return ['mois' => $mois, 'series' => array_values($series)];
    }

    /**
     * Couleur d'affichage de chaque groupe d'une législature, par sigle : les
     * coalitions ne manipulent que des sigles.
     *
     * @return array<string, string>
     */
    private function couleursParSigle(int $legislature): array
    {
        return $this->connection->fetchAllKeyValue(
            'SELECT g.libelle_abrev, ' . CouleurGroupe::SQL . '
             FROM groupe g WHERE g.legislature = :legislature',
            ['legislature' => $legislature],
        );
    }

    /**
     * Les coalitions dans lesquelles le groupe s'est le plus souvent trouvé.
     *
     * Une coalition, c'est l'ensemble des groupes ayant pris la même position
     * majoritaire sur un scrutin — « pour » ou « contre », l'abstention n'en
     * formant pas une. Les non-inscrits en sont exclus : ils ne constituent pas
     * un groupe et ne s'allient pas. Le calcul se fait à la volée plutôt que
     * dans une table précalculée, la signature d'un camp se construisant en un
     * `GROUP_CONCAT`.
     *
     * @return list<array{sigles: list<string>, votes: int}>
     */
    private function coalitions(int $groupeId, int $legislature): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            "SELECT camps.coalition, COUNT(*) AS votes
             FROM (
                 SELECT vg.scrutin_id,
                        GROUP_CONCAT(g.libelle_abrev ORDER BY g.libelle_abrev SEPARATOR '|') AS coalition,
                        SUM(vg.groupe_id = :groupe) AS nous
                 FROM vote_groupe vg
                 JOIN groupe g ON g.id = vg.groupe_id
                 WHERE g.legislature = :legislature
                   AND vg.position_majoritaire IN ('pour', 'contre')
                   AND g.libelle_abrev <> :non_inscrits
                 GROUP BY vg.scrutin_id, vg.position_majoritaire
             ) camps
             WHERE camps.nous = 1
             GROUP BY camps.coalition
             ORDER BY votes DESC
             LIMIT 24",
            ['groupe' => $groupeId, 'legislature' => $legislature, 'non_inscrits' => self::NON_INSCRITS],
        );

        // Un groupe rebaptisé en cours de législature — UDR devenu UDDPLR —
        // produirait deux signatures pour une seule et même coalition, et la
        // scinderait en deux lignes moitié moins fréquentes. Recollage sur le
        // sigle du successeur, borné à la seule filiation que le site recolle
        // (cf. SIGLES_CANONIQUES) ; UDR et UDDPLR ne coexistent sur aucun
        // scrutin, la substitution ne peut donc pas faire apparaître deux fois
        // le même sigle dans une coalition.
        $coalitions = [];
        foreach ($lignes as $ligne) {
            $sigles = array_map(
                static fn (string $sigle) => self::SIGLES_CANONIQUES[$sigle] ?? $sigle,
                explode('|', (string) $ligne['coalition']),
            );
            sort($sigles);

            $cle = implode('|', $sigles);
            $coalitions[$cle] ??= ['sigles' => $sigles, 'votes' => 0];
            $coalitions[$cle]['votes'] += (int) $ligne['votes'];
        }

        usort($coalitions, static fn (array $a, array $b) => $b['votes'] <=> $a['votes']);

        // L'ordre alphabétique ci-dessus n'est que la clé de recollage : à
        // l'affichage, le site range les badges d'une coalition par effectif
        // décroissant (`format_coalitions()`, uasort sur `groupes_effectif`) —
        // RN, EPR, DR, DEM… et non DEM, DR, EPR.
        $effectifs = $this->effectifsParSigle($legislature);
        foreach ($coalitions as &$coalition) {
            usort(
                $coalition['sigles'],
                static fn (string $a, string $b) => [$effectifs[$b] ?? 0, $a] <=> [$effectifs[$a] ?? 0, $b],
            );
        }

        return $coalitions;
    }

    /**
     * Effectif de chaque groupe de la législature, pour ranger les badges.
     *
     * La composition suit la règle du projet : les rattachements principaux
     * couvrant la date de référence du groupe — aujourd'hui pour un groupe en
     * activité, son jour de disparition pour un groupe dissous. Compter sur
     * `depute.groupe_id` renverrait zéro pour tout groupe dissous et pour
     * toute législature passée.
     *
     * @return array<string, int>
     */
    private function effectifsParSigle(int $legislature): array
    {
        $effectifs = [];

        foreach ($this->connection->fetchAllKeyValue(
            'SELECT g.libelle_abrev, COUNT(DISTINCT fg.depute_id) AS effectif
             FROM groupe g
             LEFT JOIN fonction_groupe fg ON fg.groupe_id = g.id AND fg.nomin_principale = 1
                 AND fg.date_debut <= COALESCE(g.date_fin, CURDATE())
                 AND (fg.date_fin IS NULL OR fg.date_fin >= COALESCE(g.date_fin, CURDATE()))
             WHERE g.legislature = :legislature
             GROUP BY g.id',
            ['legislature' => $legislature],
        ) as $sigle => $effectif) {
            $sigle = self::SIGLES_CANONIQUES[$sigle] ?? (string) $sigle;
            $effectifs[$sigle] = max($effectifs[$sigle] ?? 0, (int) $effectif);
        }

        return $effectifs;
    }

    /**
     * Taux de proximité du groupe avec chacun des autres, du plus proche au
     * plus éloigné.
     *
     * Deux groupes sont d'accord sur un scrutin **quand ils ont voté pour tous
     * les deux, ou contre tous les deux** — deux abstentions ne valent pas
     * accord, et une abstention face à un vote pour ne vaut pas désaccord
     * partiel : c'est 0 ou 1 (`daily.php:2176`). La règle est contre-intuitive
     * mais c'est celle qu'affiche le site depuis toujours.
     *
     * @return list<array<string, mixed>>
     */
    private function proximites(int $groupeId): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT g.id, g.libelle, g.libelle_abrev, g.legislature, g.position_politique,
                    g.date_fin IS NOT NULL AS dissous,
                    " . CouleurGroupe::SQL . " AS couleur,
                    COUNT(*) AS votes,
                    ROUND(AVG(nous.position_majoritaire = autre.position_majoritaire
                              AND nous.position_majoritaire IN ('pour', 'contre')) * 100) AS score
             FROM vote_groupe nous
             JOIN vote_groupe autre ON autre.scrutin_id = nous.scrutin_id AND autre.groupe_id <> nous.groupe_id
             JOIN groupe g ON g.id = autre.groupe_id
             WHERE nous.groupe_id = :groupe
             GROUP BY g.id
             ORDER BY score DESC, votes DESC",
            ['groupe' => $groupeId],
        );
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Paramètres de route communs aux cinq pages du groupe.
     *
     * @param array<string, mixed> $groupe
     *
     * @return array{legislature: int, abrev: string}
     */
    private function parametresRoute(array $groupe): array
    {
        return [
            'legislature' => (int) $groupe['legislature'],
            'abrev' => mb_strtolower((string) $groupe['libelle_abrev']),
        ];
    }

    /**
     * Tronc commun du fil d'Ariane : Datan, Groupes, puis le groupe. L'origine
     * n'intercale la législature — « Législature 16 » — que sur la fiche et la
     * page des membres, jamais sur les votes ni les statistiques, et seulement
     * hors législature courante.
     *
     * @param array<string, mixed> $groupe
     *
     * @return list<array{nom: string, url: string}>
     */
    private function filAriane(array $groupe, bool $avecLegislature): array
    {
        $legislature = (int) $groupe['legislature'];

        $fil = [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => 'Groupes', 'url' => $this->generateUrl('groupes_index')],
        ];

        if ($avecLegislature && $legislature !== Legislature::COURANTE) {
            $fil[] = ['nom' => 'Législature ' . $legislature, 'url' => $this->generateUrl('groupes_legislature', ['legislature' => $legislature])];
        }

        $fil[] = ['nom' => $this->datan->nameGroup($groupe['libelle']), 'url' => $this->generateUrl('groupe_individual', $this->parametresRoute($groupe))];

        return $fil;
    }

    private function groupe(int $legislature, string $abrev): array
    {
        $groupe = $this->connection->fetchAssociative(
            'SELECT id, uid, libelle, libelle_abrev, libelle_abrege, couleur, position_politique,
                    legislature, date_debut, date_fin
             FROM groupe
             WHERE legislature = :legislature AND LOWER(libelle_abrev) = :abrev
             LIMIT 1',
            ['legislature' => $legislature, 'abrev' => mb_strtolower($abrev)],
        );

        if ($groupe === false) {
            throw $this->createNotFoundException('Groupe introuvable.');
        }

        return $groupe;
    }

    /**
     * « juillet 2024 » : le mois de création tel que l'écrit la phrase de
     * présentation du groupe (`strftime('%B %Y')` du site de référence).
     */
    private function moisEtAnnee(?string $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        $jour = new \DateTimeImmutable($date);

        // Retire le quantième que rend `date_fr` : « 18 juillet 2024 » → « juillet 2024 ».
        return preg_replace('/^\d+\s+/', '', $this->datan->dateFr($jour)) ?? '';
    }

    /**
     * Une famille socio-professionnelle tirée au sort, et la part des membres
     * du groupe qui en relèvent.
     *
     * Le tirage est bien celui de l'application d'origine (`ORDER BY rand()` de
     * `Jobs_model::get_group_category_random()`) : la carte montre à chaque
     * passage une facette différente du groupe plutôt que toujours la même. Le
     * cache HTTP le fige pour la durée de vie de la page, comme le cache de
     * trois jours du site de référence.
     *
     * @return array{famille: string, n: int, pct: int, population: float}|null
     */
    private function origineSociale(int $groupeId, int $effectif): ?array
    {
        if ($effectif === 0) {
            return null;
        }

        $population = FamilleSocioPro::population();
        $famille = array_rand($population);

        $n = (int) $this->connection->fetchOne(
            'SELECT COUNT(*)
             FROM fonction_groupe fg
             JOIN profil_social ps ON ps.depute_id = fg.depute_id
             WHERE fg.groupe_id = :groupe AND fg.nomin_principale = 1 AND fg.date_fin IS NULL
               AND ps.fam_soc_pro = :famille',
            ['groupe' => $groupeId, 'famille' => $famille],
        );

        return [
            'famille' => $famille,
            'n' => $n,
            'pct' => (int) round($n / $effectif * 100),
            'population' => $population[$famille],
        ];
    }

    /**
     * Les groupes de la législature, pour le pied de page de la fiche : ceux
     * encore en activité sur la législature courante, tous sur une passée.
     *
     * @return list<array<string, mixed>>
     */
    private function groupesDeLaLegislature(int $legislature): array
    {
        $courante = $legislature === Legislature::COURANTE;

        // La législature en cours se range par effectif décroissant, du plus
        // gros groupe au plus petit ; une législature passée, par ordre
        // alphabétique — l'effectif d'un groupe dissous n'ayant plus de sens.
        // Ce sont les deux branches de `Groupes_model::get_groupes_all()`.
        return $this->connection->fetchAllAssociative(
            'SELECT g.legislature, g.libelle, g.libelle_abrev,
                    COUNT(DISTINCT fg.depute_id) AS effectif
             FROM groupe g
             LEFT JOIN fonction_groupe fg ON fg.groupe_id = g.id
                                         AND fg.nomin_principale = 1 AND fg.date_fin IS NULL
             WHERE g.legislature = :legislature AND g.libelle_abrev <> :ni'
                . ($courante ? ' AND g.date_fin IS NULL' : '') . '
             GROUP BY g.id
             ORDER BY ' . ($courante ? 'effectif DESC, ' : '') . 'g.libelle',
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS],
        );
    }

    /**
     * Effectif du groupe, présidence comprise — celui qu'annonce la carte
     * d'identité sur toutes les pages du groupe.
     *
     * @param array<string, mixed> $groupe
     */
    private function effectif(array $groupe): int
    {
        return \count(array_merge(...array_values($this->composition($groupe))));
    }

    /**
     * Effectif, âge moyen et taux de féminisation du groupe.
     *
     * Comptés sur la composition déjà chargée plutôt que par une requête sur
     * `depute.groupe_id` : celle-ci renverrait zéro pour tout groupe dissous.
     * Les apparentés comptent dans l'effectif, comme à l'Assemblée.
     *
     * @param list<array<string, mixed>> $composition
     *
     * @return array<string, int|null>
     */
    private function chiffres(array $composition): array
    {
        $ages = array_filter(array_column($composition, 'age'), static fn ($age) => $age !== null);
        $civilites = array_filter(array_column($composition, 'civilite'), static fn ($c) => $c !== null);
        $femmes = \count(array_filter($civilites, static fn (string $c) => $c === 'Mme'));

        return [
            'effectif' => \count($composition),
            'age_moyen' => $ages !== [] ? (int) round(array_sum($ages) / \count($ages)) : null,
            'femmes' => $femmes,
            'feminisation' => $civilites !== [] ? (int) round($femmes / \count($civilites) * 100) : null,
        ];
    }

    /**
     * Comportement du groupe : cohésion moyenne et participation moyenne,
     * agrégées sur toutes ses ventilations de scrutin.
     *
     * @return array<string, float|int|null>
     */
    private function comportement(int $groupeId): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS scrutins,
                    ROUND(AVG(' . self::COHESION_SQL . '), 3) AS cohesion_moyenne,
                    ROUND(AVG(' . ParticipationGroupe::SQL . ') * 100) AS participation_moyenne
             FROM vote_groupe vg
             WHERE vg.groupe_id = :groupe',
            ['groupe' => $groupeId],
        ) ?: [];

        return [
            'scrutins' => (int) ($row['scrutins'] ?? 0),
            'cohesion_moyenne' => $row['cohesion_moyenne'] !== null ? (float) $row['cohesion_moyenne'] : null,
            'participation_moyenne' => $row['participation_moyenne'] !== null ? (int) $row['participation_moyenne'] : null,
        ];
    }

    /**
     * Derniers votes décryptés, avec la position prise par le groupe.
     *
     * @return list<array<string, mixed>>
     */
    private function derniersVotes(int $groupeId): array
    {
        return $this->connection->fetchAllAssociative(
            // `position` est le nom qu'attend la carte de vote, partagée avec la
            // page des votes du groupe ; `l.name` y imprime la lecture.
            'SELECT dcr.title, dcr.legislature, dcr.vote_numero, c.name AS categorie_name,
                    l.name AS lecture_name,
                    s.sort_code, s.date_scrutin,
                    vg.position_majoritaire AS position,
                    vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions,
                    ROUND(' . self::COHESION_SQL . ', 3) AS cohesion
             FROM decryptage dcr
             JOIN scrutin s ON s.id = dcr.scrutin_id
             JOIN vote_groupe vg ON vg.scrutin_id = s.id AND vg.groupe_id = :groupe
             LEFT JOIN categorie c ON c.id = dcr.categorie_id
             LEFT JOIN lecture l ON l.id = dcr.lecture_id
             WHERE dcr.state = :published
             ORDER BY s.date_scrutin DESC
             LIMIT 6',
            ['groupe' => $groupeId, 'published' => 'published'],
        );
    }

    /**
     * Présidences successives du groupe, la plus récente en tête.
     * Celle qui n'a pas de date de fin est la présidence en cours.
     *
     * @return list<array<string, mixed>>
     */
    private function presidences(int $groupeId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    f.date_debut, f.date_fin
             FROM fonction_groupe f
             JOIN depute d ON d.id = f.depute_id
             WHERE f.groupe_id = :groupe AND f.code_qualite = :qualite
             ORDER BY f.date_fin IS NOT NULL, f.date_debut DESC',
            ['groupe' => $groupeId, 'qualite' => FonctionGroupe::QUALITE_PRESIDENT],
        );
    }

    /**
     * Les incarnations successives du groupe, de la plus ancienne à la plus
     * récente, telles que déclarées par {@see FamilleGroupe} : un groupe change
     * de nom d'une législature à l'autre, voire au sein d'une même législature.
     *
     * L'effectif est compté sur les rattachements historiques
     * ({@see \App\Entity\FonctionGroupe}) et non sur `depute.groupe_id`, qui ne
     * reflète que la situation courante.
     *
     * @return list<array<string, mixed>>
     */
    private function historique(string $uid, int $groupeIdCourant): array
    {
        $famille = FamilleGroupe::pour($uid);
        if (\count($famille) === 1) {
            return [];
        }

        return $this->connection->fetchAllAssociative(
            'SELECT g.id, g.legislature, g.libelle, g.libelle_abrev, g.couleur,
                    g.date_debut, g.date_fin,
                    COUNT(DISTINCT f.depute_id) AS effectif
             FROM groupe g
             LEFT JOIN fonction_groupe f ON f.groupe_id = g.id
             WHERE g.uid IN (:famille) AND g.id <> :courant AND g.legislature >= :premiere
             GROUP BY g.id, g.legislature, g.libelle, g.libelle_abrev, g.couleur, g.date_debut, g.date_fin
             ORDER BY g.date_debut',
            ['famille' => $famille, 'courant' => $groupeIdCourant, 'premiere' => Legislature::PREMIERE],
            ['famille' => ArrayParameterType::STRING],
        );
    }

    /**
     * Taux de soutien au gouvernement : la part des textes présentés par le
     * Gouvernement que le groupe a votés.
     *
     * Ne comptent que les scrutins qui adoptent ou rejettent un texte dans son
     * ensemble ({@see NatureVote::FINALE}) et qui portent sur un projet de loi
     * ({@see Dossier::PROCEDURES_GOUVERNEMENT}) : les votes sur des amendements
     * ou des articles ne disent rien du soutien au texte lui-même.
     *
     * @return array{votes: int, soutiens: int, pourcentage: int|null}
     */
    private function soutien(int $groupeId): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT COUNT(*) AS votes, SUM(vg.position_majoritaire = :pour) AS soutiens
             FROM scrutin s
             JOIN dossier d ON d.id = s.dossier_id
             JOIN vote_groupe vg ON vg.scrutin_id = s.id AND vg.groupe_id = :groupe
             WHERE s.nature_vote = :finale AND d.procedure_code IN (:procedures)',
            [
                'groupe' => $groupeId,
                'pour' => self::POSITION_POUR,
                'finale' => NatureVote::FINALE,
                'procedures' => Dossier::PROCEDURES_GOUVERNEMENT,
            ],
            ['procedures' => ArrayParameterType::INTEGER],
        ) ?: [];

        $votes = (int) ($row['votes'] ?? 0);
        $soutiens = (int) ($row['soutiens'] ?? 0);

        return [
            'votes' => $votes,
            'soutiens' => $soutiens,
            'pourcentage' => $votes > 0 ? (int) round($soutiens / $votes * 100) : null,
        ];
    }

    /**
     * Les députés rattachés au groupe, séparés en membres de plein droit et
     * apparentés.
     *
     * La composition ne se lit pas sur `depute.groupe_id`, qui ne porte que la
     * situation du jour : la page d'un groupe dissous serait vide. Elle se lit
     * sur `fonction_groupe`, aux dates d'existence du groupe — pour un groupe
     * clos, l'appartenance retenue est celle du jour de sa disparition, comme
     * dans l'application d'origine (`Groupes_model::get_groupe_membres()`).
     *
     * `nomin_principale` n'est pas une précaution de style : onze députés de la
     * 17e législature portent un second rattachement ouvert, et sans ce filtre
     * ils apparaîtraient dans deux groupes à la fois.
     *
     * La présidence forme une troisième liste : l'application d'origine la
     * distingue par la préséance du mandat (`preseance IN (20, 28)` pour les
     * membres, `24` pour les apparentés — le président n'a ni l'une ni
     * l'autre), et l'affiche seule au-dessus des membres. `code_qualite`
     * opère ici le même partage. Le président reste compté dans l'effectif.
     *
     * @param array<string, mixed> $groupe
     *
     * @return array{presidents: list<array<string, mixed>>, membres: list<array<string, mixed>>, apparentes: list<array<string, mixed>>}
     */
    private function composition(array $groupe): array
    {
        $clos = $groupe['date_fin'] !== null;

        // L'âge s'apprécie à la clôture d'une législature achevée et non
        // aujourd'hui : `depute.age` vieillirait d'autant une assemblée
        // dissoute depuis des années, et le site de référence annonce bien
        // « lors de la fin de la 16ème législature ».
        $reference = $this->moyennes->dateDeReference((int) $groupe['legislature']);
        $age = $reference === null ? 'CURRENT_DATE' : ':reference';

        $lignes = $this->connection->fetchAllAssociative(
            'SELECT d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    TIMESTAMPDIFF(YEAR, d.date_naissance, ' . $age . ') AS age,
                    d.departement_nom, d.departement_code, d.circonscription,
                    appartenance.code_qualite,
                    g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur,
                    dl.legislature_last
             FROM (
                 SELECT fg.depute_id, fg.code_qualite,
                        ROW_NUMBER() OVER (PARTITION BY fg.depute_id ORDER BY fg.date_debut DESC) AS rang
                 FROM fonction_groupe fg
                 WHERE fg.groupe_id = :groupe AND fg.nomin_principale = 1
                   AND ' . ($clos ? ':fin BETWEEN fg.date_debut AND fg.date_fin' : 'fg.date_fin IS NULL') . '
             ) appartenance
             JOIN depute d ON d.id = appartenance.depute_id
             JOIN groupe g ON g.id = :groupe
             LEFT JOIN (SELECT depute_id, MAX(legislature) AS legislature_last
                        FROM mandat GROUP BY depute_id) dl ON dl.depute_id = d.id
             WHERE appartenance.rang = 1
             ORDER BY d.lastname, d.firstname',
            ['groupe' => (int) $groupe['id']]
                + ($clos ? ['fin' => $groupe['date_fin']] : [])
                + ($reference === null ? [] : ['reference' => $reference]),
        );

        $presidents = [];
        $membres = [];
        $apparentes = [];

        foreach ($lignes as $ligne) {
            match ($ligne['code_qualite']) {
                FonctionGroupe::QUALITE_PRESIDENT => $presidents[] = $ligne,
                FonctionGroupe::QUALITE_APPARENTE => $apparentes[] = $ligne,
                default => $membres[] = $ligne,
            };
        }

        return ['presidents' => $presidents, 'membres' => $membres, 'apparentes' => $apparentes];
    }

    /**
     * Nombre de députés du groupe candidats aux municipales de 2026, pour
     * l'encart en tête de fiche (`Elections_model::get_n_candidates_by_group`).
     *
     * Le critère est celui, éprouvé, du bloc de l'accueil
     * ({@see HomeController::electionMunicipales}) : candidature visible ET
     * positive ET député encore en exercice. Le site, lui, compte sur le
     * `groupeId` de sa table `deputes_last` sans filtre d'activité, si bien
     * qu'un candidat qui a quitté l'Assemblée y reste compté « député membre
     * du groupe » — LFI-NFP 51 chez lui contre 50 ici, la ligne d'écart étant
     * un ex-député. Divergence assumée : la phrase de l'encart dit « députés
     * membres du groupe », on compte des députés membres du groupe.
     *
     * Nul (encart absent) tant que l'élection n'a aucune candidature en base :
     * la donnée vient d'`app:import:elections`, hors synchronisation — sans
     * import joué, annoncer « aucun candidat » serait faux.
     */
    private function candidatsMunicipales(int $groupeId): ?int
    {
        $compte = $this->connection->fetchAssociative(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(d.groupe_id = :groupe
                        AND EXISTS (SELECT 1 FROM mandat m
                                    WHERE m.depute_id = d.id
                                      AND m.legislature = :legislature
                                      AND m.date_fin IS NULL)), 0) AS candidats
             FROM candidature c
             JOIN election e ON e.id = c.election_id
             JOIN depute d ON d.id = c.depute_id
             WHERE e.slug = 'municipales-2026' AND c.visible = 1 AND c.candidat = 1",
            ['groupe' => $groupeId, 'legislature' => Legislature::COURANTE],
        );

        if ($compte === false || (int) $compte['total'] === 0) {
            return null;
        }

        return (int) $compte['candidats'];
    }
}
