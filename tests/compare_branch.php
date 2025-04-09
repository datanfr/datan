<?php declare(strict_types=1);

// --- Configuration ---
define('CURRENT_STATE_DIR', __DIR__ . '/branch_compare_current');
define('TARGET_BRANCH_DIR', __DIR__ . '/branch_compare_target');
define('CURL_TIMEOUT', 10);

$original_branch = null;
$cleanup_enabled = true;

function runCommand(string $command, ?array &$output = null, ?int &$return_var = null): bool
{
    echo 'Running command: ' . $command . PHP_EOL;
    // Reset variables
    $output = [];
    $return_var = -1;
    exec($command . ' 2>&1', $output, $return_var);  // Redirige stderr vers stdout

    if ($return_var !== 0) {
        fwrite(STDERR, 'Error running command: ' . $command . PHP_EOL);
        fwrite(STDERR, 'Return code: ' . $return_var . PHP_EOL);
        fwrite(STDERR, "Output:\n" . implode("\n", $output) . PHP_EOL);
        return false;
    }
    return true;
}

function getCurrentBranch(): ?string
{
    $command = 'git rev-parse --abbrev-ref HEAD';
    if (runCommand($command, $output, $ret)) {
        return trim($output[0] ?? '');
    }
    return null;
}

function checkoutBranch(string $branchName): bool
{
    echo PHP_EOL . '--- Checking out branch: ' . $branchName . ' ---' . PHP_EOL;
    $command = 'git checkout ' . escapeshellarg($branchName);
    $success = runCommand($command);
    if ($success) {
        // Potentiellement ajouter un délai si le serveur doit redémarrer
        // echo "Waiting 5 seconds for server to potentially reload...\n";
        // sleep(5);
    }
    return $success;
}

function sanitizeUrlToFilename(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH);
    if (empty($path) || $path === '/') {
        $path = '/index';  // Nom pour la racine
    }

    // Supprime le slash initial et remplace les autres par des underscores
    $filename = str_replace('/', '_', ltrim($path, '/'));

    // Ajoute l'extension .html si absente (on suppose du HTML)
    if (!preg_match('/\.(html|htm)$/i', $filename)) {
        $filename .= '.html';
    }

    // Supprime les caractères potentiellement problématiques
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}

function downloadUrls(array $urls, string $targetDir): bool
{
    echo PHP_EOL . '--- Downloading URLs to ' . basename($targetDir) . ' ---' . PHP_EOL;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
        fwrite(STDERR, 'Error: Could not create directory ' . $targetDir . PHP_EOL);
        return false;
    }

    $success_count = 0;
    $error_count = 0;
    $mh = curl_multi_init();
    $handles = [];

    foreach ($urls as $url) {
        $url = trim($url);
        if (empty($url))
            continue;

        $filename = sanitizeUrlToFilename($url);
        $filepath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Suivre les redirections
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Branch Compare Script');  // Être poli
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Utile pour localhost avec https auto-signé
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // Attention en production !

        curl_multi_add_handle($mh, $ch);
        $handles[$filepath] = $ch;  // Associe le chemin de fichier au handle
    }

    $active = null;
    // Exécuter les handles
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        // Attendre une activité sur n'importe quel handle
        if (curl_multi_select($mh) == -1) {
            usleep(100);  // Éviter une boucle trop active en cas d'erreur de select
        }
        // Traiter les handles actifs
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }

    // Récupérer les résultats
    foreach ($handles as $filepath => $ch) {
        $url_for_handle = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);  // URL réelle après redirection
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content = curl_multi_getcontent($ch);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);
        $filename = basename($filepath);

        echo 'Processing ' . $url_for_handle . ' -> ' . $filename . ' ... ';

        if ($curl_errno === 0 && $http_code >= 200 && $http_code < 300) {
            if (file_put_contents($filepath, $content) !== false) {
                echo 'OK (HTTP ' . $http_code . ')' . PHP_EOL;
                $success_count++;
            } else {
                echo 'Error saving file!' . PHP_EOL;
                $error_count++;
            }
        } else {
            $error_message = 'Error downloading ' . $url_for_handle . ': ';
            if ($curl_errno !== 0) {
                $error_message .= 'cURL Error (' . $curl_errno . '): ' . $curl_error;
            } else {
                $error_message .= 'HTTP Status Code: ' . $http_code;
            }
            echo $error_message . PHP_EOL;
            // Sauvegarde l'erreur dans le fichier pour comparaison
            file_put_contents($filepath, $error_message . "\nOriginal URL: " . $url . "\n");
            $error_count++;
        }
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }

    curl_multi_close($mh);

    echo 'Download complete. Success: ' . $success_count . ', Errors: ' . $error_count . PHP_EOL;
    if ($error_count > 0) {
        echo 'WARNING: Some URLs failed to download.' . PHP_EOL;
    }
    return $error_count == 0;  // Ou une logique plus permissive si nécessaire
}

