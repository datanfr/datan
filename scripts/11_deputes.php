<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>14_deputes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table 'deputes_last'
  Evolution of this script in order to take all MPs
  (not only the current legislature).

  Change the name of the table from deputes_inactifs to deputes_last.

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
				<h1>12. Create deputes_inactifs table</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_deputes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
  				<?php
            include 'bdd-connexion.php';
            $bdd->exec('DROP TABLE IF EXISTS deputes_last');
            $bdd->exec('
              CREATE TABLE deputes_last AS
              SELECT A.mpId, d.civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dptSlug, dpt.libelle_1 AS dptLibelle1, dpt.libelle_2 AS dptLibelle2, dpt.departement_nom AS departementNom, dpt.departement_code AS departementCode, A.electionCirco AS circo, A.premiereElection, A.mandatId, A.legislature, A.dateFin AS dateFinMP, A.causeFin, d.birthDate, d.birthCity, d.birthCountry, d.job,
              YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age, A.datePriseFonction,
              CASE WHEN (legislature = 15 AND dateFin IS NULL) THEN 1 ELSE 0 END AS active,
              CURDATE() AS dateMaj
              FROM
              (
              SELECT *, case when dateFin IS NULL THEN "ongoing" ELSE dateFin END AS dateFinJoin
              FROM mandat_principal
              WHERE preseance = 50
              ) A
              JOIN
              (
              SELECT mpId, case when MAX(dateFin IS NULL) = 0 THEN max(dateFin) ELSE "ongoing" END AS legislatureLastDate
              FROM mandat_principal
              GROUP BY mpId
              ) x ON A.mpId = x.mpId AND A.dateFinJoin = x.legislatureLastDate
              LEFT JOIN deputes d ON A.mpId = d.mpId
              LEFT JOIN departement dpt ON A.electionDepartementNumero = dpt.departement_code
            ');

             $bdd->exec('
             CREATE INDEX idx_mp ON deputes_last (nameUrl, dptSlug);
             ');
  				?>
        </div>
			</div>
		</div>
	</body>
</html>
