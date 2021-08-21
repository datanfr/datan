<?php
  include('../bdd-connexion.php');

  $bdd->query('ALTER TABLE `circos` CHANGE `dpt_nom` `dpt_nom` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'); // Fix issues with importing the backup file
  $bdd->query('ALTER TABLE `cities_mayors` CHANGE `profession` `profession` VARCHAR(4) NULL DEFAULT NULL;'); // Fix issues with importing the backup file
  $bdd->query('ALTER TABLE `departement` CHANGE `region` `region` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'); //Fix issues with importing the backup file
  $bdd->query('ALTER TABLE `departement` CHANGE `libelle_1` `libelle_1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'); //Fix issues with importing the backup file
  $bdd->query('ALTER TABLE `departement` CHANGE `libelle_2` `libelle_2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'); //Fix issues with importing the backup file
  $bdd->query('ALTER TABLE `departement` CHANGE `slug` `slug` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'); //Fix issues with importing the backup file
  $bdd->query('ALTER TABLE `regions` CHANGE `nccner` `nccner` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'); //Fix issues with importing the backup file
  $bdd->query('ALTER TABLE `regions` CHANGE `libelle` `libelle` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;'); //Fix issues with importing the backup file
