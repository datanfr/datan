<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>9_organes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table 'groupes_effectif'

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_next = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>9. Create groupes_effectif table</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_deputes.php" role="button">Next</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <?php

          	include 'bdd-connexion.php';

          	$bdd->query('
                DROP TABLE IF EXISTS groupes_effectif;
                CREATE TABLE groupes_effectif AS
                SELECT @s:=@s+1 AS "classement", A.*, CURDATE() AS dateMaj
                FROM
                (
                SELECT t1.organeRef, o.libelle, count(t1.mpId) AS effectif, t1.legislature
                FROM mandat_groupe t1
                LEFT JOIN organes o ON t1.organeRef = o.uid
                WHERE t1.typeOrgane = "GP" AND t1.codeQualite != "Président" AND t1.dateFin IS NULL
                GROUP BY t1.organeRef, t1.legislature
                ) A,
                (SELECT @s:= 0) AS s
                ORDER BY A.effectif DESC;
                ALTER TABLE groupes_effectif ADD INDEX idx_organeRef (organeRef);
          		');

          	echo '<hr>';




          ?>
        </div>
      </div>
    </div>
  </body>
</html>
