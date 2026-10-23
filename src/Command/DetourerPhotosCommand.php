<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Détoure les portraits publiés par {@see ImportPhotosCommand} : le fond de
 * studio de l'Assemblée s'en va, le député reste, sur un PNG transparent.
 *
 * **Pourquoi une commande et pas une copie.** Datan sert depuis toujours des
 * portraits détourés (`assets/imgs/deputes_nobg/`), découpés à la main et
 * conservés sur le serveur du site : une centaine de mégaoctets qui ne se
 * régénèrent pas et qu'aucune source ouverte ne republie. Tant qu'ils ne sont
 * pas recopiés, nos cartes affichent le portrait brut de l'Assemblée, fond
 * bleu compris. Cette commande le refait toute seule, à partir des photos que
 * le sync quotidien publie déjà — un député qui arrive en cours de législature
 * est donc détouré le lendemain, sans que personne n'ouvre un éditeur d'image.
 *
 * **Elle n'écrase jamais le travail manuel.** Sa sortie va dans un dossier à
 * elle, `deputes_detoures/`, et {@see \App\Twig\PhotoExtension} ne s'y sert
 * qu'après avoir cherché les deux jeux faits à la main. Le jour où ceux-ci
 * sont recopiés, ils reprennent la main sans qu'on touche à quoi que ce soit.
 *
 * **Comment.** Le fond de l'Assemblée est un cyan pâle uniforme en teinte mais
 * inégalement éclairé — plus sombre dans les coins bas. Trois idées suffisent :
 *
 * 1. juger sur la **chrominance**, pas sur la luminosité ({@see distance()}) ;
 * 2. propager **depuis la bordure** plutôt que seuiller toute l'image, pour
 *    qu'un vêtement de la couleur du fond soit protégé par sa position ;
 * 3. rendre un alpha **progressif** entre les deux seuils, et retirer ensuite
 *    la part de fond des pixels à demi transparents, sans quoi les cheveux
 *    gardent un liseré bleu qui saute aux yeux sur la carte blanche.
 *
 * Mesuré sur les 647 portraits publiés : 15 ms par photo, part transparente
 * médiane de 32 %, et sept cas hors bornes dont deux images entièrement
 * blanches — l'Assemblée publie un rectangle vide pour deux députés sans
 * portrait, que la garde de vraisemblance écarte au lieu d'en faire un PNG
 * totalement transparent.
 *
 * Ce qui résiste, et c'est admis : une chevelure bouclée enferme des poches de
 * fond que la propagation n'atteint pas — elles restent visibles en très léger
 * halo. Un détourage à la main ne fait pas mieux à cette taille.
 */
#[AsCommand(
    name: 'app:photos:detourer',
    description: 'Retire le fond de studio des portraits des députés et publie des PNG transparents.',
)]
class DetourerPhotosCommand extends Command
{
    /** Où la commande écrit, sous `public/`. */
    public const DOSSIER_PUBLIC = 'assets/imgs/deputes_detoures';

    /**
     * WebP, et pas PNG : c'est le seul format qui tienne la transparence sans
     * peser.
     *
     * Mesuré sur le même portrait détouré — PNG truecolor 47,6 ko, PNG à
     * palette 18,5 ko, WebP à cette qualité 5,8 ko, quand le JPG d'origine en
     * fait 7,2. La liste des députés charge 577 vignettes : le PNG truecolor y
     * ajoutait 27 Mo, ce qui contredit la première exigence du site. Le WebP,
     * lui, l'allège.
     *
     * Sans le WebP dans GD, la commande s'arrête plutôt que de se rabattre sur
     * le PNG : les portraits bruts restent servis, ce qui est laid mais rapide.
     */
    private const QUALITE_WEBP = 82;

    /**
     * En deçà de cette distance, le pixel est du fond franc : alpha nul.
     *
     * Le seuil vient de la mesure : sur les 647 portraits, 99 % des pixels de
     * bordure — donc du fond certain — se tiennent sous 26, et le pire sous 37.
     */
    private const SEUIL_FOND = 28.0;

