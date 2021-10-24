<?php
  include('../bdd-connexion.php');

  // Create table dossiers_acteurs
  $bdd->query('DROP TABLE IF EXISTS `dossiers_acteurs`');
  $bdd->query("CREATE TABLE `dossiers_acteurs` (
    `id` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `legislature` INT(5) NOT NULL ,
    `etape` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
    `value` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `type` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `ref` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `mandate` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
    `dateMaj` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_id (id),
    INDEX idx_ref (ref)
  ) ENGINE = MyISAM;");

  // Create table amendements
  $bdd->query('DROP TABLE IF EXISTS `amendements`');
  $bdd->query("CREATE TABLE `amendements` (
    `id` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `dossier` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `legislature` INT(5) NOT NULL ,
    `texteLegislatifRef` VARCHAR(35) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `num` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `numOrdre` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `seanceRef` VARCHAR(35) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
    `expose` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
    `dateMaj` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX idx_dossier (dossier),
    INDEX idx_numOrdre (numOrdre),
    INDEX idx_seanceRef (seanceRef)
  ) ENGINE = MyISAM;");

  // Create table amendements_auteurs
  $bdd->query('DROP TABLE IF EXISTS `amendements_auteurs`');
  $bdd->query("CREATE TABLE `amendements_auteurs` (
    `id` VARCHAR(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `type` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `acteurRef` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `groupeId` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
    `auteurOrgane` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
    `dateMaj` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX idx_acteurRef (acteurRef),
    INDEX idx_groupeId (groupeId)
  ) ENGINE = MyISAM;");