/**
 * Normalise le contenu d'un fichier pour la comparaison en ignorant les différences non significatives.
 *
 * @param string $content Le contenu à normaliser.
 * @return string Le contenu normalisé.
 */
function normalizeFileContent(string $content): string {
    // Normaliser les fins de ligne
    $content = str_replace("\r\n", "\n", $content);
    
    // Si c'est du HTML, utiliser DOMDocument pour normaliser
    if (preg_match('/<html|<!DOCTYPE html/i', $content)) {
        $dom = new DOMDocument();
        // Handle UTF-8 encoding properly
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $content);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        
        // Find all elements with the class "randomized"
        $nodesToRemove = $xpath->query('//*[contains(@class, "randomized")]');
        $removedCount = 0;
        $nodesToRemoveArray = [];
        foreach ($nodesToRemove as $node) {
            $nodesToRemoveArray[] = $node;
        }
        foreach ($nodesToRemoveArray as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
                $removedCount++;
            }
        }
        
        // Find all elements with a name attribute containing "csrf"
        $nodesToRemove = $xpath->query('//*[contains(@name, "csrf")]');
        $nodesToRemoveArray = [];
        foreach ($nodesToRemove as $node) {
            $nodesToRemoveArray[] = $node;
        }
        foreach ($nodesToRemoveArray as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
                $removedCount++;
            }
        }
        
        // Save the modified HTML
        $modified_html = $dom->saveHTML();
        
        // Normaliser les espaces blancs et les nouvelles lignes
        // $modified_html = preg_replace('/\s+/', ' ', $modified_html); // Remplacer les séquences d'espaces blancs par un seul espace
        // $modified_html = preg_replace('/\s*>\s*/', '>', $modified_html); // Supprimer les espaces avant >
        // $modified_html = preg_replace('/\s*<\s*/', '<', $modified_html); // Supprimer les espaces après <
        
        return trim($modified_html);
    } else {
        // Pour les fichiers non-HTML, normaliser les espaces blancs
        $normalized = preg_replace('/\s+/', ' ', $content); // Remplacer les séquences d'espaces blancs par un seul espace
        return trim($normalized);
    }
}

/**
 * Compare les fichiers correspondants dans deux répertoires en utilisant 'diff'.
 *
 * @param string $dir1 Premier répertoire.
 * @param string $dir2 Deuxième répertoire.
 * @param array $urls Liste des URLs originales pour déterminer les fichiers à comparer.
 * @return bool True si des différences sont trouvées.
 */
