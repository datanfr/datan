<?php
  include('../bdd-connexion.php');

  $bdd->query('DROP TABLE IF EXISTS class_groups_cohesion_all;');
  $bdd->query('DROP TABLE IF EXISTS class_groups_majorite_all;');
  $bdd->query('DROP TABLE IF EXISTS class_groups_participation_all;');
  $bdd->query('DROP TABLE IF EXISTS class_loyaute_all;');
  $bdd->query('DROP TABLE IF EXISTS class_majorite_all;');
  $bdd->query('DROP TABLE IF EXISTS class_participation_all;');
  $bdd->query('DROP TABLE IF EXISTS class_participation_commission_all;')

?>
