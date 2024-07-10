<?php
$servername =  $_SERVER['DATABASE_HOST'];
$username = $_SERVER['DATABASE_USERNAME'];
$password = $_SERVER['DATABASE_PASSWORD'];
$oldDbName = $_SERVER['DATABASE_NAME'];
$newDbName = "datan_light";
$sqlFilePath = 'export.sql';

// Définir manuellement les relations entre les tables
$relations = [
    // 'table' => ['foreign_key' => 'referenced_table.referenced_key']
    'votes_datan' => ['vote_id' => 'votes.votesId', 'voteNumero' => 'votes.voteNumero', 'created_by' => 'users.id'],
    // Ajoutez ici toutes vos relations manuelles
];

try {
    // Connexion à l'ancienne base de données
    $conn = new PDO("mysql:host=$servername;dbname=$oldDbName", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des noms de tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    // Initialisation des tableaux pour stocker les données
    $data = [];
    $processed = [];

    // Fonction pour récupérer les données et les entrées associées par clés étrangères manuelles
    function fetchTableData($conn, $tableName, &$data, &$processed, $relations) {
        global $oldDbName;

        // if (isset($processed[$tableName])) {
        //     return;
        // }
        // $processed[$tableName] = true;

        if (!isset($data[$tableName])) {
            $data[$tableName] = [];
        }
        $stmt = $conn->query("SELECT * FROM $oldDbName.$tableName LIMIT 5000");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (!in_array($row, $data[$tableName])) {
                $data[$tableName][] = $row;
            }
        }

        if (isset($relations[$tableName])) {
            foreach ($relations[$tableName] as $foreignKey => $reference) {
                list($refTable, $refColumn) = explode('.', $reference);

                $ids = array_column($data[$tableName], $foreignKey);
                if (!empty($ids)) {
                    $idList = implode(",", array_map('intval', $ids));
                    $stmt = $conn->query("SELECT * FROM $oldDbName.$refTable WHERE $refColumn IN ($idList)");
                    $refData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($refData as $refRow) {
                        if (!in_array($refRow, $data[$refTable] ?? [])) {
                            $data[$refTable][] = $refRow;
                        }
                    }

                    fetchTableData($conn, $refTable, $data, $processed, $relations);
                }
            }
        }
    }

    foreach ($tables as $table) {
        fetchTableData($conn, $table, $data, $processed, $relations);
    }

    // Ouvrir le fichier SQL pour écriture
    $sqlFile = fopen($sqlFilePath, 'w');
    if ($sqlFile === false) {
        throw new Exception("Impossible d'ouvrir le fichier $sqlFilePath pour écriture.");
    }

    // Écrire les instructions de création de la nouvelle base de données
    fwrite($sqlFile, "CREATE DATABASE IF NOT EXISTS `$newDbName`;\nUSE `$newDbName`;\n");

    // Création des tables dans le fichier SQL et insertion des données
    foreach ($data as $table => $rows) {
        if (!empty($rows)) {
            $createTableStmt = $conn->query("SHOW CREATE TABLE $oldDbName.$table")->fetch(PDO::FETCH_ASSOC);
            fwrite($sqlFile, $createTableStmt['Create Table'] . ";\n");

            foreach ($rows as $row) {
                $columns = array_keys($row);
                $columnsList = implode("`, `", $columns);
                $valuesList = implode(", ", array_map(function ($value) use ($conn) {
                    return $conn->quote($value);
                }, array_values($row)));
                $insertStmt = "INSERT INTO `$table` (`$columnsList`) VALUES ($valuesList);\n";
                fwrite($sqlFile, $insertStmt);
            }
        }
    }

    fclose($sqlFile);

    echo "Données copiées avec succès dans le fichier $sqlFilePath.";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}

$conn = null;
?>
