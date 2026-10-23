<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les professions de foi des députés (bloc « Ses professions de foi »
 * de la fiche) depuis un export TSV de la base de production.
 *
 * Export à régénérer ainsi :
 *
 *   docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan -B -e "
 *     SELECT id, mpId, electionId, file, tour
 *     FROM profession_foi ORDER BY id;" > var/legacy/professions_foi.tsv
 *
 * **Le backup public livre `profession_foi` VIDE** (jeu réduit, comme
 * `users_mp`) : en local cet import écrit zéro ligne et le bloc de la fiche
 * reste masqué. Au déploiement, le rejouer contre la vraie base — et copier le
 * répertoire `assets/data/professions/` du serveur, où vivent les PDF (les
 * chemins servis sont les mêmes que ceux de datan.fr).
 *
 * Les fichiers eux-mêmes ne sont PAS téléchargés ici : ils n'ont pas d'index
 * public, seule la table sait lesquels existent.
 */
#[AsCommand(
    name: 'app:import:professions-foi',
    description: 'Importe les professions de foi des députés depuis un export de la base de production.',
)]
class ImportProfessionsFoiCommand extends ImportLegacyCommand
{
    private const COLONNES = ['mp_id', 'election_id', 'fichier', 'tour'];

    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'Export TSV de la table profession_foi', 'var/legacy/professions_foi.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des professions de foi');

        $fichier = (string) $input->getOption('fichier');
        $lot = [];
        $lues = 0;
        $ecartees = 0;

        foreach ($this->lignes($fichier) as $champs) {
            ++$lues;

            // [id, mpId, electionId, file, tour] — l'id d'origine ne sert pas :
            // la clé naturelle (mp, élection, tour) suffit et survit aux rejeux.
            if (\count($champs) < 5 || $champs[1] === null || $champs[2] === null || $champs[3] === null || $champs[4] === null) {
                ++$ecartees;
                continue;
            }

            $lot[] = [$champs[1], (int) $champs[2], $champs[3], (int) $champs[4]];

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('profession_foi', self::COLONNES, $lot, ['fichier']);
                $lot = [];
            }
        }

        if ($lot !== []) {
            $this->upsert('profession_foi', self::COLONNES, $lot, ['fichier']);
        }

        if ($ecartees > 0) {
            $io->text(sprintf('%d lignes écartées : champ obligatoire absent.', $ecartees));
        }

        $total = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM profession_foi');
        $io->success(sprintf('%d lignes lues, %d professions de foi en base.', $lues, $total));

        if ($total === 0) {
            $io->warning('Table vide : le backup public ne livre pas les professions de foi — rejouer contre la vraie base au déploiement (cf. docblock).');
        }

        return self::SUCCESS;
    }
}
