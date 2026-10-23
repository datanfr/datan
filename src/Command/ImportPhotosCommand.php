<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Publie les photographies officielles des députés depuis le dépôt des
 * Tricoteuses vers `public/`, où le serveur web les sert en statique.
 *
 * Contrairement aux autres imports, celui-ci n'écrit pas en base : aucune
 * colonne ne porte la photo d'un député, et il n'en faut pas. Le nom du fichier
 * se déduit du `mp_id` — `PA720892` donne `720892.jpg` — donc l'existence du
 * fichier est la seule information à retenir, et {@see \App\Twig\PhotoExtension}
 * la lit directement sur le disque publié. Une colonne de plus serait une
 * seconde source de vérité à tenir synchronisée avec le système de fichiers.
 *
 * Le dépôt n'est pas servi tel quel : il pèse 186 Mo, dont l'essentiel en objets
 * Git, et vit sous `var/`, hors racine web. Seule la variante d'affichage est
 * recopiée, soit 5 Mo.
 */
#[AsCommand(
    name: 'app:import:photos',
    description: 'Publie les photographies des députés depuis le dépôt des Tricoteuses.',
)]
class ImportPhotosCommand extends ImportTricoteusesCommand
{
    /** Où les photos sont publiées, sous `public/`. */
    public const DOSSIER_PUBLIC = 'assets/imgs/deputes';

    /**
     * Variante retenue : c'est exactement la taille à laquelle les fiches et les
     * cartes affichent la photo (150 × 192), et elle pèse 6 ko contre 29 ko pour
     * l'originale — qui, elle, n'est pas plus grande, seulement moins compressée.
     *
     * La variante `_155x225` existe aussi ; elle n'a pas d'usage dans les gabarits.
     */
    private const SUFFIXE_AFFICHAGE = '_150x192';

    public function __construct(
        Connection $connection,
        #[Autowire('%tricoteuses.racine%')] string $racineTricoteuses,
        #[Autowire('%kernel.project_dir%')] private readonly string $racineProjet,
    ) {
        parent::__construct($connection, $racineTricoteuses);
    }

    protected function depotParDefaut(): string
    {
        return 'photos';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tout = (bool) $input->getOption('tout');
        $depot = $this->chemin($input);
        $destination = $this->racineProjet . '/public/' . self::DOSSIER_PUBLIC;

        $io->title('Publication des photographies des députés');
        $io->text($this->incremental($depot, $tout) ? 'Régime incrémental : seuls les fichiers modifiés.' : 'Publication complète du dépôt.');

        if (!is_dir($destination) && !@mkdir($destination, 0o775, true) && !is_dir($destination)) {
            $io->error(sprintf('Impossible de créer « %s ».', $destination));

            return Command::FAILURE;
        }

        // Le dépôt contient une photo de groupe (`deputes.jpg`, 3875 × 5850) et
        // des acteurs qui ne sont pas dans notre base : on ne publie que ce à
        // quoi une fiche correspond.
        $connus = array_flip($this->connection->fetchFirstColumn(
            "SELECT SUBSTRING(mp_id, 3) FROM depute WHERE mp_id LIKE 'PA%'",
        ));

        $publiees = 0;
        $inchangees = 0;
        $inconnues = 0;
        $sansVariante = 0;

        foreach ($this->identifiants($depot, $tout) as $identifiant) {
            if (!isset($connus[$identifiant])) {
                ++$inconnues;
                continue;
            }

            $source = $depot . '/' . $identifiant . self::SUFFIXE_AFFICHAGE . '.jpg';

            // Une vingtaine de photos n'ont pas de variante d'affichage : leur
            // originale mesure 149 × 192, un pixel de moins que le format
            // attendu par l'outillage amont, qui a donc renoncé à la produire.
            // À cette taille l'originale fait tout aussi bien l'affaire.
            if (!is_file($source)) {
                $source = $depot . '/' . $identifiant . '.jpg';
                ++$sansVariante;
            }

            if (!is_file($source)) {
                continue;
            }

            $cible = $destination . '/' . $identifiant . '.jpg';

            if ($this->aJour($source, $cible)) {
                ++$inchangees;
                continue;
            }

            if (!@copy($source, $cible)) {
                $io->warning(sprintf('Copie impossible : %s', $source));
                continue;
            }

            ++$publiees;
        }

        if ($inconnues > 0) {
            $io->text(sprintf('%d photos ignorées : acteur inconnu de la table depute.', $inconnues));
        }
        if ($sansVariante > 0) {
            $io->text(sprintf('%d photos publiées depuis l\'originale, faute de variante %s.', $sansVariante, self::SUFFIXE_AFFICHAGE));
        }

        $io->success(sprintf(
            '%d photos publiées, %d déjà à jour — %d députés sur %d en ont une.',
            $publiees,
            $inchangees,
            \count(glob($destination . '/*.jpg') ?: []),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM depute'),
        ));

        return Command::SUCCESS;
    }

    /**
     * Les identifiants d'acteur — partie numérique du `mp_id` — dont le dépôt
     * porte une photo.
     *
     * @return iterable<string>
     */
    private function identifiants(string $depot, bool $tout): iterable
    {
        $vus = [];

        foreach ($this->fichiers($depot, '', $tout, 'jpg') as $fichier) {
            // Les trois tailles d'une même photo ramènent au même identifiant :
            // `720892.jpg`, `720892_150x192.jpg` et `720892_155x225.jpg`. Le
            // delta peut n'en signaler qu'une, la publication les traite ensemble.
            $identifiant = strtok(basename($fichier, '.jpg'), '_');

            if (!ctype_digit((string) $identifiant) || isset($vus[$identifiant])) {
                continue;
            }

            $vus[$identifiant] = true;

            yield $identifiant;
        }
    }

    /**
     * Une photo déjà publiée est réputée à jour tant qu'elle a la taille de sa
     * source : le dépôt republie les mêmes fichiers à chaque moisson, et leur
     * date de modification est celle de la moisson, pas celle du cliché.
     */
    private function aJour(string $source, string $cible): bool
    {
        return is_file($cible) && filesize($cible) === filesize($source);
    }
}
