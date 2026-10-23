<?php

namespace App\Command;

use App\Entity\Categorie;
use App\Entity\Decryptage;
use App\Entity\Lecture;
use App\Enum\DecryptageState;
use App\Repository\CategorieRepository;
use App\Repository\DecryptageRepository;
use App\Repository\LectureRepository;
use App\Repository\ScrutinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les décryptages éditoriaux (votes_datan) et leurs référentiels
 * (fields → categorie, readings → lecture) depuis la base de l'application
 * CodeIgniter d'origine vers la base Symfony.
 *
 * L'opération est idempotente : elle peut être rejouée sans créer de doublon
 * (rapprochement des décryptages sur legislature + voteNumero). Elle NE
 * supprime jamais de décryptage existant — objectif : préserver le travail déjà fait.
 */
#[AsCommand(
    name: 'app:import:decryptages',
    description: 'Importe les décryptages de votes (votes_datan) depuis la base datan d\'origine.',
)]
class ImportDecryptagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DecryptageRepository $decryptageRepo,
        private readonly CategorieRepository $categorieRepo,
        private readonly LectureRepository $lectureRepo,
        private readonly ScrutinRepository $scrutinRepo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Hôte de la base source', '127.0.0.1')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Port de la base source', '3306')
            ->addOption('dbname', null, InputOption::VALUE_REQUIRED, 'Nom de la base source', 'datan')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Utilisateur source', 'datan')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe source', 'datan')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simule sans écrire en base');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $input->getOption('host'),
            $input->getOption('port'),
            $input->getOption('dbname'),
        );

        try {
            $source = new \PDO($dsn, (string) $input->getOption('user'), (string) $input->getOption('password'), [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            $io->error(sprintf('Connexion à la base source impossible : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $io->title(sprintf('Import des décryptages depuis %s%s', $dsn, $dryRun ? ' (DRY-RUN)' : ''));

        // 1) Catégories (fields → categorie), indexées par id source.
        $categorieBySourceId = $this->importCategories($source, $io, $dryRun);

        // 2) Lectures (readings → lecture), indexées par id source.
        $lectureBySourceId = $this->importLectures($source, $io, $dryRun);

        if (!$dryRun) {
            $this->em->flush();
        }

        // 3) Décryptages (votes_datan → decryptage).
        $this->importDecryptages($source, $io, $dryRun, $categorieBySourceId, $lectureBySourceId);

        if (!$dryRun) {
            $this->em->flush();
        }

        $io->success('Import terminé.');

        return Command::SUCCESS;
    }

    /**
     * @return array<int, Categorie>
     */
    private function importCategories(\PDO $source, SymfonyStyle $io, bool $dryRun): array
    {
        $map = [];
        $created = 0;
        $rows = $source->query('SELECT id, name, slug, libelle FROM fields')->fetchAll();

        foreach ($rows as $row) {
            $categorie = $this->categorieRepo->findOneBy(['slug' => $row['slug']]);
            if (!$categorie) {
                $categorie = (new Categorie())->setSlug($row['slug']);
                if (!$dryRun) {
                    $this->em->persist($categorie);
                }
                ++$created;
            }
            $categorie->setName($row['name'])->setLibelle($row['libelle'] ?? null);
            $map[(int) $row['id']] = $categorie;
        }

        $io->text(sprintf('Catégories : %d source, %d créées.', \count($rows), $created));

        return $map;
    }

    /**
     * @return array<int, Lecture>
     */
    private function importLectures(\PDO $source, SymfonyStyle $io, bool $dryRun): array
    {
        $map = [];
        $created = 0;
        $rows = $source->query('SELECT id, name FROM readings')->fetchAll();

        foreach ($rows as $row) {
            $lecture = $this->lectureRepo->findOneBy(['name' => $row['name']]);
            if (!$lecture) {
                $lecture = (new Lecture())->setName($row['name']);
                if (!$dryRun) {
                    $this->em->persist($lecture);
                }
                ++$created;
            }
            $map[(int) $row['id']] = $lecture;
        }

        $io->text(sprintf('Lectures : %d source, %d créées.', \count($rows), $created));

        return $map;
    }

    /**
     * @param array<int, Categorie> $categorieBySourceId
     * @param array<int, Lecture>   $lectureBySourceId
     */
    private function importDecryptages(
        \PDO $source,
        SymfonyStyle $io,
        bool $dryRun,
        array $categorieBySourceId,
        array $lectureBySourceId,
    ): void {
        $rows = $source->query(
            'SELECT id, legislature, voteNumero, vote_id, title, slug, category, reading, description, state, created_at, modified_at, created_by, modified_by FROM votes_datan'
        )->fetchAll();

        $created = 0;
        $updated = 0;
        $linkedToScrutin = 0;

        foreach ($rows as $row) {
            $legislature = (int) $row['legislature'];
            $voteNumero = (int) $row['voteNumero'];

            $decryptage = $this->decryptageRepo->findOneByVote($legislature, $voteNumero);
            if (!$decryptage) {
                $decryptage = (new Decryptage())
                    ->setLegislature($legislature)
                    ->setVoteNumero($voteNumero);
                if (!$dryRun) {
                    $this->em->persist($decryptage);
                }
                ++$created;
            } else {
                ++$updated;
            }

            $decryptage
                ->setVoteId($row['vote_id'] ?? null)
                ->setTitle((string) $row['title'])
                ->setSlug((string) $row['slug'])
                ->setDescription((string) ($row['description'] ?? ''))
                ->setState(DecryptageState::tryFrom((string) $row['state']) ?? DecryptageState::Draft)
                ->setCreatedBy($row['created_by'] ?? null)
                ->setModifiedBy($row['modified_by'] ?? null)
                ->setCreatedAt($this->toDate($row['created_at']))
                ->setModifiedAt($this->toDate($row['modified_at']));

            $decryptage->setCategorie($categorieBySourceId[(int) $row['category']] ?? null);
            $decryptage->setLecture($row['reading'] !== null && $row['reading'] !== ''
                ? ($lectureBySourceId[(int) $row['reading']] ?? null)
                : null);

            // Rapprochement avec le scrutin brut si déjà chargé.
            $scrutin = $this->scrutinRepo->findOneBy(['legislature' => $legislature, 'numero' => $voteNumero]);
            if ($scrutin) {
                $decryptage->setScrutin($scrutin);
                ++$linkedToScrutin;
            }
        }

        $io->text(sprintf(
            'Décryptages : %d source → %d créés, %d mis à jour, %d rattachés à un scrutin.',
            \count($rows),
            $created,
            $updated,
            $linkedToScrutin,
        ));
    }

    private function toDate(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
