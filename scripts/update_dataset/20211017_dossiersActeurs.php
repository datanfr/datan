<?php
  include('../bdd-connexion.php');

  $bdd->query('DROP TABLE IF EXISTS `dossiersActeurs`');

  $bdd->query("CREATE TABLE `dossiersActeurs` (
    `id` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `value` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `type` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `ref` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `mandate` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
  ) ENGINE = MyISAM;");
