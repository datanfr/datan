<?php
  include('../bdd-connexion.php');

  $bdd->query('DROP TABLE IF EXISTS class_groups_cohesion_all;');
  $bdd->query('DROP TABLE IF EXISTS class_groups_majorite_all;');
  $bdd->query('DROP TABLE IF EXISTS class_groups_participation_all;');
  $bdd->query('DROP TABLE IF EXISTS class_loyaute_all;');
  $bdd->query('DROP TABLE IF EXISTS class_majorite_all;');
  $bdd->query('DROP TABLE IF EXISTS class_participation_all;');
  $bdd->query('DROP TABLE IF EXISTS class_participation_commission_all;');
  $bdd->query('DROP TABLE IF EXISTS class_groups_cohesion;');
  $bdd->query('DROP TABLE IF EXISTS class_groups_majorite;');
  $bdd->query('DROP TABLE IF EXISTS class_groups_participation;');

  try {
    $bdd->query('ALTER TABLE `departement` ADD `region` VARCHAR(255) NULL DEFAULT NULL AFTER `slug`;');
    $bdd->query('Update departement Set region= (Select region_name from departements_regions where departements_regions.num_dep=departement.departement_code)');
    $bdd->query('DROP TABLE IF EXISTS departements_regions;');
  }catch(Exception $e){
    echo 'departement_regions déjà migrées, enfin vérifie ta base quand même';
  }

  try {
    $bdd->query('ALTER TABLE `insee` ADD `new_region` INT;');
    $bdd->query('Update insee Set region=(Select new_code from regions_old_new where regions_old_new.former_code=insee.region))');
    $bdd->query('DROP TABLE IF EXISTS regions_old_new;');
  }catch(Exception $e){
    echo 'regions_old_new déjà migrées, enfin vérifie ta base quand même';
  }
?>
