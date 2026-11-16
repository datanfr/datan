<?php

namespace App\Command;

use App\Enum\DecryptageState;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les décryptages éditoriaux (`votes_datan`) et leurs référentiels
 * (`fields` → `categorie`, `readings` → `lecture`) depuis des exports TSV de la
 * base de production.
 *
 * C'est la donnée qui fait la valeur du site : l'import ne supprime jamais un
 * décryptage, et se rejoue sans doublon (rapprochement sur législature +
 * numéro du vote, la clé unique de `decryptage`).
 *
 * **Le scrutin se résout par son uid** (`votes_datan.vote_id`) avant le numéro :
 * un vote du Congrès porte `voteNumero = -1` et son numéro réel est aussi celui
 * d'un scrutin de l'Assemblée — l'uid (`VTCGR…`) est la seule clé sans
 * ambiguïté.
 *
 * **L'auteur se résout par `utilisateur.id_origine`** : `created_by` et
 * `modified_by` sont des `users.id` du legacy, que `app:import:utilisateurs`
 * a gardés. D'où l'ordre : comptes d'abord. Un auteur introuvable (compte
 * supprimé au legacy) laisse le lien vide — et l'identifiant brut reste dans
 * `created_by`, seule trace d'attribution, que `Decryptage::getSignature()`
 * affiche à défaut.
 *
 * Requêtes d'export : `bin/recuperation/legacy/decryptages_categories.sql`,
 * `decryptages_lectures.sql` et `decryptages.sql`.
 *
 * Import de récupération : la production fait foi. Après la bascule, un rejeu
 * écraserait les décryptages publiés ou corrigés ici.
 */
