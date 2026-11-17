<?php

namespace App\Tests\Scrutin;

use App\Scrutin\MotionDeCensure;
use PHPUnit\Framework\TestCase;

final class MotionDeCensureTest extends TestCase
{
    /**
     * Le scrutin 7979 de la 17e : l'open data donnait « pour » aux socialistes
     * pour 20 voix sur 69 membres, et aucune position aux groupes restés à
     * l'écart du vote.
     */
    public function testUnGroupeEstPourALaMoitieDeSesMembres(): void
    {
        $groupes = MotionDeCensure::positions([
            ['libelle_abrev' => 'LFI-NFP', 'effectif' => 71, 'nombrePours' => 71, 'positionMajoritaire' => 'pour'],
            ['libelle_abrev' => 'SOC', 'effectif' => 69, 'nombrePours' => 20, 'positionMajoritaire' => 'pour'],
            ['libelle_abrev' => 'EPR', 'effectif' => 92, 'nombrePours' => 0, 'positionMajoritaire' => 'nv'],
            ['libelle_abrev' => 'DEMI', 'effectif' => 10, 'nombrePours' => 5, 'positionMajoritaire' => 'pour'],
        ]);

        self::assertSame(['pour', 'contre', 'contre', 'pour'], array_column($groupes, 'positionMajoritaire'));
    }

    public function testUnGroupeSansEffectifNEstPasDivise(): void
    {
        $groupes = MotionDeCensure::positions([
            ['libelle_abrev' => 'X', 'effectif' => 0, 'nombrePours' => 0, 'positionMajoritaire' => null],
        ]);

        self::assertSame('contre', $groupes[0]['positionMajoritaire']);
    }
}
