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

  This script creates the table 'deputes_all'
  Evolution of this script in order to take all MPs
  (not only the current legislature).

  Change the name of the table from deputes_inactifs to deputes_all.

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
            $bdd->exec('DROP TABLE IF EXISTS deputes_inactifs');
             $bdd->exec('
              CREATE TABLE deputes_inactifs AS
              SELECT d.mpId, d.civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dptSlug, dpt.libelle_1 AS dptLibelle1, dpt.libelle_2 AS dptLibelle2, dpt.departement_nom AS departementNom, dpt.departement_code AS departementCode,  mp.electionCirco AS circo, mp.premiereElection, mp.dateFin AS dateFinMP, mp.causeFin, d.birthDate, d.birthCity, d.birthCountry, d.job,
              YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age,
              CURDATE() AS dateMaj
              FROM mandat_principal mp
              LEFT JOIN deputes d ON d.mpId = mp.mpId
              LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
              LEFT JOIN deputes_contacts_cleaned dc ON dc.mpId = mp.mpId
              WHERE mp.legislature = 15
              AND mp.dateFin IS NOT NULL
              AND mp.codeQualite = "membre"
              AND mp.mpId NOT IN (SELECT d.mpId FROM mandat_principal mp2 LEFT JOIN deputes d ON d.mpId = mp2.mpId WHERE mp2.legislature = 15 AND mp2.dateFin IS NULL AND mp2.codeQualite = "membre")
             ');

             $bdd->exec('
             CREATE INDEX idx_mp ON deputes_inactifs (nameUrl, dptSlug);
             ');
  				?>
        </div>
			</div>
		</div>
	</body>
</html>
