<?php

  /*
    With this file, the new version of the database will be => 2
    The name of the file needs to be v_X.php

    What needs to be changed in this file:
    1. CHANGE THE $new_v value
    2. MAKE YOUR CHANGE TO THE DATABASE

  */

  include '../../bdd-connexion.php';

  /* 1. CHANGE THE new_v VALUE */
  $new_v = 2;

  $last_v_installed = $bdd->query('SELECT version FROM mysql_v ORDER BY version DESC LIMIT 1');
  $last_v_installed = $last_v_installed->fetchAll(PDO::FETCH_ASSOC);
  $last_v_installed = $last_v_installed[0]['version'];

  echo "New version to install = " . $new_v . "<br>";
  echo "Last version installed = " . $last_v_installed . "<br>";

  if ($new_v > $last_v_installed) {
    echo "Install the new version <br>";

    /* 2. MAKE YOUR CHANGE TO THE DATABASE */

    // ADD FIELDS
    if (!count($bdd->query("SHOW COLUMNS FROM `amendements` LIKE 'sort'")->fetchAll())) {
      $bdd->query('ALTER TABLE `amendements` ADD `sort` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `expose`');
    }
    if (!count($bdd->query("SHOW COLUMNS FROM `amendements` LIKE 'state'")->fetchAll())) {
      $bdd->query('ALTER TABLE `amendements` ADD `state` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `expose`');
    }

    $prepare = $bdd->prepare("INSERT INTO mysql_v (version) VALUES (:new_version)");
    $prepare->execute(array('new_version' => $new_v));

  } else {
    echo "The last version installed is already up to date <br>";
    die();
  }

?>
