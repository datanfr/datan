<?php

namespace App\Tests\Twig;

use App\Twig\PhotoExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Filesystem\Filesystem;

final class PhotoExtensionTest extends TestCase
{
    private string $racine;

    protected function setUp(): void
    {
        $this->racine = sys_get_temp_dir() . '/datan-photos-' . bin2hex(random_bytes(4));
        mkdir($this->racine . '/public/assets/imgs', 0777, true);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->racine);
    }

    /**
     * Le portrait d'origine existe en PNG (~130 Ko) et en WebP (~10 Ko) : c'est
     * le WebP qui sort, comme le faisait la balise `<picture>` du site
     * d'origine pour tout navigateur récent.
     */
    public function testLeJumeauWebpPasseAvantLePng(): void
    {
        $this->poser('deputes_original/depute_720892.png');
        $this->poser('deputes_webp/depute_720892_webp.webp');

        self::assertSame('/assets/imgs/deputes_webp/depute_720892_webp.webp', $this->extension()->photoDepute('PA720892'));
    }

    public function testSansJumeauLePngReste(): void
    {
        $this->poser('deputes_original/depute_720892.png');

        self::assertSame('/assets/imgs/deputes_original/depute_720892.png', $this->extension()->photoDepute('PA720892'));
    }

    /** Avant la 17e, le portrait détouré en hauteur — et son jumeau à lui. */
    public function testUnAncienDeputePrendLeJeuDetoure(): void
    {
        $this->poser('deputes_original/depute_1234.png');
        $this->poser('deputes_webp/depute_1234_webp.webp');
        $this->poser('deputes_nobg/depute_1234.png');
        $this->poser('deputes_nobg_webp/depute_1234_webp.webp');

        self::assertSame(
            '/assets/imgs/deputes_nobg_webp/depute_1234_webp.webp',
            $this->extension()->photoDepute(['mp_id' => 'PA1234', 'legislature_last' => 15]),
        );
    }

    public function testSansAucunePhotoLeVisageGenerique(): void
    {
        self::assertSame('/assets/imgs/placeholder/placeholder-face.png', $this->extension()->photoDepute('PA999'));
    }

    private function poser(string $fichier): void
    {
        $chemin = $this->racine . '/public/assets/imgs/' . $fichier;
        if (!is_dir(\dirname($chemin))) {
            mkdir(\dirname($chemin), 0777, true);
        }
        file_put_contents($chemin, 'image');
    }

    private function extension(): PhotoExtension
    {
        return new PhotoExtension(new Packages(new PathPackage('/', new EmptyVersionStrategy())), $this->racine);
    }
}
