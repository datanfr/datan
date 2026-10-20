<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base des pages qui ont besoin d'une session (connexion, dashboard, admin...).
 * Remplace Auth_Controller (CI3).
 * Le CSRF est appliqué par le filtre « csrf » sur les routes concernées.
 */
abstract class AuthController extends BaseController
{
    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Démarre la session (et pose le cookie) uniquement sur ces pages
        $this->session = service('session');
    }
}