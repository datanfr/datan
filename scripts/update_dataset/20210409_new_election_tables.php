<?php
  echo "Update";
  include('../bdd-connexion.php');
  $bdd->query('DROP TABLE IF EXISTS `election2017_results`');
?>
