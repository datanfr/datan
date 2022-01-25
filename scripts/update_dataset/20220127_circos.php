<?php
  // Add a 'insee' field to the circos table

  include('../bdd-connexion.php');


  $bdd->query('ALTER TABLE `circos` ADD `insee` VARCHAR(15) NULL DEFAULT NULL AFTER `commune`');
  $bdd->query('UPDATE circos SET insee =
    CASE
      WHEN commune < 10 THEN Concat(dpt, "00", commune)
      WHEN commune < 100 THEN Concat(dpt, "0", commune)
      ELSE Concat(dpt, "", commune)
    END
  ');
