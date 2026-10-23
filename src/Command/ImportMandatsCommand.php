<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Complète les députés avec leur circonscription, leur profession et leur
 * commission, et importe l'historique de leurs mandats parlementaires, depuis
 * des exports TSV de la base de référence « canutes ».
 *
 * Le segment d'URL de département (« nord-59 ») est calculé ici une fois pour
 * toutes, puisqu'il sert au routage des fiches de députés.
 */
#[AsCommand(
    name: 'app:import:mandats',
    description: 'Importe circonscriptions, professions, commissions et historique des mandats.',
)]
class ImportMandatsCommand extends Command
{
    private const BATCH_SIZE = 500;

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('details', null, InputOption::VALUE_REQUIRED, 'TSV des détails de députés')
            ->addOption('mandats', null, InputOption::VALUE_REQUIRED, 'TSV de l\'historique des mandats');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des circonscriptions et mandats');

        $deputeIdByMpId = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');

        $this->connection->beginTransaction();

        try {
            if ($file = $input->getOption('details')) {
                $io->text(sprintf('Députés complétés : %d.', $this->importDetails($file, $deputeIdByMpId)));
            }
            if ($file = $input->getOption('mandats')) {
                $io->text(sprintf('Mandats : %d.', $this->importMandats($file, $deputeIdByMpId)));
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            $io->error(sprintf('Import interrompu : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $avecDpt = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM depute WHERE dpt_slug IS NOT NULL');
        $io->success(sprintf(
            '%d députés avec une circonscription, %d mandats en base.',
            $avecDpt,
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM mandat'),
        ));

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $deputeIdByMpId
     */
    private function importDetails(string $file, array $deputeIdByMpId): int
    {
        $slugger = new AsciiSlugger('fr');

        // Le segment de département de l'URL est `departement.slug`, la table
        // tenue à la main du legacy — c'est LUI l'adresse servie par datan.fr,
        // indexée depuis des années. Cinq départements diffèrent du slug qu'on
        // fabriquerait depuis le nom (CLAUDE.md, « deux jeux de slugs ») :
        // `francais-de-letranger` et non `francais-etablis-hors-de-france-099`,
        // `cote-dor-21` et non `cote-d-or-21` (l'AsciiSlugger coupe l'apostrophe
        // en tiret là où le legacy l'élide), idem Côtes-d'Armor, Val-d'Oise, et
        // `saint-barthelemy-et-saint-martin` sans le code. On indexe en
        // minuscules : `departement.code` écrit la Corse « 2B », le TSV « 2B »
        // aussi, mais la casse ne doit rien changer à l'appariement.
        $slugDeptParCode = [];
        foreach ($this->connection->fetchAllKeyValue('SELECT LOWER(code), slug FROM departement') as $code => $slug) {
            $slugDeptParCode[$code] = $slug;
        }

        $handle = fopen($file, 'r');
        $count = 0;
        $batch = [];

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }

            [$mpId, $departement, $numDept, $circo, $region, $place, $profession, , $commission, $civilite]
                = array_pad(array_map($this->unquote(...), explode("\t", $line)), 10, '');

            if (!isset($deputeIdByMpId[$mpId]) || $departement === '') {
                continue;
            }

            // `departement.slug` fait foi dès qu'il existe (la quasi-totalité des
            // cas). Repli seulement pour un code absent de la table du legacy —
            // un département qu'il n'aurait pas encore slugué : « Nord » + « 59 »
            // → « nord-59 ». Le strtolower() y enveloppe AUSSI le code (« 2B » →
            // « 2b »), sans quoi les routes de député ([a-z0-9\-]+) refuseraient
            // la Corse — mais ce chemin ne sert plus qu'aux départements que le
            // legacy ignore, la table tenue à la main tranchant tous les autres.
            $dptSlug = $slugDeptParCode[strtolower($numDept)]
                ?? strtolower($slugger->slug($departement)->toString() . '-' . $numDept);

            $batch[] = [
                $dptSlug,
                $departement,
                $numDept !== '' ? $numDept : null,
                is_numeric($circo) ? (int) $circo : null,
                $region !== '' ? $region : null,
                is_numeric($place) ? (int) $place : null,
                $profession !== '' ? $profession : null,
                $commission !== '' ? $commission : null,
                $civilite !== '' ? $civilite : null,
                $deputeIdByMpId[$mpId],
            ];
            ++$count;

            if (\count($batch) >= self::BATCH_SIZE) {
                $this->flushDetails($batch);
                $batch = [];
            }
        }
        if ($batch !== []) {
            $this->flushDetails($batch);
        }
        fclose($handle);

        return $count;
    }

    /**
     * @param list<list<mixed>> $batch
     */
    private function flushDetails(array $batch): void
    {
        $sql = 'UPDATE depute SET dpt_slug = ?, departement_nom = ?, departement_code = ?,
                    circonscription = ?, region = ?, place_hemicycle = ?, profession = ?,
                    commission = ?, civilite = ?
                WHERE id = ?';

        foreach ($batch as $params) {
            $this->connection->executeStatement($sql, $params);
        }
    }

    /**
     * @param array<string, mixed> $deputeIdByMpId
     */
    private function importMandats(string $file, array $deputeIdByMpId): int
    {
        $handle = fopen($file, 'r');
        $count = 0;
        $batch = [];

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }

            [$mpId, $legislature, $debut, $fin, $departement, $numDept, $circo, $cause]
                = array_pad(array_map($this->unquote(...), explode("\t", $line)), 8, '');

            if (!isset($deputeIdByMpId[$mpId]) || !is_numeric($legislature) || $debut === '') {
                continue;
            }

            $batch[] = [
                $deputeIdByMpId[$mpId],
                (int) $legislature,
                $debut,
                $fin !== '' ? $fin : null,
                $departement !== '' ? $departement : null,
                $numDept !== '' ? $numDept : null,
                is_numeric($circo) ? (int) $circo : null,
                $cause !== '' ? $cause : null,
            ];
            ++$count;

            if (\count($batch) >= self::BATCH_SIZE) {
                $this->flushMandats($batch);
                $batch = [];
            }
        }
        if ($batch !== []) {
            $this->flushMandats($batch);
        }
        fclose($handle);

        return $count;
    }

    /**
     * @param list<list<mixed>> $batch
     */
    private function flushMandats(array $batch): void
    {
        $placeholders = implode(', ', array_fill(0, \count($batch), '(?, ?, ?, ?, ?, ?, ?, ?)'));

        $this->connection->executeStatement(
            'INSERT INTO mandat (depute_id, legislature, date_debut, date_fin, departement_nom, departement_code, circonscription, cause_mandat)
             VALUES ' . $placeholders . '
             ON DUPLICATE KEY UPDATE
                date_fin = VALUES(date_fin), departement_nom = VALUES(departement_nom),
                departement_code = VALUES(departement_code), circonscription = VALUES(circonscription),
                cause_mandat = VALUES(cause_mandat)',
            array_merge(...$batch)
        );
    }

    /** Les exports CSV de psql entourent de guillemets les valeurs vides ou contenant un séparateur. */
    private function unquote(string $value): string
    {
        $value = trim($value);
        if (\strlen($value) >= 2 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return str_replace('""', '"', substr($value, 1, -1));
        }

        return $value;
    }
}
