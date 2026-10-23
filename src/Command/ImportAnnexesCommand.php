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
 * Importe les données annexes d'un scrutin depuis des exports TSV de la base
 * d'origine : dossiers législatifs, amendements (avec leur résumé) et
 * explications de vote des députés.
 *
 * Chaque fichier est optionnel : on peut n'en rejouer qu'une partie.
 */
#[AsCommand(
    name: 'app:import:annexes',
    description: 'Importe dossiers, amendements et explications de vote depuis des exports TSV.',
)]
class ImportAnnexesCommand extends Command
{
    private const BATCH_SIZE = 1000;

    /** Colonnes du TSV des dossiers, dans l'ordre. */
    private const COLONNES_DOSSIER = [
        'dossier_id', 'legislature', 'titre', 'titre_chemin',
        'procedure_parlementaire', 'procedure_code',
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dossiers', null, InputOption::VALUE_REQUIRED, 'TSV des dossiers')
            ->addOption('scrutin-dossier', null, InputOption::VALUE_REQUIRED, 'TSV des liens scrutin → dossier')
            ->addOption('amendements', null, InputOption::VALUE_REQUIRED, 'TSV des amendements liés aux scrutins')
            ->addOption('explications', null, InputOption::VALUE_REQUIRED, 'TSV des explications de vote');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des annexes de scrutin');

        $scrutinIdByUid = $this->connection->fetchAllKeyValue('SELECT uid, id FROM scrutin');
        $deputeIdByMpId = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        $this->connection->beginTransaction();

        try {
            if ($file = $input->getOption('dossiers')) {
                $io->text(sprintf('Dossiers : %d traités.', $this->importDossiers($file)));
            }
            if ($file = $input->getOption('scrutin-dossier')) {
                $io->text(sprintf('Rattachements scrutin → dossier : %d.', $this->linkDossiers($file, $scrutinIdByUid)));
            }
            if ($file = $input->getOption('amendements')) {
                $io->text(sprintf('Amendements : %d traités.', $this->importAmendements($file, $scrutinIdByUid)));
            }
            if ($file = $input->getOption('explications')) {
                $io->text(sprintf('Explications de vote : %d traitées.', $this->importExplications($file, $scrutinIdByUid, $deputeIdByMpId)));
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            $io->error(sprintf('Import interrompu : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $io->success('Import des annexes terminé.');

        return Command::SUCCESS;
    }

    private function importDossiers(string $file): int
    {
        $handle = fopen($file, 'r');
        $count = 0;
        $batch = [];

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }
            [$dossierId, $legislature, $titre, $chemin, $procedure, $procedureCode] = array_pad(explode("\t", $line), 6, '');
            if ($dossierId === '') {
                continue;
            }

            $batch[] = [
                $dossierId,
                is_numeric($legislature) ? (int) $legislature : null,
                $titre !== '' ? $titre : null,
                $chemin !== '' ? $chemin : null,
                $procedure !== '' ? $procedure : null,
                is_numeric($procedureCode) ? (int) $procedureCode : null,
            ];
            ++$count;

            if (\count($batch) >= self::BATCH_SIZE) {
                $this->flush('dossier', self::COLONNES_DOSSIER, $batch, \array_slice(self::COLONNES_DOSSIER, 1));
                $batch = [];
            }
        }
        if ($batch !== []) {
            $this->flush('dossier', self::COLONNES_DOSSIER, $batch, \array_slice(self::COLONNES_DOSSIER, 1));
        }
        fclose($handle);

        return $count;
    }

    /**
     * @param array<string, mixed> $scrutinIdByUid
     */
    private function linkDossiers(string $file, array $scrutinIdByUid): int
    {
        $dossierIdByRef = $this->connection->fetchAllKeyValue('SELECT dossier_id, id FROM dossier');
        $handle = fopen($file, 'r');
        $linked = 0;

        // Un scrutin n'affiche qu'un dossier : on garde le premier rencontré.
        $pairs = [];
        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }
            [$voteUid, $dossierRef] = array_pad(explode("\t", $line), 2, '');
            $scrutinId = $scrutinIdByUid[$voteUid] ?? null;
            $dossierId = $dossierIdByRef[$dossierRef] ?? null;
            if ($scrutinId === null || $dossierId === null || isset($pairs[$scrutinId])) {
                continue;
            }
            $pairs[$scrutinId] = $dossierId;
        }
        fclose($handle);

        foreach (array_chunk($pairs, 500, true) as $chunk) {
            $cases = [];
            $params = [];
            foreach ($chunk as $scrutinId => $dossierId) {
                $cases[] = 'WHEN ? THEN ?';
                $params[] = $scrutinId;
                $params[] = $dossierId;
            }
            $ids = implode(',', array_map('intval', array_keys($chunk)));
            $linked += (int) $this->connection->executeStatement(
                'UPDATE scrutin SET dossier_id = CASE id ' . implode(' ', $cases) . ' END WHERE id IN (' . $ids . ')',
                $params
            );
        }

        return $linked;
    }

