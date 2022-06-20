<?php
  include('../bdd-connexion.php');

  $bdd->query('UPDATE `elect_libelle` SET `resultsUrl` = "https://www.resultats-elections.interieur.gouv.fr/legislatives-2022/" WHERE `elect_libelle`.`id` = 4');
