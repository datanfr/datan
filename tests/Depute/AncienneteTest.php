<?php

namespace App\Tests\Depute;

use App\Depute\Anciennete;
use PHPUnit\Framework\TestCase;

final class AncienneteTest extends TestCase
{
    /** @return iterable<string, array{int, string}> */
    public static function durees(): iterable
    {
        yield 'en jours, sous deux semaines' => [10, '10 jours'];
        yield 'en mois, sous six mois' => [150, '5 mois'];
        yield 'six mois s\'arrondissent à un an' => [200, '1 an'];
        yield 'un an, au singulier' => [365, '1 an'];
        yield 'arrondi à l\'année la plus proche' => [3100, '8 ans'];
    }

    /** @dataProvider durees */
    public function testEnLettres(int $jours, string $attendu): void
    {
        self::assertSame($attendu, Anciennete::enLettres($jours));
    }
}
