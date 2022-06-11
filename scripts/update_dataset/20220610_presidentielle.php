<?php
  include('../bdd-connexion.php');

  $bdd->query('DROP TABLE IF EXISTS elect_2017_pres_2');

  $query = file_get_contents("sql/elect_pres_2.sql");
  $stmt = $bdd->prepare($query);

  if ($stmt->execute()){
    echo "Success";
  } else {
    echo "Fail";
  }
