<?php
include('../bdd-connexion.php');

$bdd->query('CREATE TABLE IF NOT EXISTS `newsletter` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `email` varchar(255) NOT NULL,
        `general` tinyint(1) NOT NULL DEFAULT 1,
        `depute` json DEFAULT NULL,
        `departement` json DEFAULT NULL,
        `id_user` int(11) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    )
');
