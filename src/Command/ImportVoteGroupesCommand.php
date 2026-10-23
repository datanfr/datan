<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe la ventilation des scrutins par groupe parlementaire depuis un export
 * TSV de la table « votes_groupes » de l'application d'origine.
 *
 * Cette ventilation est historique : elle reflète la composition des groupes au
 * moment du scrutin, ce qu'on ne peut pas reconstituer depuis les votes
 * individuels (voir VoteGroupe).
 *
 * Format attendu (TSV, sans en-tête) :
 *   voteId <TAB> organeRef <TAB> nombreMembresGroupe <TAB> positionMajoritaire
 *   <TAB> nombrePours <TAB> nombreContres <TAB> nombreAbstentions
 *   <TAB> nonVotants <TAB> nonVotantsVolontaires
 */
#[AsCommand(
    name: 'app:import:vote-groupes',
    description: 'Importe la ventilation des scrutins par groupe depuis un export TSV.',
)]
class ImportVoteGroupesCommand extends Command
{
    private const BATCH_SIZE = 1000;

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('file', null, InputOption::VALUE_REQUIRED, 'Chemin du TSV votes_groupes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getOption('file');

        if ($file === '' || !is_readable($file)) {
            $io->error(sprintf('Fichier introuvable ou illisible : "%s".', $file));

            return Command::FAILURE;
        }

        $io->title('Import de la ventilation par groupe');

        $scrutinIdByUid = $this->connection->fetchAllKeyValue('SELECT uid, id FROM scrutin');
        $groupeIdByUid = $this->connection->fetchAllKeyValue('SELECT uid, id FROM groupe');
        $io->text(sprintf('Correspondances : %d scrutins, %d groupes.', \count($scrutinIdByUid), \count($groupeIdByUid)));

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $io->error('Impossible d\'ouvrir le fichier.');

            return Command::FAILURE;
        }

        $read = 0;
        $written = 0;
        $skippedScrutin = 0;
        $skippedGroupe = 0;
        $batch = [];

        $this->connection->beginTransaction();

        try {
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line, "\r\n");
                if ($line === '') {
                    continue;
                }
                ++$read;

                [$voteId, $organeRef, $membres, $position, $pours, $contres, $abstentions, $nv, $nvv]
                    = array_pad(explode("\t", $line), 9, '');

                $scrutinId = $scrutinIdByUid[$voteId] ?? null;
                if ($scrutinId === null) {
                    ++$skippedScrutin;
                    continue;
                }

                $groupeId = $groupeIdByUid[$organeRef] ?? null;
                if ($groupeId === null) {
                    ++$skippedGroupe;
                    continue;
                }

                $batch[] = [
                    $scrutinId,
                    $groupeId,
                    (int) $membres,
                    $position !== '' ? $position : null,
                    (int) $pours,
                    (int) $contres,
                    (int) $abstentions,
                    (int) $nv,
                    (int) $nvv,
                ];

                if (\count($batch) >= self::BATCH_SIZE) {
                    $written += $this->flushBatch($batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $written += $this->flushBatch($batch);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            fclose($handle);
            $io->error(sprintf('Import interrompu : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        fclose($handle);

        if ($skippedScrutin > 0 || $skippedGroupe > 0) {
            $io->warning(sprintf(
                'Ignorées : %d (scrutin inconnu), %d (groupe inconnu).',
                $skippedScrutin,
                $skippedGroupe,
            ));
        }

        $io->success(sprintf(
            '%d lignes lues — %d en base.',
            $read,
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM vote_groupe'),
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<list<mixed>> $batch
     */
    private function flushBatch(array $batch): int
    {
        $placeholders = implode(', ', array_fill(0, \count($batch), '(?, ?, ?, ?, ?, ?, ?, ?, ?)'));
        $params = array_merge(...$batch);

        return (int) $this->connection->executeStatement(
            'INSERT INTO vote_groupe (scrutin_id, groupe_id, nombre_membres_groupe, position_majoritaire,
                                      nombre_pours, nombre_contres, nombre_abstentions, non_votants, non_votants_volontaires)
             VALUES ' . $placeholders . '
             ON DUPLICATE KEY UPDATE
                nombre_membres_groupe = VALUES(nombre_membres_groupe),
                position_majoritaire = VALUES(position_majoritaire),
                nombre_pours = VALUES(nombre_pours), nombre_contres = VALUES(nombre_contres),
                nombre_abstentions = VALUES(nombre_abstentions), non_votants = VALUES(non_votants),
                non_votants_volontaires = VALUES(non_votants_volontaires)',
            $params
        );
    }
}
