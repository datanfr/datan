<?php
  include('../bdd-connexion.php');

  $bdd->query('
    DROP TABLE IF EXISTS `deputes_all`;
    CREATE TABLE IF NOT EXISTS `deputes_all` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `mpId` varchar(25) NOT NULL,
      `legislature` int(5) NOT NULL,
      `nameUrl` varchar(255) NOT NULL,
      `civ` varchar(15) NOT NULL,
      `nameFirst` text NOT NULL,
      `nameLast` text NOT NULL,
      `age` int(3) DEFAULT NULL,
      `dptSlug` varchar(255) NOT NULL,
      `departementNom` text NOT NULL,
      `departementCode` varchar(10) NOT NULL,
      `circo` varchar(5) NOT NULL,
      `mandatId` varchar(25) NOT NULL,
      `libelle` text,
      `libelleAbrev` text,
      `groupeId` varchar(25) DEFAULT NULL,
      `groupeMandat` varchar(15) DEFAULT NULL,
      `couleurAssociee` varchar(25) DEFAULT NULL,
      `dateFin` date DEFAULT NULL,
      `datePriseFonction` date NOT NULL,
      `causeFin` text,
      `img` tinyint(1) NOT NULL,
      `imgOgp` tinyint(1) NOT NULL,
      `dateMaj` date NOT NULL,
      PRIMARY KEY (`id`),
      KEY `ids_legislature` (`legislature`),
      KEY `idx_mpId` (`mpId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
  ');

?>