    /**
     * Au-delà, le pixel est du sujet franc : opaque. Entre les deux, l'alpha
     * est proportionnel, ce qui donne son fondu au contour.
     *
     * 48 laisse une marge nette avec ce qui s'en approche le plus dans une
     * photo : une chevelure blanche (57 à 65 selon le fond) et une chemise
     * blanche (57 à 76). Au-dessus de 55, les chemises claires commencent à
     * disparaître ; c'est le premier réglage à revoir si le studio change de
     * fond.
     */
    private const SEUIL_SUJET = 48.0;

    /**
     * Poids de l'écart de luminosité dans la distance, face à celui de teinte.
     *
     * Faible **à dessein** : le fond est un aplat de teinte constante mais
     * inégalement éclairé — les coins bas perdent jusqu'à soixante niveaux.
     * Peser la luminosité à égalité rendait ces coins « à moitié sujets », et
     * la carte sortait avec deux ombres translucides. La teinte, elle, ne bouge
     * pas : c'est sur elle qu'il faut juger.
     */
    private const POIDS_LUMINANCE = 0.10;

    /**
     * Bornes de vraisemblance de la part détourée.
     *
     * Un portrait de l'Assemblée donne entre 9 % et 44 % de fond. En sortir
     * signale autre chose qu'une photo de studio — image blanche, cadrage
     * inattendu, fond changé — et la commande préfère alors ne rien écrire :
     * le portrait brut reste servi, ce qui est laid mais juste, là où un PNG
     * raté afficherait un demi-visage.
     */
    private const PART_MINIMALE = 0.05;
    private const PART_MAXIMALE = 0.60;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $racineProjet,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Dossier des portraits bruts, sous public/', ImportPhotosCommand::DOSSIER_PUBLIC)
            ->addOption('destination', null, InputOption::VALUE_REQUIRED, 'Dossier des portraits détourés, sous public/', self::DOSSIER_PUBLIC)
            ->addOption('tout', null, InputOption::VALUE_NONE, 'Refait aussi les portraits déjà détourés')
            ->addOption('depute', null, InputOption::VALUE_REQUIRED, 'N\'en traiter qu\'un, par identifiant d\'acteur (720892 ou PA720892)')
            ->addOption('simulation', null, InputOption::VALUE_NONE, 'Rend le bilan sans rien écrire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $debut = microtime(true);

        if (!\function_exists('imagecreatefromjpeg')) {
            $io->error('L\'extension GD est absente : rien à faire ici.');

            return Command::FAILURE;
        }

        if (!\function_exists('imagewebp')) {
            $io->error('GD est là mais sans le WebP (cf. QUALITE_WEBP) : les portraits bruts restent servis.');

            return Command::FAILURE;
        }

        $source = $this->racineProjet . '/public/' . trim((string) $input->getOption('source'), '/');
        $destination = $this->racineProjet . '/public/' . trim((string) $input->getOption('destination'), '/');
        $tout = (bool) $input->getOption('tout');
        $simulation = (bool) $input->getOption('simulation');

        $io->title('Détourage des portraits des députés');

        if (!is_dir($source)) {
            $io->error(sprintf('Portraits introuvables : « %s ». Lancer d\'abord app:import:photos.', $source));

            return Command::FAILURE;
        }

        if (!$simulation && !is_dir($destination) && !@mkdir($destination, 0o775, true) && !is_dir($destination)) {
            $io->error(sprintf('Impossible de créer « %s ».', $destination));

            return Command::FAILURE;
        }

        $fichiers = glob($source . '/*.jpg') ?: [];

        if (($un = $input->getOption('depute')) !== null) {
            $identifiant = str_starts_with((string) $un, 'PA') ? substr((string) $un, 2) : (string) $un;
            $fichiers = array_values(array_filter($fichiers, static fn (string $f) => basename($f, '.jpg') === $identifiant));

            if ($fichiers === []) {
                $io->error(sprintf('Aucun portrait publié pour « %s ».', $un));

                return Command::FAILURE;
            }
        }

        $io->text(sprintf('%d portraits dans %s', \count($fichiers), $source));
        if ($simulation) {
            $io->note('Simulation : aucun fichier ne sera écrit.');
        }

        $ecrits = 0;
        $inchanges = 0;
        $ecartes = [];
        $illisibles = [];
        $parts = [];

        foreach ($fichiers as $fichier) {
            $identifiant = basename($fichier, '.jpg');
            $cible = $destination . '/' . $identifiant . '.webp';

            // Un portrait déjà détouré ne se refait que si sa source a bougé :
            // l'import republie le même fichier à chaque moisson, seule sa date
            // change, d'où la comparaison sur la date de la CIBLE contre celle
            // de la source — et `--tout` pour passer outre après un réglage.
            if (!$tout && is_file($cible) && filemtime($cible) >= filemtime($fichier)) {
                ++$inchanges;
                continue;
            }

            $detoure = $this->detoure($fichier);

            if ($detoure === null) {
                $illisibles[] = $identifiant;
                continue;
            }

            [$image, $part] = $detoure;
            $parts[] = $part;

            if ($part < self::PART_MINIMALE || $part > self::PART_MAXIMALE) {
                $ecartes[$identifiant] = $part;
                imagedestroy($image);
                continue;
            }

            if (!$simulation && !imagewebp($image, $cible, self::QUALITE_WEBP)) {
                $io->warning(sprintf('Écriture impossible : %s', $cible));
                imagedestroy($image);
                continue;
            }

            imagedestroy($image);
            ++$ecrits;
        }

        if ($illisibles !== []) {
            $io->text(sprintf('%d fichiers illisibles : %s', \count($illisibles), implode(', ', $illisibles)));
        }

        // Le bilan des écartés à l'unité, avec la part détourée qui a motivé le
        // refus : sans lui, un réglage devenu faux ressemble en tout point à
        // une source qui aurait changé de fond.
        if ($ecartes !== []) {
            $io->section(sprintf('%d portraits écartés — part détourée hors de [%d %%, %d %%]', \count($ecartes), (int) (self::PART_MINIMALE * 100), (int) (self::PART_MAXIMALE * 100)));
            foreach ($ecartes as $identifiant => $part) {
                $io->text(sprintf('  %-10s %5.1f %% — %s', $identifiant, $part * 100, $part > 0.9 ? 'image vide, l\'Assemblée n\'a pas de portrait' : 'fond inattendu'));
            }
            $io->text('Ces députés gardent leur portrait brut : mieux vaut un fond bleu qu\'un demi-visage.');
        }

        if ($parts !== []) {
            sort($parts);
            $io->text(sprintf(
                'Part détourée : médiane %.1f %%, de %.1f %% à %.1f %%.',
                $parts[intdiv(\count($parts), 2)] * 100,
                $parts[0] * 100,
                $parts[\count($parts) - 1] * 100,
            ));
        }

        $io->success(sprintf(
            '%d portraits détourés, %d déjà à jour, %d écartés — en %d s.',
            $ecrits,
            $inchanges,
            \count($ecartes),
            (int) (microtime(true) - $debut),
        ));

        return Command::SUCCESS;
    }

