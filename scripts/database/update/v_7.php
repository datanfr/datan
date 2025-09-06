<?php

  /*
    With this file, the new version of the database will be => 7
    The name of the file needs to be v_X.php

    What needs to be changed in this file:
    1. CHANGE THE $new_v value
    2. MAKE YOUR CHANGE TO THE DATABASE

  */

  include '../../bdd-connexion.php';

  /* 1. CHANGE THE new_v VALUE */
  $new_v = 7;

  $last_v_installed = $bdd->query('SELECT version FROM mysql_v ORDER BY version DESC LIMIT 1');
  $last_v_installed = $last_v_installed->fetchAll(PDO::FETCH_ASSOC);
  $last_v_installed = $last_v_installed[0]['version'];

  echo "New version to install = " . $new_v . "<br>";
  echo "Last version installed = " . $last_v_installed . "<br>";

  if ($new_v > $last_v_installed) {
    echo "Install the new version <br>";

    /* Check if the column already exists */ 
    $check = $bdd->prepare("
      SELECT COUNT(*) AS col_exists
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_NAME = 'posts'
        AND COLUMN_NAME = 'image_name'
        AND TABLE_SCHEMA = DATABASE()
    ");
    $check->execute();
    $result = $check->fetch(PDO::FETCH_ASSOC);

    /* 2. MAKE YOUR CHANGE TO THE DATABASE */

    if ($result['col_exists'] == 0) {
      // Column does not exist → add it
      $prepare = $bdd->prepare("ALTER TABLE posts ADD COLUMN image_name VARCHAR(255)");
      if (!$prepare->execute()) {
        echo "The update did not work <br>";
        die();
      } else {
        echo "The column was added successfully <br>";
      }
    } else {
      echo "The column already exists, skipping ALTER TABLE <br>";
    }

    // Update versioning system regardless
    $prepare = $bdd->prepare("INSERT INTO mysql_v (version) VALUES (:new_version)");
    $prepare->execute(array('new_version' => $new_v));
    echo "Database version updated to $new_v <br>";

  } else {
    echo "The last version installed is already up to date <br>";
    die();
  }

?>
