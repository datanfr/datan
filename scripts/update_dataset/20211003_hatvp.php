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
