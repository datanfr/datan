<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les parrainages de l'élection présidentielle de 2022 depuis la base
 * de production (table « parrainages », 13 427 lignes).
 *
 * **Source régénérable.** Le Conseil constitutionnel republie ces parrainages
 * dans son open data : ils ne sont pas irremplaçables comme les décryptages ou
 * les explications de vote. On les reprend néanmoins depuis la base de
 * production, qui les tient déjà nettoyés et adossés aux acteurs de l'Assemblée
 * (`mpId`), pour servir `/parrainages-2022` sans dépendre d'une moisson externe.
 * Le jour où cette source disparaîtrait, le rejeu se fait depuis l'open data du
 * Conseil.
 *
 * La table est reprise en entier — pas seulement le volet des 530 députés — car
 * le bloc « candidats ayant récolté plus de 500 signatures » de la page se
 * calcule sur l'ensemble des élus (maires, sénateurs, conseillers…).
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e \
 *   "SELECT id, civ, nameLast, nameFirst, mandat, circo, dpt, candidat, \
 *           datePublication, year, mpId, dateMaj \
 *    FROM parrainages ORDER BY id" > var/legacy/parrainages.tsv
 * ```
 *
 * Import de récupération : pour le chargement initial, jamais dans
 * `app:sync:quotidien`.
 */
#[AsCommand(
    name: 'app:import:parrainages',
    description: 'Récupère les parrainages 2022 depuis un export TSV de la production.',
)]
class ImportParrainagesCommand extends ImportLegacyCommand
{
    private const COLONNES = [
        'source_id', 'civilite', 'nom', 'prenom', 'mandat', 'circonscription',
        'departement', 'candidat', 'date_publication', 'annee', 'mp_id', 'date_maj',
    ];

    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'TSV de la table parrainages', 'var/legacy/parrainages.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $lot = [];
        $lues = 0;
        $ecrites = 0;
        $rebuts = 0;

        foreach ($this->lignes((string) $input->getOption('fichier')) as $ligne) {
            ++$lues;

            [$id, $civ, $nom, $prenom, $mandat, $circo, $dpt, $candidat,
                $datePublication, $annee, $mpId, $dateMaj] = array_pad($ligne, 12, null);

            $nom = $this->texte($nom);
            $prenom = $this->texte($prenom);
            $candidat = $this->texte($candidat);

            // Un parrainage sans parrain nommé ni candidat n'a rien à montrer : on
            // l'écarte plutôt que d'insérer une ligne creuse. La source n'en
            // produit pas, mais la garde rend l'écart visible s'il en apparaissait.
            if ($nom === null || $prenom === null || $candidat === null) {
                ++$rebuts;
                continue;
            }

            $lot[] = [
                $this->entier($id),
                $this->texte($civ),
                $nom,
                $prenom,
                $this->texte($mandat),
                $this->texte($circo),
                $this->texte($dpt),
                $candidat,
                $this->dateSeule($datePublication),
                $this->entier($annee),
                $this->texte($mpId),
                $this->dateSeule($dateMaj),
            ];

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('parrainage', self::COLONNES, $lot, \array_slice(self::COLONNES, 1));
                $ecrites += \count($lot);
                $lot = [];
            }
        }

        if ($lot !== []) {
            $this->upsert('parrainage', self::COLONNES, $lot, \array_slice(self::COLONNES, 1));
            $ecrites += \count($lot);
        }

        $io->success(sprintf('%d parrainage(s) écrit(s) sur %d lu(s).', $ecrites, $lues));

        if ($rebuts > 0) {
            $io->text(sprintf('%d ligne(s) écartée(s) — parrain ou candidat manquant.', $rebuts));
        }

        $deputes = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM parrainage WHERE annee = 2022 AND mandat IN ('député', 'députée')",
        );
        $io->text(sprintf('%d parrainage(s) émanant de députés (le volet affiché par la page).', $deputes));

        return Command::SUCCESS;
    }

    /** `mariadb` écrit les dates nulles « 0000-00-00 » ; on les rend en null. */
    private function dateSeule(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
