<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table 'deputes_actifs'

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
				<h1><?= $_SERVER['REQUEST_URI'] ?></h1>
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
            $bdd->exec('DROP TABLE IF EXISTS deputes_actifs');
             $bdd->exec('
              CREATE TABLE deputes_actifs AS
              SELECT A.*, dpt.slug AS dptSlug, dpt.libelle_1 AS dptLibelle1, dpt.libelle_2 AS dptLibelle2, dpt.departement_nom AS departementNom, dpt.departement_code AS departementCode, mp.electionCirco AS circo, mp.datePriseFonction, mp.premiereElection, mp.placeHemicyle AS placeHemicycle,
              YEAR(current_timestamp()) - YEAR(A.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(A.birthDate) OR (MONTH(current_timestamp()) = MONTH(A.birthDate) AND DAY(current_timestamp()) < DAY(A.birthDate)) THEN 1 ELSE 0 END AS age,
              COUNT(mg.mandatId) AS groupesMandatsN,
              CURDATE() AS dateMaj
              FROM
              (
                SELECT d.nameLast, d.nameFirst, d.civ, d.nameUrl, d.birthDate, d.birthCity, d.birthCountry, mg.mpId, o.libelle, o.libelleAbrev, o.uid AS groupeId, o.couleurAssociee, mg.codeQualite, d.job
                FROM mandat_groupe mg
                LEFT JOIN deputes d ON d.mpId = mg.mpId
                LEFT JOIN organes o ON o.uid = mg.organeRef
                WHERE (mg.dateFin IS NULL) AND (mg.nominPrincipale = 1)
              ) A
              LEFT JOIN mandat_principal mp ON (mp.mpId = A.mpId) AND (mp.dateFin IS NULL) AND (mp.preseance = 50)
              LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
              LEFT JOIN mandat_groupe mg ON mp.mpId = mg.mpId
              WHERE mg.legislature = 15 AND mg.dateDebut != "2017-06-21" AND (DATEDIFF(mg.dateFin, mg.dateDebut) > 3 OR DATEDIFF(mg.dateFin, mg.dateDebut) IS NULL) AND mg.preseance >= 20
              GROUP BY mg.mpId
              ORDER BY A.nameLast ASC
             ');

             $bdd->exec('
             CREATE INDEX idx_mp ON deputes_actifs (nameUrl, dptSlug);
             CREATE INDEX idx_mpId ON deputes_actifs (mpId);
             CREATE INDEX idx_groupeId ON deputes_actifs (groupeId);
             ');
  				?>
        </div>
			</div>
		</div>
	</body>
</html>
