<?php declare(strict_types=1);

/**
 * Script de tests fonctionnels pour l'API Datan.
 * Crée ses propres clés API via la DB, teste tout, puis nettoie.
 *
 * Usage:
 *   php tests/api_test.php [base_url]
 *
 * Charge automatiquement le .env à la racine du projet.
 * Si base_url n'est pas passé en argument, utilise BASE_URL du .env.
 */

// --- Configuration ---
define('CURL_TIMEOUT', 15);

$pass_count = 0;
$fail_count = 0;
$skip_count = 0;
$last_response = null;
$db = null;
$cleanup_ids = array('users' => array(), 'api_keys' => array(), 'votes_datan' => array(), 'exposes' => array());

// --- Chargement du .env ---
function load_env(string $path): void
{
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

// Chercher le .env à la racine du projet
$env_file = __DIR__ . '/../.env';
load_env($env_file);

// --- Helpers HTTP ---

function request(string $method, string $url, string $api_key, ?array $body = null): array
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $headers = array(
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        return array('http_code' => 0, 'body' => null, 'error' => $curl_error);
    }

    return array(
        'http_code' => $http_code,
        'body' => json_decode($response, true),
        'raw' => $response
    );
}

function api(string $method, string $url, string $api_key, ?array $body = null): array
{
    global $last_response;
    $last_response = request($method, $url, $api_key, $body);
    return $last_response;
}

// --- Helpers test ---

function assert_test(string $name, bool $condition, string $detail = ''): void
{
    global $pass_count, $fail_count, $last_response;
    if ($condition) {
        echo "  PASS  " . $name . PHP_EOL;
        $pass_count++;
    } else {
        $info = $detail;
        if (isset($last_response)) {
            $info .= ($info ? ' | ' : '') . 'HTTP ' . $last_response['http_code'];
            if (isset($last_response['body']['message'])) {
                $info .= ' — ' . $last_response['body']['message'];
            } elseif (isset($last_response['raw'])) {
                $info .= ' — ' . substr($last_response['raw'], 0, 200);
            }
        }
        echo "  FAIL  " . $name . ($info ? " — " . $info : '') . PHP_EOL;
        $fail_count++;
    }
}

function section(string $title): void
{
    echo PHP_EOL . str_repeat('=', 60) . PHP_EOL;
    echo "  " . $title . PHP_EOL;
    echo str_repeat('=', 60) . PHP_EOL;
}

// --- Helpers DB ---

