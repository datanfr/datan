<?php

namespace App\Controller;

use App\Depute\ComportementDepute;
use App\Legislature;
use App\Repository\ResultatCirconscriptionRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Brique de diffusion embarquée par des sites tiers, portée du contrôleur Iframe
 * de l'application d'origine (Iframe.php, vues iframe/**).
 *
 * Deux contraintes tiennent lieu de contrat : l'**adresse** (indexée, citée dans
 * des intégrations existantes) et l'**embarquabilité**. La réponse ne porte donc
 * aucun `X-Frame-Options` ni `Content-Security-Policy: frame-ancestors` — l'appli
 * n'en pose aucun globalement, et ce contrôleur n'en ajoute pas. Le gabarit est
 * autonome (ni barre de navigation, ni pied de page de site), et n'embarque pas
 * le pistage Matomo du site hôte — ni, du temps où il en avait, sa bannière de
 * consentement et GTM, retirés du site le 10 septembre 2026 ; ni le compteur de
 * pages de sa modale de dons, que son pied d'iframe ne charge pas non plus :
 * les injecter chez un tiers serait un défaut, pas une parité.
 *
 * Le contenu reprend les blocs de la fiche de député tels que ce portage les
 * rend : positions importantes, carrousel des derniers votes décryptés,
 * élection, et graphiques de comportement politique (participation, loyauté,
 * proximité par groupe). Les lectures de comportement et du carrousel sont
 * partagées avec la fiche via {@see \App\Depute\ComportementDepute} — plus de
 * duplication ici. Seul le rendu diffère : l'iframe bascule ses intitulés à la
 * première personne (« Mon comportement politique ») sous `?first-person=true`,
 * comme les partials `iframe/*_first_person` de l'origine.
 */
class IframeController extends AbstractController
{
    /**
     * Cache de trois jours, comme le `$this->output->cache("4320")` (minutes) de
     * l'application d'origine : une page très rappelée par des tiers, dont les
     * données ne bougent qu'au rythme des scrutins.
     */
    private const CACHE_TTL = 259200;

    /** Blocs affichables, dans l'ordre par défaut de l'origine (hors explication et questions, non portés). */
    private const BLOCS = ['positions-importantes', 'derniers-votes', 'election', 'comportement-politique'];

    public function __construct(
        private readonly Connection $connection,
        private readonly ResultatCirconscriptionRepository $resultatsCirconscription,
        private readonly ComportementDepute $comportement,
    ) {
    }

    /**
     * Page de démonstration : un exemple d'intégration, comme iframe/index.php.
     * Autonome et sans pistage, elle aussi.
     */
    #[Route('/iframe', name: 'iframe_index', methods: ['GET'])]
    public function index(): Response
    {
        $response = $this->render('iframe/index.html.twig');
        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    #[Route('/iframe/depute/{slug}', name: 'iframe_depute', requirements: ['slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function depute(string $slug, Request $request): Response
    {
        // Même parade que DeputeController (jamais modifié ici) : un slug n'est
        // pas unique — un sénateur homonyme sans page peut le partager —, la ligne
        // qui porte un dpt_slug (donc une fiche) l'emporte, et sans dpt_slug il
        // n'y a pas de page à embarquer, donc 404.
        $depute = $this->connection->fetchAssociative(
            // `dep.libelle_de` est l'article du département (« des », « du », avec son
            // espace final quand il en faut un). Le bloc élection est rendu par le
            // partial de la fiche, qui l'attend : sans cette jointure l'iframe écrivait
            // « circonscription Hauts-de-Seine (92) » quand la fiche — et datan.fr, qui
            // charge le même `_election.php` dans les deux contextes — écrit « des ».
            'SELECT d.id, d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    d.departement_nom, d.departement_code, d.circonscription, d.region,
                    g.id AS groupe_id, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    g.couleur AS groupe_couleur,
                    dep.libelle_de
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             LEFT JOIN departement dep ON dep.code = d.departement_code
             WHERE d.slug = :slug
             ORDER BY (d.dpt_slug IS NULL), d.id
             LIMIT 1',
            ['slug' => $slug],
        );

        if ($depute === false || $depute['dpt_slug'] === null) {
            throw $this->createNotFoundException('Député introuvable ou sans fiche.');
        }

        $deputeId = (int) $depute['id'];
        $groupeId = $depute['groupe_id'] !== null ? (int) $depute['groupe_id'] : null;
        $mandats = $this->mandats($deputeId);

        // Le site ne diffuse que la 14e législature et au-delà (Iframe.php). Nos
        // députés à fiche siègent tous à partir de la 14e ; la garde reste posée.
        if ($mandats === [] || (int) $mandats[0]['legislature'] < Legislature::PREMIERE) {
            throw $this->createNotFoundException('Législature non diffusée.');
        }

        // Statut (présent/passé des phrases) : un mandat le plus récent sans date
        // de fin signifie que le député est en exercice — comme sur la fiche, et
        // non depute.date_fin, jamais rafraîchie.
        $actif = $mandats[0]['date_fin'] === null;

        $blocs = $this->blocsDemandes($request->query->get('categories'));

        $election = \in_array('election', $blocs, true)
            ? $this->resultatsCirconscription->pourDepute(
                $mandats[0]['departement_code'],
                $mandats[0]['circonscription'] !== null ? (int) $mandats[0]['circonscription'] : null,
                (int) $mandats[0]['legislature'],
                (string) $depute['lastname'],
            )
            : null;

        $premierePersonne = $request->query->get('first-person') === 'true';
        $femme = $depute['civilite'] === 'Mme';

        $response = $this->render('iframe/depute.html.twig', [
            'depute' => $depute,
            'blocs' => $blocs,
            'actif' => $actif,
            'nom_complet' => $depute['firstname'] . ' ' . $depute['lastname'],
            // Bloc « positions importantes » : la sélection éditoriale figée de
            // scrutins marquants, celle-là même que rend la fiche. L'iframe
            // lisait jusqu'ici `positionsCles()` — des votes décryptés
            // quelconques, une autre donnée sous le même titre.
            'positions_importantes' => \in_array('positions-importantes', $blocs, true) ? $this->comportement->positionsImportantes($deputeId) : [],
            // Carrousel « Ses derniers votes » : les scrutins DÉCRYPTÉS où le
            // député s'est exprimé, mêmes cartes que la fiche (lecture partagée).
            'carrousel_votes' => \in_array('derniers-votes', $blocs, true) ? $this->comportement->votesDecryptes($deputeId, 5) : [],
            // Graphiques de comportement : mêmes jauges que la fiche. Null pour
            // une fiche sans votes nominatifs (avant la 17e) — le bloc se tait,
            // il n'affiche pas zéro (cf. CLAUDE.md), dans l'iframe comme ailleurs.
            'statistiques' => \in_array('comportement-politique', $blocs, true)
                ? $this->comportement->statistiques($deputeId, $groupeId, Legislature::COURANTE)
                : null,
            'election' => $election,
            // Titres : « la députée » / « le député » (gender() de l'origine), et
            // « e » d'accord. La première personne (?first-person=true) bascule
            // « Son » en « Mon », comme les partials _first_person du legacy.
            'depute_mot' => $femme ? 'députée' : 'député',
            'accord' => $femme ? 'e' : '',
            'pronom' => $femme ? 'elle' : 'il',
            'premiere_personne' => $premierePersonne,
            'possessif' => $premierePersonne ? 'Mon' : 'Son',
            'possessif_pluriel' => $premierePersonne ? 'Mes' : 'Ses',
            // ?main-title=hide et ?secondary-title=hide, comme l'origine.
            'titre_principal_visible' => $request->query->get('main-title') !== 'hide',
            'sous_titres_visibles' => $request->query->get('secondary-title') !== 'hide',
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Blocs à afficher : la liste `?categories=` filtrée sur ceux que nous
     * portons, dans l'ordre demandé, ou l'ordre par défaut de l'origine. Les
     * sous-catégories pointées (`comportement-politique.participation-votes`)
     * sont ramenées à leur bloc principal.
     *
     * @return list<string>
     */
    private function blocsDemandes(?string $categories): array
    {
        if ($categories === null || $categories === '') {
            return self::BLOCS;
        }

        $demandes = array_map(static fn (string $c): string => explode('.', trim($c))[0], explode(',', $categories));
        $retenus = array_values(array_unique(array_filter(
            $demandes,
            static fn (string $c): bool => \in_array($c, self::BLOCS, true),
        )));

        return $retenus !== [] ? $retenus : self::BLOCS;
    }

    /**
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
}
