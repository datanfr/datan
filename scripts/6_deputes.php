<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>6_deputes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the new table 'deputes_contacts_cleaned' from the table
  'deputes_contacts'

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
				<h1>6. Création de la nouvelle table 'deputes_contacts_cleaned'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_mandats.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
  				<?php
            include 'bdd-connexion.php';
            $bdd->exec('DROP TABLE IF EXISTS deputes_contacts_cleaned');
            $bdd->exec('
              CREATE TABLE deputes_contacts_cleaned AS
              SELECT A.*, t2.valElec AS website, t3.valElec AS mailAn, t4.valElec AS mailPerso, t5.valElec AS twitter, t6.valElec AS facebook, CURDATE() AS dateMaj
              FROM
              (
              SELECT mpId
              FROM deputes_contacts t1
              GROUP BY mpId
              ) A
              LEFT JOIN deputes_contacts t2 ON t2.mpId = A.mpId AND t2.type = 22
              LEFT JOIN deputes_contacts t3 ON t3.mpId = A.mpId AND t3.type = 15 AND t3.valElec LIKE "%nationale.fr%"
              LEFT JOIN deputes_contacts t4 ON t4.mpId = A.mpId AND t4.type = 15 AND t4.valElec NOT LIKE "%nationale.fr%"
              LEFT JOIN deputes_contacts t5 ON t5.mpId = A.mpId AND t5.type = 24
              LEFT JOIN deputes_contacts t6 ON t6.mpId = A.mpId AND t6.type = 25
              GROUP BY A.mpId
             ');
            $bdd->exec('CREATE INDEX idx_mpId ON deputes_contacts_cleaned(mpId)');
  				?>
        </div>
			</div>
		</div>
	</body>
</html>
