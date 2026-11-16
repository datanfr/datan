<?php

namespace App\Tests\Ia\Tag;

use App\Ia\Tag\NormaliseurTag;
use PHPUnit\Framework\TestCase;

final class NormaliseurTagTest extends TestCase
{
    /**
     * Variantes relevées dans `amendements_tags` de la production : elles
     * doivent se rejoindre sans IA.
     *
     * @dataProvider variantes
     */
    public function testLesVariantesSimplesOntLaMemeCle(string $a, string $b): void
    {
        self::assertSame(NormaliseurTag::slug($a), NormaliseurTag::slug($b));
    }

    public static function variantes(): iterable
    {
        yield 'casse' => ['Retraites', 'retraites'];
        yield 'pluriel' => ['retraite', 'retraites'];
        yield 'accents' => ['élevage', 'Elevage'];
        yield 'trait d\'union' => ['méga-bassines', 'Méga bassines'];
        yield 'pluriel en -aux' => ['hôpitaux', 'hôpital'];
        yield 'pluriel en -eaux' => ['réseaux', 'réseau'];
        yield 'sigle' => ['IVG', 'ivg'];
        yield 'deux mots' => ['soins palliatifs', 'Soin palliatif'];
        yield 'apostrophe typographique' => ['l’eau', "l'eau"];
        yield 'ligature' => ['œuvres', 'oeuvre'];
    }

    /** @dataProvider distincts */
    public function testLesNotionsDifferentesRestentDistinctes(string $a, string $b): void
    {
        self::assertNotSame(NormaliseurTag::slug($a), NormaliseurTag::slug($b));
    }

    public static function distincts(): iterable
    {
        yield 'synonyme : affaire du modèle' => ['IVG', 'interruption volontaire de grossesse'];
        yield 'plus large' => ['agriculture', 'agriculture biologique'];
        yield '« prix » n\'est pas un pluriel de « pri »' => ['prix', 'pri'];
    }

    public function testCleLisible(): void
    {
        self::assertSame('mega-bassine', NormaliseurTag::slug('Méga-bassines'));
        self::assertSame('impot-sur-les-societe', NormaliseurTag::slug('impôt sur les sociétés'));
        self::assertSame('taux', NormaliseurTag::slug('taux'), '« taux » (4 lettres) ne devient pas « tal »');
        self::assertSame('stress', NormaliseurTag::slug('stress'));
        self::assertSame('bus', NormaliseurTag::slug('bus'));
        self::assertSame('jeux-olympique', NormaliseurTag::slug('Jeux olympiques'));
    }

    public function testLibelle(): void
    {
        self::assertSame('pêche artisanale', NormaliseurTag::libelle("  pêche \n artisanale. "));
        self::assertNull(NormaliseurTag::libelle(''));
        self::assertNull(NormaliseurTag::libelle(' « » '));
        self::assertNull(NormaliseurTag::libelle(42));
        self::assertNull(NormaliseurTag::libelle(str_repeat('a', 101)));
    }
}
