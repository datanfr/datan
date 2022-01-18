<?php
  // Add a new table 'quizz'

  include('../bdd-connexion.php');

  $bdd->query('CREATE TABLE IF NOT EXISTS `quizz` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `quizz` SMALLINT UNSIGNED NOT NULL,
    `voteNumero` SMALLINT UNSIGNED NOT NULL,
    `legislature` TINYINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `for1` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `for2` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `for3` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `against1` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `against2` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `against3` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `context` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `category` TINYINT UNSIGNED NULL DEFAULT NULL,
    `state` varchar(15) NOT NULL,
    `created_by` SMALLINT NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified_by` SMALLINT DEFAULT NULL,
    `modified_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM CHARSET=utf8');
