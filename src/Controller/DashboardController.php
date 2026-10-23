<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Depute;
use App\Entity\Election;
use App\Entity\Explication;
use App\Entity\Scrutin;
use App\Entity\Utilisateur;
use App\Form\ExplicationType;
use App\Http\RechauffeurCache;
use App\Repository\ExplicationRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Espace personnel d'un député (`DashboardMP` de l'application d'origine).
 *
 * Un député y rédige ses explications de vote : la couche éditoriale que Datan
 * ne produit pas lui-même, et le second pilier du site après les décryptages.
 * Elles s'affichent ensuite sur la page du scrutin, sur la page de votes du
 * député et sur la page d'accueil.
 *
 * Une explication ne se rattache qu'à un scrutin **décrypté** : c'est la règle
 * du site, rappelée à l'écran — « cette fonctionnalité n'est disponible que
 * pour les votes contextualisés par Datan ».
 *
 * Aucune de ces pages n'est mise en cache HTTP, contrairement aux pages
 * publiques : elles sont personnelles et derrière le pare-feu.
 */
#[Route('/dashboard')]
#[IsGranted(Utilisateur::ROLE_DEPUTE)]
class DashboardController extends AbstractController
{
    /**
     * Élections dont l'espace député ouvre une fiche de candidature. L'application
     * d'origine n'en ouvre qu'une, les législatives 2022 (`DashboardMP::elections`
     * filtre `in_array($id, array(4))`) ; la constante évite d'écrire « 4 » en dur
     * au fil du contrôleur.
     */
    private const ELECTIONS_FICHE = [4];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ExplicationRepository $explications,
        private readonly Connection $connection,
        private readonly RechauffeurCache $cache,
    ) {
    }

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function accueil(): Response
    {
        $depute = $this->depute();

        return $this->render('dashboard/index.html.twig', [
            'depute' => $depute,
            'brouillons' => $this->explications->explicationsDuDepute($depute->getId(), false),
        ]);
    }

    #[Route('/explications', name: 'dashboard_explications', methods: ['GET'])]
    public function explications(): Response
    {
        $depute = $this->depute();

        return $this->render('dashboard/explications/index.html.twig', [
            'depute' => $depute,
            'brouillons' => $this->explications->explicationsDuDepute($depute->getId(), false),
            'publiees' => $this->explications->explicationsDuDepute($depute->getId(), true),
        ]);
    }

    /**
     * Liste des scrutins que le député peut encore expliquer.
     */
    #[Route('/explications/liste', name: 'dashboard_explications_liste', methods: ['GET'])]
    public function liste(): Response
    {
        $votes = $this->explications->votesAExpliquer($this->depute()->getId());

        return $this->render('dashboard/explications/liste.html.twig', [
            'votes' => $votes,
            'suggestions' => $this->explications->suggestions($votes),
        ]);
    }

    #[Route(
        '/explications/create/l{legislature}v{numero}',
        name: 'dashboard_explication_creer',
        requirements: ['legislature' => '\d+', 'numero' => '-?\d+'],
        methods: ['GET', 'POST'],
    )]
    public function creer(Request $requete, int $legislature, int $numero): Response
    {
        $depute = $this->depute();
        $scrutinId = $this->scrutinDecrypte($legislature, $numero);

        if ($scrutinId === null) {
            throw $this->createNotFoundException('Aucun scrutin décrypté sous ce numéro.');
        }

        $contexte = $this->explications->contexteDuScrutin($scrutinId, $depute->getId());

        // Un député n'explique que ses propres votes : sans position exprimée,
        // il n'a rien à expliquer. L'application d'origine renvoie au choix
        // d'un scrutin plutôt qu'à une page d'erreur.
        if ($contexte === null || $contexte['position_depute'] === null) {
            $this->addFlash('erreur', sprintf(
                'Vous n\'avez pas pris part au vote n° %d. Merci de choisir un scrutin dans cette liste.',
                $numero,
            ));

            return $this->redirectToRoute('dashboard_explications_liste');
        }

        $existante = $this->explications->findOneBy(['scrutin' => $scrutinId, 'depute' => $depute]);

        if ($existante !== null) {
            $this->addFlash('erreur', sprintf(
                'Vous avez déjà rédigé une explication pour le vote n° %d. Vous pouvez la modifier.',
                $numero,
            ));

            return $this->redirectToRoute('dashboard_explication_modifier', [
                'legislature' => $legislature,
                'numero' => $numero,
            ]);
        }

        $explication = new Explication();
        $explication->setDepute($depute);
        $explication->setScrutin($this->entityManager->getReference(Scrutin::class, $scrutinId));

        $formulaire = $this->createForm(ExplicationType::class, $explication);
        $formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $explication->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($explication);
            $this->entityManager->flush();

            if ($explication->isPubliee()) {
                $this->cache->apresPublication();
            }

            $this->addFlash('succes', $explication->isPubliee()
                ? 'Explication publiée. Elle est visible sur la page du scrutin et sur votre page Datan.'
                : 'Explication enregistrée en brouillon. Elle ne sera visible qu\'une fois publiée.');

            return $this->redirectToRoute('dashboard_explications');
        }

        return $this->render('dashboard/explications/form.html.twig', [
            'formulaire' => $formulaire,
            'contexte' => $contexte,
            'explication' => null,
            'legislature' => $legislature,
            'numero' => $numero,
        ]);
    }

    #[Route(
        '/explications/modify/l{legislature}v{numero}',
        name: 'dashboard_explication_modifier',
        requirements: ['legislature' => '\d+', 'numero' => '-?\d+'],
        methods: ['GET', 'POST'],
    )]
    public function modifier(Request $requete, int $legislature, int $numero): Response
    {
        $explication = $this->explicationDuDepute($legislature, $numero);

        if ($explication === null) {
            return $this->redirectToRoute('dashboard_explications_liste');
        }

        $etaitPubliee = $explication->isPubliee();

        $formulaire = $this->createForm(ExplicationType::class, $explication);
        $formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $explication->setModifiedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            if ($etaitPubliee || $explication->isPubliee()) {
                $this->cache->apresPublication();
            }

            $this->addFlash('succes', $explication->isPubliee()
                ? 'Explication enregistrée et publiée.'
                : 'Explication enregistrée en brouillon. Elle n\'est plus visible sur le site.');

            return $this->redirectToRoute('dashboard_explications');
        }

        return $this->render('dashboard/explications/form.html.twig', [
            'formulaire' => $formulaire,
            'contexte' => $this->explications->contexteDuScrutin($explication->getScrutin()->getId(), $this->depute()->getId()),
            'explication' => $explication,
            'legislature' => $legislature,
            'numero' => $numero,
        ]);
    }

    #[Route(
        '/explications/delete/l{legislature}v{numero}',
        name: 'dashboard_explication_supprimer',
        requirements: ['legislature' => '\d+', 'numero' => '-?\d+'],
        methods: ['GET', 'POST'],
    )]
    public function supprimer(Request $requete, int $legislature, int $numero): Response
    {
        $explication = $this->explicationDuDepute($legislature, $numero);

        if ($explication === null) {
            return $this->redirectToRoute('dashboard_explications_liste');
        }

        if ($requete->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('supprimer_explication_' . $explication->getId(), (string) $requete->request->get('_token'))) {
                $this->addFlash('erreur', 'Jeton de sécurité invalide, suppression annulée.');

                return $this->redirectToRoute('dashboard_explications');
            }

            $etaitPubliee = $explication->isPubliee();
            $this->entityManager->remove($explication);
            $this->entityManager->flush();

            if ($etaitPubliee) {
                $this->cache->apresPublication();
            }

            $this->addFlash('succes', sprintf('L\'explication de vote pour le scrutin n° %d a bien été supprimée.', $numero));

            return $this->redirectToRoute('dashboard_explications');
        }

        return $this->render('dashboard/explications/supprimer.html.twig', [
            'explication' => $explication,
            'contexte' => $this->explications->contexteDuScrutin($explication->getScrutin()->getId(), $this->depute()->getId()),
            'legislature' => $legislature,
            'numero' => $numero,
        ]);
    }

    /**
     * Fiche de candidature du député à une élection (`DashboardMP::elections`).
     *
     * La page se visite tant que l'élection en ouvre une (les législatives 2022) ;
     * le bouton de modification n'apparaît que dans la fenêtre où elle est encore
     * modifiable, close depuis des années pour ce scrutin.
     */
    #[Route('/elections/{slug}', name: 'dashboard_elections', requirements: ['slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function elections(string $slug): Response
    {
        $election = $this->electionOuverteAuxFiches($slug);
        $depute = $this->depute();

        return $this->render('dashboard/elections/index.html.twig', [
            'depute' => $depute,
            'election' => $election,
            'candidature' => $this->candidatureFiche($depute->getId(), (int) $election['id']),
            'modifiable' => $this->fenetreDeModificationOuverte($election),
        ]);
    }

    /**
     * Modification de la candidature (`DashboardMP::elections_modify`).
     *
     * L'application d'origine ferme cette page par un `show_404()` volontaire dès
     * qu'on est à deux jours ou plus du premier tour : reproduit ici, /modifier
     * répond donc 404 pour les législatives 2022, dont la fenêtre est close. La
     * fiche, elle, reste visitable.
     */
    #[Route('/elections/{slug}/modifier', name: 'dashboard_elections_modifier', requirements: ['slug' => '[a-z0-9\-]+'], methods: ['GET', 'POST'])]
    public function electionsModifier(string $slug, Request $requete): Response
    {
        $election = $this->electionOuverteAuxFiches($slug);

        if (!$this->fenetreDeModificationOuverte($election)) {
            throw $this->createNotFoundException('La fenêtre de modification de la candidature est fermée.');
        }

        $depute = $this->depute();
        $candidature = $this->entityManager->getRepository(Candidature::class)
            ->findOneBy(['depute' => $depute->getId(), 'election' => (int) $election['id']]);

        if ($requete->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('modifier_candidature_' . $election['id'], (string) $requete->request->get('_token'))) {
                $this->addFlash('erreur', 'Jeton de sécurité invalide, modification annulée.');

                return $this->redirectToRoute('dashboard_elections', ['slug' => $slug]);
            }

            if ($candidature === null) {
                $candidature = new Candidature();
                $candidature->setDepute($depute);
                $candidature->setElection($this->entityManager->getReference(Election::class, (int) $election['id']));
            }

            $candidature->setCandidat($requete->request->get('candidature') === '1');
            $candidature->setDistrict(($district = trim((string) $requete->request->get('district'))) !== '' ? $district : null);
            $candidature->setLien(($lien = trim((string) $requete->request->get('link'))) !== '' ? $lien : null);

            $this->entityManager->persist($candidature);
            $this->entityManager->flush();

            $this->addFlash('succes', 'Votre candidature a bien été mise à jour.');

            return $this->redirectToRoute('dashboard_elections', ['slug' => $slug]);
        }

        return $this->render('dashboard/elections/modifier.html.twig', [
            'depute' => $depute,
            'election' => $election,
            'candidature' => $this->candidatureFiche($depute->getId(), (int) $election['id']),
            'departements' => $this->departements(),
        ]);
    }

    /**
     * L'élection désignée par le slug, à condition qu'elle ouvre une fiche de
     * candidature ; 404 sinon, comme le `show_404()` du legacy sur toute autre.
     *
     * @return array<string, mixed>
     */
    private function electionOuverteAuxFiches(string $slug): array
    {
        $election = $this->connection->fetchAssociative(
            'SELECT id, identifiant, slug, libelle, libelle_abrege, annee, date_tour1
             FROM election WHERE slug = :slug',
            ['slug' => $slug],
        );

        if ($election === false || !\in_array((int) $election['identifiant'], self::ELECTIONS_FICHE, true)) {
            throw $this->createNotFoundException('Cette élection n\'ouvre pas de fiche de candidature.');
        }

        return $election;
    }

    /**
     * La candidature du député, mise en forme pour la fiche : l'état, le libellé
     * du département de candidature (le district est un code de département aux
     * législatives, traduit comme `get_district('Législatives', …)`) et le lien de
     * campagne. Null si aucune candidature n'a été relevée.
     *
     * La collation insensible à la casse de MariaDB retrouve la Corse dont le code
     * de district s'écrit en minuscules (« 2a » ↔ « 2A »), cf. CLAUDE.md.
     *
     * @return array{candidat: ?bool, district: ?string, district_libelle: ?string, lien: ?string}|null
     */
    private function candidatureFiche(int $deputeId, int $electionId): ?array
    {
        $ligne = $this->connection->fetchAssociative(
            'SELECT candidat, district, lien FROM candidature WHERE depute_id = :depute AND election_id = :election',
            ['depute' => $deputeId, 'election' => $electionId],
        );

        if ($ligne === false) {
            return null;
        }

        $libelle = null;
        if ($ligne['district'] !== null && $ligne['district'] !== '') {
            $nom = $this->connection->fetchOne('SELECT nom FROM departement WHERE code = :code', ['code' => $ligne['district']]);
            $libelle = $nom === false ? null : $nom;
        }

        return [
            'candidat' => $ligne['candidat'] === null ? null : (bool) $ligne['candidat'],
            'district' => $ligne['district'],
            'district_libelle' => $libelle,
            'lien' => $ligne['lien'],
        ];
    }

    /**
     * La candidature reste modifiable jusqu'à moins de deux jours du premier tour.
     * L'application d'origine compare `diff(1er tour, aujourd'hui)->days` à 2 : la
     * fenêtre s'ouvre et se referme autour du scrutin, et pour les législatives
     * 2022 elle est close depuis des années.
     *
     * @param array<string, mixed> $election
     */
    private function fenetreDeModificationOuverte(array $election): bool
    {
        if (empty($election['date_tour1'])) {
            return false;
        }

        $tour1 = new \DateTimeImmutable((string) $election['date_tour1']);

        return (new \DateTimeImmutable('now'))->diff($tour1)->days < 2;
    }

    /**
     * Les départements du menu de modification, au format de
     * `Elections_model::get_all_districts()` pour les législatives : code + libellé.
     *
     * @return list<array{id: string, libelle: string}>
     */
    private function departements(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT code AS id, CONCAT(code, " - ", nom) AS libelle FROM departement ORDER BY code',
        );
    }

    /**
     * Explication du député connecté sur un scrutin donné, ou null — avec le
     * message qui va bien — s'il n'en a pas encore écrit.
     *
     * Le filtre sur le député n'est pas une précaution de style : sans lui,
     * n'importe quel élu connecté modifierait l'explication d'un autre en
     * changeant le numéro dans l'adresse.
     */
    private function explicationDuDepute(int $legislature, int $numero): ?Explication
    {
        $scrutinId = $this->scrutinDecrypte($legislature, $numero);

        if ($scrutinId === null) {
            throw $this->createNotFoundException('Aucun scrutin décrypté sous ce numéro.');
        }

        $explication = $this->explications->findOneBy([
            'scrutin' => $scrutinId,
            'depute' => $this->depute(),
        ]);

        if ($explication === null) {
            $this->addFlash('erreur', sprintf(
                'Vous n\'avez pas encore rédigé d\'explication pour le vote n° %d. Vous pouvez en créer une.',
                $numero,
            ));
        }

        return $explication;
    }

    /**
     * Scrutin désigné par le couple (législature, numéro) de l'adresse.
     *
     * Ce numéro est celui du décryptage, pas celui du scrutin : les deux
     * coïncident presque toujours, mais un vote du Congrès porte le même numéro
     * qu'un scrutin ordinaire et se distingue par le signe négatif que lui
     * donne `decryptage.vote_numero`. Passer par le décryptage tranche donc
     * l'ambiguïté, et vérifie du même coup que le scrutin est décrypté.
     */
    private function scrutinDecrypte(int $legislature, int $numero): ?int
    {
        $scrutinId = $this->connection->fetchOne(
            'SELECT scrutin_id FROM decryptage WHERE legislature = :legislature AND vote_numero = :numero',
            ['legislature' => $legislature, 'numero' => $numero],
        );

        return $scrutinId === false ? null : (int) $scrutinId;
    }

    /**
     * Le député dont le compte connecté est l'espace.
     *
     * ROLE_DEPUTE n'est accordé qu'aux comptes qui en portent un
     * (`Utilisateur::getRoles`) ; l'exception ne devrait donc jamais être levée.
     */
    private function depute(): Depute
    {
        $utilisateur = $this->getUser();

        if (!$utilisateur instanceof Utilisateur || ($depute = $utilisateur->getDepute()) === null) {
            throw $this->createAccessDeniedException('Ce compte n\'est rattaché à aucun député.');
        }

        return $depute;
    }
}
