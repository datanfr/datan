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

  This script creates the table 'history_per_mps_average'

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", "votes_", ".php"), "", $url);
      $url_current = substr($url, 0, 2);
      $url_second = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>Create table history_per_mps_average</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./code_vote_10_class_participation_all.php" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col-12">
        <?php

        // CONNEXION SQL //
        	include 'bdd-connexion.php';

          $bdd->query('
            DROP TABLE IF EXISTS history_per_mps_average;
            CREATE TABLE history_per_mps_average AS
            SELECT B.*,
                  	CASE
                  	WHEN ROUND(B.mpLength/365) = 1 THEN CONCAT(ROUND(B.mpLength/365), " an")
                      WHEN ROUND(B.mpLength/365) > 1 THEN CONCAT(ROUND(B.mpLength/365), " ans")
                      WHEN ROUND(B.mpLength/30) != 0 THEN CONCAT(ROUND(B.mpLength/30), " mois")
                      ELSE CONCAT(B.mpLength, " jours")
                      END AS lengthEdited, CURDATE() AS dateMaj
                  FROM
                  (
                  	SELECT A.mpId,
                  		SUM(A.length) AS mpLength,
            			count(distinct(A.legislature)) AS mandatesN
                        FROM
                        (
                        	SELECT mp.legislature, mp.mpId,
                        		CASE
                        		WHEN mp.dateFin IS NOT NULL THEN datediff(mp.dateFin, mp.datePriseFonction)
                        		ELSE datediff(curdate(), mp.datePriseFonction)
                        	END AS length
                        	FROM mandat_principal mp
                        	WHERE mp.typeOrgane = "ASSEMBLEE" AND mp.codeQualite = "membre"
                        ) A
                        GROUP BY A.mpId
                  ) B;
              ALTER TABLE history_per_mps_average ADD INDEX idx_mpId (mpId);

          ');

        ?>

        </div>
      </div>
    </div>
  </body>
</html>
