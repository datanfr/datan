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
 * Importe les rattachements des députés à leurs groupes parlementaires
 * (mandats de type « GP » de la base de référence canutes), avec la qualité
 * exercée : président, membre, membre apparenté, député non-inscrit.
 *
 * Format attendu (TSV, sans en-tête) :
 *   acteurUid <TAB> organeUid <TAB> codeQualite <TAB> libelleQualite
 *   <TAB> dateDebut <TAB> dateFin
 */
#[AsCommand(
    name: 'app:import:fonctions-groupe',
    description: 'Importe les fonctions des députés dans leurs groupes (dont les présidences).',
)]
class ImportFonctionsGroupeCommand extends Command
{
    private const BATCH_SIZE = 1000;

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('file', null, InputOption::VALUE_REQUIRED, 'TSV des mandats de groupe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getOption('file');

        if ($file === '' || !is_readable($file)) {
            $io->error(sprintf('Fichier introuvable ou illisible : "%s".', $file));

            return Command::FAILURE;
        }

        $io->title('Import des fonctions dans les groupes');

        $deputeIdByMpId = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');
        $groupeIdByUid = $this->connection->fetchAllKeyValue('SELECT uid, id FROM groupe');

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $io->error('Impossible d\'ouvrir le fichier.');

            return Command::FAILURE;
        }

        $read = 0;
        $skippedDepute = 0;
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

                [$acteurUid, $organeUid, $codeQualite, $libelleQualite, $debut, $fin]
                    = array_pad(array_map($this->unquote(...), explode("\t", $line)), 6, '');

                $deputeId = $deputeIdByMpId[$acteurUid] ?? null;
                if ($deputeId === null) {
                    ++$skippedDepute;
                    continue;
                }

                $groupeId = $groupeIdByUid[$organeUid] ?? null;
                if ($groupeId === null) {
                    ++$skippedGroupe;
                    continue;
                }

                $batch[] = [
                    $deputeId,
                    $groupeId,
                    $codeQualite !== '' ? $codeQualite : 'Membre',
                    $libelleQualite !== '' ? $libelleQualite : null,
                    $debut !== '' ? $debut : null,
                    $fin !== '' ? $fin : null,
                ];

                if (\count($batch) >= self::BATCH_SIZE) {
                    $this->flushBatch($batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $this->flushBatch($batch);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            fclose($handle);
            $io->error(sprintf('Import interrompu : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        fclose($handle);

        if ($skippedDepute > 0 || $skippedGroupe > 0) {
            $io->warning(sprintf(
                'Ignorées : %d (député inconnu), %d (groupe inconnu — législatures non importées).',
                $skippedDepute,
                $skippedGroupe,
            ));
        }

        $presidences = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM fonction_groupe WHERE code_qualite = :q',
            ['q' => 'Président'],
        );

        $io->success(sprintf(
            '%d lignes lues — %d fonctions en base, dont %d présidences.',
            $read,
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM fonction_groupe'),
            $presidences,
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<list<mixed>> $batch
     */
    private function flushBatch(array $batch): void
    {
        $placeholders = implode(', ', array_fill(0, \count($batch), '(?, ?, ?, ?, ?, ?)'));

        $this->connection->executeStatement(
            'INSERT INTO fonction_groupe (depute_id, groupe_id, code_qualite, libelle_qualite, date_debut, date_fin)
             VALUES ' . $placeholders . '
             ON DUPLICATE KEY UPDATE
                libelle_qualite = VALUES(libelle_qualite), date_fin = VALUES(date_fin)',
            array_merge(...$batch)
        );
    }

    private function unquote(string $value): string
    {
        $value = trim($value);
        if (\strlen($value) >= 2 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return str_replace('""', '"', substr($value, 1, -1));
        }

        return $value;
    }
}
