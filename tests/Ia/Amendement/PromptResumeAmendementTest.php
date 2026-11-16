<?php

namespace App\Tests\Ia\Amendement;

use App\Ia\Amendement\LotsResumes;
use App\Ia\Amendement\PromptResumeAmendement;
use App\Ia\FabriqueClientAnthropic;
use App\Ia\TransporteurSimulation;
use PHPUnit\Framework\TestCase;

/**
 * La requête de lot telle que le SDK l'envoie réellement : sérialisée par lui,
 * captée par un transporteur qui n'envoie rien.
 */
final class PromptResumeAmendementTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $corps;

    protected function setUp(): void
    {
        $transporteur = (new TransporteurSimulation())->repondreJson(TransporteurSimulation::lot('msgbatch_1', 'in_progress'));
        $client = (new FabriqueClientAnthropic('', 'claude-haiku-5-5'))->client($transporteur);

        $lot = $client->messages->batches->create(requests: LotsResumes::requetes('claude-haiku-5-5', [
            ['id' => 12, 'expose' => 'Premier exposé.'],
            ['id' => 13, 'expose' => 'Second exposé.'],
        ]));

        self::assertSame('msgbatch_1', $lot->id);
        self::assertStringEndsWith('/v1/messages/batches', (string) $transporteur->requetes[0]->getUri());
        $this->corps = $transporteur->corps();
    }

    public function testUneRequeteParAmendement(): void
    {
        self::assertSame(['amd_12', 'amd_13'], array_column($this->corps['requests'], 'custom_id'));
        foreach ($this->corps['requests'] as $requete) {
            self::assertMatchesRegularExpression('/^[a-zA-Z0-9_-]{1,64}$/', $requete['custom_id']);
        }
    }

    public function testParametresDuModele(): void
    {
        $p = $this->corps['requests'][0]['params'];

        self::assertSame('claude-haiku-5-5', $p['model']);
        self::assertSame(600, $p['max_tokens']);
        self::assertSame(['type' => 'disabled'], $p['thinking']);
        self::assertSame('low', $p['output_config']['effort']);
        // Haiku 5.5 refuse tout échantillonnage hors défaut.
        self::assertArrayNotHasKey('temperature', $p);
        self::assertArrayNotHasKey('top_p', $p);
    }

    public function testConsignesFigeesEtCacheablesExposeApres(): void
    {
        $premier = $this->corps['requests'][0]['params'];
        $second = $this->corps['requests'][1]['params'];

        self::assertSame(['type' => 'ephemeral', 'ttl' => '1h'], $premier['system'][0]['cache_control']);
        // Le préfixe caché est identique octet pour octet d'une requête à l'autre…
        self::assertSame(json_encode($premier['system']), json_encode($second['system']));
        self::assertSame(json_encode($premier['output_config']), json_encode($second['output_config']));
        // … et l'exposé, qui varie, vient après lui.
        self::assertStringNotContainsString('Premier exposé', $premier['system'][0]['text']);
        self::assertSame("Exposé des motifs :\nPremier exposé.", $premier['messages'][0]['content']);
    }

    public function testSortieStructureeAvecCategoriesFermees(): void
    {
        $format = $this->corps['requests'][0]['params']['output_config']['format'];

        self::assertSame('json_schema', $format['type']);
        self::assertSame(PromptResumeAmendement::CATEGORIES, $format['schema']['properties']['categorie']['enum']);
        self::assertCount(18, PromptResumeAmendement::CATEGORIES);
        self::assertFalse($format['schema']['additionalProperties']);
        self::assertSame(['titre', 'resume', 'justification', 'simplicite', 'categorie', 'tags'], $format['schema']['required']);
        // Les consignes ne demandent plus « uniquement du JSON valide » : le format s'en charge.
        self::assertStringNotContainsString('retourne uniquement', PromptResumeAmendement::CONSIGNES);
        // Les dix-huit libellés des consignes sont exactement ceux de l'enum.
        self::assertStringContainsString(implode(' | ', PromptResumeAmendement::CATEGORIES), PromptResumeAmendement::CONSIGNES);
    }

    public function testExposeNettoyeEtTronque(): void
    {
        self::assertSame(
            'Premier paragraphe. Second, avec une espace insécable.',
            PromptResumeAmendement::expose("<p>Premier   paragraphe.</p><p>Second,&#x00A0;avec une espace ins&#x00E9;cable.</p>"),
        );

        $long = PromptResumeAmendement::expose(str_repeat('é', 400));
        self::assertSame(PromptResumeAmendement::MAX_EXPOSE + 1, mb_strlen($long));
        self::assertStringEndsWith('…', $long);
        self::assertSame('', PromptResumeAmendement::expose('<p></p>'));
    }
}