#[AsCommand(
    name: 'app:import:decryptages',
    description: 'Importe les décryptages de votes (votes_datan) depuis des exports TSV de la production.',
)]
class ImportDecryptagesCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this
            ->addOption('categories', null, InputOption::VALUE_REQUIRED, 'TSV de la table fields', 'var/legacy/decryptages_categories.tsv')
            ->addOption('lectures', null, InputOption::VALUE_REQUIRED, 'TSV de la table readings', 'var/legacy/decryptages_lectures.tsv')
            ->addOption('decryptages', null, InputOption::VALUE_REQUIRED, 'TSV de la table votes_datan', 'var/legacy/decryptages.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categorieParIdLegacy = $this->importerCategories($io, (string) $input->getOption('categories'));
        $lectureParIdLegacy = $this->importerLectures($io, (string) $input->getOption('lectures'));
        $this->importerDecryptages($io, (string) $input->getOption('decryptages'), $categorieParIdLegacy, $lectureParIdLegacy);

        return Command::SUCCESS;
    }

    /**
     * Thèmes, rapprochés sur le slug — l'adresse `/votes/decryptes/{slug}`.
     *
     * @return array<string, int> id legacy → id local
     */
    private function importerCategories(SymfonyStyle $io, string $fichier): array
    {
        $idParSlug = $this->connection->fetchAllKeyValue('SELECT slug, id FROM categorie');
        $correspondance = [];
        $ecartees = ['sans slug' => 0];
        $ecrites = 0;

        foreach ($this->lignes($fichier) as [$idLegacy, $nom, $slug, $libelle]) {
            $slug = $this->texte($slug);
            if ($slug === null) {
                ++$ecartees['sans slug'];

                continue;
            }

            $nom = (string) $this->texte($nom);
            if (isset($idParSlug[$slug])) {
                $this->connection->executeStatement(
                    'UPDATE categorie SET name = ?, libelle = ? WHERE id = ?',
                    [$nom, $this->texte($libelle), $idParSlug[$slug]],
                );
            } else {
                $this->connection->executeStatement(
                    'INSERT INTO categorie (name, slug, libelle) VALUES (?, ?, ?)',
                    [$nom, $slug, $this->texte($libelle)],
                );
                $idParSlug[$slug] = (int) $this->connection->lastInsertId();
            }

            $correspondance[(string) $idLegacy] = (int) $idParSlug[$slug];
            ++$ecrites;
        }

        $this->consigner($io, 'Thèmes des décryptages', $fichier, $ecrites, $ecartees);

        return $correspondance;
    }

    /**
     * Lectures, rapprochées sur le libellé : la table n'a pas d'autre clé.
     *
     * @return array<string, int> id legacy → id local
     */
    private function importerLectures(SymfonyStyle $io, string $fichier): array
    {
        $idParNom = [];
        foreach ($this->connection->fetchAllAssociative('SELECT id, name FROM lecture') as $l) {
            $idParNom[$l['name']] = (int) $l['id'];
        }

        $correspondance = [];
        $ecartees = ['sans libellé' => 0];
        $ecrites = 0;

        foreach ($this->lignes($fichier) as [$idLegacy, $nom]) {
            $nom = $this->texte($nom);
            if ($nom === null) {
                ++$ecartees['sans libellé'];

                continue;
            }

            if (!isset($idParNom[$nom])) {
                $this->connection->executeStatement('INSERT INTO lecture (name) VALUES (?)', [$nom]);
                $idParNom[$nom] = (int) $this->connection->lastInsertId();
            }

            $correspondance[(string) $idLegacy] = $idParNom[$nom];
            ++$ecrites;
        }

        $this->consigner($io, 'Lectures', $fichier, $ecrites, $ecartees);

        return $correspondance;
    }

    /**
     * @param array<string, int> $categorieParIdLegacy
     * @param array<string, int> $lectureParIdLegacy
     */
    private function importerDecryptages(SymfonyStyle $io, string $fichier, array $categorieParIdLegacy, array $lectureParIdLegacy): void
    {
        $scrutinParUid = $this->connection->fetchAllKeyValue('SELECT uid, id FROM scrutin');
        $scrutinParNumero = [];
        foreach ($this->connection->iterateAssociative('SELECT legislature, numero, id FROM scrutin WHERE uid LIKE \'VTANR%\'') as $s) {
            $scrutinParNumero[$s['legislature'] . '|' . $s['numero']] = (int) $s['id'];
        }
        $compteParOrigine = $this->connection->fetchAllKeyValue('SELECT id_origine, id FROM utilisateur WHERE id_origine IS NOT NULL');

        $lignes = [];
        $ecartees = ['sans législature ni numéro de vote' => 0];
        $sansScrutin = 0;
        $auteurs = ['résolus' => 0, 'introuvables' => 0, 'absents' => 0];

        foreach ($this->lignes($fichier) as $ligne) {
            [, $legislature, $numero, $voteId, $titre, $slug, $categorie, $lecture, $description, $etat, $creeLe, $modifieLe, $creePar, $modifiePar]
                = array_pad($ligne, 14, null);

            $legislature = $this->entier($legislature);
            $numero = $this->entier($numero);
            if ($legislature === null || $numero === null) {
                ++$ecartees['sans législature ni numéro de vote'];

                continue;
            }

            $voteId = $this->texte($voteId);
            $scrutinId = ($voteId !== null ? ($scrutinParUid[$voteId] ?? null) : null)
                ?? $scrutinParNumero[$legislature . '|' . $numero]
                ?? null;
            if ($scrutinId === null) {
                ++$sansScrutin;
            }

            $creePar = $this->texte($creePar);
            $modifiePar = $this->texte($modifiePar);
            $auteurId = $creePar === null ? null : ($compteParOrigine[$creePar] ?? null);
            ++$auteurs[$creePar === null ? 'absents' : ($auteurId === null ? 'introuvables' : 'résolus')];

            $lignes[] = [
                $legislature,
                $numero,
                $voteId,
                (string) $titre,
                (string) $slug,
                (string) $description,
                (DecryptageState::tryFrom((string) $etat) ?? DecryptageState::Draft)->value,
                $creePar,
                $modifiePar,
                $this->dateHeure($creeLe),
                $this->dateHeure($modifieLe),
                $categorieParIdLegacy[(string) $categorie] ?? null,
                $this->texte($lecture) === null ? null : ($lectureParIdLegacy[(string) $lecture] ?? null),
                $scrutinId,
                $auteurId,
                $modifiePar === null ? null : ($compteParOrigine[$modifiePar] ?? null),
            ];
        }

        foreach (array_chunk($lignes, 100) as $lot) {
            // Le scrutin et les auteurs ne s'effacent pas faute d'être résolus :
            // un rattachement déjà fait ici (le Congrès, un compte relié à la
            // main) survit à un rejeu dont la résolution échoue.
            $this->upsert(
                'decryptage',
                ['legislature', 'vote_numero', 'vote_id', 'title', 'slug', 'description', 'state', 'created_by', 'modified_by',
                    'created_at', 'modified_at', 'categorie_id', 'lecture_id', 'scrutin_id', 'auteur_id', 'modifie_par_id'],
                $lot,
                ['vote_id', 'title', 'slug', 'description', 'state', 'created_by', 'modified_by', 'created_at', 'modified_at', 'categorie_id', 'lecture_id'],
                ['scrutin_id', 'auteur_id', 'modifie_par_id'],
            );
        }

        $this->consigner($io, 'Décryptages', $fichier, \count($lignes), $ecartees);
        $io->text(sprintf(
            'Auteurs : %d rattaché(s) à un compte, %d compte(s) disparu(s) du legacy (identifiant brut gardé), %d sans auteur.',
            $auteurs['résolus'],
            $auteurs['introuvables'],
            $auteurs['absents'],
        ));

        if ($sansScrutin > 0) {
            $io->warning(sprintf('%d décryptage(s) sans scrutin en base : rattachement à refaire après le sync.', $sansScrutin));
        }
    }

    private function dateHeure(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