function compareDirectories(string $dir1, string $dir2, array $urls): bool
{
    echo PHP_EOL . '--- Comparing downloaded pages ---' . PHP_EOL;
    $differences_found = false;

    // Obtenir la liste des noms de fichiers attendus
    $expected_files = [];
    foreach ($urls as $url) {
        $url = trim($url);
        if (!empty($url)) {
            $expected_files[sanitizeUrlToFilename($url)] = $url;  // Stocke l'URL originale aussi
        }
    }
    ksort($expected_files);  // Trie par nom de fichier

    foreach ($expected_files as $filename => $original_url) {
        $file1 = $dir1 . DIRECTORY_SEPARATOR . $filename;
        $file2 = $dir2 . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($file1)) {
            echo 'Warning: File missing in ' . basename($dir1) . ': ' . $filename . ' (from URL: ' . $original_url . ')' . PHP_EOL;
            $differences_found = true;
            continue;
        }
        if (!file_exists($file2)) {
            echo 'Warning: File missing in ' . basename($dir2) . ': ' . $filename . ' (from URL: ' . $original_url . ')' . PHP_EOL;
            $differences_found = true;
            continue;
        }

        echo 'Comparing: ' . $filename . ' ... ';

        // Normaliser le contenu des fichiers avant comparaison
        $content1 = normalizeFileContent(file_get_contents($file1));
        $content2 = normalizeFileContent(file_get_contents($file2));

        if ($content1 === $content2) {
            echo 'Identical' . PHP_EOL;
            continue;
        }

        // Si les contenus normalisés sont différents, montrer le diff pour le débogage
        echo 'DIFFERENCES FOUND' . PHP_EOL;
        $differences_found = true;

        // Créer des fichiers temporaires avec le contenu normalisé pour le diff
        $temp1 = tempnam(sys_get_temp_dir(), 'diff1_');
        $temp2 = tempnam(sys_get_temp_dir(), 'diff2_');
        file_put_contents($temp1, $content1);
        file_put_contents($temp2, $content2);
        // Utiliser la commande 'diff -u' pour obtenir un diff unifié
        $command = 'diff -u -w ' . escapeshellarg($temp1) . ' ' . escapeshellarg($temp2);
        $output = [];
        $return_var = -1;
        exec($command, $output, $return_var);

        echo '--- Diff for ' . $filename . ' (URL: ' . $original_url . ') ---' . PHP_EOL;
        echo implode("\n", $output) . PHP_EOL;
        echo '--- End Diff ---' . PHP_EOL;

        // Nettoyer les fichiers temporaires
        @unlink($temp1);
        @unlink($temp2);

        echo str_repeat('-', 80) . PHP_EOL;  // Séparateur
    }

    if (!$differences_found) {
        echo PHP_EOL . '--- No differences found between the two sets of pages. ---' . PHP_EOL;
    }

    if ($differences_found) {
        echo PHP_EOL . '--- Differences detected. Review the output above. ---' . PHP_EOL;
    }
    return $differences_found;
}

/**
 * Supprime récursivement un répertoire.
 *
 * @param string $dir Le chemin du répertoire à supprimer.
 * @return bool True en cas de succès, False sinon.
 */
function recursiveRemoveDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return true;  // N'existe pas, considéré comme un succès
    }
    try {
        $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        return rmdir($dir);
    } catch (Exception $e) {
        fwrite(STDERR, 'Error removing directory ' . $dir . ': ' . $e->getMessage() . PHP_EOL);
        return false;
    }
}

/**
 * Nettoie les répertoires temporaires.
 *
 * @param array $dirs Tableau des chemins de répertoires à supprimer.
 */
function cleanup(array $dirs): void
{
    echo PHP_EOL . '--- Cleaning up temporary directories ---' . PHP_EOL;
    foreach ($dirs as $dir_path) {
        if (is_dir($dir_path)) {
            if (recursiveRemoveDirectory($dir_path)) {
                echo 'Removed: ' . $dir_path . PHP_EOL;
            } else {
                fwrite(STDERR, 'Failed to fully remove: ' . $dir_path . PHP_EOL);
            }
        }
    }
}

