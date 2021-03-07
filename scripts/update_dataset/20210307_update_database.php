<?php
  include('../bdd-connexion.php');

  $bdd->query('
    ALTER TABLE `votes` CHANGE `vote` `vote` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL
  ');

?>
