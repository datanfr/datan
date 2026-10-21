<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\CouleurParti;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Partis politiques (Parties::index et Parties::individual de l'application
 * CodeIgniter d'origine, vues application/views/parties/).
 *
 * Les deux pages affichent la même partition des 58 partis en trois blocs, d'où
 * un seul contrôleur : l'index en fait des cartes, la fiche les répète en pied
 * de page.
 *
 * À ne pas confondre avec les groupes parlementaires ({@see GroupeController}) :
 * le rattachement à un parti est *financier*, il conditionne les subventions
 * publiques et n'implique pas l'adhésion.
 */
class PartiController extends AbstractController
{
    /** Le rattachement financier ne bouge qu'au fil des déclarations. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Ces deux « partis » n'en sont pas : l'Assemblée ouvre un organe PARPOL
     * pour les députés qui déclarent n'être rattachés à aucun parti et un autre
     * pour ceux qui ne déclarent rien. Ils ont leur fiche, mais jamais de carte
     * dans la grille — l'application d'origine les écarte par leur libellé.
     */
    private const NON_RATTACHES = 'NR';
    private const NON_DECLARES = 'ND';

    /**
     * Un rattachement ne compte que si le député siège encore, `dl.dateFin IS
     * NULL` dans `daily.php`. Comme pour les groupes, `depute.parti_id` est
     * renseigné pour tous les députés jamais élus : sans ce filtre, l'effectif
     * d'un parti agrégerait quatre législatures.
     */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = d.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/partis-politiques', name: 'partis_index', methods: ['GET'])]
    public function index(): Response
    {
        $response = $this->render('parti/all.html.twig', $this->blocs() + [
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Partis politiques', 'url' => $this->generateUrl('partis_index')],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    #[Route('/partis-politiques/{abrev}', name: 'parti_individual', requirements: ['abrev' => '[a-zA-Z0-9_\-]+'], methods: ['GET'])]
    public function individual(string $abrev): Response
    {
        $blocs = $this->blocs();
        $parti = $blocs['partis'][mb_strtoupper($abrev)] ?? null;

        if ($parti === null) {
            throw $this->createNotFoundException('Parti politique inconnu.');
        }

        $response = $this->render('parti/individual.html.twig', $blocs + [
            'parti' => $parti,
            'deputes' => $this->deputes((int) $parti['id']),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Partis politiques', 'url' => $this->generateUrl('partis_index')],
                [
                    'nom' => $parti['libelle'],
                    'url' => $this->generateUrl('parti_individual', ['abrev' => mb_strtolower((string) $parti['libelle_abrev'])]),
                ],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Les 58 partis, rangés dans les trois blocs que présentent les deux pages.
     *
     * @return array{
     *     partis: array<string, array<string, mixed>>,
     *     rattaches: list<array<string, mixed>>,
     *     sans_depute: list<array<string, mixed>>,
     *     anciens: list<array<string, mixed>>,
     * }
     */
    private function blocs(): array
    {
        $logos = $this->logos();

        $partis = [];
        $rattaches = [];
        $sansDepute = [];
        $anciens = [];

        foreach ($this->tousLesPartis() as $parti) {
            $parti['effectif'] = (int) $parti['effectif'];
            $parti['logo'] = isset($logos[mb_strtolower((string) $parti['libelle_abrev'])]);

            $partis[$parti['libelle_abrev']] = $parti;

            if ($parti['date_fin'] !== null) {
                // Un parti dissous est un ancien parti, même si des députés lui
                // restent rattachés dans nos données — voir tousLesPartis().
                $anciens[] = $parti;
            } elseif ($parti['effectif'] === 0) {
                $sansDepute[] = $parti;
            } elseif (!\in_array($parti['libelle_abrev'], [self::NON_RATTACHES, self::NON_DECLARES], true)) {
                $rattaches[] = $parti;
            }
        }

        // Les cartes sortent par effectif décroissant, les ex æquo départagés
        // par identifiant d'organe : c'est l'ordre dans lequel `daily.php`
        // matérialise la table `parties` (`ORDER BY effectif DESC` sur un
        // agrégat groupé par `organeRef`), et donc celui de datan.fr. Les deux
        // listes de liens gardent le tri par libellé de la requête, qui suit la
        // collation de la base — trier ici mettrait « Écologistes » après
        // « Républicains ».
        usort($rattaches, static fn (array $a, array $b) => [$b['effectif'], $a['uid']] <=> [$a['effectif'], $b['uid']]);

        return [
            'partis' => $partis,
            'rattaches' => $rattaches,
            'sans_depute' => $sansDepute,
            'anciens' => $anciens,
        ];
    }

    /**
     * Tous les partis et leur effectif de députés rattachés.
     *
     * L'effectif se compte sur `depute.parti_id`, qui porte le rattachement le
     * plus récent — y compris quand il est clos, à la différence de `groupe_id`.
     * Trois députés traînent ainsi un rattachement à Ensemble, dissous en
     * décembre 2025 ; c'est `date_fin` qui les écarte des cartes, pas l'effectif.
     *
     * @return list<array<string, mixed>>
     */
    private function tousLesPartis(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT p.id, p.uid, p.libelle, p.libelle_abrev, p.date_fin,
                    COUNT(d.id) AS effectif,
                    ' . CouleurParti::SQL . ' AS couleur
             FROM parti p
             LEFT JOIN depute d ON d.parti_id = p.id AND ' . self::EN_EXERCICE . '
             GROUP BY p.id
             ORDER BY p.libelle',
            ['legislature' => Legislature::COURANTE],
        );
    }

    /**
     * Députés en exercice rattachés à un parti, avec le groupe parlementaire
     * affiché en pied de carte — un même parti pouvant en réunir plusieurs.
     *
     * @return list<array<string, mixed>>
     */
    private function deputes(int $parti): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug,
                    d.departement_nom, d.departement_code,
                    g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur,
                    (SELECT MAX(ml.legislature) FROM mandat ml WHERE ml.depute_id = d.id) AS legislature_last
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE d.parti_id = :parti AND ' . self::EN_EXERCICE . '
             ORDER BY d.lastname, d.firstname',
            ['parti' => $parti, 'legislature' => Legislature::COURANTE],
        );
    }

    /**
     * Sigles, en minuscules, des partis dont le logo est versionné.
     *
     * L'application d'origine teste l'existence du fichier parti par parti ;
     * un seul parcours du répertoire suffit et évite 58 appels système.
     *
     * @return array<string, true>
     */
    private function logos(): array
    {
        $logos = [];

        foreach (glob($this->getParameter('kernel.project_dir') . '/public/assets/imgs/partis/*.png') ?: [] as $fichier) {
            $logos[basename($fichier, '.png')] = true;
        }

        return $logos;
    }
}
