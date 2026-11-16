<?php

namespace App\Tests\Ia\Amendement;

use App\Ia\Amendement\LotsResumes;
use App\Ia\FabriqueClientAnthropic;
use App\Ia\Tag\AttributionTags;
use App\Ia\Tag\RapprochementTags;
use App\Ia\Tag\VocabulaireTags;
use App\Ia\TransporteurSimulation;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * La chaîne entière — soumission, résultats de lot, écriture, rapprochement des
 * mots-clés — contre une vraie base, dans une transaction annulée à la fin :
 * rien ne reste.
 *
 * La base n'est pas celle de l'environnement de test (vide) mais une base
 * peuplée, désignée par `DATAN_BASE_ESSAI` (même forme que DATABASE_URL) ;
 * sans elle, le test est ignoré :
 *
 *   DATAN_BASE_ESSAI="mysqli://datan:datan@127.0.0.1:3307/datan_symfony?serverVersion=11.7.2-MariaDB&charset=utf8mb4" vendor/bin/phpunit
 */
final class LotsResumesBaseTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $dsn = getenv('DATAN_BASE_ESSAI');
        if (!\is_string($dsn) || $dsn === '') {
            self::markTestSkipped('DATAN_BASE_ESSAI non renseignée.');
        }

        $this->connection = DriverManager::getConnection((new DsnParser(['mysqli' => 'mysqli', 'mysql' => 'pdo_mysql']))->parse($dsn));
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->connection) && $this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }

    public function testChaineComplete(): void
    {
        $c = $this->connection;
        $vocabulaire = new VocabulaireTags($c);
        $ivg = $vocabulaire->creer('IVG', 'ivg');
        $retraite = $vocabulaire->creer('retraites', 'retraite');
        $lots = new LotsResumes($c, new AttributionTags($vocabulaire, new RapprochementTags(), new NullLogger()));

        $selection = $lots->aResumer(17, 4);
        self::assertCount(4, $selection['amendements'], 'la base d\'essai doit porter au moins quatre amendements de L17 sans résumé');
        [$a, $b, $cc, $d] = array_column($selection['amendements'], 'id');

        // Soumission.
        $transporteur = (new TransporteurSimulation())->repondreJson(TransporteurSimulation::lot('msgbatch_essai', 'in_progress'));
        $fabrique = new FabriqueClientAnthropic('', 'claude-haiku-5-5');
        self::assertSame('msgbatch_essai', $lots->soumettre($fabrique->client($transporteur), 'claude-haiku-5-5', $selection['amendements']));
        self::assertSame(4, (int) $c->fetchOne("SELECT COUNT(*) FROM lot_ia_element e JOIN lot_ia l ON l.id = e.lot_id WHERE l.identifiant = 'msgbatch_essai' AND e.statut = 'en_attente'"));
        // Un amendement en attente n'est pas resoumis.
        self::assertEmpty(array_intersect([$a, $b, $cc, $d], array_column($lots->aResumer(17, 1000)['amendements'], 'id')));

        // Pendant le lot, la rédaction écrit le résumé de B.
        $c->executeStatement('UPDATE amendement SET resume_ia = ? WHERE id = ?', ['Écrit par la rédaction.', $b]);

        // Lot en cours : rien n'est écrit.
        $transporteur = (new TransporteurSimulation())->repondreJson(TransporteurSimulation::lot('msgbatch_essai', 'in_progress'));
        $bilan = $lots->traiter($fabrique->client($transporteur), 'claude-haiku-5-5', static function (): void {});
        self::assertSame(1, $bilan['lots_en_cours']);
        self::assertSame(0, $bilan['ecrits']);

        // Lot terminé.
        $usage = ['input_tokens' => 120, 'output_tokens' => 230, 'cache_read_input_tokens' => 1050, 'cache_creation_input_tokens' => 0];
        $reponse = static fn (array $tags) => json_encode([
            'titre' => 'Autoriser la retraite progressive', 'resume' => 'Cet amendement vise à étendre la retraite progressive.',
            'justification' => null, 'simplicite' => 4, 'categorie' => 'Protection sociale', 'tags' => $tags,
        ], \JSON_UNESCAPED_UNICODE);
        $transporteur = (new TransporteurSimulation())
            ->repondreJson(TransporteurSimulation::lot('msgbatch_essai', 'ended'))
            ->repondreJsonl([
                ['custom_id' => 'amd_' . $a, 'result' => ['type' => 'succeeded', 'message' => TransporteurSimulation::message($reponse(['Retraites', 'Interruption volontaire de grossesse', 'loup']), 'end_turn', $usage)]],
                ['custom_id' => 'amd_' . $b, 'result' => ['type' => 'succeeded', 'message' => TransporteurSimulation::message($reponse(['retraite']), 'end_turn', $usage)]],
                ['custom_id' => 'amd_' . $cc, 'result' => ['type' => 'errored', 'error' => ['type' => 'error', 'error' => ['type' => 'overloaded_error', 'message' => 'Overloaded']]]],
                ['custom_id' => 'amd_' . $d, 'result' => ['type' => 'succeeded', 'message' => TransporteurSimulation::message('{"titre": "Aut', 'max_tokens', $usage)]],
                ['custom_id' => 'amd_999999999', 'result' => ['type' => 'expired']],
            ])
            // Le rapprochement : un seul appel, pour les deux inconnus.
            ->repondreJson(TransporteurSimulation::message('{"synonymes":[{"candidat":"interruption volontaire de grossesse","cible":"IVG"}]}'));

        $bilan = $lots->traiter($fabrique->client($transporteur), 'claude-haiku-5-5', static function (): void {});

        self::assertSame(1, $bilan['lots_termines']);
        self::assertSame(1, $bilan['ecrits']);
        self::assertSame(1, $bilan['ecartes']);
        self::assertEqualsCanonicalizing(['errored' => 1, 'réponse coupée au plafond de 600 jetons' => 1], $bilan['echecs']);
        self::assertSame(['entree' => 360, 'sortie' => 690, 'cache_lus' => 3150, 'cache_ecrits' => 0], $bilan['usage']);
        self::assertSame(['connus' => 1, 'nouveaux' => 1, 'alias_existant' => 1, 'alias_candidat' => 0, 'liens' => 3], array_diff_key($bilan['tags'], ['rapprochement' => 0]));
        self::assertCount(3, $transporteur->requetes, 'état du lot, résultats, un seul rapprochement');

        // Le rapprochement n'a reçu que les deux inconnus.
        $requete = $transporteur->corps(2);
        self::assertStringContainsString('Candidats : Interruption volontaire de grossesse | loup', $requete['messages'][0]['content']);

        // A est écrit ; B, résumé entre-temps, garde le texte de la rédaction.
        $ecrit = $c->fetchAssociative('SELECT titre_ia, resume_ia, justification_ia, simplicite_ia, categorie_ia FROM amendement WHERE id = ?', [$a]);
        self::assertSame(['Autoriser la retraite progressive', 'Cet amendement vise à étendre la retraite progressive.', null, 4, 'Protection sociale'], array_values(array_map(static fn ($v) => is_numeric($v) ? (int) $v : $v, $ecrit)));
        self::assertSame('Écrit par la rédaction.', $c->fetchOne('SELECT resume_ia FROM amendement WHERE id = ?', [$b]));

        // Les mots-clés de A : trois canoniques, l'IVG par son alias.
        $tags = $c->fetchFirstColumn('SELECT t.slug FROM amendement_tag at JOIN tag t ON t.id = at.tag_id WHERE at.amendement_id = ? ORDER BY t.slug', [$a]);
        self::assertSame(['ivg', 'loup', 'retraite'], $tags);
        self::assertSame($ivg, (int) $c->fetchOne("SELECT synonyme_de_id FROM tag WHERE slug = 'interruption-volontaire-de-grossesse'"));
        self::assertSame(0, (int) $c->fetchOne('SELECT COUNT(*) FROM amendement_tag WHERE amendement_id = ?', [$b]));
        self::assertSame($retraite, $vocabulaire->canonique('retraite'));

        // Le lot est clos, ses compteurs posés ; un nouveau traitement n'appelle rien.
        self::assertEquals(['termine', 3150], array_values($c->fetchAssociative("SELECT statut, jetons_cache_lus FROM lot_ia WHERE identifiant = 'msgbatch_essai'")));
        $transporteur = new TransporteurSimulation();
        $lots->traiter($fabrique->client($transporteur), 'claude-haiku-5-5', static function (): void {});
        self::assertCount(0, $transporteur->requetes);

        // Les échecs repartent au prochain lot, les autres non.
        $suivants = array_column($lots->aResumer(17, 1000)['amendements'], 'id');
        self::assertContains($cc, $suivants);
        self::assertContains($d, $suivants);
        self::assertNotContains($a, $suivants);
        self::assertNotContains($b, $suivants);
    }
}
