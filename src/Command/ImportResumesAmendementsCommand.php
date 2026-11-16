<?php

namespace App\Command;

use App\Ia\Amendement\PromptResumeAmendement;
use App\Ia\Tag\AttributionTags;
use App\Ia\Tag\NormaliseurTag;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les résumés IA des amendements et leurs mots-clés depuis la base de
 * production (`amendements_ia`, `amendements_tags`).
 *
 * **De la donnée déjà payée** : 6 509 générations au 15 novembre 2026, qu'on ne
 * régénère pas. Les mots-clés passent par la même normalisation que ceux du
 * traitement des lots ({@see NormaliseurTag}) — les 4 124 libellés distincts
 * deviennent 3 853 clés —, mais sans appel au modèle : les inconnus entrent
 * comme canoniques, et `app:ia:tags:nettoyer` les rapproche ensuite, catégorie
 * par catégorie.
 *
 * Règles de reprise :
 * - **N'écrase rien.** Une colonne déjà remplie chez nous (résumé repris par
 *   `app:import:annexes`, écrit par un lot, corrigé par la rédaction) garde
 *   sa valeur ; l'import ne fait que combler. Le drapeau « relu » ne fait que
 *   monter. C'est la garde des résumés, appliquée ici aussi : contrairement aux
 *   autres imports de récupération, celui-ci peut donc se rejouer sans risque.
 * - **Une ligne par amendement.** `amendements_ia` n'a pas de clé unique sur
 *   l'amendement : trois amendements « rédactionnels » y ont 252, 341 et 530
 *   lignes, la boucle du legacy les ayant resoumis toutes les dix minutes
 *   pendant deux jours (le modèle rendait un résumé vide une fois sur deux, et
 *   la sélection retenait l'amendement dès qu'une de ses lignes était vide).
 *   On garde la plus récente qui porte un résumé.
 * - **Catégorie dans la liste ou rien.** La production en compte trois hors
 *   liste (« Énergie et environnement », « Tourisme et loisirs », vide) : elles
 *   n'existent pas pour le reste du site.
 *
 * Requêtes d'export : `bin/recuperation/legacy/amendements_ia.sql` et
 * `amendements_tags.sql`. Les tables `anthropic_batches` et
 * `anthropic_batch_items` ne se reprennent pas : le suivi des lots repart de
 * zéro ici (`lot_ia`).
 *
 * Les amendements doivent être en base (sync quotidien) : un résumé dont
 * l'amendement manque est compté, pas écrit.
 *
 * `--simulation` fait tout dans une transaction annulée à la fin : le bilan
 * est exact, rien ne reste.
 */
#[AsCommand(
    name: 'app:import:resumes-amendements',
    description: 'Récupère les résumés IA et les mots-clés des amendements depuis des exports TSV de la production.',
)]
class ImportResumesAmendementsCommand extends ImportLegacyCommand
{
    public function __construct(Connection $connection, private readonly AttributionTags $tags)
    {
        parent::__construct($connection);
    }

