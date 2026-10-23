<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Mise à jour quotidienne de la base depuis les données des Tricoteuses.
 *
 * C'est le point d'entrée unique à planifier. L'ordre des étapes suit les
 * dépendances : un scrutin se rattache à un dossier et ventile ses votes par
 * groupe, il faut donc que dossiers et organes soient connus avant lui.
 *
 * À lancer impérativement en APP_ENV=prod : en dev, le journal Doctrine
 * conserve chaque requête en mémoire et fait échouer les imports volumineux.
 */
#[AsCommand(
    name: 'app:sync:quotidien',
    description: 'Moissonne les Tricoteuses et met à jour la base (à planifier quotidiennement).',
)]
class SyncQuotidienCommand extends Command
{
    /**
     * Les étapes, dans l'ordre des dépendances.
     *
     * @var list<array{0: string, 1: array<string, mixed>, 2: string}>
     */
    private const ETAPES = [
        ['app:tricoteuses:sync', [], 'Moisson des dépôts'],
        ['app:import:acteurs', [], 'Organes, députés, mandats et fonctions'],
        // Après les acteurs, dont elle a besoin pour rattacher chaque mandat à sa
        // fiche : les délégations du Bureau (« Postes Assemblée »), qui se créent
        // et s'éteignent en cours de législature.
        ['app:import:organes', [], 'Organes hors table dédiée (délégations du Bureau)'],
        // Juste après les acteurs : les profils se lisent dans les mêmes
        // fichiers, et c'est pour qu'ils se rafraîchissent qu'ils ont quitté la
        // base gelée dont ils venaient.
        ['app:import:profils-sociaux', [], 'Profils socio-professionnels'],
        ['app:import:photos', [], 'Photographies des députés'],
        // Dans la foulée : une photo publiée sans son détourage sortirait avec
        // le fond de studio bleu au milieu d'une carte blanche. L'étape ne
        // retraite que ce que la précédente vient d'écrire — 647 portraits déjà
        // faits coûtent le temps d'un `filemtime()` chacun.
        ['app:photos:detourer', [], 'Détourage des photographies'],
        ['app:import:dossiers', [], 'Dossiers législatifs'],
        // Avant les scrutins : c'est au scrutin de désigner l'amendement qu'il
        // met aux voix, l'amendement doit donc déjà exister.
        ['app:import:amendements', [], 'Amendements'],
        ['app:import:scrutins', [], 'Scrutins, ventilations et votes'],
        // Les comptes rendus paraissent quelques jours après la séance ; le
        // delta quotidien n'en touche qu'une poignée. Ils nourrissent le
        // brouillon de décryptage par IA de l'espace de rédaction.
        ['app:import:comptes-rendus', [], 'Comptes rendus des séances'],
        // Après les scrutins, puisqu'elle apparie les deux. Elle ne sort pas
        // sur le réseau : tout se joue sur les dépôts déjà clonés. Le rattrapage
        // par scraping (`app:scraper:scrutins`) reste hors de cette chaîne —
        // c'est la seule étape qui interroge un site public, et elle mérite
        // d'être lancée sciemment.
        ['app:lien:scrutins', [], 'Rattachement des scrutins à leur amendement'],
        // En dernier : les classements se calculent sur les votes du jour.
        ['app:calcul:classements', [], 'Classements des députés et des groupes'],
    ];

    protected function configure(): void
    {
        $this->addOption('tout', null, InputOption::VALUE_NONE, 'Réimporte l\'intégralité des dépôts au lieu du seul delta');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tout = (bool) $input->getOption('tout');
        $debut = microtime(true);

        $io->title(sprintf('Mise à jour quotidienne — %s', date('d/m/Y H:i')));

        foreach (self::ETAPES as [$nom, $arguments, $libelle]) {
            $io->section($libelle);

            $commande = $this->getApplication()?->find($nom);
            if ($commande === null) {
                $io->error(sprintf('Commande « %s » introuvable.', $nom));

                return Command::FAILURE;
            }

            if ($tout && $commande->getDefinition()->hasOption('tout')) {
                $arguments['--tout'] = true;
            }

            $code = $commande->run(new ArrayInput($arguments), $output);

            if ($code !== Command::SUCCESS) {
                $io->error(sprintf('Étape « %s » en échec (code %d) — mise à jour interrompue.', $nom, $code));

                return $code;
            }
        }

        $io->success(sprintf('Base à jour en %d s.', (int) (microtime(true) - $debut)));

        return Command::SUCCESS;
    }
}
