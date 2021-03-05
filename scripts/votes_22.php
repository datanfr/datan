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

  This script creates the table 'class_groups_proximite'

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
				<h1><?= $_SERVER['REQUEST_URI'] ?></h1>
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
          <h2 class="bg-success">Run this script only once.</h2>
          <?php

          // CONNEXION SQL //
          	include 'bdd-connexion.php';
            $bdd->query('DROP TABLE IF EXISTS class_groups_proximite');

            $bdd->query('
              CREATE TABLE class_groups_proximite AS
              SELECT  ga.organeRef, ga.organeRefAccord AS prox_group, ROUND(AVG(accord), 4) AS score, COUNT(accord) AS votesN, curdate() AS dateMaj
              FROM groupes_accord ga
              WHERE ga.organeRef != ga.organeRefAccord
              GROUP BY ga.organeRef, ga.organeRefAccord
            ');

            $bdd->query("ALTER TABLE class_groups_proximite ADD INDEX idx_organeRef (organeRef)");


          ?>

        </div>
      </div>
    </div>
  </body>
</html>
