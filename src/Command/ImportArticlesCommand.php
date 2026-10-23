<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère le blog de la base de production : ses rubriques et ses articles.
 *
 * Contenu éditorial, à préserver comme les décryptages. L'auteur est résolu en
 * texte à l'export (jointure sur `users.name`) : un article garde sa signature
 * même si le compte n'est pas repris.
 *
 * Le corps est du HTML : `mariadb -B` en échappe les sauts de ligne (`\n`), les
 * tabulations (`\t`) et les antislashs (`\\`), que l'import défait — sans quoi
 * l'article s'afficherait avec des `\n` littéraux.
 *
 * Régénérer les exports :
 *
 *   docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *     "SELECT id, name, slug FROM categories" > var/legacy/article_categories.tsv
 *
 *   docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *     "SELECT p.category_id, p.title, p.slug, p.body, p.state, p.image_name, \
 *             p.created_at, p.modified_at, u.name AS auteur \
 *      FROM posts p LEFT JOIN users u ON u.id = p.user_id" > var/legacy/articles.tsv
 */
#[AsCommand(
    name: 'app:import:articles',
    description: 'Récupère le blog (rubriques et articles) depuis des exports TSV de la production.',
)]
class ImportArticlesCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('categories', null, InputOption::VALUE_REQUIRED, 'TSV des rubriques', 'var/legacy/article_categories.tsv')
            ->addOption('articles', null, InputOption::VALUE_REQUIRED, 'TSV des articles', 'var/legacy/articles.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Rubriques d'abord : les articles s'y rattachent.
        $slugParIdLegacy = [];
        $rubriques = [];

        foreach ($this->lignes((string) $input->getOption('categories')) as [$idLegacy, $nom, $slug]) {
            $slug = $this->texte($slug);
            $nom = $this->texte($nom);

            if ($slug === null || $nom === null) {
                continue;
            }

            $slugParIdLegacy[$idLegacy] = $slug;
            $rubriques[] = [$nom, $slug];
        }

        $this->upsert('categorie_article', ['nom', 'slug'], $rubriques, ['nom']);

        // slug → id local, pour rattacher les articles.
        $idParSlug = $this->connection->fetchAllKeyValue('SELECT slug, id FROM categorie_article');

        $articles = [];
        $sansRubrique = 0;

        foreach ($this->lignes((string) $input->getOption('articles')) as $ligne) {
            [$idCategorie, $titre, $slug, $corps, $etat, $image, $creeLe, $modifieLe, $auteur] = array_pad($ligne, 9, null);

            $slug = $this->texte($slug);
            $titre = $this->texte($titre);
            $corps = $this->desechappe($corps);

            if ($slug === null || $titre === null || $corps === null) {
                continue;
            }

            $slugRubrique = $slugParIdLegacy[$idCategorie] ?? null;
            $categorieId = $slugRubrique === null ? null : ($idParSlug[$slugRubrique] ?? null);

            if ($categorieId === null) {
                ++$sansRubrique;
            }

            $articles[] = [
                $categorieId,
                $titre,
                $slug,
                $corps,
                $this->texte($auteur),
                $this->texte($etat),
                $this->texte($image),
                $this->dateHeure($creeLe),
                $this->dateHeure($modifieLe),
            ];
        }

        $this->upsert(
            'article',
            ['categorie_id', 'titre', 'slug', 'corps', 'auteur', 'etat', 'image_nom', 'cree_le', 'modifie_le'],
            $articles,
            ['categorie_id', 'titre', 'corps', 'auteur', 'etat', 'image_nom', 'modifie_le'],
        );

        $io->success(sprintf('%d rubrique(s) et %d article(s) récupéré(s).', \count($rubriques), \count($articles)));

        if ($sansRubrique > 0) {
            $io->text(sprintf('%d article(s) sans rubrique connue (rattachement laissé vide).', $sansRubrique));
        }

        return Command::SUCCESS;
    }

    private function desechappe(?string $valeur): ?string
    {
        if ($valeur === null) {
            return null;
        }

        return strtr($valeur, ['\\t' => "\t", '\\n' => "\n", '\\\\' => '\\']);
    }

    private function dateHeure(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
