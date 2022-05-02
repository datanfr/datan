<?php
  include('../bdd-connexion.php');

  // Create table usersMP
  try {
    $bdd->query("SELECT 1 FROM usersMP LIMIT 1");
  } catch (Exception $e) {
    // We got an exception (table not found)
    //return FALSE;
    $bdd->query("CREATE TABLE `usersMP` (
      `user` INT(11) NOT NULL ,
      `mpId` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
      INDEX `user_idx` (`user`)
    ) ENGINE = MyISAM;");
  }

  $bdd->query('ALTER TABLE `elect_deputes_candidats` CHANGE `district` `district` VARCHAR(5) NULL DEFAULT NULL;');

  try {
    $bdd->query("SELECT candidature FROM elect_deputes_candidats LIMIT 1");
  } catch (\Exception $e) {
    $bdd->query('ALTER TABLE `elect_deputes_candidats` ADD `candidature` INT(5) NULL DEFAULT 1 AFTER `election`;');
  }

  $bdd->query('ALTER TABLE users ADD UNIQUE (username);');
