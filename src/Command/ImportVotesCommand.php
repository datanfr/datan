<?php

namespace App\Command;

use App\Enum\VotePosition;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les votes nominatifs des députés depuis un export TSV de la base
 * d'origine (table « votes »).
 *
 * Volume attendu : ~1,05 million de lignes. L'implémentation est donc taillée
 * pour le débit : lecture en flux, correspondances mpId/voteId résolues en
 * mémoire (deux requêtes au total plutôt que deux par ligne), et insertions
 * groupées. Passer par l'ORM ici serait plusieurs ordres de grandeur plus lent.
 *
 * Format attendu (TSV, sans en-tête) :
 *   mpId <TAB> voteId <TAB> position <TAB> voteType <TAB> causePosition <TAB> parDelegation
 *
 * **Sert encore à combler ce que l'open data a perdu.** L'Assemblée republie
 * aujourd'hui sept scrutins de la 15e sans le bloc du groupe MODEM (PO730970) —
 * 2418, 2744, 2751, 2752, 2761, 2762, 2766 : 234 votes que le legacy, moissonné
 * à l'époque, a gardés (cf. l'avertissement « Source incomplète »
 * d'`app:import:scrutins`). Requête d'export : `bin/recuperation/legacy/votes_l15_modem.sql`
 * (3 019 lignes, sans en-tête), jouée par `bin/recuperation/exporter-legacy.sh`
 * et rejouée par `app:recuperation:legacy`, qui n'appelle cette commande
 * qu'avec `--file`.
 *
 * Jamais `--truncate` pour une reprise ciblée : il vide la table entière.
 */
#[AsCommand(
    name: 'app:import:votes',
    description: 'Importe les votes nominatifs des députés depuis un export TSV.',
)]
class ImportVotesCommand extends Command
{
    private const BATCH_SIZE = 2000;

    /** Encodage des positions dans l'application d'origine. */
    private const POSITION_MAP = [
        '1' => VotePosition::Pour,
        '-1' => VotePosition::Contre,
        '0' => VotePosition::Abstention,
        'nv' => VotePosition::NonVotant,
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Chemin du TSV des votes')
            ->addOption('truncate', null, InputOption::VALUE_NONE, 'Vide la table vote avant import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getOption('file');

        if ($file === '' || !is_readable($file)) {
            $io->error(sprintf('Fichier introuvable ou illisible : "%s".', $file));

            return Command::FAILURE;
        }

        $io->title('Import des votes nominatifs');

        $deputeIdByMpId = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');
        $scrutinIdByUid = $this->connection->fetchAllKeyValue('SELECT uid, id FROM scrutin');
        // Date recopiée sur chaque vote (voir Vote::$scrutinDate).
        $scrutinDateByUid = $this->connection->fetchAllKeyValue('SELECT uid, date_scrutin FROM scrutin');
        $io->text(sprintf(
            'Correspondances chargées : %d députés, %d scrutins.',
            \count($deputeIdByMpId),
            \count($scrutinIdByUid),
        ));

        if ($input->getOption('truncate')) {
            $this->connection->executeStatement('DELETE FROM vote');
            $io->text('Table vote vidée.');
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $io->error('Impossible d\'ouvrir le fichier.');

            return Command::FAILURE;
        }

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $read = 0;
        $inserted = 0;
        $skippedDepute = 0;
        $skippedScrutin = 0;
        $batch = [];

        $start = microtime(true);

        // Chargement de masse : les contrôles sont rétablis juste après.
        //
        // Surtout PAS `unique_checks = 0` : InnoDB cesse alors de vérifier
        // l'index unique (depute_id, scrutin_id, vote_type), si bien que
        // l'`ON DUPLICATE KEY UPDATE` ne se déclenche plus et que la ligne est
        // insérée une seconde fois. Essayé le 16/11 sur un scrutin déjà en
        // base : 186 lignes relues, 91 doublons créés. Sans conséquence sur
        // une table vide (le premier chargement), mais toute reprise ciblée
        // — celle des votes MODEM de la 15e en est une — doublerait des votes.
        $this->connection->executeStatement('SET foreign_key_checks = 0');
        $this->connection->beginTransaction();

        try {
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line, "\r\n");
                if ($line === '') {
                    continue;
                }
                ++$read;

                [$mpId, $voteId, $position, $voteType, $causePosition, $parDelegation] = array_pad(explode("\t", $line), 6, '');

                $deputeId = $deputeIdByMpId[$mpId] ?? null;
                if ($deputeId === null) {
                    ++$skippedDepute;
                    continue;
                }

                $scrutinId = $scrutinIdByUid[$voteId] ?? null;
                if ($scrutinId === null) {
                    ++$skippedScrutin;
                    continue;
                }

                $batch[] = [
                    $deputeId,
                    $scrutinId,
                    (self::POSITION_MAP[$position] ?? null)?->value,
                    $voteType !== '' ? $voteType : 'decompteNominatif',
                    $causePosition !== '' ? $causePosition : null,
                    $parDelegation === 'true' ? 1 : 0,
                    $scrutinDateByUid[$voteId] ?? null,
                    $now,
                    $now,
                ];

                if (\count($batch) >= self::BATCH_SIZE) {
                    $inserted += $this->flushBatch($batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $inserted += $this->flushBatch($batch);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            fclose($handle);
            $this->restoreChecks();
            $io->error(sprintf('Import interrompu : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        fclose($handle);
        $this->restoreChecks();

        $elapsed = microtime(true) - $start;
        $total = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM vote');

        $io->text(sprintf('Lignes lues : %d — écrites : %d.', $read, $inserted));
        if ($skippedDepute > 0 || $skippedScrutin > 0) {
            $io->warning(sprintf(
                'Ignorées : %d (député inconnu), %d (scrutin inconnu).',
                $skippedDepute,
                $skippedScrutin,
            ));
        }
        $io->success(sprintf(
            '%d votes en base — %.1f s (%s lignes/s).',
            $total,
            $elapsed,
            number_format($elapsed > 0 ? $read / $elapsed : 0, 0, ',', ' '),
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
            'INSERT INTO vote (depute_id, scrutin_id, position, vote_type, cause_position, par_delegation, scrutin_date, created_at, updated_at)
             VALUES ' . $placeholders . '
             ON DUPLICATE KEY UPDATE
                position = VALUES(position), cause_position = VALUES(cause_position),
                par_delegation = VALUES(par_delegation), scrutin_date = VALUES(scrutin_date),
                updated_at = VALUES(updated_at)',
            $params
        );
    }

    private function restoreChecks(): void
    {
        $this->connection->executeStatement('SET unique_checks = 1');
        $this->connection->executeStatement('SET foreign_key_checks = 1');
    }
}
