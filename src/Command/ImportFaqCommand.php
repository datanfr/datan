<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère la foire aux questions de la base de production : ses catégories et
 * ses questions.
 *
 * Contenu éditorial, à préserver comme les décryptages et le blog : rien de cela
 * n'est dans l'open data. Le brouillon (`state = 'draft'`) est repris lui aussi —
 * c'est une donnée de production — mais la page publique ne montre que le
 * « published ».
 *
 * La réponse est du HTML : `mariadb -B` en échappe les sauts de ligne (`\n`),
 * les tabulations (`\t`) et les antislashs (`\\`), que l'import défait — sans
 * quoi la réponse s'afficherait avec des `\n` littéraux.
 *
 * Le jeton `[[ageMean]]` que porte une réponse n'est PAS résolu ici : le legacy
 * le remplace au rendu par l'âge moyen des députés, une valeur qui bouge. Il est
 * gardé tel quel et interpolé par `FaqController`.
 *
 * L'auteur (`created_by`/`modified_by`) n'est pas repris : la page ne l'affiche
 * pas, et il ne se résout pas dans le backup réduit (un seul compte).
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *   "SELECT id, name, slug FROM faq_categories ORDER BY id" > var/legacy/faq_categories.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *   "SELECT id, title, slug, text, category, state, created_at, modified_at \
 *    FROM faq_posts ORDER BY id" > var/legacy/faq_posts.tsv
 * ```
 *
 * Import de récupération : pour le chargement initial, jamais dans
 * `app:sync:quotidien`, et à ne pas rejouer une fois que la rédaction écrit dans
 * l'application — il écraserait ses ajouts.
 */
#[AsCommand(
    name: 'app:import:faq',
    description: 'Récupère la FAQ (catégories et questions) depuis des exports TSV de la production.',
)]
class ImportFaqCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('categories', null, InputOption::VALUE_REQUIRED, 'TSV des catégories', 'var/legacy/faq_categories.tsv')
            ->addOption('questions', null, InputOption::VALUE_REQUIRED, 'TSV des questions', 'var/legacy/faq_posts.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Catégories d'abord : les questions s'y rattachent. On garde l'identifiant
        // d'origine comme ordre d'affichage (la source n'a pas de colonne dédiée,
        // le legacy trie par id).
        $slugParIdLegacy = [];
        $categories = [];

        foreach ($this->lignes((string) $input->getOption('categories')) as [$idLegacy, $nom, $slug]) {
            $slug = $this->texte($slug);
            $nom = $this->texte($nom);

            if ($slug === null || $nom === null) {
                continue;
            }

            $slugParIdLegacy[$idLegacy] = $slug;
            $categories[] = [$nom, $slug, $this->entier($idLegacy)];
        }

        $this->upsert('faq_categorie', ['nom', 'slug', 'ordre'], $categories, ['nom', 'ordre']);

        // slug → id local, pour rattacher les questions.
        $idParSlug = $this->connection->fetchAllKeyValue('SELECT slug, id FROM faq_categorie');

        $questions = [];
        $sansCategorie = 0;

        foreach ($this->lignes((string) $input->getOption('questions')) as $ligne) {
            [$idLegacy, $titre, $slug, $reponse, $categorie, $etat, $creeLe, $modifieLe]
                = array_pad($ligne, 8, null);

            $slug = $this->texte($slug);
            $titre = $this->texte($titre);
            $reponse = $this->desechappe($reponse);

            if ($slug === null || $titre === null || $reponse === null) {
                continue;
            }

            $slugCategorie = $slugParIdLegacy[$categorie] ?? null;
            $categorieId = $slugCategorie === null ? null : ($idParSlug[$slugCategorie] ?? null);

            if ($categorieId === null) {
                ++$sansCategorie;
            }

            $questions[] = [
                $categorieId,
                $titre,
                $slug,
                $reponse,
                $this->texte($etat),
                $this->entier($idLegacy),
                $this->dateHeure($creeLe),
                $this->dateHeure($modifieLe),
            ];
        }

        $this->upsert(
            'faq_post',
            ['categorie_id', 'question', 'slug', 'reponse', 'etat', 'ordre', 'cree_le', 'modifie_le'],
            $questions,
            ['categorie_id', 'question', 'reponse', 'etat', 'ordre', 'modifie_le'],
        );

        $io->success(sprintf('%d catégorie(s) et %d question(s) récupérée(s).', \count($categories), \count($questions)));

        $publiees = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM faq_post WHERE etat = 'published'");
        $io->text(sprintf('%d question(s) publiée(s), le reste en brouillon.', $publiees));

        if ($sansCategorie > 0) {
            $io->text(sprintf('%d question(s) sans catégorie connue (rattachement laissé vide).', $sansCategorie));
        }

        return Command::SUCCESS;
    }

    /** Défait l'échappement de `mariadb -B` sur le HTML (\t \n \\). */
    private function desechappe(?string $valeur): ?string
    {
        if ($valeur === null) {
            return null;
        }

        return strtr($valeur, ['\\t' => "\t", '\\n' => "\n", '\\\\' => '\\']);
    }

    /** `mariadb` écrit les dates nulles « 0000-00-00 » ; on les rend en null. */
    private function dateHeure(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
