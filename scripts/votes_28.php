<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
    <style>
      table, th, td {
          border: 1px solid black;
          border-collapse: collapse;
      }
    </style>
  </head>
  <!--

  This script creates the table 'history_mps_average'

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", "votes_", ".php"), "", $url);
      $url_current = substr($url, 0, 2);
      $url_second = $url_current + 1;

      include "include/legislature.php";
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>Create table history_mps_average</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./code_vote_10_class_participation_all.php" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
          <?php if ($legislature_to_get == 15): ?>
            <a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php" role="button">Next</a>
            <?php else: ?>
            <a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php?legislature=<?= $legislature_to_get ?>" role="button">Next</a>
          <?php endif; ?>
        </div>
			</div>
			<div class="row mt-3">
        <div class="col-12">
          <h2 class="bg-success">Run this script only once.</h2>
          <p class="bg-warning">This scripts does not depend on legislature. All legislatures are managed by a single script. </p>
          <?php

            // CONNEXION SQL //
          	include 'bdd-connexion.php';

            $bdd->query('DROP TABLE IF EXISTS history_mps_average;');

            $bdd->query('CREATE TABLE `history_mps_average` ( `id` TINYINT NOT NULL AUTO_INCREMENT , `legislature` TINYINT NOT NULL , `length` DECIMAL(4,2) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;');


            $terms = array(14, 15);

            foreach ($terms as $term) {
              echo "<p>get average for term => " . $term . "</p>";

              $bdd->query('
              INSERT INTO history_mps_average (legislature, length)
              SELECT "'.$term.'" AS legislature, ROUND(AVG(B.mpLength)/365, 2) as length
              FROM
              (
              	SELECT A.mpId, sum(A.duree) AS mpLength
              	FROM
              	(
              		SELECT m1.mpId, m1.legislature,
              		CASE
              		WHEN m1.dateFin IS NOT NULL THEN datediff(m1.dateFin, m1.datePriseFonction)
              		ELSE datediff(curdate(), m1.datePriseFonction)
              		END AS duree
              		FROM mandat_principal m1
              		LEFT JOIN deputes_all da ON m1.mpId = da.mpId AND da.legislature = "'.$term.'"
              		WHERE m1.codeQualite = "membre" AND m1.typeOrgane = "ASSEMBLEE" AND m1.legislature <= "'.$term.'"
              		ORDER BY m1.mpId
              	) A
              	GROUP BY A.mpId
              ) B
              ');

            }

            $bdd->query('ALTER TABLE history_mps_average ADD INDEX idx_legislature (legislature)');


          ?>

        </div>
      </div>
    </div>
  </body>
</html>
