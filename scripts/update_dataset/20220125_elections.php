<?php
  // Add a 'reading' field to the votes_datan database

  include('../bdd-connexion.php');

  $query = file_get_contents("sql/elect_2017_leg_results_communes.sql");
  $stmt = $bdd->prepare($query);

  if ($stmt->execute()){
    echo "Success";
  } else {
    echo "Fail";
  }
