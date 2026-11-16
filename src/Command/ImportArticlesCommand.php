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
 * tabulations (`\t`) et les antislashs (`\\`), que {@see lignes()} défait —
 * sans quoi l'article s'afficherait avec des `\n` littéraux.
 *
 * Requêtes d'export : `bin/recuperation/legacy/article_categories.sql` et
 * `articles.sql`.
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
        $fichierRubriques = (string) $input->getOption('categories');
        $fichierArticles = (string) $input->getOption('articles');
        $rubriquesIncompletes = 0;

        foreach ($this->lignes($fichierRubriques) as [$idLegacy, $nom, $slug]) {
            $slug = $this->texte($slug);
            $nom = $this->texte($nom);

            if ($slug === null || $nom === null) {
                ++$rubriquesIncompletes;

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
        $articlesIncomplets = 0;

        foreach ($this->lignes($fichierArticles) as $ligne) {
            [$idCategorie, $titre, $slug, $corps, $etat, $image, $creeLe, $modifieLe, $auteur] = array_pad($ligne, 9, null);

            $slug = $this->texte($slug);
            $titre = $this->texte($titre);

            if ($slug === null || $titre === null || $corps === null) {
                ++$articlesIncomplets;

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

        $this->consigner($io, 'Rubriques du blog', $fichierRubriques, \count($rubriques), ['sans nom ni slug' => $rubriquesIncompletes]);
        $this->consigner($io, 'Articles', $fichierArticles, \count($articles), ['sans titre, slug ou corps' => $articlesIncomplets]);

        if ($sansRubrique > 0) {
            $io->text(sprintf('%d article(s) sans rubrique connue (rattachement laissé vide).', $sansRubrique));
        }

        return Command::SUCCESS;
    }

    private function dateHeure(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
