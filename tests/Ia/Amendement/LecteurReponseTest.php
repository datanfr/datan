<?php

namespace App\Tests\Ia\Amendement;

use App\Ia\Amendement\LecteurReponse;
use App\Ia\Amendement\ReponseInvalide;
use PHPUnit\Framework\TestCase;

final class LecteurReponseTest extends TestCase
{
    public const REPONSE = '{"titre": "Augmenter le plafond du taux réduit d\'impôt sur les sociétés", "resume": "Cet amendement vise à porter le seuil à 60 000 €.", "justification": "Les auteurs de l\'amendement estiment que le plafond est ancien.", "simplicite": 3, "categorie": "Fiscalité et budget", "tags": ["impôt sur les sociétés", "PME", "PME", "entreprises", "TPE"]}';

    public function testReponseComplete(): void
    {
        $champs = LecteurReponse::lire(self::REPONSE, 'end_turn');

        self::assertSame('Cet amendement vise à porter le seuil à 60 000 €.', $champs['resume']);
        self::assertSame('Fiscalité et budget', $champs['categorie']);
        self::assertSame(3, $champs['simplicite']);
        // Doublon retiré, trois au plus, comme le legacy.
        self::assertSame(['impôt sur les sociétés', 'PME', 'entreprises'], $champs['tags']);
    }

    public function testJustificationAbsenteEtCategorieHorsListe(): void
    {
        $champs = LecteurReponse::lire('{"titre":"T","resume":"R","justification":null,"simplicite":9,"categorie":"Tourisme et loisirs","tags":[]}', 'end_turn');

        self::assertNull($champs['justification']);
        self::assertNull($champs['categorie']);
        self::assertSame(5, $champs['simplicite']);
        self::assertSame([], $champs['tags']);

        self::assertNull(LecteurReponse::lire('{"titre":"T","resume":"R","justification":"null","simplicite":2,"categorie":"Défense","tags":[]}', 'end_turn')['justification']);
    }

    public function testBalisesRetirees(): void
    {
        $champs = LecteurReponse::lire('{"titre":"T","resume":"<script>x</script>Cet amendement vise à <b>tout</b>.","justification":null,"simplicite":2,"categorie":"Défense","tags":[]}', 'end_turn');

        self::assertSame('xCet amendement vise à tout.', $champs['resume']);
    }

    /** @dataProvider inutilisables */
    public function testReponsesInutilisables(?string $texte, string $arret, string $motif): void
    {
        $this->expectException(ReponseInvalide::class);
        $this->expectExceptionMessageMatches('/' . $motif . '/');

        LecteurReponse::lire($texte, $arret);
    }

    public static function inutilisables(): iterable
    {
        yield 'refus' => [null, 'refusal', 'refus'];
        yield 'coupée' => ['{"titre": "Aug', 'max_tokens', 'coupée'];
        yield 'vide' => [null, 'end_turn', 'aucun bloc'];
        yield 'pas du JSON' => ['Voici le résumé', 'end_turn', 'illisible'];
        // Le cas des amendements « rédactionnels » que le legacy resoumettait
        // en boucle : un résumé vide n'est pas un résumé.
        yield 'résumé vide' => ['{"titre":"Corriger","resume":"","justification":null,"simplicite":1,"categorie":"Institutions et démocratie","tags":[]}', 'end_turn', 'vide'];
    }
}
