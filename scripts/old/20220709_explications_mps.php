<?php
  include('../bdd-connexion.php');

  $bdd->query('CREATE TABLE IF NOT EXISTS `explications_mp` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `voteNumero` INT NOT NULL ,
    `legislature` INT NOT NULL ,
    `mpId` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `state` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    PRIMARY KEY (`id`))
    ENGINE = MyISAM;');

  $bdd->query('ALTER TABLE `explications_mp` ADD INDEX `idx_legislature` (`legislature`)');
  $bdd->query('ALTER TABLE `explications_mp` ADD INDEX `idx_voteNumero` (`voteNumero`)');
  $bdd->query('ALTER TABLE `explications_mp` ADD INDEX `idx_mpId` (`mpId`)');