// --- Gestion de la fin du script (nettoyage et retour à la branche) ---
register_shutdown_function(function () {
    global $original_branch, $cleanup_enabled;

    echo PHP_EOL . '--- Script shutting down ---' . PHP_EOL;

    // 6. Revenir à la branche originale (TRÈS IMPORTANT)
    if ($original_branch !== null) {
        echo 'Attempting to restore original branch: ' . $original_branch . PHP_EOL;
        // Utiliser --force peut être dangereux si des modifs ont été faites
        checkoutBranch($original_branch);
    } else {
        echo 'Warning: Could not determine original branch to restore.' . PHP_EOL;
    }

    // 7. Nettoyage (sauf si demandé de ne pas le faire)
    if ($cleanup_enabled) {
        cleanup([CURRENT_STATE_DIR, TARGET_BRANCH_DIR]);
    } else {
        echo PHP_EOL . '--- Skipping cleanup as requested. Directories remain: ---' . PHP_EOL;
        echo '- ' . CURRENT_STATE_DIR . PHP_EOL;
        echo '- ' . TARGET_BRANCH_DIR . PHP_EOL;
    }
    echo PHP_EOL . '--- Script finished. ---' . PHP_EOL;
});

// --- Exécution Principale ---

// 1. Vérifier/Parser les arguments
if ($argc < 3) {
    fwrite(STDERR, 'Usage: php compare_branches.php <url_file> <target_branch> [--no-cleanup]' . PHP_EOL);
    exit(1);
}

$url_file = $argv[1];
$target_branch = $argv[2];
$cleanup_enabled = !in_array('--no-cleanup', array_slice($argv, 3));

// Vérifier si le fichier d'URLs existe
if (!is_file($url_file)) {
    fwrite(STDERR, "Error: URL file not found at '" . $url_file . "'" . PHP_EOL);
    exit(1);
}

// 2. Lire les URLs
$urls_to_test = file($url_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($urls_to_test === false) {
    fwrite(STDERR, "Error: Could not read URL file '" . $url_file . "'" . PHP_EOL);
    exit(1);
}

// 3. Nettoyer les anciens répertoires (au cas où)
cleanup([CURRENT_STATE_DIR, TARGET_BRANCH_DIR]);

// 4. Obtenir la branche actuelle
$original_branch = getCurrentBranch();
if ($original_branch === null) {
    fwrite(STDERR, 'Error: Could not determine current Git branch.' . PHP_EOL);
    exit(1);  // Sortie gérée par le shutdown handler pour le cleanup
}
echo 'Current branch: ' . $original_branch . PHP_EOL;
if ($original_branch === $target_branch) {
    fwrite(STDERR, "Error: Current branch is the same as the target branch ('" . $target_branch . "'). No comparison needed." . PHP_EOL);
    exit(1);
}

// 5. Télécharger les pages de la branche actuelle
if (!downloadUrls($urls_to_test, CURRENT_STATE_DIR)) {
    fwrite(STDERR, 'Error occurred during download for current branch. Aborting comparison.' . PHP_EOL);
    exit(1);  // Sortie gérée par le shutdown handler
}

// 6. Passer à la branche cible
if (!checkoutBranch($target_branch)) {
    fwrite(STDERR, "Error checking out target branch '" . $target_branch . "'. Aborting." . PHP_EOL);
    // Le shutdown handler tentera de revenir à l'originale
    exit(1);
}

// 7. Télécharger les pages de la branche cible
//    (Assurez-vous que votre serveur local sert maintenant cette branche !)
if (!downloadUrls($urls_to_test, TARGET_BRANCH_DIR)) {
    fwrite(STDERR, 'Error occurred during download for target branch. Comparison might be incomplete.' . PHP_EOL);
    // On continue pour comparer ce qui a été téléchargé, mais on note l'erreur.
    // Ou exit(1); si on veut être strict.
}

// 8. Comparer les répertoires
compareDirectories(CURRENT_STATE_DIR, TARGET_BRANCH_DIR, $urls_to_test);

// La fin normale du script déclenchera la fonction enregistrée via register_shutdown_function
// qui gère le retour à la branche originale et le nettoyage.

exit(0);  // Termine normalement, le shutdown handler s'exécute.

?>