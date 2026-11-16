<?php

namespace App\Tests\Ia\Tag;

use App\Ia\FabriqueClientAnthropic;
use App\Ia\Tag\RapprochementTags;
use App\Ia\TransporteurSimulation;
use PHPUnit\Framework\TestCase;

final class RapprochementTagsTest extends TestCase
{
    private const VOCABULAIRE = [
        'ivg' => 'IVG',
        'retraite' => 'retraites',
        'age-de-depart' => 'âge de départ',
        'nucleaire' => 'nucléaire',
        'energie' => 'énergie',
        'fin-de-vie' => 'fin de vie',
        'aide-a-mourir' => 'aide à mourir',
    ];

    public function testSectionsRestreintesALaCategorieEtAuxVoisinsLexicaux(): void
    {
        $sections = RapprochementTags::sections(
            [
                'retraite-anticipee' => ['retraite anticipée', 'Protection sociale'],
                'euthanasie' => ['euthanasie', 'Santé publique'],
            ],
            self::VOCABULAIRE,
            ['retraite' => 53, 'fin-de-vie' => 282, 'aide-a-mourir' => 200, 'nucleaire' => 63],
            [
                'Santé publique' => ['fin-de-vie' => 250, 'aide-a-mourir' => 190],
                'Environnement et énergie' => ['nucleaire' => 60, 'energie' => 20],
            ],
        );

        self::assertSame(['Protection sociale', 'Santé publique'], array_keys($sections));
        // Voisin lexical (même racine « retra »), quelle que soit sa catégorie.
        self::assertSame(['retraite' => 'retraites'], $sections['Protection sociale']['existants']);
        // Sans voisin lexical : les plus portés de la catégorie, où se trouve le synonyme.
        self::assertSame(['fin-de-vie' => 'fin de vie', 'aide-a-mourir' => 'aide à mourir'], $sections['Santé publique']['existants']);
        // Le reste du vocabulaire n'est pas envoyé.
        self::assertArrayNotHasKey('nucleaire', $sections['Santé publique']['existants']);
    }

    public function testRequeteEtLectureParLeSdk(): void
    {
        $transporteur = (new TransporteurSimulation())->repondreJson(TransporteurSimulation::message(
            '{"synonymes":[{"candidat":"Interruption volontaire de grossesse","cible":"IVG"}]}',
        ));
        $client = (new FabriqueClientAnthropic('', 'claude-haiku-5-5'))->client($transporteur);

        $couples = (new RapprochementTags())->rapprocher($client, 'claude-haiku-5-5', [
            'Santé publique' => ['existants' => ['ivg' => 'IVG'], 'candidats' => ['interruption-volontaire-de-grossesse' => 'interruption volontaire de grossesse']],
        ]);

        // La réponse est ramenée aux slugs, quelle que soit la casse rendue par le modèle.
        self::assertSame([['interruption-volontaire-de-grossesse', 'ivg']], $couples);

        $corps = $transporteur->corps();
        self::assertStringEndsWith('/v1/messages', (string) $transporteur->requetes[0]->getUri());
        self::assertSame("## Santé publique\nExistants : IVG\nCandidats : interruption volontaire de grossesse", $corps['messages'][0]['content']);
        self::assertSame('medium', $corps['output_config']['effort']);
        self::assertSame('json_schema', $corps['output_config']['format']['type']);
        self::assertArrayNotHasKey('thinking', $corps, 'réflexion adaptative, défaut de Haiku 5.5');
    }

    public function testRefusDuModele(): void
    {
        $transporteur = (new TransporteurSimulation())->repondreJson(TransporteurSimulation::message('', 'refusal'));
        $client = (new FabriqueClientAnthropic('', 'claude-haiku-5-5'))->client($transporteur);

        $this->expectException(\RuntimeException::class);
        (new RapprochementTags())->rapprocher($client, 'claude-haiku-5-5', ['' => ['existants' => [], 'candidats' => ['a' => 'a']]]);
    }

    public function testResolutionPrivilegieLExistant(): void
    {
        $decisions = RapprochementTags::resoudre(
            [
                ['interruption-volontaire-de-grossesse', 'ivg'],
                // Chaîne entre candidats : elle suit l'existant désigné par l'un d'eux.
                ['avortement', 'interruption-volontaire-de-grossesse'],
                // Termes inconnus de la requête : écartés.
                ['euthanasie', 'mot-invente'],
                ['hors-requete', 'ivg'],
                // Boucle sur soi : ignorée.
                ['loup', 'loup'],
            ],
            ['interruption-volontaire-de-grossesse' => 'interruption volontaire de grossesse', 'avortement' => 'avortement', 'euthanasie' => 'euthanasie', 'loup' => 'loup'],
            ['ivg' => 'IVG'],
            ['ivg' => 40],
        );

        self::assertSame([
            'interruption-volontaire-de-grossesse' => 'ivg',
            'avortement' => 'ivg',
            'euthanasie' => null,
            'loup' => null,
        ], $decisions + array_fill_keys(['interruption-volontaire-de-grossesse', 'avortement', 'euthanasie', 'loup'], 'absent'));
    }

    public function testGroupeDeCandidatsSousLePlusPorte(): void
    {
        // Le modèle désigne « retraites progressives » comme cible ; c'est
        // pourtant « retraite progressive », plus porté, qui devient canonique.
        $decisions = RapprochementTags::resoudre(
            [['retraite-progressive', 'retraites-progressives-x'], ['retraites-progressives-x', 'retraite-progressive']],
            ['retraite-progressive' => 'retraite progressive', 'retraites-progressives-x' => 'retraites progressives'],
            [],
            ['retraite-progressive' => 3, 'retraites-progressives-x' => 1],
        );

        self::assertSame(['retraite-progressive' => null, 'retraites-progressives-x' => 'retraite-progressive'], $decisions);
    }
}
