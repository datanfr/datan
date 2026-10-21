<?php

namespace App\Http;

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le reverse proxy intégré, à un détail près : un porteur de cookie de session
 * ne lit jamais le magasin.
 *
 * Toute page publique est en cache partagé, et la navbar y est celle d'un
 * anonyme. Sans cette parade, un utilisateur connecté verrait la page déjà en
 * magasin — donc sans son menu « Mon compte / Dashboard » — sur toute adresse
 * déjà visitée par quelqu'un d'autre. C'est le « pass on session cookie »
 * classique des VCL Varnish.
 *
 * Les anonymes ne paient rien : le pare-feu est lazy, la session ne démarre
 * qu'à la connexion, donc un visiteur ordinaire ne porte pas ce cookie et
 * garde ses ~20 ms. Les connectés — une poignée de comptes — ont un rendu
 * complet à chaque page, comme sur l'application d'origine.
 *
 * Surtout pas de `Vary: Cookie` à la place : Matomo pose ses cookies `_pk_*` à
 * tout visiteur — et, jusqu'au 10 septembre 2026, Tarte au citron et le
 * compteur mensuel de pages en posaient aussi —, le magasin deviendrait un
 * cache par visiteur.
 */
final class CacheAvecSession extends HttpCache
{
    protected function lookup(Request $request, bool $catch = false): Response
    {
        // session_name() : la configuration `session: true` est aux défauts,
        // le nom du cookie est celui du php.ini — le même que Symfony posera.
        if ($request->cookies->has(session_name())) {
            return $this->pass($request, $catch);
        }

        return parent::lookup($request, $catch);
    }
}
