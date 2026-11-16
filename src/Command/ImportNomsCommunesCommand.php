<?php

namespace App\Command;

use App\Http\RechauffeurCache;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Renseigne `commune.nom_de` — « de Marseille », « d'Ajaccio », « du Havre »,
 * « d'Hirson » — depuis le jeu public des communes de data.gouv.fr.
 *
 * C'est la colonne `cities.nom_de` de l'application d'origine, que son
 * `daily.php` (`getCities()`) charge du même fichier. Elle ne se recompose pas
 * par une règle : le jeu élide devant les h muets et pas devant les h aspirés
 * (« d'Hirson » mais « de Hautmont »), ce qu'aucun test sur l'initiale ne
 * devine — 437 communes sur 34 935 s'écartent de la meilleure règle simple.
 * La page de résultats des municipales s'en sert (« aucun député candidat […]
 * dans la ville d'Hirson »).
 *
 * Écriture par `UPDATE` sur le code INSEE, en minuscules des deux côtés : le
 * référentiel écrit la Corse `2a004`, le jeu `2A004`. Une commune absente du
 * jeu garde sa valeur ; la page se rabat alors sur la règle simple.
 */
#[AsCommand(
    name: 'app:import:noms-communes',
    description: 'Renseigne l\'article des communes (« de Marseille », « du Havre ») depuis data.gouv.fr.',
)]
class ImportNomsCommunesCommand extends Command
{
    /** « Communes et villes de France », ressource CSV stable du jeu data.gouv. */
    public const SOURCE = 'https://www.data.gouv.fr/api/1/datasets/r/f5df602b-3800-44d7-b2df-fa40a0350325';

    private const TAILLE_LOT = 1000;

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $client,
        private readonly RechauffeurCache $cacheHttp,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'CSV déjà téléchargé, à la place de la source en ligne');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Article des communes');

        $fichier = $input->getOption('fichier');
        if ($fichier === null) {
            $fichier = tempnam(sys_get_temp_dir(), 'communes_');
            file_put_contents($fichier, $this->client->request('GET', self::SOURCE)->getContent());
        }

        $flux = fopen((string) $fichier, 'r');
        if ($flux === false) {
            throw new \RuntimeException(sprintf('CSV introuvable : « %s ».', $fichier));
        }

        $entete = fgetcsv($flux, null, ',', '"', '\\');
        $colonneInsee = array_search('code_insee', (array) $entete, true);
        $colonneNomDe = array_search('nom_de', (array) $entete, true);
        if ($colonneInsee === false || $colonneNomDe === false) {
            throw new \RuntimeException('Le CSV n\'a plus les colonnes code_insee et nom_de.');
        }

        $communes = array_change_key_case($this->connection->fetchAllKeyValue('SELECT code_insee, id FROM commune'));

        $lus = 0;
        $vides = 0;
        $inconnues = 0;
        $lot = [];

        while (($ligne = fgetcsv($flux, null, ',', '"', '\\')) !== false) {
            if (\count($ligne) < 2) {
                continue;
            }
            ++$lus;

            $nomDe = trim((string) ($ligne[$colonneNomDe] ?? ''));
            if ($nomDe === '') {
                ++$vides;

                continue;
            }

            $id = $communes[strtolower((string) $ligne[$colonneInsee])] ?? null;
            if ($id === null) {
                ++$inconnues;

                continue;
            }

            $lot[(int) $id] = $nomDe;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->ecris($lot);
                $lot = [];
            }
        }
        fclose($flux);
        $this->ecris($lot);

        $io->text(sprintf('%d communes lues.', $lus));
        if ($vides > 0) {
            $io->text(sprintf('  %d écartées : article non renseigné.', $vides));
        }
        if ($inconnues > 0) {
            $io->text(sprintf('  %d écartées : code INSEE absent du référentiel des communes (communes sans circonscription).', $inconnues));
        }

        $io->success(sprintf(
            '%d communes sur %d ont leur article.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM commune WHERE nom_de IS NOT NULL'),
            \count($communes),
        ));

        // Les phrases des pages de résultats changent : purge, comme après
        // toute écriture visible ({@see RechauffeurCache}).
        $this->cacheHttp->purger();

        return Command::SUCCESS;
    }

    /** @param array<int, string> $lot */
    private function ecris(array $lot): void
    {
        if ($lot === []) {
            return;
        }

        $cas = implode(' ', array_fill(0, \count($lot), 'WHEN ? THEN ?'));
        $parametres = [];
        foreach ($lot as $id => $nomDe) {
            $parametres[] = $id;
            $parametres[] = $nomDe;
        }

        $this->connection->executeStatement(
            sprintf('UPDATE commune SET nom_de = CASE id %s END WHERE id IN (%s)', $cas, implode(', ', array_keys($lot))),
            $parametres,
        );
    }
}
