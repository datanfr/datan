<?php

namespace App\Controllers;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Exceptions\PageNotFoundException;

// TEMPORAIRE : à supprimer avant la bascule
class Ping extends BaseController
{
    public function index()
    {
        if (ENVIRONMENT === 'production') {
            throw PageNotFoundException::forPageNotFound();
        }

        $db  = \Config\Database::connect();
        $row = $db->query(
            'SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = ?',
            [$db->getDatabase()]
        )->getRow();

        return $this->response->setJSON([
            'php'      => PHP_VERSION,
            'ci'       => CodeIgniter::CI_VERSION,
            'env'      => ENVIRONMENT,
            'baseURL'  => base_url(),
            'database' => $db->getDatabase(),
            'tables'   => (int) $row->n,
        ]);
    }
}