    /**
     * @param array<string, mixed> $scrutinIdByUid
     */
    private function importAmendements(string $file, array $scrutinIdByUid): int
    {
        $handle = fopen($file, 'r');
        $count = 0;
        $batch = [];
        $links = [];

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }
            [$voteUid, $amendementId, $legislature, $href, $expose, $resume, $titreIa, $reviewed]
                = array_pad(explode("\t", $line), 8, '');
            if ($amendementId === '') {
                continue;
            }

            $batch[] = [
                $amendementId,
                is_numeric($legislature) ? (int) $legislature : null,
                $href !== '' ? $href : null,
                $expose !== '' ? $expose : null,
                $resume !== '' ? $resume : null,
                $titreIa !== '' ? $titreIa : null,
                $reviewed === '1' ? 1 : 0,
            ];
            ++$count;

            if (isset($scrutinIdByUid[$voteUid])) {
                $links[$scrutinIdByUid[$voteUid]] = $amendementId;
            }

            if (\count($batch) >= self::BATCH_SIZE) {
                $this->flush('amendement', ['amendement_id', 'legislature', 'href', 'expose', 'resume_ia', 'titre_ia', 'resume_relu'], $batch, ['legislature', 'href', 'expose', 'resume_ia', 'titre_ia', 'resume_relu']);
                $batch = [];
            }
        }
        if ($batch !== []) {
            $this->flush('amendement', ['amendement_id', 'legislature', 'href', 'expose', 'resume_ia', 'titre_ia', 'resume_relu'], $batch, ['legislature', 'href', 'expose', 'resume_ia', 'titre_ia', 'resume_relu']);
        }
        fclose($handle);

        // Rattachement des scrutins à leur amendement.
        $amendementIdByRef = $this->connection->fetchAllKeyValue('SELECT amendement_id, id FROM amendement');
        foreach (array_chunk($links, 500, true) as $chunk) {
            $cases = [];
            $params = [];
            $ids = [];
            foreach ($chunk as $scrutinId => $ref) {
                if (!isset($amendementIdByRef[$ref])) {
                    continue;
                }
                $cases[] = 'WHEN ? THEN ?';
                $params[] = $scrutinId;
                $params[] = $amendementIdByRef[$ref];
                $ids[] = (int) $scrutinId;
            }
            if ($ids === []) {
                continue;
            }
            $this->connection->executeStatement(
                'UPDATE scrutin SET amendement_id = CASE id ' . implode(' ', $cases) . ' END WHERE id IN (' . implode(',', $ids) . ')',
                $params
            );
        }

        return $count;
    }

    /**
     * @param array<string, mixed> $scrutinIdByUid
     * @param array<string, mixed> $deputeIdByMpId
     */
    private function importExplications(string $file, array $scrutinIdByUid, array $deputeIdByMpId): int
    {
        $handle = fopen($file, 'r');
        $count = 0;
        $batch = [];

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }
            [$voteUid, $mpId, $texte, $state, $createdAt, $modifiedAt] = array_pad(explode("\t", $line), 6, '');

            $scrutinId = $scrutinIdByUid[$voteUid] ?? null;
            $deputeId = $deputeIdByMpId[$mpId] ?? null;
            if ($scrutinId === null || $deputeId === null || $texte === '') {
                continue;
            }

            $batch[] = [
                $scrutinId,
                $deputeId,
                $texte,
                $state === '1' ? 1 : 0,
                $this->toDateTime($createdAt),
                $this->toDateTime($modifiedAt),
            ];
            ++$count;
        }
        if ($batch !== []) {
            $this->flush('explication', ['scrutin_id', 'depute_id', 'texte', 'publiee', 'created_at', 'modified_at'], $batch, ['texte', 'publiee', 'modified_at']);
        }
        fclose($handle);

        return $count;
    }

    /**
     * @param list<string>      $columns
     * @param list<list<mixed>> $batch
     * @param list<string>      $updatable
     */
    private function flush(string $table, array $columns, array $batch, array $updatable): void
    {
        $placeholders = implode(', ', array_fill(0, \count($batch), '(' . implode(', ', array_fill(0, \count($columns), '?')) . ')'));
        $updates = implode(', ', array_map(static fn (string $c) => sprintf('%1$s = VALUES(%1$s)', $c), $updatable));

        $this->connection->executeStatement(
            sprintf('INSERT INTO %s (%s) VALUES %s ON DUPLICATE KEY UPDATE %s', $table, implode(', ', $columns), $placeholders, $updates),
            array_merge(...$batch)
        );
    }

    private function toDateTime(?string $value): ?string
    {
        if ($value === null || $value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }
}
