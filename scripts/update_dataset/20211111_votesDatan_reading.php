<?php
  // Add a 'reading' field to the votes_datan database

  include('../bdd-connexion.php');

  $votes_datan = $bdd->query("SELECT * FROM votes_datan LIMIT 1");
  // Change category field --> from TEXT to INT
  $bdd->query("ALTER TABLE `votes_datan` CHANGE `category` `category` INT NOT NULL;");

  $x = $votes_datan->fetch();

  // Create new field: 'reading'
  if (!array_key_exists("reading", $x)) {
    $bdd->query("ALTER TABLE `votes_datan` ADD `reading` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `category`;");
  }

  // Delete unused field: 'contexte'
  if (array_key_exists("contexte", $x)) {
    $bdd->query("ALTER TABLE `votes_datan` DROP `contexte`;");
  }

  // New table 'readings'
  $bdd->query("CREATE TABLE IF NOT EXISTS `readings` (
    `id` int NOT NULL,
    `name` text NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

  // Insert into 'readings'
  $bdd->query('TRUNCATE TABLE readings');
  $bdd->query("INSERT INTO `readings` (`id`, `name`) VALUES
  (1, 'Première lecture'),
  (2, 'Deuxième lecture'),
  (3, 'Lecture définitive'),
  (4, 'Texte de la commission mixte paritaire')
  ");

  // Create new table: votes_datan_requested
  $bdd->query("CREATE TABLE IF NOT EXISTS `votes_datan_requested` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `legislature` INT NOT NULL,
    `vote` INT NOT NULL,
    `email` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
    `date_requested` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
