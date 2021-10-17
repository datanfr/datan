<?php
  include('../bdd-connexion.php');

  $bdd->query('DROP TABLE IF EXISTS `dossiersActeurs`');

  $bdd->query("CREATE TABLE `dossiersActeurs` (
    `id` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `acteurType` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `acteurRef` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `mandatRef` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
  ) ENGINE = MyISAM;");
