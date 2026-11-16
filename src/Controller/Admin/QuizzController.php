<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\QuestionQuiz;
use App\Entity\Utilisateur;
use App\Form\QuestionQuizType;
use App\Repository\QuestionQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Administration du questionnaire (`Admin::quizz_list` et suivantes).
 *
 * Chaque question présente une mesure soumise au vote, avec trois arguments pour
 * et trois contre, et pointe le scrutin visé par le couple (numéro, législature).
 *
 * Règles de rôle reprises du legacy : un rédacteur crée une question (en
 * brouillon), la reprend et la publie tant qu'elle est en brouillon ; une fois
 * **publiée, seul un administrateur peut la reprendre** (`modify_quizz`) ou la
 * **supprimer** (`delete_quizz`).
 */
#[Route('/admin/quizz')]
#[IsGranted(Utilisateur::ROLE_REDACTEUR)]
class QuizzController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionQuizRepository $questions,
    ) {
    }

    #[Route('', name: 'admin_quizz_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/quizz/index.html.twig', [
            'questions' => $this->questions->findBy([], ['numeroQuiz' => 'ASC', 'id' => 'ASC']),
        ]);
    }

    #[Route('/create', name: 'admin_quizz_creer', methods: ['GET', 'POST'])]
    public function creer(Request $request): Response
    {
        $question = new QuestionQuiz();
        $question->setEtat('draft');

        $formulaire = $this->createForm(QuestionQuizType::class, $question, ['avec_etat' => false]);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $this->appliquerCategorieEtInverse($formulaire, $question);
            // `source_id` est la clé stable d'import des questions du legacy ; une
            // question créée ici n'en a pas, on lui donne le rang libre suivant.
            $question->setSourceId($this->prochainSourceId());
            $question->setCreeLe(new \DateTimeImmutable());

            $this->entityManager->persist($question);
            $this->entityManager->flush();

            $this->addFlash('succes', 'Question créée. Elle reste en brouillon tant que vous ne la publiez pas.');

            return $this->redirectToRoute('admin_quizz_index');
        }

        return $this->render('admin/quizz/form.html.twig', [
            'formulaire' => $formulaire,
            'question' => null,
        ]);
    }

    #[Route('/modify/{id}', name: 'admin_quizz_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifier(Request $request, QuestionQuiz $question): Response
    {
        if (!$this->peutModifier($question)) {
            $this->addFlash('erreur', 'Cette question est publiée : seul un administrateur peut la reprendre.');

            return $this->redirectToRoute('admin_quizz_index');
        }

        $formulaire = $this->createForm(QuestionQuizType::class, $question);
        // Champs non mappés : on garnit l'affichage initial depuis la question.
        $formulaire->get('categorie')->setData($this->categorieCourante($question));
        $formulaire->get('inverse')->setData($question->getInverse() === 1);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $this->appliquerCategorieEtInverse($formulaire, $question);
            $question->setModifieLe(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->addFlash('succes', $question->getEtat() === 'published'
                ? 'Question enregistrée et publiée.'
                : 'Question enregistrée en brouillon.');

            return $this->redirectToRoute('admin_quizz_index');
        }

        return $this->render('admin/quizz/form.html.twig', [
            'formulaire' => $formulaire,
            'question' => $question,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_quizz_supprimer', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(Utilisateur::ROLE_ADMIN)]
    public function supprimer(Request $request, QuestionQuiz $question): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('supprimer_quizz_' . $question->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('erreur', 'Jeton de sécurité invalide, suppression annulée.');

                return $this->redirectToRoute('admin_quizz_index');
            }

            $titre = $question->getTitre();
            $this->entityManager->remove($question);
            $this->entityManager->flush();

            $this->addFlash('succes', sprintf('Question « %s » supprimée.', $titre));

            return $this->redirectToRoute('admin_quizz_index');
        }

        return $this->render('admin/quizz/supprimer.html.twig', ['question' => $question]);
    }

    /**
     * Recopie la catégorie choisie (slug + nom) et l'inversion depuis les champs
     * non mappés du formulaire. La question porte le slug et le nom, non une clé
     * étrangère ; l'inversion est un SMALLINT 0/1.
     */
    private function appliquerCategorieEtInverse(FormInterface $formulaire, QuestionQuiz $question): void
    {
        $categorie = $formulaire->get('categorie')->getData();
        if ($categorie instanceof Categorie) {
            $question->setCategorieSlug($categorie->getSlug());
            $question->setCategorieNom($categorie->getName());
        } else {
            $question->setCategorieSlug(null);
            $question->setCategorieNom(null);
        }

        $question->setInverse($formulaire->get('inverse')->getData() ? 1 : 0);
    }

    private function categorieCourante(QuestionQuiz $question): ?Categorie
    {
        if ($question->getCategorieSlug() === null) {
            return null;
        }

        return $this->entityManager->getRepository(Categorie::class)
            ->findOneBy(['slug' => $question->getCategorieSlug()]);
    }

    private function peutModifier(QuestionQuiz $question): bool
    {
        return $question->getEtat() !== 'published' || $this->isGranted(Utilisateur::ROLE_ADMIN);
    }

    private function prochainSourceId(): int
    {
        $max = (int) $this->entityManager
            ->createQuery('SELECT COALESCE(MAX(q.sourceId), 0) FROM App\Entity\QuestionQuiz q')
            ->getSingleScalarResult();

        return $max + 1;
    }
}
