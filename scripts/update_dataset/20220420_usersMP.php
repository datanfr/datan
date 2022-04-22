<?php
  include('../bdd-connexion.php');

  // Create table usersMP
  $bdd->query('DROP TABLE IF EXISTS `usersMP`');
  $bdd->query("CREATE TABLE `usersMP` (
    `user` INT(11) NOT NULL ,
    `mpId` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    INDEX `user_idx` (`user`)
  ) ENGINE = MyISAM;");

  $bdd->query('ALTER TABLE `elect_deputes_candidats` CHANGE `district` `district` VARCHAR(5) NULL DEFAULT NULL;');
  $bdd->query('ALTER TABLE `elect_deputes_candidats` ADD `candidature` INT(5) NULL DEFAULT '1' AFTER `election`;');