    /**
     * Le portrait détouré et la part de l'image rendue transparente, ou null si
     * le fichier n'est pas une image lisible.
     *
     * @return array{0: \GdImage, 1: float}|null
     */
    private function detoure(string $fichier): ?array
    {
        $source = @imagecreatefromjpeg($fichier);

        if ($source === false) {
            return null;
        }

        $largeur = imagesx($source);
        $hauteur = imagesy($source);
        $pixels = [];

        for ($i = 0, $n = $largeur * $hauteur; $i < $n; ++$i) {
            $couleur = imagecolorat($source, $i % $largeur, intdiv($i, $largeur));
            $pixels[$i] = [($couleur >> 16) & 0xFF, ($couleur >> 8) & 0xFF, $couleur & 0xFF];
        }

        $fond = $this->fondEstime($pixels, $largeur, $hauteur);
        $alpha = $this->alpha($pixels, $fond, $largeur, $hauteur);

        $image = imagecreatetruecolor($largeur, $hauteur);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparents = 0;

        for ($i = 0, $n = $largeur * $hauteur; $i < $n; ++$i) {
            $a = $alpha[$i];
            [$r, $v, $b] = $pixels[$i];

            if ($a === 0) {
                ++$transparents;
            } elseif ($a < 255) {
                // Décontamination : le pixel a été mesuré mélangé au fond, dans
                // la proportion que l'alpha annonce. Le rendre tel quel laisse
                // un liseré cyan tout autour des cheveux ; on retranche la part
                // de fond pour retrouver la couleur du seul sujet.
                $part = $a / 255;
                $r = (int) max(0, min(255, ($r - (1 - $part) * $fond[0]) / $part));
                $v = (int) max(0, min(255, ($v - (1 - $part) * $fond[1]) / $part));
                $b = (int) max(0, min(255, ($b - (1 - $part) * $fond[2]) / $part));
            }

            // GD compte la transparence à l'envers et sur 7 bits : 0 opaque,
            // 127 invisible.
            $transparence = 127 - (int) round($a * 127 / 255);
            imagesetpixel($image, $i % $largeur, intdiv($i, $largeur), ($transparence << 24) | ($r << 16) | ($v << 8) | $b);
        }

        imagedestroy($source);

        return [$image, $transparents / ($largeur * $hauteur)];
    }

