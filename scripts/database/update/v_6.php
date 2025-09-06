<?php

  /*
    With this file, the new version of the database will be => 6
    The name of the file needs to be v_5.php

    What needs to be changed in this file:
    1. CHANGE THE $new_v value
    2. MAKE YOUR CHANGE TO THE DATABASE

  */

  include '../../bdd-connexion.php';

  /* 1. CHANGE THE new_v VALUE */
  $new_v = 6;

  $last_v_installed = $bdd->query('SELECT version FROM mysql_v ORDER BY version DESC LIMIT 1');
  $last_v_installed = $last_v_installed->fetchAll(PDO::FETCH_ASSOC);
  $last_v_installed = $last_v_installed[0]['version'];

  echo "New version to install = " . $new_v . "<br>";
  echo "Last version installed = " . $last_v_installed . "<br>";

  if ($new_v > $last_v_installed) {
    echo "Install the new version <br>";

    /* 2. MAKE YOUR CHANGE TO THE DATABASE */
    if (count($bdd->query("SHOW COLUMNS FROM `deputes_last` LIKE 'imgOgp'")->fetchAll())) {
      $bdd->query("ALTER TABLE `deputes_last` DROP `imgOgp`");
    }

    if (count($bdd->query("SHOW COLUMNS FROM `deputes_all` LIKE 'imgOgp'")->fetchAll())) {
      $bdd->query("ALTER TABLE `deputes_all` DROP `imgOgp`");
    }

    $prepare = $bdd->prepare("INSERT INTO mysql_v (version) VALUES (:new_version)");
    $prepare->execute(array('new_version' => $new_v));

  } else {
    echo "The last version installed is already up to date <br>";
    die();
  }
