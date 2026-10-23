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
 * Importe les groupes parlementaires (organes « GP ») puis les députés (acteurs)
 * depuis des exports CSV de la base de référence « canutes ».
 *
 * Les députés sont mis à jour sans écraser les données déjà migrées depuis
 * l'application d'origine (catégorie socio-professionnelle, date et cause de fin
 * de mandat) : seuls l'état civil, le slug et le rattachement au groupe sont repris.
 */
#[AsCommand(
    name: 'app:import:deputes',
    description: 'Importe les groupes parlementaires et les députés depuis canutes.',
)]
class ImportDeputesCommand extends Command
{
    private const BATCH_SIZE = 500;

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('groupes-file', null, InputOption::VALUE_REQUIRED, 'CSV des organes GP exportés de canutes')
            ->addOption('acteurs-file', null, InputOption::VALUE_REQUIRED, 'CSV des acteurs exportés de canutes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $groupesFile = (string) $input->getOption('groupes-file');
        $acteursFile = (string) $input->getOption('acteurs-file');

        foreach (['groupes' => $groupesFile, 'acteurs' => $acteursFile] as $label => $path) {
            if ($path === '' || !is_readable($path)) {
                $io->error(sprintf('CSV %s introuvable ou illisible : "%s".', $label, $path));

                return Command::FAILURE;
            }
        }

        $io->title('Import des groupes parlementaires et des députés');
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $this->connection->beginTransaction();

        try {
            $groupes = $this->importGroupes($groupesFile, $now);
            $io->text(sprintf('Groupes parlementaires : %d traités.', $groupes));

            // uid de groupe → id interne, chargé en une fois pour éviter une requête par député.
            $groupeIdByUid = $this->connection->fetchAllKeyValue('SELECT uid, id FROM groupe');

            $deputes = $this->importDeputes($acteursFile, $groupeIdByUid, $now);
            $io->text(sprintf('Députés : %d traités.', $deputes));

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            $io->error(sprintf('Import interrompu : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $totalDeputes = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM depute');
        $avecGroupe = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM depute WHERE groupe_id IS NOT NULL');
        $io->success(sprintf(
            '%d députés en base, dont %d rattachés à un groupe ; %d groupes.',
            $totalDeputes,
            $avecGroupe,
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM groupe'),
        ));

        return Command::SUCCESS;
    }

    private function importGroupes(string $file, string $now): int
    {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);
        $header = array_map(static fn ($c) => trim((string) $c), $header);

        $count = 0;
        $batch = [];
        while (($line = fgetcsv($handle)) !== false) {
            $row = @array_combine($header, array_map(static fn ($v) => $v === '' ? null : $v, $line));
            if ($row === false || ($row['uid'] ?? null) === null) {
                continue;
            }

            $batch[] = [
                $row['uid'],
                $row['libelle'] ?? $row['uid'],
                $row['libelle_abrev'] ?? null,
                $row['libelle_abrege'] ?? null,
                $row['couleur'] ?? null,
                $row['position_politique'] ?? null,
                is_numeric($row['legislature'] ?? null) ? (int) $row['legislature'] : null,
                $this->toDate($row['date_debut'] ?? null),
                $this->toDate($row['date_fin'] ?? null),
                $now,
                $now,
            ];
            ++$count;

            if (\count($batch) >= self::BATCH_SIZE) {
                $this->flushGroupes($batch);
                $batch = [];
            }
        }
        if ($batch !== []) {
            $this->flushGroupes($batch);
        }
        fclose($handle);

        return $count;
    }

    /**
     * @param list<list<mixed>> $batch
     */
    private function flushGroupes(array $batch): void
    {
        $placeholders = implode(', ', array_fill(0, \count($batch), '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'));
        $params = array_merge(...$batch);

        $this->connection->executeStatement(
            'INSERT INTO groupe (uid, libelle, libelle_abrev, libelle_abrege, couleur, position_politique, legislature, date_debut, date_fin, created_at, updated_at)
             VALUES ' . $placeholders . '
             ON DUPLICATE KEY UPDATE
                libelle = VALUES(libelle), libelle_abrev = VALUES(libelle_abrev), libelle_abrege = VALUES(libelle_abrege),
                couleur = VALUES(couleur), position_politique = VALUES(position_politique), legislature = VALUES(legislature),
                date_debut = VALUES(date_debut), date_fin = VALUES(date_fin), updated_at = VALUES(updated_at)',
            $params
        );
    }

    /**
     * @param array<string, mixed> $groupeIdByUid
     */
    private function importDeputes(string $file, array $groupeIdByUid, string $now): int
    {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);
        $header = array_map(static fn ($c) => trim((string) $c), $header);

        $count = 0;
        $batch = [];
        while (($line = fgetcsv($handle)) !== false) {
            $row = @array_combine($header, array_map(static fn ($v) => $v === '' ? null : $v, $line));
            if ($row === false || ($row['uid'] ?? null) === null) {
                continue;
            }

            $groupeUid = $row['groupe_uid'] ?? null;

            $batch[] = [
                $row['uid'],
                $row['prenom'] ?? '',
                $row['nom'] ?? '',
                $row['slug'] ?? null,
                $this->ageFrom($row['date_naissance'] ?? null),
                $groupeUid !== null ? ($groupeIdByUid[$groupeUid] ?? null) : null,
                $now,
                $now,
            ];
            ++$count;

            if (\count($batch) >= self::BATCH_SIZE) {
                $this->flushDeputes($batch);
                $batch = [];
            }
        }
        if ($batch !== []) {
            $this->flushDeputes($batch);
        }
        fclose($handle);

        return $count;
    }

    /**
     * @param list<list<mixed>> $batch
     */
    private function flushDeputes(array $batch): void
    {
        $placeholders = implode(', ', array_fill(0, \count($batch), '(?, ?, ?, ?, ?, ?, ?, ?)'));
        $params = array_merge(...$batch);

        // COALESCE : on ne remplace une valeur existante que si la source en fournit une,
        // pour ne pas perdre ce qui a été migré depuis l'application d'origine.
        $this->connection->executeStatement(
            'INSERT INTO depute (mp_id, firstname, lastname, slug, age, groupe_id, created_at, updated_at)
             VALUES ' . $placeholders . '
             ON DUPLICATE KEY UPDATE
                firstname = VALUES(firstname), lastname = VALUES(lastname),
                slug = COALESCE(VALUES(slug), slug), age = COALESCE(VALUES(age), age),
                groupe_id = COALESCE(VALUES(groupe_id), groupe_id), updated_at = VALUES(updated_at)',
            $params
        );
    }

    private function ageFrom(?string $birthDate): ?int
    {
        if ($birthDate === null || $birthDate === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($birthDate))->diff(new \DateTimeImmutable())->y;
        } catch (\Exception) {
            return null;
        }
    }

    private function toDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