    /**
     * La couleur du fond, prise à la médiane de la bordure haute et du haut des
     * montants.
     *
     * La médiane et non la moyenne : quelques mèches de cheveux touchent le
     * bord sur certains cadrages, et une moyenne les ferait entrer dans la
     * couleur de référence.
     *
     * @param array<int, array{0: int, 1: int, 2: int}> $pixels
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private function fondEstime(array $pixels, int $largeur, int $hauteur): array
    {
        $echantillons = [];

        for ($x = 0; $x < $largeur; ++$x) {
            $echantillons[] = $pixels[$x];
            $echantillons[] = $pixels[$largeur + $x];
        }

        for ($y = 0, $limite = (int) ($hauteur * 0.4); $y < $limite; ++$y) {
            $echantillons[] = $pixels[$y * $largeur];
            $echantillons[] = $pixels[$y * $largeur + $largeur - 1];
        }

        $fond = [];

        for ($canal = 0; $canal < 3; ++$canal) {
            $valeurs = array_column($echantillons, $canal);
            sort($valeurs);
            $fond[$canal] = $valeurs[intdiv(\count($valeurs), 2)];
        }

        return $fond;
    }

    /**
     * L'opacité de chaque pixel, de 0 (fond ôté) à 255 (sujet gardé).
     *
     * @param array<int, array{0: int, 1: int, 2: int}> $pixels
     * @param array{0: int, 1: int, 2: int}             $fond
     *
     * @return array<int, int>
     */
    private function alpha(array $pixels, array $fond, int $largeur, int $hauteur): array
    {
        $alpha = array_fill(0, $largeur * $hauteur, 255);
        $vus = array_fill(0, $largeur * $hauteur, false);

        // Amorces : la ligne du haut, et les montants sur la moitié haute
        // seulement. Le cadrage de l'Assemblée coupe le buste, si bien que le
        // bas de l'image est toujours du vêtement : amorcer depuis le bas
        // laisse la propagation remonter dans une chemise bleu pâle et manger
        // le torse — c'était le cas sur la moitié des costumes clairs. Le fond
        // qui borde le bas n'est pas perdu pour autant : il est relié à celui
        // du haut, la propagation l'atteint.
        $file = [];

        for ($x = 0; $x < $largeur; ++$x) {
            $file[] = $x;
        }

        for ($y = 0, $limite = intdiv($hauteur, 2); $y < $limite; ++$y) {
            $file[] = $y * $largeur;
            $file[] = $y * $largeur + $largeur - 1;
        }

        for ($tete = 0; $tete < \count($file); ++$tete) {
            $i = $file[$tete];

            if ($vus[$i]) {
                continue;
            }

            $vus[$i] = true;
            $distance = $this->distance($pixels[$i], $fond);

            if ($distance >= self::SEUIL_SUJET) {
                continue;
            }

            $alpha[$i] = $distance <= self::SEUIL_FOND
                ? 0
                : (int) round(255 * ($distance - self::SEUIL_FOND) / (self::SEUIL_SUJET - self::SEUIL_FOND));

            $x = $i % $largeur;
            $y = intdiv($i, $largeur);

            if ($x > 0) {
                $file[] = $i - 1;
            }
            if ($x < $largeur - 1) {
                $file[] = $i + 1;
            }
            if ($y > 0) {
                $file[] = $i - $largeur;
            }
            if ($y < $hauteur - 1) {
                $file[] = $i + $largeur;
            }
        }

        return $this->boucheLesMoucherons($alpha, $largeur, $hauteur);
    }

