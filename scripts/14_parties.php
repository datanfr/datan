<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>14_parties</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table parties

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 2);
      $url_next = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>14. Create parties table</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./" role="button">END</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <p>Create table parties</p>
          <?php
            include 'bdd-connexion.php';

            $bdd->exec('DROP TABLE IF EXISTS parties');

            $bdd->exec('
            CREATE TABLE parties AS
            SELECT A.*, B.effectif
            FROM
            (
            SELECT o.uid, o.libelleAbrev, o.libelle, o.dateFin, COUNT(ms.mpId) AS effectifTotal
            FROM organes o
            LEFT JOIN mandat_secondaire ms ON o.uid = ms.organeRef
            WHERE o.coteType = "PARPOL" AND (ms.mpId IN (select mpId from deputes_actifs) OR ms.mpId IN (select mpId from deputes_inactifs))
            GROUP BY o.uid
            ) A
            LEFT JOIN (SELECT o.uid, o.libelle, o.libelleAbrev, COUNT(ms.mpId) AS effectif
            FROM deputes_actifs da
            LEFT JOIN mandat_secondaire ms ON ms.mpId = da.mpId
            LEFT JOIN organes o ON o.uid = ms.organeRef
            WHERE ms.typeOrgane = "PARPOL" AND ms.dateFin IS NULL
            GROUP BY ms.organeRef) B ON A.uid = B.uid
            ORDER BY B.effectif DESC
            ');

            $bdd->exec('
            CREATE INDEX idx_uid ON parties (uid);
            ');


          ?>
        </div>
			</div>
		</div>
	</body>
</html>
