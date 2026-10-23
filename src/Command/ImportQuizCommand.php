<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère le questionnaire de la base de production : les questions rédigées à
 * la main, chacune adossée à un scrutin.
 *
 * Contenu éditorial, à préserver — rien de cela n'est dans l'open data. Seule la
 * donnée est portée ; la page `/questionnaire` viendra plus tard. Le brouillon
 * (`state = 'draft'`) est repris comme donnée de production.
 *
 * Le contenu est du texte brut (pas de HTML) : accents et apostrophes réels,
 * aucun déséchappement à prévoir, contrairement à la FAQ ou au blog.
 *
 * L'auteur (`created_by`/`modified_by`) n'est pas repris : aucune page ne
 * l'affiche, et il ne se résout pas dans le backup réduit.
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *   "SELECT q.id, q.quizz, q.voteNumero, q.legislature, q.title, q.explication, \
 *           q.for1, q.for2, q.for3, q.against1, q.against2, q.against3, \
 *           f.slug AS category_slug, f.name AS category_name, q.swap, q.state, \
 *           q.created_at, q.modified_at \
 *    FROM quizz q LEFT JOIN fields f ON f.id = q.category \
 *    ORDER BY q.id" > var/legacy/quiz.tsv
 * ```
 *
 * Import de récupération : pour le chargement initial, jamais dans
 * `app:sync:quotidien`, et à ne pas rejouer une fois la rédaction devenue la
 * source.
 */
#[AsCommand(
    name: 'app:import:quiz',
    description: 'Récupère le questionnaire (questions rédigées) depuis un export TSV de la production.',
)]
class ImportQuizCommand extends ImportLegacyCommand
{
    private const COLONNES = [
        'source_id', 'numero_quiz', 'legislature', 'scrutin_numero', 'titre', 'explication',
        'pour1', 'pour2', 'pour3', 'contre1', 'contre2', 'contre3',
        'categorie_slug', 'categorie_nom', 'inverse', 'etat', 'cree_le', 'modifie_le',
    ];

    protected function configure(): void
    {
        $this->addOption('questions', null, InputOption::VALUE_REQUIRED, 'TSV de la table quizz', 'var/legacy/quiz.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $questions = [];
        $lues = 0;
        $rebuts = 0;

        foreach ($this->lignes((string) $input->getOption('questions')) as $ligne) {
            ++$lues;

            [$id, $quiz, $voteNumero, $legislature, $titre, $explication,
                $pour1, $pour2, $pour3, $contre1, $contre2, $contre3,
                $categorieSlug, $categorieNom, $swap, $etat, $creeLe, $modifieLe] = array_pad($ligne, 18, null);

            $titre = $this->texte($titre);

            // Une seule ligne de test (id 34, `quizz = 0`, scrutin 0) traîne dans la
            // table : ce n'est pas une des questions rédigées, on l'écarte.
            if ($this->entier($quiz) === 0 || $titre === null) {
                ++$rebuts;
                continue;
            }

            $questions[] = [
                $this->entier($id),
                $this->entier($quiz),
                $this->entier($legislature),
                $this->entier($voteNumero),
                $titre,
                $this->texte($explication),
                $this->texte($pour1), $this->texte($pour2), $this->texte($pour3),
                $this->texte($contre1), $this->texte($contre2), $this->texte($contre3),
                $this->texte($categorieSlug), $this->texte($categorieNom),
                $this->drapeau($swap) ?? 0,
                $this->texte($etat),
                $this->dateHeure($creeLe),
                $this->dateHeure($modifieLe),
            ];
        }

        $this->upsert('question_quiz', self::COLONNES, $questions, \array_slice(self::COLONNES, 1));

        $io->success(sprintf('%d question(s) récupérée(s) sur %d lues.', \count($questions), $lues));

        if ($rebuts > 0) {
            $io->text(sprintf('%d ligne(s) écartée(s) — brouillon de test (quizz = 0).', $rebuts));
        }

        $publiees = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM question_quiz WHERE etat = 'published'");
        $io->text(sprintf('%d question(s) publiée(s), le reste en brouillon.', $publiees));

        return Command::SUCCESS;
    }

    /** `mariadb` écrit les dates nulles « 0000-00-00 » ; on les rend en null. */
    private function dateHeure(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