    protected function configure(): void
    {
        $this
            ->addOption('resumes', null, InputOption::VALUE_REQUIRED, 'Export TSV de amendements_ia', 'var/legacy/amendements_ia.tsv')
            ->addOption('tags', null, InputOption::VALUE_REQUIRED, 'Export TSV de amendements_tags', 'var/legacy/amendements_tags.tsv')
            ->addOption('simulation', null, InputOption::VALUE_NONE, 'Tout faire puis annuler : bilan sans écriture');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Récupération des résumés IA des amendements');
        $simulation = (bool) $input->getOption('simulation');

        // 1. Une ligne par amendement : la plus récente qui porte un résumé.
        // `lignes()` défait déjà l'échappement de `mariadb -B` : le refaire ici
        // doublerait les antislashs d'origine en sauts de ligne.
        $fichierResumes = (string) $input->getOption('resumes');
        $fichierTags = (string) $input->getOption('tags');
        $resumes = [];
        $lues = 0;
        $ecartes = ['doublon' => 0, 'sans_resume' => 0, 'categorie_hors_liste' => 0];
        foreach ($this->lignes($fichierResumes) as $ligne) {
            [, $identifiant, $titre, $resume, $simplicite, $justification, $categorie, $relu] = array_pad($ligne, 8, null);
            ++$lues;
            $identifiant = $this->texte($identifiant);
            $resume = $this->texte($resume);
            if ($identifiant === null || $resume === null) {
                ++$ecartes['sans_resume'];
                continue;
            }
            if (isset($resumes[$identifiant])) {
                ++$ecartes['doublon'];
            }
            $categorie = $this->texte($categorie);
            if ($categorie !== null && !\in_array($categorie, PromptResumeAmendement::CATEGORIES, true)) {
                ++$ecartes['categorie_hors_liste'];
                $categorie = null;
            }
            $simplicite = $this->entier($simplicite);
            $resumes[$identifiant] = [
                mb_substr((string) $this->texte($titre), 0, 255) ?: null,
                $resume,
                $this->texte($justification),
                $simplicite === null ? null : max(1, min(5, $simplicite)),
                $categorie,
                (int) ($this->drapeau($relu) ?? 0),
            ];
        }

        $tagsLus = 0;
        $tagsVides = 0;
        $tagsParIdentifiant = [];
        foreach ($this->lignes($fichierTags) as $ligne) {
            [$identifiant, $tag] = array_pad($ligne, 2, null);
            ++$tagsLus;
            $libelle = NormaliseurTag::libelle($tag);
            if (($identifiant = $this->texte($identifiant)) !== null && $libelle !== null) {
                $tagsParIdentifiant[$identifiant][] = $libelle;
            } else {
                ++$tagsVides;
            }
        }

        $ids = array_map('intval', $this->connection->fetchAllKeyValue('SELECT amendement_id, id FROM amendement'));

        $this->connection->beginTransaction();
        try {
            // 2. Les résumés : combler, jamais remplacer.
            $this->connection->executeStatement(
                'CREATE TEMPORARY TABLE import_resume (
                    amendement_id INT NOT NULL PRIMARY KEY, titre VARCHAR(255) NULL, resume LONGTEXT NULL,
                    justification LONGTEXT NULL, simplicite SMALLINT NULL, categorie VARCHAR(60) NULL, relu TINYINT NOT NULL
                ) DEFAULT CHARACTER SET utf8mb4',
            );
            $absents = 0;
            $lot = [];
            foreach ($resumes as $identifiant => $valeurs) {
                if (!isset($ids[$identifiant])) {
                    ++$absents;
                    continue;
                }
                $lot[] = [$ids[$identifiant], ...$valeurs];
                if (\count($lot) >= self::TAILLE_LOT) {
                    $this->upsert('import_resume', ['amendement_id', 'titre', 'resume', 'justification', 'simplicite', 'categorie', 'relu'], $lot, []);
                    $lot = [];
                }
            }
            $this->upsert('import_resume', ['amendement_id', 'titre', 'resume', 'justification', 'simplicite', 'categorie', 'relu'], $lot, []);

            $avant = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM amendement a JOIN import_resume i ON i.amendement_id = a.id WHERE a.resume_ia IS NOT NULL AND a.resume_ia <> \'\'');
            $modifies = $this->connection->executeStatement(
                'UPDATE amendement a JOIN import_resume i ON i.amendement_id = a.id SET
                    a.titre_ia = COALESCE(NULLIF(a.titre_ia, \'\'), i.titre),
                    a.resume_ia = COALESCE(NULLIF(a.resume_ia, \'\'), i.resume),
                    a.justification_ia = COALESCE(NULLIF(a.justification_ia, \'\'), i.justification),
                    a.simplicite_ia = COALESCE(a.simplicite_ia, i.simplicite),
                    a.categorie_ia = COALESCE(a.categorie_ia, i.categorie),
                    a.resume_relu = GREATEST(a.resume_relu, i.relu)',
            );
            $this->connection->executeStatement('DROP TEMPORARY TABLE import_resume');

            // 3. Les mots-clés, par la même normalisation que les lots, sans IA.
            $propositions = [];
            $tagsAbsents = 0;
            foreach ($tagsParIdentifiant as $identifiant => $libelles) {
                if (!isset($ids[$identifiant])) {
                    $tagsAbsents += \count($libelles);
                    continue;
                }
                $propositions[$ids[$identifiant]] = ['categorie' => $resumes[$identifiant][4] ?? null, 'tags' => $libelles];
            }
            $bilanTags = $this->tags->attribuer($propositions);

            $simulation ? $this->connection->rollBack() : $this->connection->commit();
        } catch (\Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            throw $e;
        }

        $retenus = \count($resumes) - $absents;

        // Bilan à l'unité : chaque ligne lue est écrite (rapprochée d'un
        // amendement) ou écartée pour une raison dite. Un doublon est une ligne
        // supplantée par une plus récente du même amendement.
        $this->consigner($io, 'Résumés IA des amendements', $fichierResumes, $retenus, [
            'sans résumé' => $ecartes['sans_resume'],
            'supplantée par une ligne plus récente du même amendement' => $ecartes['doublon'],
            'amendement absent de la base' => $absents,
        ], $lues);
        $this->consigner($io, 'Mots-clés des amendements', $fichierTags, $tagsLus - $tagsVides - $tagsAbsents, [
            'sans amendement ni libellé' => $tagsVides,
            'amendement absent de la base' => $tagsAbsents,
        ], $tagsLus);

        $io->definitionList(
            ['Lignes de résumés lues' => $lues],
            ['Amendements distincts' => \count($resumes)],
            ['Écartées : doublon d\'un même amendement' => $ecartes['doublon']],
            ['Écartées : sans résumé' => $ecartes['sans_resume']],
            ['Catégories hors liste ramenées à vide' => $ecartes['categorie_hors_liste']],
            ['Amendements absents de la base' => $absents],
            ['Amendements rapprochés' => $retenus],
            ['… dont résumé déjà présent (gardé)' => $avant],
            ['Lignes modifiées' => $modifies],
            ['Mots-clés lus' => $tagsLus],
            ['… d\'amendements absents' => $tagsAbsents],
            ['Mots-clés déjà connus' => $bilanTags['connus']],
            ['Mots-clés créés' => $bilanTags['nouveaux']],
            ['Liens posés' => $bilanTags['liens']],
        );

        $io->success($simulation
            ? 'Simulation : tout a été annulé.'
            : 'Résumés et mots-clés repris. Rapprocher les synonymes : app:ia:tags:nettoyer ; rendre les pages à jour : app:cache:rechauffer.');

        return self::SUCCESS;
    }
}
