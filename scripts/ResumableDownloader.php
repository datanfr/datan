<?php
/**
 * Script de téléchargement avec reprise automatique
 */

class ResumableDownloader {
    private $url;
    private $outputFile;
    private $maxRetries;
    private $timeout;
    private $chunkSize;

    public function __construct($url, $outputFile, $maxRetries = 100, $timeout = 300, $chunkSize = 1024 * 1024) {
        $this->url = $url;
        $this->outputFile = $outputFile;
        $this->maxRetries = $maxRetries;
        $this->timeout = $timeout;
        $this->chunkSize = $chunkSize; // 1MB par défaut
    }

    /**
     * Sauvegarde le fichier existant dans le dossier backup
     */
    private function backupExistingFile() {
        if (!file_exists($this->outputFile)) {
            return; // Pas de fichier à sauvegarder
        }

        $backupDir = dirname($this->outputFile) . '/backup';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = basename($this->outputFile);
        $backupPath = $backupDir . '/' . $filename;

        // Si une sauvegarde existe déjà, la supprimer
        if (file_exists($backupPath)) {
            unlink($backupPath);
        }

        // Déplacer le fichier vers backup
        if (rename($this->outputFile, $backupPath)) {
            echo "Fichier existant sauvegardé dans : $backupPath\n";
        }
    }

    /**
     * Lance le téléchargement avec gestion de reprise
     */
    public function download() {
        // Sauvegarder le fichier existant avant de commencer
        $this->backupExistingFile();

        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $attempt++;
                echo "Tentative #$attempt...\n";

                // Vérifier la taille actuelle du fichier local
                clearstatcache();
                usleep(100000);
                echo "sleep..\n";
                $currentSize = file_exists($this->outputFile) ? filesize($this->outputFile) : 0;

                // Obtenir la taille totale du fichier distant
                $remoteSize = $this->getRemoteFileSize();

                if ($remoteSize === false) {
                    throw new Exception("Impossible d'obtenir la taille du fichier distant");
                }

                echo "Taille du fichier distant : " . $this->formatBytes($remoteSize) . "\n";

                if ($currentSize > 0) {
                    echo "Déjà téléchargé : " . $this->formatBytes($currentSize) . " (" .
                         round(($currentSize / $remoteSize) * 100, 2) . "%)\n";

                    // Si le fichier est déjà complet
                    if ($currentSize >= $remoteSize) {
                        echo "Le fichier est déjà complètement téléchargé !\n";
                        return true;
                    }
                }

                // Télécharger la partie manquante
                if ($this->downloadRange($currentSize, $remoteSize)) {
                    echo "\n✓ Téléchargement terminé avec succès !\n";
                    echo "Fichier sauvegardé : {$this->outputFile}\n";
                    echo "Taille finale : " . $this->formatBytes(filesize($this->outputFile)) . "\n";
                    return true;
                }

            } catch (Exception $e) {
                echo "Erreur : " . $e->getMessage() . "\n";

                if ($attempt < $this->maxRetries) {
                    $waitTime = 2; // Backoff exponentiel, max 60s
                    echo "Attente de {$waitTime}s avant nouvelle tentative...\n\n";
                    sleep($waitTime);
                } else {
                    echo "Nombre maximum de tentatives atteint.\n";
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Obtient la taille du fichier distant
     */
    private function getRemoteFileSize() {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        return $size > 0 ? (int)$size : false;
    }

    /**
     * Télécharge une partie du fichier (range)
     */
    private function downloadRange($startByte, $totalSize) {
        $ch = curl_init($this->url);

        // Ouvrir le fichier en mode append
        $fp = fopen($this->outputFile, 'ab');
        if (!$fp) {
            throw new Exception("Impossible d'ouvrir le fichier en écriture : {$this->outputFile}");
        }

        // Configuration de cURL
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, $this->chunkSize);

        // Requête range pour reprendre le téléchargement
        if ($startByte > 0) {
            curl_setopt($ch, CURLOPT_RANGE, $startByte . '-');
        }

        // Barre de progression
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) use ($startByte, $totalSize) {
            if ($downloadSize > 0) {
                $totalDownloaded = $startByte + $downloaded;
                $percent = ($totalDownloaded / $totalSize) * 100;
                $bar = str_repeat('=', (int)($percent / 2)) . '>' . str_repeat(' ', 50 - (int)($percent / 2));
                printf("\r[%s] %0.2f%% - %s / %s",
                    $bar,
                    $percent,
                    $this->formatBytes($totalDownloaded),
                    $this->formatBytes($totalSize)
                );
            }
        });

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        fclose($fp);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Erreur cURL : $error");
        }

        curl_close($ch);

        // Codes HTTP acceptables : 200 (OK) ou 206 (Partial Content)
        if ($httpCode !== 200 && $httpCode !== 206) {
            throw new Exception("Code HTTP inattendu : $httpCode");
        }

        // Vérifier que le téléchargement est complet
        clearstatcache();
        usleep(100000);
        echo "sleep..\n";
        $currentSize = filesize($this->outputFile);
        if ($currentSize < $totalSize) {
            throw new Exception("Téléchargement incomplet : $currentSize / $totalSize octets");
        }

        return true;
    }

    /**
     * Formate les octets en taille lisible
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}