<?php
  include('../bdd-connexion.php');

  $query = $bdd->query("SELECT * FROM newsletter LIMIT 1");

  while ($x = $query->fetch()) {

    if (isset($x["votes"])) {
      $bdd->query("UPDATE newsletter SET votes = general");
    } else {
      $bdd->query("
        ALTER TABLE `newsletter` ADD `votes` TINYINT(1) NOT NULL DEFAULT '1' AFTER `general`
      ");
      $bdd->query("UPDATE newsletter SET votes = general");
    }

  }
