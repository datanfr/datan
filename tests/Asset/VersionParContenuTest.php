<?php

namespace App\Tests\Asset;

use App\Asset\VersionParContenu;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class VersionParContenuTest extends TestCase
{
    private string $racine;

    protected function setUp(): void
    {
        $this->racine = sys_get_temp_dir() . '/datan-assets-' . bin2hex(random_bytes(4));
        mkdir($this->racine . '/assets/imgs', 0777, true);
        file_put_contents($this->racine . '/assets/imgs/logo.png', 'premier contenu');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->racine);
    }

    public function testLAdresseGardeSonCheminEtPrendUneVersion(): void
    {
        $adresse = (new VersionParContenu($this->racine))->applyVersion('assets/imgs/logo.png');

        self::assertMatchesRegularExpression('#^assets/imgs/logo\.png\?v=[0-9a-f]{10}$#', $adresse);
    }

    /**
     * Le déploiement recopie le dépôt et redate donc chaque fichier : la
     * version ne doit pas en dépendre, sinon tout se retélécharge à chaque
     * mise en ligne.
     */
    public function testLaVersionNeSuitQueLeContenu(): void
    {
        $avant = (new VersionParContenu($this->racine))->getVersion('assets/imgs/logo.png');

        touch($this->racine . '/assets/imgs/logo.png', time() + 3600);
        clearstatcache();
        self::assertSame($avant, (new VersionParContenu($this->racine))->getVersion('assets/imgs/logo.png'));

        file_put_contents($this->racine . '/assets/imgs/logo.png', 'second contenu');
        self::assertNotSame($avant, (new VersionParContenu($this->racine))->getVersion('assets/imgs/logo.png'));
    }

    public function testUnFichierAbsentSortSansVersion(): void
    {
        self::assertSame('assets/js/absent.js', (new VersionParContenu($this->racine))->applyVersion('assets/js/absent.js'));
    }

    public function testLAncreResteEnFinDAdresse(): void
    {
        $adresse = (new VersionParContenu($this->racine))->applyVersion('/assets/imgs/logo.png#icone');

        self::assertMatchesRegularExpression('#^/assets/imgs/logo\.png\?v=[0-9a-f]{10}\#icone$#', $adresse);
    }
}
