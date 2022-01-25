<?php
  // Add a 'insee' field to the circos table

  include('../bdd-connexion.php');


  $bdd->query('ALTER TABLE `circos` ADD `insee` VARCHAR(15) NULL DEFAULT NULL AFTER `commune`');
  $bdd->query('UPDATE circos SET insee =
    CASE
    	WHEN dpt = "971" AND commune < 110 THEN Concat(dpt, "0", commune - 100)
      WHEN dpt = "971" AND commune >= 110 THEN Concat(dpt, commune - 100)
      WHEN dpt = "972" AND commune < 210 THEN Concat(dpt, "0", commune - 200)
      WHEN dpt = "972" AND commune >= 210 THEN Concat(dpt, commune - 200)
      WHEN dpt = "973" AND commune < 310 THEN Concat(dpt, "0", commune - 300)
      WHEN dpt = "973" AND commune >= 310 THEN Concat(dpt, commune - 300)
    	WHEN dpt = "974" AND commune < 410 THEN Concat(dpt, "0", commune - 400)
      WHEN dpt = "974" AND commune >= 410 THEN Concat(dpt, commune - 400)
    	WHEN dpt = "976" AND commune < 610 THEN Concat(dpt, "0", commune - 600)
      WHEN dpt = "976" AND commune >= 610 THEN Concat(dpt, commune - 600)
    	WHEN dpt = "988" AND commune < 810 THEN Concat(dpt, "0", commune - 800)
      WHEN dpt = "988" AND commune >= 810 THEN Concat(dpt, commune - 800)
    	WHEN commune < 10 THEN Concat(dpt, "00", commune)
      WHEN commune < 100 THEN Concat(dpt, "0", commune)
      ELSE Concat(dpt, "", commune)
    END
  ');
