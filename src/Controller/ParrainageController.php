<?php

namespace App\Controller;

use App\Http\RechauffeurCache;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Parrainages de l'élection présidentielle de 2022 (contrôleur Parrainages de
 * l'application CodeIgniter d'origine, vue application/views/parrainages/).
 *
 * La page présente trois choses : les candidats ayant réuni plus de 500
 * signatures (tous parrains confondus), le décompte des parrainages accordés par
 * les seuls députés, et la liste nominative de ces 530 députés parrains. La table
 * `parrainage` est reprise en entier ({@see \App\Command\ImportParrainagesCommand}) ;
 * seul le volet des députés est nominatif, les autres élus ne comptent que dans
 * les totaux.
 */
class ParrainageController extends AbstractController
{
    /** L'élection est passée et ses parrainages ne bougent plus. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** La seule présidentielle portée : la page du legacy est figée sur 2022. */
    private const ANNEE = 2022;

    /**
     * Seuil de signatures d'un candidat « sélectionné » (`Parrainages_model::get_500`).
     * Strictement supérieur : le Conseil constitutionnel valide à partir de 500,
     * et le legacy affiche ceux qui dépassent ce plancher.
     */
    private const SEUIL_SELECTION = 500;

    /**
     * Les mandats qui font d'un parrain un député (`where_in('mandat', …)` du
     * legacy). La source écrit le libellé en minuscules et au féminin distinct.
     */
    private const MANDATS_DEPUTE = ['député', 'députée'];

    /**
     * Nom d'affichage d'un candidat, du « NOM Prénom » brut de la source vers la
     * forme lisible (`Parrainages_model::change_candidate_name`).
     *
     * Reprise telle quelle du legacy, idiosyncrasies comprises — « Gaspar Koenig »
     * y perd son « d », mais c'est la forme que datan.fr affiche, et ce candidat
     * n'a qu'un parrainage de député. Un « NOM Prénom » absent de la table reste
     * inchangé, comme dans la source.
     */
    private const NOMS_CANDIDATS = [
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

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/parrainages-2022', name: 'parrainages_index', methods: ['GET'])]
    public function index(): Response
    {
        $selectionnes = array_map($this->nommer(...), $this->candidatsSelectionnes());
        $deputes = array_map($this->nommer(...), $this->parrainagesDeputes());
        $candidats = array_map($this->nommer(...), $this->candidatsDeputes());

        $response = $this->render('parrainages/index.html.twig', [
            'selectionnes' => $selectionnes,
            'deputes' => $deputes,
            'candidats' => $candidats,
            // Fil d'Ariane de `Parrainages::index()` : « Datan » › « Parrainages
            // 2022 » (maillon courant). L'origine pointe son URL de JSON-LD sur
            // `/parrainages`, qui répond 404 ; on rend la vraie adresse.
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Parrainages 2022', 'url' => $this->generateUrl('parrainages_index')],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Candidats ayant réuni plus de 500 signatures, tous parrains confondus
     * (`get_500`). Le décompte porte sur l'ensemble des élus, pas seulement les
     * députés.
     *
     * @return list<array<string, mixed>>
     */
    private function candidatsSelectionnes(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT candidat, COUNT(*) AS n
             FROM parrainage
             WHERE annee = :annee
             GROUP BY candidat
             HAVING n > :seuil
             ORDER BY n DESC',
            ['annee' => self::ANNEE, 'seuil' => self::SEUIL_SELECTION],
        );
    }

    /**
     * Nombre de parrainages accordés par des députés, par candidat, du plus
     * parrainé au moins parrainé (`get_candidates`). Sert le graphique et la
     * phrase de tête (« le candidat qui arrive en tête… »).
     *
     * @return list<array<string, mixed>>
     */
    private function candidatsDeputes(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT candidat, COUNT(*) AS parrainages
             FROM parrainage
             WHERE annee = :annee AND mandat IN (:mandats)
             GROUP BY candidat
             ORDER BY parrainages DESC',
            ['annee' => self::ANNEE, 'mandats' => self::MANDATS_DEPUTE],
            ['mandats' => \Doctrine\DBAL\ArrayParameterType::STRING],
        );
    }

    /**
     * Les 530 parrainages de députés, nommément (`get_parrainages(2022, TRUE)`).
     *
     * Le nom affiché reste celui de la source (`p.prenom`, `p.nom`) : c'est ce que
     * montre datan.fr, jusqu'à ses accents manquants (« Eric ») et son instantané
     * de 2022 (« Nicole Gries-trisse », depuis « Nicole Trisse »). Le reste vient
     * de la fiche du député, comme le legacy le tire de `deputes_last` :
     *
     * - **le groupe** par le rattachement le plus récent (`fonction_groupe`,
     *   encore ouvert d'abord, puis fin la plus tardive) et non par
     *   `depute.groupe_id`, qui ne porte que l'appartenance courante — 377 de ces
     *   530 sont d'anciens députés, sans groupe courant, mais que datan.fr montre
     *   avec leur dernier groupe connu ;
     * - **le département** par `depute`, en casse canonique (« Haute-Corse (2B) »),
     *   le legacy l'écrivant en minuscules (« Haute-corse (2b) ») comme sur les
     *   pages d'élections — même correction assumée.
     *
     * Le lien vers la fiche (`slug`, `dpt_slug`) est joint pour que le gabarit
     * n'en fabrique un que si le député en a une : un parrain sans fiche (aucun
     * ici, mais la garde tient) s'affiche en clair, jamais en lien qui répondrait
     * 404 ({@see DeputeController}).
     *
     * @return list<array<string, mixed>>
     */
    private function parrainagesDeputes(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT p.nom, p.prenom, p.candidat,
                    d.slug, d.dpt_slug, d.departement_nom, d.departement_code,
                    (SELECT g.libelle FROM fonction_groupe fg
                     JOIN groupe g ON g.id = fg.groupe_id
                     WHERE fg.depute_id = d.id
                     ORDER BY (fg.date_fin IS NULL) DESC, fg.date_fin DESC, fg.id DESC
                     LIMIT 1) AS groupe_libelle
             FROM parrainage p
             LEFT JOIN depute d ON d.mp_id = p.mp_id
             WHERE p.annee = :annee AND p.mandat IN (:mandats)
             ORDER BY p.nom, p.prenom',
            ['annee' => self::ANNEE, 'mandats' => self::MANDATS_DEPUTE],
            ['mandats' => \Doctrine\DBAL\ArrayParameterType::STRING],
        );
    }

    /**
     * Applique le nom d'affichage du candidat à une ligne, sur la clé `candidat`.
     *
     * @param array<string, mixed> $ligne
     *
     * @return array<string, mixed>
     */
    private function nommer(array $ligne): array
    {
        $brut = (string) $ligne['candidat'];
        $ligne['candidat'] = self::NOMS_CANDIDATS[$brut] ?? $brut;

        return $ligne;
    }
}
