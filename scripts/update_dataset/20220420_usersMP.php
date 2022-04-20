<?php
  include('../bdd-connexion.php');

  // Create table usersMP
  $bdd->query('DROP TABLE IF EXISTS `usersMP`');
  $bdd->query("CREATE TABLE `usersMP` (
    `user` INT(11) NOT NULL ,
    `mpId` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    INDEX `user_idx` (`user`)
  ) ENGINE = MyISAM;");
