<?php
  include('../bdd-connexion.php');

  $query = $bdd->query("SELECT * FROM deputes LIMIT 1");

  while ($x = $query->fetch()) {
    if (!isset($x["hatvp"])) {
      $bdd->query("ALTER TABLE `deputes` ADD `hatvp` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `famSocPro`;");
    } else {
      echo "hatvp column already exists!";
    }
  }

  $bdd->query("CREATE TABLE IF NOT EXISTS `datan`.`hatvp` (
    `mpId` VARCHAR(10) NOT NULL ,
    `url` TEXT NOT NULL ,
    `category` VARCHAR(35) NOT NULL ,
    `value` TEXT NOT NULL ,
    `valueCleaned` TEXT NULL ,
    `conservee` BOOLEAN NOT NULL ,
    `dateDebut` DATE NULL ,
    `dateFin` DATE NULL,
    `dateMaj` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mpId` (`mpId`)
  ) ENGINE = MyISAM;");
