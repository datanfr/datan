<?php
  // Add a 'reading' field to the votes_datan database

  include('../bdd-connexion.php');
  
  $bdd->query('ALTER TABLE `circos` ADD INDEX `idx_insee` (`insee`);');
  $bdd->query('ALTER TABLE `insee` DROP INDEX `idx_insee`');
  $bdd->query('ALTER TABLE `insee` ADD INDEX `idx_insee` (`insee`);');
