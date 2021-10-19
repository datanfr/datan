<?php
  include('../bdd-connexion.php');

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
