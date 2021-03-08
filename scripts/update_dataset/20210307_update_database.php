<?php
  include('../bdd-connexion.php');

  $bdd->query('
    ALTER TABLE `votes` CHANGE `vote` `vote` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL
    ');
    
    $bdd->query('
    DROP TABLE IF EXISTS `deputes_accord`;
    CREATE TABLE IF NOT EXISTS `deputes_accord` (
    `uid` int(5) NOT NULL AUTO_INCREMENT,
    `voteNumero` int(5) NOT NULL,
    `legislature` int(3) NOT NULL,
    `mpId` varchar(10) NOT NULL,
    `organeRef` varchar(10) NOT NULL,
    `accord` int(2) DEFAULT NULL,
    PRIMARY KEY (`uid`)
  ) ENGINE=MyISAM AUTO_INCREMENT=3201272 DEFAULT CHARSET=utf8;
  ');
