<?php
  include('../bdd-connexion.php');

  $bdd->query('UPDATE `elect_libelle` SET `dateFirstRound` = "2021-06-20" WHERE `elect_libelle`.`id` IN (1,2)');
  $bdd->query('UPDATE `elect_libelle` SET `dateSecondRound` = "2021-06-27" WHERE `elect_libelle`.`id` IN (1,2)');
