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

  This script creates the table 'class_groups'.
  The table merge information from the previous
  ** class_groups_cohesion
  ** clas_groups_majorite
  ** class_groups_participation

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

            $bdd->query('
              DROP TABLE IF EXISTS class_groups;
              CREATE TABLE class_groups AS
              SELECT c.*, p.participation, m.majoriteAccord, m.votesN, curdate() AS dateMaj
              FROM
              (
              	SELECT gc.organeRef, gc.legislature, ROUND(AVG(gc.cohesion),3) AS cohesion,
              	CASE WHEN o.dateFin IS NULL THEN 1 ELSE 0 END AS active
              	FROM groupes_cohesion gc
              	LEFT JOIN organes o ON o.uid = gc.organeRef
              	GROUP BY gc.organeRef
              ) c
              LEFT JOIN
              (
              SELECT B.organeRef, AVG(B.participation_rate) AS participation
              FROM
              (
              	SELECT A.*, A.total / A.n AS participation_rate
              	FROM
              	(
              		SELECT voteNumero, organeRef, nombreMembresGroupe as n, nombrePours as pour, nombreContres as contre, nombreAbstentions as abstention, nonVotants as nv, nonVotantsVolontaires as nvv, nombrePours+nombreContres+nombreAbstentions as total
              		FROM votes_groupes
              	) A
              ) B
              GROUP BY B.organeRef
              ) p ON p.organeRef = c.organeRef
              LEFT JOIN
              (
        			SELECT ga.organeRef, ROUND(AVG(ga.accord), 4) AS majoriteAccord, COUNT(ga.accord) AS votesN, CASE WHEN o.dateFin IS NULL THEN 1 ELSE 0 END AS active
        			FROM groupes_accord ga
        			LEFT JOIN organes o ON o.uid = ga.organeRef
        			WHERE organeRefAccord IN ("PO730964")
        			GROUP BY ga.organeRef
              ) m ON m.organeRef = c.organeRef;
              ALTER TABLE class_groups ADD INDEX idx_organeRef (organeRef);
              ALTER TABLE class_groups ADD INDEX idx_active (active);
            ');

          ?>

        </div>
      </div>
    </div>
  </body>
</html>
