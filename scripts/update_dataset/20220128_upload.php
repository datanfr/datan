<?php
  // Add a 'reading' field to the votes_datan database

  include('../bdd-connexion.php');

  $query = file_get_contents("sql/20220128_upload.sql");
  $stmt = $bdd->prepare($query);

  if ($stmt->execute()){
    echo "Success";
  } else {
    echo "Fail";
  }
