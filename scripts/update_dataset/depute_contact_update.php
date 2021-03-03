<?php
  include('../bdd-connexion.php');

  $bdd->query('DROP TABLE IF EXISTS deputes_contacts;');
  $bdd->query('DROP TABLE IF EXISTS deputes_contacts_cleaned;');
  $bdd->query('CREATE TABLE `deputes_contacts` ( `mpId` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `website` VARCHAR(255) NULL DEFAULT NULL , `mailAn` VARCHAR(255) NULL DEFAULT NULL , `mailPerso` VARCHAR(255) NULL DEFAULT NULL , `twitter` VARCHAR(255) NULL DEFAULT NULL , `facebook` VARCHAR(255) NULL DEFAULT NULL , `dateMaj` DATE NOT NULL , PRIMARY KEY (`mpId`)) ENGINE = InnoDB;');

?>