function db_connect(): mysqli
{
    $host = getenv('DATABASE_HOST') ?: ($_SERVER['DATABASE_HOST'] ?? null);
    $user = getenv('DATABASE_USERNAME') ?: ($_SERVER['DATABASE_USERNAME'] ?? null);
    $pass = getenv('DATABASE_PASSWORD') ?: ($_SERVER['DATABASE_PASSWORD'] ?? null);
    $name = getenv('DATABASE_NAME') ?: ($_SERVER['DATABASE_NAME'] ?? null);
    $port = getenv('MYSQL_PORT') ?: ($_SERVER['MYSQL_PORT'] ?? 3306);

    if (!$host || !$user || !$name) {
        fwrite(STDERR, "Erreur: Variables d'environnement DB manquantes (DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME)" . PHP_EOL);
        exit(1);
    }

    // Si le host Docker ne résout pas (ex: "db"), fallback vers localhost
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = @new mysqli($host, $user, $pass ?? '', $name, (int) $port);
    if ($mysqli->connect_error) {
        echo "  Host '$host' inaccessible, tentative avec localhost..." . PHP_EOL;
        $mysqli = @new mysqli('localhost', $user, $pass ?? '', $name, (int) $port);
        if ($mysqli->connect_error) {
            $mysqli = @new mysqli('127.0.0.1', $user, $pass ?? '', $name, (int) $port);
        }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    if ($mysqli->connect_error) {
        fwrite(STDERR, "Erreur connexion DB: " . $mysqli->connect_error . PHP_EOL);
        exit(1);
    }
    $mysqli->set_charset('utf8');
    return $mysqli;
}

function create_test_user(mysqli $db, string $type): int
{
    global $cleanup_ids;
    $username = 'test_api_' . $type . '_' . time();
    $stmt = $db->prepare("INSERT INTO users (name, type, zipcode, email, username, password) VALUES (?, ?, '00000', ?, ?, '')");
    $name = 'Test API ' . ucfirst($type);
    $email = $username . '@test.local';
    $stmt->bind_param('ssss', $name, $type, $email, $username);
    $stmt->execute();
    $id = (int) $stmt->insert_id;
    $stmt->close();
    $cleanup_ids['users'][] = $id;
    return $id;
}

function create_test_api_key(mysqli $db, int $user_id, ?array $permissions = null): string
{
    global $cleanup_ids;
    $raw_key = 'datan_test_' . bin2hex(random_bytes(20));
    $key_hash = hash('sha256', $raw_key);
    $key_prefix = substr($raw_key, 0, 8);
    $perms_json = $permissions !== null ? json_encode($permissions) : null;

    $stmt = $db->prepare("INSERT INTO api_keys (name, key_hash, key_prefix, user_id, permissions, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
    $name = 'test_key_' . time() . '_' . rand(100, 999);
    $stmt->bind_param('sssis', $name, $key_hash, $key_prefix, $user_id, $perms_json);
    $stmt->execute();
    $cleanup_ids['api_keys'][] = (int) $stmt->insert_id;
    $stmt->close();
    return $raw_key;
}

function cleanup(mysqli $db): void
{
    global $cleanup_ids;
    echo PHP_EOL . "--- Nettoyage ---" . PHP_EOL;

    foreach ($cleanup_ids['votes_datan'] as $id) {
        $db->query("DELETE FROM votes_datan WHERE id = " . (int)$id);
    }
    foreach ($cleanup_ids['exposes'] as $id) {
        $db->query("DELETE FROM exposes WHERE id = " . (int)$id);
    }
    foreach ($cleanup_ids['api_keys'] as $id) {
        $db->query("DELETE FROM api_keys WHERE id = " . (int)$id);
    }
    foreach ($cleanup_ids['users'] as $id) {
        $db->query("DELETE FROM users WHERE id = " . (int)$id);
    }

    $counts = array();
    foreach ($cleanup_ids as $table => $ids) {
        if (!empty($ids)) {
            $counts[] = count($ids) . ' ' . $table;
        }
    }
    echo "  Supprimé: " . (empty($counts) ? 'rien' : implode(', ', $counts)) . PHP_EOL;
}

// --- Parsing des arguments ---

$base_url = isset($argv[1]) ? rtrim($argv[1], '/') : rtrim(getenv('BASE_URL') ?: '', '/');

if (empty($base_url)) {
    fwrite(STDERR, 'Usage: php tests/api_test.php [base_url]' . PHP_EOL);
    fwrite(STDERR, 'Exemple: php tests/api_test.php http://dev-datan.fr' . PHP_EOL);
    fwrite(STDERR, 'Ou définir BASE_URL dans le .env' . PHP_EOL);
    exit(1);
}

echo PHP_EOL . "API Test Suite — " . $base_url . PHP_EOL;
echo "Date: " . date('Y-m-d H:i:s') . PHP_EOL;

// --- Connexion DB et création des clés ---
section('0. Setup — Création des clés de test');

$db = db_connect();
echo "  DB connectée" . PHP_EOL;

// Nettoyage garanti en fin de script
register_shutdown_function(function () {
    global $db;
    if ($db) {
        cleanup($db);
        $db->close();
    }
});

// Créer utilisateurs test
$admin_user_id = create_test_user($db, 'admin');
$writer_user_id = create_test_user($db, 'writer');
echo "  Users créés: admin=#" . $admin_user_id . ", writer=#" . $writer_user_id . PHP_EOL;

// Clé admin (toutes permissions)
$admin_key = create_test_api_key($db, $admin_user_id, null);
echo "  Clé admin créée (permissions: toutes)" . PHP_EOL;

// Clé writer (toutes permissions)
$writer_key = create_test_api_key($db, $writer_user_id, null);
echo "  Clé writer créée (permissions: toutes)" . PHP_EOL;

// Clé writer restreinte (pas de :id)
$restricted_perms = array(
    '/api/votes' => array('GET'),
    '/api/decrypted_votes' => array('GET', 'POST'),
    '/api/exposes' => array('GET', 'POST')
);
$restricted_key = create_test_api_key($db, $writer_user_id, $restricted_perms);
echo "  Clé restreinte créée (sans endpoints :id)" . PHP_EOL;

// ============================================================
// 1. AUTHENTIFICATION
// ============================================================
section('1. Authentification');

$r = api('GET', $base_url . '/api/votes', '');
assert_test('Sans token -> 401', $r['http_code'] === 401);

$r = api('GET', $base_url . '/api/votes', 'datan_invalid_key_00000000');
assert_test('Mauvais token -> 401', $r['http_code'] === 401);

$r = api('GET', $base_url . '/api/votes?per_page=1', $admin_key);
assert_test('Clé admin -> 200', $r['http_code'] === 200);

$r = api('GET', $base_url . '/api/votes?per_page=1', $writer_key);
assert_test('Clé writer -> 200', $r['http_code'] === 200);

// ============================================================
// 2. PERMISSIONS
// ============================================================
section('2. Permissions');

// Clé restreinte : GET liste OK
$r = api('GET', $base_url . '/api/decrypted_votes?per_page=1', $restricted_key);
assert_test('Restreinte: GET liste -> 200', $r['http_code'] === 200);

// Clé restreinte : GET {id} -> 403
$r = api('GET', $base_url . '/api/decrypted_votes/1', $restricted_key);
assert_test('Restreinte: GET {id} -> 403', $r['http_code'] === 403);

// Clé restreinte : PUT {id} -> 403
$r = api('PUT', $base_url . '/api/decrypted_votes/1', $restricted_key, array('title' => 'x'));
assert_test('Restreinte: PUT {id} -> 403', $r['http_code'] === 403);

// Clé restreinte : DELETE {id} -> 403
$r = api('DELETE', $base_url . '/api/decrypted_votes/1', $restricted_key);
assert_test('Restreinte: DELETE {id} -> 403', $r['http_code'] === 403);

// ============================================================
// 3. GET /api/votes
// ============================================================
section('3. GET /api/votes');

$r = api('GET', $base_url . '/api/votes?per_page=5', $admin_key);
assert_test('Liste votes -> 200', $r['http_code'] === 200);
assert_test('Contient success=true', isset($r['body']['success']) && $r['body']['success'] === true);
assert_test('Contient pagination', isset($r['body']['pagination']));
assert_test('Contient data (array)', isset($r['body']['data']) && is_array($r['body']['data']));
assert_test('Contient count', isset($r['body']['count']));

// Pagination
$r = api('GET', $base_url . '/api/votes?per_page=2&page=1', $admin_key);
assert_test('Pagination per_page=2', $r['http_code'] === 200 && $r['body']['count'] <= 2);

// Filtre legislature
$r = api('GET', $base_url . '/api/votes?legislature=17&per_page=1', $admin_key);
assert_test('Filtre legislature=17', $r['http_code'] === 200);

// Sélection de champs
$r = api('GET', $base_url . '/api/votes?fields=voteId,legislature&per_page=1', $admin_key);
assert_test('Sélection champs', $r['http_code'] === 200);
if ($r['http_code'] === 200 && !empty($r['body']['data'])) {
    $first = $r['body']['data'][0];
    assert_test('Champs filtrés correctement', isset($first['voteId']) && isset($first['legislature']));
}

// Champ invalide
$r = api('GET', $base_url . '/api/votes?fields=invalid_field', $admin_key);
assert_test('Champ invalide -> 400', $r['http_code'] === 400);

// Tri
$r = api('GET', $base_url . '/api/votes?sort=legislature&order=ASC&per_page=1', $admin_key);
assert_test('Tri sort=legislature&order=ASC', $r['http_code'] === 200);

// Vote par ID
$r = api('GET', $base_url . '/api/votes?per_page=1', $admin_key);
if ($r['http_code'] === 200 && !empty($r['body']['data'])) {
    $vote_id = $r['body']['data'][0]['voteId'];
    $r = api('GET', $base_url . '/api/votes/' . $vote_id, $admin_key);
    assert_test('GET /api/votes/{id} -> 200', $r['http_code'] === 200);
    assert_test('Vote data présent', isset($r['body']['data']) && !empty($r['body']['data']));
}

// Vote inexistant
$r = api('GET', $base_url . '/api/votes/XXXXXX_INEXISTANT', $admin_key);
assert_test('Vote inexistant -> 404', $r['http_code'] === 404);

// Méthode non autorisée
$r = api('POST', $base_url . '/api/votes', $admin_key, array('test' => 1));
assert_test('POST /api/votes -> 405', $r['http_code'] === 405);

// ============================================================
// 4. GET /api/non_decrypted_votes
// ============================================================
section('4. GET /api/non_decrypted_votes');

$r = api('GET', $base_url . '/api/non_decrypted_votes?per_page=5', $admin_key);
assert_test('Liste non décryptés -> 200', $r['http_code'] === 200);
assert_test('Contient pagination', isset($r['body']['pagination']));
assert_test('Contient data', isset($r['body']['data']) && is_array($r['body']['data']));

$r = api('GET', $base_url . '/api/non_decrypted_votes?legislature=17&per_page=1', $admin_key);
assert_test('Filtre legislature=17', $r['http_code'] === 200);

// ============================================================
// 5. CRUD /api/decrypted_votes
// ============================================================
section('5. CRUD /api/decrypted_votes');

// --- GET list ---
$r = api('GET', $base_url . '/api/decrypted_votes?per_page=5', $admin_key);
assert_test('Liste décryptés -> 200', $r['http_code'] === 200);
assert_test('Contient pagination', isset($r['body']['pagination']));
assert_test('Contient data', isset($r['body']['data']) && is_array($r['body']['data']));

// Filtres
$r = api('GET', $base_url . '/api/decrypted_votes?state=published&per_page=1', $admin_key);
assert_test('Filtre state=published', $r['http_code'] === 200);

$r = api('GET', $base_url . '/api/decrypted_votes?legislature=17&per_page=1', $admin_key);
assert_test('Filtre legislature=17', $r['http_code'] === 200);

// --- POST create ---
$test_vote_numero = '99999';
$post_data = array(
    'title' => 'TEST API — Vote de test automatique',
    'legislature' => '17',
    'voteNumero' => $test_vote_numero,
    'category' => '1',
    'description' => 'Créé par le script de test api_test.php',
    'reading' => '1'
);

// Champ manquant
$r = api('POST', $base_url . '/api/decrypted_votes', $admin_key, array(
    'title' => 'test',
    'legislature' => '17'
));
assert_test('POST sans champ requis -> 400', $r['http_code'] === 400);

// Nettoyage préventif (si un run précédent a laissé des données)
$db->query("DELETE FROM votes_datan WHERE voteNumero = " . (int)$test_vote_numero . " AND legislature = 17");

// Création avec clé admin
$r = api('POST', $base_url . '/api/decrypted_votes', $admin_key, $post_data);
assert_test('POST create -> 201', $r['http_code'] === 201);
assert_test('Réponse contient data', isset($r['body']['data']));

if (isset($r['body']['data']['id'])) {
    $created_id = $r['body']['data']['id'];
    $cleanup_ids['votes_datan'][] = $created_id;

    // Vérifier les champs auto-générés
    $data = $r['body']['data'];
    assert_test('Auto: slug généré', !empty($data['slug']));
    assert_test('Auto: vote_id généré', !empty($data['vote_id']));
    assert_test('Auto: state = draft', $data['state'] === 'draft');
    assert_test('Auto: created_by rempli', !empty($data['created_by']));
    assert_test('Auto: created_by_name rempli', !empty($data['created_by_name']));
    assert_test('Auto: created_at rempli', !empty($data['created_at']));

    // --- GET by ID ---
    $r = api('GET', $base_url . '/api/decrypted_votes/' . $created_id, $admin_key);
    assert_test('GET /' . $created_id . ' -> 200', $r['http_code'] === 200);
    assert_test('Titre correct', isset($r['body']['data']['title']) && $r['body']['data']['title'] === $post_data['title']);

    // --- PUT update ---
    $r = api('PUT', $base_url . '/api/decrypted_votes/' . $created_id, $admin_key, array(
        'title' => 'TEST API — Titre modifié',
        'description' => 'Description modifiée par le test'
    ));
    assert_test('PUT update -> 200', $r['http_code'] === 200);
    if ($r['http_code'] === 200) {
        assert_test('Titre modifié', $r['body']['data']['title'] === 'TEST API — Titre modifié');
        assert_test('Description modifiée', $r['body']['data']['description'] === 'Description modifiée par le test');
        assert_test('Auto: modified_by rempli', !empty($r['body']['data']['modified_by']));
        assert_test('Auto: modified_by_name rempli', !empty($r['body']['data']['modified_by_name']));
        assert_test('Auto: modified_at rempli', !empty($r['body']['data']['modified_at']));
    }

    // PUT avec state invalide
    $r = api('PUT', $base_url . '/api/decrypted_votes/' . $created_id, $admin_key, array(
        'state' => 'invalid_state'
    ));
    assert_test('PUT state invalide -> 400', $r['http_code'] === 400);

    // --- POST doublon ---
    $r = api('POST', $base_url . '/api/decrypted_votes', $admin_key, $post_data);
    assert_test('POST doublon -> 409', $r['http_code'] === 409);

    // --- Writer ne peut pas modifier un vote publié ---
    $db->query("UPDATE votes_datan SET state = 'published' WHERE id = " . (int)$created_id);
    $r = api('PUT', $base_url . '/api/decrypted_votes/' . $created_id, $writer_key, array(
        'title' => 'Tentative writer'
    ));
    assert_test('Writer PUT publié -> 403', $r['http_code'] === 403);
    $db->query("UPDATE votes_datan SET state = 'draft' WHERE id = " . (int)$created_id);

    // --- Writer ne peut pas supprimer ---
    $r = api('DELETE', $base_url . '/api/decrypted_votes/' . $created_id, $writer_key);
    assert_test('Writer DELETE -> 403', $r['http_code'] === 403);

    // --- Admin peut supprimer ---
    $r = api('DELETE', $base_url . '/api/decrypted_votes/' . $created_id, $admin_key);
    assert_test('Admin DELETE -> 200', $r['http_code'] === 200);
}

// GET vote inexistant
$r = api('GET', $base_url . '/api/decrypted_votes/999999', $admin_key);
assert_test('GET inexistant -> 404', $r['http_code'] === 404);

// PUT vote inexistant
$r = api('PUT', $base_url . '/api/decrypted_votes/999999', $admin_key, array('title' => 'x'));
assert_test('PUT inexistant -> 404', $r['http_code'] === 404);

// ============================================================
// 6. CRUD /api/exposes
// ============================================================
section('6. CRUD /api/exposes');

// --- GET list ---
$r = api('GET', $base_url . '/api/exposes?per_page=5', $admin_key);
assert_test('Liste exposés -> 200', $r['http_code'] === 200);
assert_test('Contient pagination', isset($r['body']['pagination']));
assert_test('Contient data', isset($r['body']['data']) && is_array($r['body']['data']));

// --- GET stats ---
$r = api('GET', $base_url . '/api/exposes/stats', $admin_key);
assert_test('Stats -> 200', $r['http_code'] === 200);
assert_test('Stats contient total', isset($r['body']['data']['total']));
assert_test('Stats contient done', isset($r['body']['data']['done']));
assert_test('Stats contient pending', isset($r['body']['data']['pending']));

// --- POST create ---
$test_expose_vote = '99998';
$expose_data = array(
    'legislature' => '17',
    'voteNumero' => $test_expose_vote,
    'exposeOriginal' => 'Texte original de test',
    'exposeSummary' => 'Résumé de test'
);

// Champ manquant
$r = api('POST', $base_url . '/api/exposes', $admin_key, array(
    'legislature' => '17'
));
assert_test('POST sans voteNumero -> 400', $r['http_code'] === 400);

// Nettoyage préventif
$db->query("DELETE FROM exposes WHERE voteNumero = " . (int)$test_expose_vote . " AND legislature = 17");

// Création
$r = api('POST', $base_url . '/api/exposes', $admin_key, $expose_data);
assert_test('POST create exposé -> 201', $r['http_code'] === 201);
assert_test('Réponse contient data', isset($r['body']['data']));

if (isset($r['body']['data']['id'])) {
    $expose_id = $r['body']['data']['id'];
    $cleanup_ids['exposes'][] = $expose_id;

    $data = $r['body']['data'];
    assert_test('Auto: id généré', !empty($data['id']));
    assert_test('Auto: dateMaj rempli', !empty($data['dateMaj']));

    // --- GET by ID ---
    $r = api('GET', $base_url . '/api/exposes/' . $expose_id, $admin_key);
    assert_test('GET /' . $expose_id . ' -> 200', $r['http_code'] === 200);

    // --- GET by vote ---
    $r = api('GET', $base_url . '/api/exposes/by_vote/17/' . $test_expose_vote, $admin_key);
    assert_test('GET by_vote -> 200', $r['http_code'] === 200);
    assert_test('Données correctes', isset($r['body']['data']['voteNumero']) && $r['body']['data']['voteNumero'] == $test_expose_vote);

    // --- PUT update ---
    $r = api('PUT', $base_url . '/api/exposes/' . $expose_id, $admin_key, array(
        'exposeSummary' => 'Résumé modifié par le test'
    ));
    assert_test('PUT update -> 200', $r['http_code'] === 200);
    if ($r['http_code'] === 200) {
        assert_test('Résumé modifié', $r['body']['data']['exposeSummary'] === 'Résumé modifié par le test');
    }

    // --- POST doublon ---
    $r = api('POST', $base_url . '/api/exposes', $admin_key, $expose_data);
    assert_test('POST doublon -> 409', $r['http_code'] === 409);

    // --- Writer ne peut pas supprimer ---
    $r = api('DELETE', $base_url . '/api/exposes/' . $expose_id, $writer_key);
    assert_test('Writer DELETE exposé -> 403', $r['http_code'] === 403);

    // --- Admin peut supprimer ---
    $r = api('DELETE', $base_url . '/api/exposes/' . $expose_id, $admin_key);
    assert_test('Admin DELETE exposé -> 200', $r['http_code'] === 200);
}

// GET exposé inexistant
$r = api('GET', $base_url . '/api/exposes/999999', $admin_key);
assert_test('GET inexistant -> 404', $r['http_code'] === 404);

// GET by_vote inexistant
$r = api('GET', $base_url . '/api/exposes/by_vote/99/999999', $admin_key);
assert_test('GET by_vote inexistant -> 404', $r['http_code'] === 404);

// ============================================================
// RÉSULTATS
// ============================================================
section('RESULTATS');

$total = $pass_count + $fail_count + $skip_count;
echo PHP_EOL;
echo "  Total:  " . $total . PHP_EOL;
echo "  PASS:   " . $pass_count . PHP_EOL;
echo "  FAIL:   " . $fail_count . PHP_EOL;
echo PHP_EOL;

if ($fail_count > 0) {
    echo "  ECHEC — " . $fail_count . " test(s) en erreur." . PHP_EOL;
    // Le cleanup se fait via register_shutdown_function
    exit(1);
} else {
    echo "  SUCCES — Tous les tests passent." . PHP_EOL;
    exit(0);
}
