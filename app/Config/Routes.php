<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// TEMPORAIRE : test de la chaîne CI4 + base de données
$routes->get('ping', 'Ping::index');