    /**
     * Un pixel à demi transparent cerné de fond franc est un accident du
     * dégradé, pas une frontière : sans ce bouchage, le fond garde des
     * moucherons gris qui se voient dès que la carte est claire.
     *
     * @param array<int, int> $alpha
     *
     * @return array<int, int>
     */
    private function boucheLesMoucherons(array $alpha, int $largeur, int $hauteur): array
    {
        for ($passe = 0; $passe < 2; ++$passe) {
            $avant = $alpha;

            for ($i = 0, $n = $largeur * $hauteur; $i < $n; ++$i) {
                if ($avant[$i] === 0 || $avant[$i] === 255) {
                    continue;
                }

                $x = $i % $largeur;
                $y = intdiv($i, $largeur);
                $voisins = 0;
                $fondAutour = 0;

                for ($dy = -1; $dy <= 1; ++$dy) {
                    for ($dx = -1; $dx <= 1; ++$dx) {
                        $nx = $x + $dx;
                        $ny = $y + $dy;

                        if (($dx === 0 && $dy === 0) || $nx < 0 || $ny < 0 || $nx >= $largeur || $ny >= $hauteur) {
                            continue;
                        }

                        ++$voisins;

                        if ($avant[$ny * $largeur + $nx] === 0) {
                            ++$fondAutour;
                        }
                    }
                }

                if ($fondAutour >= $voisins - 1) {
                    $alpha[$i] = 0;
                }
            }
        }

        return $alpha;
    }

    /**
     * Distance d'une couleur au fond, la teinte pesée bien plus lourd que la
     * luminosité.
     *
     * Le fond de l'Assemblée est un cyan pâle : son vert dépasse son rouge
     * d'environ 33, son bleu de 49, et ces deux écarts sont d'une constance
     * remarquable sur les 647 portraits (5e centile : 28 et 39). Une chevelure
     * blanche, elle, a ses trois canaux égaux. Une distance RGB ordinaire les
     * confond — elles ne diffèrent que de cinquante niveaux de gris — quand
     * celle-ci les sépare du simple au double. C'est ce qui permet de détourer
     * sans manger ni les cheveux blancs ni les chemises claires.
     *
     * @param array{0: int, 1: int, 2: int} $couleur
     * @param array{0: int, 1: int, 2: int} $fond
     */
    private function distance(array $couleur, array $fond): float
    {
        $luminosite = ($couleur[0] + $couleur[1] + $couleur[2]) / 3 - ($fond[0] + $fond[1] + $fond[2]) / 3;
        $vertRouge = ($couleur[1] - $couleur[0]) - ($fond[1] - $fond[0]);
        $bleuRouge = ($couleur[2] - $couleur[0]) - ($fond[2] - $fond[0]);

        return sqrt(self::POIDS_LUMINANCE * $luminosite ** 2 + $vertRouge ** 2 + $bleuRouge ** 2);
    }
}
