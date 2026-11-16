<?php

namespace App\Tests\Newsletter;

use App\Newsletter\Mailjet;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MailjetTest extends TestCase
{
    private const LISTE_INCONNUE = '{ "ErrorMessage" : "Object properties invalid", "StatusCode" : 400, "ErrorInfo" : { "ContactsLists" : [{ "ListID" : 47010, "Action" : "addforce", "Error" : "List ID 47010 is invalid or deleted" }] } }';

    /**
     * La panne de la bascule : Mailjet rejetait d'un bloc l'inscription aux
     * deux listes parce que la seconde n'existait plus, et la liste générale
     * ne recevait plus personne.
     */
    public function testUneListeInconnueNEmpechePasLAutre(): void
    {
        $appels = [];
        $client = new MockHttpClient(function (string $methode, string $url, array $options) use (&$appels): MockResponse {
            $appels[] = [$methode, parse_url($url, \PHP_URL_PATH), $options['body'] ?? null];

            if (str_ends_with($url, '/v3/REST/contact')) {
                return new MockResponse('{"Count":1,"Data":[{"ID":1}],"Total":1}', ['http_code' => 201]);
            }

            return str_contains((string) ($options['body'] ?? ''), '47010')
                ? new MockResponse(self::LISTE_INCONNUE, ['http_code' => 400])
                : new MockResponse('{"Count":1,"Data":[{"ContactsLists":[]}],"Total":1}', ['http_code' => 201]);
        });

        (new Mailjet($client, new NullLogger(), 'cle', 'secret'))
            ->inscrire('lectrice@exemple.fr', [Mailjet::LISTE_GENERALE, Mailjet::LISTE_VOTES]);

        // Le contact, puis une requête par liste.
        self::assertCount(3, $appels);
        self::assertStringContainsString((string) Mailjet::LISTE_GENERALE, (string) $appels[1][2]);
        self::assertStringNotContainsString((string) Mailjet::LISTE_VOTES, (string) $appels[1][2]);
        self::assertStringContainsString((string) Mailjet::LISTE_VOTES, (string) $appels[2][2]);
    }

    public function testUnAutreRefusResteUneErreur(): void
    {
        $client = new MockHttpClient(static fn (string $methode, string $url): MockResponse => str_ends_with($url, '/v3/REST/contact')
            ? new MockResponse('{"Count":1,"Data":[{"ID":1}],"Total":1}', ['http_code' => 201])
            : new MockResponse('{ "ErrorMessage" : "Invalid email address", "StatusCode" : 400 }', ['http_code' => 400]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid email address');

        (new Mailjet($client, new NullLogger(), 'cle', 'secret'))->inscrire('pas-une-adresse', [Mailjet::LISTE_GENERALE]);
    }

    /**
     * Se désinscrire d'un contact que Mailjet ne connaît pas n'est pas une
     * erreur : il n'y a rien à retirer.
     */
    public function testDesinscrireUnInconnuNeLevePas(): void
    {
        $client = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            '{ "ErrorMessage" : "Object not found", "StatusCode" : 404 }',
            ['http_code' => 404],
        ));

        (new Mailjet($client, new NullLogger(), 'cle', 'secret'))->desinscrire('inconnu@exemple.fr', [Mailjet::LISTE_GENERALE]);

        self::assertSame(1, $client->getRequestsCount());
    }
}
