<?php
  include('../bdd-connexion.php');

  $bdd->query('ALTER TABLE `elect_libelle` ADD `candidates` BOOLEAN NULL DEFAULT NULL AFTER `dateSecondRound`;');
  $bdd->query('UPDATE `elect_libelle` SET `candidates` = true WHERE `elect_libelle`.`id` = 1');
  $bdd->query('UPDATE `elect_libelle` SET `candidates` = false WHERE `elect_libelle`.`id` = 2');

  $bdd->query('ALTER TABLE `elect_deputes_candidats` ADD `secondRun` BOOLEAN NULL AFTER `visible`, ADD `elected` BOOLEAN NULL AFTER `secondRun`');
