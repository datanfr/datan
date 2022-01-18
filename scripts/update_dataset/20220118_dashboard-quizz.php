<?php
  // Add a new table 'quizz'

  include('../bdd-connexion.php');

  $bdd->query('CREATE TABLE IF NOT EXISTS `quizz` (
    `voteNumero` SMALLINT(11) UNSIGNED NOT NULL,
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
    PRIMARY KEY (`voteNumero`, `legislature`)
  ) ENGINE=MyISAM CHARSET=utf8');
