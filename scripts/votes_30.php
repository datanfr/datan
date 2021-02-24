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

  This script creates the file for opendata (MPs)'

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
				<h1>Export dataset with MPs for Open Data !</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./code_vote_10_class_participation_all.php" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="." role="button">END</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col-12">

        <?php


          // Connexion Database
        	include 'bdd-connexion.php';

          // filename for export
          $csv_filename = 'deputes_15.csv';

          // create var to be filled with export data
          $csv_export = '';

          // query to get data from database
          $query = $bdd->query('
          SELECT
          	da.mpId AS id,
              da.civ,
          	da.nameLast AS nom,
              da.nameFirst AS prenom,
              d.birthDate AS naissance,
              da.age,
              da.libelle AS groupe,
              da.libelleAbrev AS groupeAbrev,
              da.departementNom,
              da.departementCode,
              da.circo,
              da.datePriseFonction,
              d.job,
              dc.mailAn AS mail,
              dc.twitter,
              dc.facebook,
              dc.website,
              h.mandatesN AS nombreMandats,
              h.lengthEdited AS experienceDepute,
              cp.score AS scoreParticipation,
              cpm.score AS scoreParticipationSpecialite,
              cl.score AS scoreLoyaute,
              cm.score AS scoreMajorite,
              CASE WHEN da.dateFin IS NULL THEN 1 ELSE 0 END AS "active",
              da.dateMaj
          FROM deputes_all da
          LEFT JOIN class_participation_all cp ON da.mpId = cp.mpId
          LEFT JOIN class_participation_commission_all cpm ON da.mpId = cpm.mpId
          LEFT JOIN class_loyaute_all cl ON da.mpId = cl.mpId
          LEFT JOIN class_majorite_all cm ON da.mpId = cm.mpId
          LEFT JOIN deputes_contacts_cleaned dc ON da.mpId = dc.mpId
          LEFT JOIN history_per_mps_average h ON da.mpId = h.mpId
          LEFT JOIN deputes d ON da.mpId = d.mpId
          WHERE da.legislature = 15
          ');

          // Fetch the result
          $results = $query->fetchAll(PDO::FETCH_ASSOC);

          // Get the number of rows
          $number_of_rows = $query->columnCount();

          // Create line with field names
          $fields = [];
          foreach ($results[0] as $key => $value) {
            $fields[] = $key;
          }

          // Export the data
          $dir = __DIR__;
          $dir = str_replace(array("/", "scripts", ".php"), "", $dir);
          $dir = "../assets/opendata/";
          echo $dir;
          $fp = fopen($dir."".$csv_filename, "w");

          // Print the header
          fputcsv($fp, $fields);

          // Create new line with results
          foreach ($results as $key => $result) {
            fputcsv($fp, $result);
          }

          // CLose the file
          fclose($fp);



        ?>

        </div>
      </div>
    </div>
  </body>
</html>
