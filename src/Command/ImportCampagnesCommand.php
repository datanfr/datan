<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les campagnes d'appel aux dons (`campaigns`) de la base de production.
 *
 * Le bandeau et son écran d'administration sont portés (`Admin\CampagneController`),
 * pas les campagnes déjà écrites : sans cet import, la rédaction retrouverait
 * une liste vide. Deux campagnes au 16 novembre 2026, toutes deux inactives —
 * l'enjeu est l'historique et le texte, pas l'affichage du jour.
 *
 * La table n'a pas de clé naturelle : une campagne se reconnaît à sa date de
 * début et à son texte, ce qui suffit à un rejeu sans doublon. Une campagne
 * dont on a corrigé le texte ici serait en revanche reprise en double — d'où
 * le garde-fou de `app:recuperation:legacy` contre tout rejeu après la bascule.
 *
 * Requête d'export : `bin/recuperation/legacy/campagnes.sql` (l'auteur y est
 * résolu en nom affiché, ce que porte `campagne.auteur`).
 */
#[AsCommand(
    name: 'app:import:campagnes',
    description: 'Récupère les campagnes d\'appel aux dons depuis un export TSV de la production.',
)]
class ImportCampagnesCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'Export TSV de la table campaigns', 'var/legacy/campagnes.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fichier = (string) $input->getOption('fichier');

        $existantes = [];
        foreach ($this->connection->fetchAllAssociative('SELECT id, date_debut, texte FROM campagne') as $c) {
            $existantes[$c['date_debut'] . '|' . sha1((string) $c['texte'])] = (int) $c['id'];
        }

        $creees = 0;
        $misesAJour = 0;
        $ecartees = ['sans texte ni dates' => 0];

        foreach ($this->lignes($fichier) as $ligne) {
            [, $texte, $debut, $fin, $auteur, $position, $page, $active, $creeLe, $modifieLe] = array_pad($ligne, 10, null);

            $debut = $this->date($debut);
            $fin = $this->date($fin);
            if ($this->texte($texte) === null || $debut === null || $fin === null) {
                ++$ecartees['sans texte ni dates'];

                continue;
            }

            $valeurs = [
                'texte' => $texte,
                'date_debut' => $debut,
                'date_fin' => $fin,
                'auteur' => $this->texte($auteur) === null ? null : mb_substr((string) $auteur, 0, 100),
                'position' => $this->entier($position),
                'page' => $this->texte($page),
                'active' => (int) ($active === '1'),
                'cree_le' => $this->dateHeure($creeLe),
                'modifie_le' => $this->dateHeure($modifieLe),
            ];

            $id = $existantes[$debut . '|' . sha1((string) $texte)] ?? null;
            if ($id === null) {
                $this->connection->insert('campagne', $valeurs);
                ++$creees;
            } else {
                $this->connection->update('campagne', $valeurs, ['id' => $id]);
                ++$misesAJour;
            }
        }

        $this->consigner($io, 'Campagnes de dons', $fichier, $creees + $misesAJour, $ecartees);
        $io->text(sprintf('%d créée(s), %d déjà présente(s) et réalignée(s).', $creees, $misesAJour));

        return Command::SUCCESS;
    }

    private function date(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : substr($valeur, 0, 10);
    }

    private function dateHeure(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        return $valeur === null || str_starts_with($valeur, '0000') ? null : $valeur;
    }
}
