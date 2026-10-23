<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\Decryptage;
use App\Entity\Utilisateur;
use App\Enum\DecryptageState;
use App\Form\DecryptageType;
use App\Http\RechauffeurCache;
use App\Ia\CollecteurDecryptage;
use App\Ia\GenerateurBrouillon;
use App\Repository\DecryptageRepository;
use App\Repository\ScrutinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Rédaction des décryptages de votes.
 *
 * C'est la raison d'être du site : le décryptage est la seule donnée que Datan
 * produit au lieu de la recevoir. Les règles reprennent celles de
 * l'application d'origine (`Admin::create_vote` / `modify_vote` /
 * `delete_vote`) : un rédacteur écrit et publie ; une fois publié, seul un
 * administrateur peut reprendre le texte ou le supprimer.
 */
#[Route('/admin/decryptages')]
#[IsGranted(Utilisateur::ROLE_REDACTEUR)]
class DecryptageController extends AbstractController
{
    /**
     * Mots-clés → slug de catégorie, pour pré-sélectionner le classement
     * d'après le titre du scrutin et du dossier. Table reprise du
     * ScrutinController de PoliticAnalysis (detectCategory), ordonnée du plus
     * spécifique au plus général pour limiter les faux positifs : « budget de
     * la défense » doit tomber en Défense, pas en Économie. À la différence de
     * l'origine, pas de repli « Économie » quand rien ne matche — une
     * pré-sélection fausse coûte plus cher qu'un champ laissé au choix.
     */
    private const MOTS_CLES_CATEGORIES = [
        'defense-armee' => ['défense', 'armée', 'militaire', 'soldat', 'gendarmerie', 'sécurité nationale'],
        'agriculture' => ['agriculture', 'agricole', 'paysan', 'rural', 'élevage', 'forêt', 'agroalimentaire'],
        'sports' => ['sport', 'olympique', 'paralympique'],
        'sante-solidarite' => ['santé', 'hôpital', 'maladie', 'médicament', 'soins', 'assurance maladie'],
        'justice' => ['justice', 'judiciaire', 'pénal', 'tribunal', 'magistrat', 'prison'],
        'universites-recherche' => ['université', 'enseignement supérieur', 'recherche', 'innovation'],
        'affaires-etrangeres' => ['diplomatique', 'affaires étrangères', 'accord international', 'traité'],
        'europe' => ['union européenne', 'europe', 'communautaire', 'fonds européen'],
        'institutions' => ['constitution', 'élection', 'collectivité', 'commune', 'territorial', 'sénat', 'parlement'],
        'environnement' => ['environnement', 'écologie', 'énergie', 'climat', 'biodiversité', 'nucléaire', 'carbone'],
        'economie' => ['budget', 'finances', 'fiscal', 'économie', 'tva', 'impôt', 'taxe', 'lfi', 'plfss'],
        'affaires-sociales' => ['travail', 'emploi', 'retraite', 'handicap', 'famille', 'solidarités', 'logement'],
        'education' => ['éducation', 'enseignement', 'école', 'collège', 'lycée', 'scolaire', 'jeunesse'],
        'culture' => ['culture', 'patrimoine', 'audiovisuel', 'cinéma', 'musée', 'presse'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DecryptageRepository $decryptages,
        private readonly ScrutinRepository $scrutins,
        private readonly GenerateurBrouillon $generateur,
        private readonly CollecteurDecryptage $collecteur,
        private readonly RechauffeurCache $cache,
    ) {
    }

    #[Route('', name: 'admin_decryptage_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/decryptage/index.html.twig', [
            'decryptages' => $this->decryptages->createQueryBuilder('d')
                ->leftJoin('d.categorie', 'c')->addSelect('c')
                ->leftJoin('d.auteur', 'a')->addSelect('a')
                ->leftJoin('d.scrutin', 's')->addSelect('s')
                ->orderBy('d.createdAt', 'DESC')
                ->getQuery()
                ->getResult(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_decryptage_nouveau', methods: ['GET', 'POST'])]
    public function nouveau(Request $request): Response
    {
        $decryptage = new Decryptage();
        $decryptage->setState(DecryptageState::Draft);

        // Arrivée depuis l'écran des amendements (« Décrypter ») : le scrutin
        // est déjà connu, on le pose dans le formulaire. Le legacy envoyait ce
        // bouton vers PoliticAnalysis, service externe désormais internalisé.
        if ($request->query->getInt('legislature') > 0) {
            $decryptage->setLegislature($request->query->getInt('legislature'));
        }
        if ($request->query->getInt('numero') > 0) {
            $decryptage->setVoteNumero($request->query->getInt('numero'));
        }

        // La matière de l'atelier (contexte du vote, exposé, débats, résultat)
        // se collecte avant le formulaire : elle permet aussi de pré-cocher la
        // catégorie d'après le titre — avant createForm(), sans quoi le champ
        // ne verrait pas la suggestion.
        $matiere = $this->matiereDuScrutin($decryptage->getLegislature(), $decryptage->getVoteNumero());
        if ($matiere !== null && $decryptage->getCategorie() === null) {
            $decryptage->setCategorie($this->categorieSuggeree($matiere));
        }

        $formulaire = $this->createForm(DecryptageType::class, $decryptage, [
            'creation' => true,
            'peut_publier' => false,
        ]);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $existant = $this->decryptages->findOneBy([
                'legislature' => $decryptage->getLegislature(),
                'voteNumero' => $decryptage->getVoteNumero(),
            ]);

            if ($existant !== null) {
                $this->addFlash('erreur', sprintf(
                    'Le scrutin n° %d de la %de législature est déjà décrypté : « %s ».',
                    $decryptage->getVoteNumero(),
                    $decryptage->getLegislature(),
                    $existant->getTitle(),
                ));

                return $this->redirectToRoute('admin_decryptage_modifier', ['id' => $existant->getId()]);
            }

            $scrutin = $this->scrutins->findOneBy([
                'legislature' => $decryptage->getLegislature(),
                'numero' => $decryptage->getVoteNumero(),
            ]);

            if ($scrutin === null) {
                $this->addFlash('erreur', sprintf(
                    'Aucun scrutin n° %d en %de législature. Vérifiez le numéro.',
                    $decryptage->getVoteNumero(),
                    $decryptage->getLegislature(),
                ));
            } else {
                $decryptage->setScrutin($scrutin);
                $decryptage->setVoteId($scrutin->getUid());
                $decryptage->setSlug($this->slug($decryptage->getTitle()));
                $decryptage->setCreatedAt(new \DateTimeImmutable());
                $decryptage->setAuteur($this->utilisateur());

                $this->entityManager->persist($decryptage);
                $this->entityManager->flush();

                $this->addFlash('succes', 'Décryptage créé. Il reste en brouillon tant que vous ne le publiez pas.');

                return $this->redirectToRoute('admin_decryptage_modifier', ['id' => $decryptage->getId()]);
            }
        }

        // Après une soumission refusée, législature et numéro peuvent différer
        // de la query string : la matière suit ce que porte le formulaire.
        if ($formulaire->isSubmitted()) {
            $matiere = $this->matiereDuScrutin($decryptage->getLegislature(), $decryptage->getVoteNumero());
        }

        return $this->render('admin/decryptage/form.html.twig', [
            'formulaire' => $formulaire,
            'decryptage' => null,
            'matiere' => $matiere,
            'ia_actif' => $this->generateur->estActif(),
            'ia_modele' => $this->generateur->modele(),
        ]);
    }

    /**
     * Génère un brouillon par IA pour pré-remplir le formulaire.
     *
     * Le brouillon ne touche pas à la base : il revient au navigateur, qui le
     * verse dans les champs, et c'est la rédaction qui enregistre — ou pas.
     * Jamais de publication automatique : le décryptage est la seule donnée
     * que Datan produit au lieu de la recevoir.
     */
    #[Route('/brouillon-ia', name: 'admin_decryptage_brouillon_ia', methods: ['POST'])]
    public function brouillonIa(Request $request): JsonResponse
    {
        if (!$this->generateur->estActif()) {
            return new JsonResponse(['erreur' => 'Aucun modèle configuré (IA_MODELE).'], Response::HTTP_NOT_FOUND);
        }

        $corps = json_decode($request->getContent(), true) ?: [];

        if (!$this->isCsrfTokenValid('brouillon_ia', (string) ($corps['_token'] ?? ''))) {
            return new JsonResponse(['erreur' => 'Jeton de sécurité invalide, rechargez la page.'], Response::HTTP_FORBIDDEN);
        }

        $legislature = (int) ($corps['legislature'] ?? 0);
        $numero = (int) ($corps['numero'] ?? 0);

        if ($legislature <= 0 || $numero <= 0) {
            return new JsonResponse(['erreur' => 'Renseignez la législature et le numéro du scrutin.'], Response::HTTP_BAD_REQUEST);
        }

        $scrutin = $this->scrutins->findOneBy(['legislature' => $legislature, 'numero' => $numero]);
        if ($scrutin === null) {
            return new JsonResponse(['erreur' => sprintf('Aucun scrutin n° %d en %de législature.', $numero, $legislature)], Response::HTTP_NOT_FOUND);
        }

        // Une génération locale (Ollama) peut durer plusieurs minutes : ne pas
        // laisser le max_execution_time de PHP couper la requête avant elle.
        set_time_limit(320);

        $brouillon = $this->generateur->generer($legislature, $numero);
        if ($brouillon === null) {
            return new JsonResponse([
                'erreur' => sprintf('Le moteur (%s) n\'a pas rendu de brouillon — détail dans les journaux.', $this->generateur->modele()),
            ], Response::HTTP_BAD_GATEWAY);
        }

        return new JsonResponse($brouillon);
    }

    #[Route('/{id}/modifier', name: 'admin_decryptage_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifier(Request $request, Decryptage $decryptage): Response
    {
        if (!$this->peutModifier($decryptage)) {
            $this->addFlash('erreur', 'Ce décryptage est publié : seul un administrateur peut le reprendre.');

            return $this->redirectToRoute('admin_decryptage_index');
        }

        // Lu avant que le formulaire ne l'écrase : dépublier retire aussi le
        // décryptage du site, ce qui demande la même purge que le publier.
        $etaitPublie = $decryptage->getState() === DecryptageState::Published;

        $formulaire = $this->createForm(DecryptageType::class, $decryptage);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $decryptage->setSlug($this->slug($decryptage->getTitle()));
            $decryptage->setModifiedAt(new \DateTimeImmutable());
            $decryptage->setModifiePar($this->utilisateur());

            $this->entityManager->flush();

            if ($etaitPublie || $decryptage->getState() === DecryptageState::Published) {
                $this->cache->apresPublication();
            }

            $this->addFlash('succes', $decryptage->getState() === DecryptageState::Published
                ? 'Décryptage enregistré et publié.'
                : 'Décryptage enregistré en brouillon.');

            return $this->redirectToRoute('admin_decryptage_index');
        }

        return $this->render('admin/decryptage/form.html.twig', [
            'formulaire' => $formulaire,
            'decryptage' => $decryptage,
            'matiere' => $this->matiereDuScrutin($decryptage->getLegislature(), $decryptage->getVoteNumero()),
            'ia_actif' => $this->generateur->estActif(),
            'ia_modele' => $this->generateur->modele(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_decryptage_supprimer', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(Utilisateur::ROLE_ADMIN)]
    public function supprimer(Request $request, Decryptage $decryptage): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('supprimer_decryptage_' . $decryptage->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('erreur', 'Jeton de sécurité invalide, suppression annulée.');

                return $this->redirectToRoute('admin_decryptage_index');
            }

            $titre = $decryptage->getTitle();
            $etaitPublie = $decryptage->getState() === DecryptageState::Published;
            $this->entityManager->remove($decryptage);
            $this->entityManager->flush();

            if ($etaitPublie) {
                $this->cache->apresPublication();
            }

            $this->addFlash('succes', sprintf('Décryptage « %s » supprimé.', $titre));

            return $this->redirectToRoute('admin_decryptage_index');
        }

        return $this->render('admin/decryptage/supprimer.html.twig', ['decryptage' => $decryptage]);
    }

    /**
     * La matière du scrutin pour l'atelier de décryptage. Null quand il n'y a
     * rien à montrer : paramètres absents, scrutin inconnu — ou vote du
     * Congrès, dont le numéro est noté négatif chez nous (la garde « > 0 »
     * l'écarte, et le collecteur ne lit de toute façon que les uid VTANR :
     * le compte rendu du Congrès n'est pas dans nos données).
     *
     * @return array<string, mixed>|null
     */
    private function matiereDuScrutin(?int $legislature, ?int $numero): ?array
    {
        if ($legislature === null || $numero === null || $legislature <= 0 || $numero <= 0) {
            return null;
        }

        return $this->collecteur->collecter($legislature, $numero);
    }

    /**
     * La catégorie suggérée par les mots-clés du titre (scrutin + dossier).
     * Null quand rien ne matche : le champ reste au choix de la rédaction.
     *
     * @param array<string, mixed> $matiere
     */
    private function categorieSuggeree(array $matiere): ?Categorie
    {
        $texte = mb_strtolower(
            ($matiere['scrutin']['titre'] ?? '') . ' ' . ($matiere['dossier']['titre'] ?? ''),
        );

        foreach (self::MOTS_CLES_CATEGORIES as $slug => $motsCles) {
            foreach ($motsCles as $motCle) {
                if (str_contains($texte, $motCle)) {
                    return $this->entityManager->getRepository(Categorie::class)->findOneBy(['slug' => $slug]);
                }
            }
        }

        return null;
    }

    /**
     * Un décryptage publié est figé pour son rédacteur : le corriger relève de
     * l'administrateur, comme dans l'application d'origine.
     */
    private function peutModifier(Decryptage $decryptage): bool
    {
        return $decryptage->getState() !== DecryptageState::Published
            || $this->isGranted(Utilisateur::ROLE_ADMIN);
    }

    private function utilisateur(): ?Utilisateur
    {
        $utilisateur = $this->getUser();

        return $utilisateur instanceof Utilisateur ? $utilisateur : null;
    }

    /**
     * Le slug détermine l'adresse publique du décryptage.
     *
     * Le titre est nettoyé avant d'être translittéré : le slugger refuse une
     * chaîne mal encodée, et un client qui enverrait autre chose que de l'UTF-8
     * ne doit pas provoquer une erreur serveur.
     */
    private function slug(?string $titre): string
    {
        $titre = mb_convert_encoding((string) $titre, 'UTF-8', 'UTF-8');

        return (new AsciiSlugger())->slug($titre)->lower()->toString();
    }
}
