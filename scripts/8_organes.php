<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>8_organes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script exports data from the XML into the table 'organes'.
  It also truncates the table 'organes' before updating it.

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
				<h1>8. Mise à jour base 'organes'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_organes.php" role="button">Next</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">file</th>
                <th scope="col">uid</th>
                <th scope="col">codeType</th>
                <th scope="col">libelle</th>
                <th scope="col">libelleEdition</th>
                <th scope="col">libelleAbrege</th>
                <th scope="col">libelleAbrev</th>
                <th scope="col">dateDebut</th>
                <th scope="col">dateFin</th>
                <th scope="col">legislature</th>
                <th scope="col">positionPolitique</th>
                <th scope="col">preseance</th>
                <th scope="col">couleurAssociee</th>
              </tr>
            </thead>
            <tbody>
              <?php
              ini_set('memory_limit','500M');
              $dateMaj = date('Y-m-d');
              include 'bdd-connexion.php';
              $bdd->query("TRUNCATE TABLE organes");

              //Online file
              $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
              $file = trim($file);
              $newfile = 'tmp_acteurs_organes.zip';
              if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
              }

              $zip = new ZipArchive();
              if ($zip->open($newfile)!==TRUE) {
                exit("cannot open <$filename>\n");
              } else {

                for ($i=0; $i < $zip->numFiles ; $i++) {
                  $filename = $zip->getNameIndex($i);
                  $sub = substr($filename, 0, 13);

                  if ($sub == 'xml/organe/PO') {
                    $xml_string = $zip->getFromName($filename);

                    if ($xml_string != false) {
                      $xml = simplexml_load_string($xml_string);
                      $uid = str_replace("xml/organe/", "", $filename);
                      $uid = str_replace(".xml", "", $uid);
                      $codeType = $xml->codeType;
                      $libelle = $xml->libelle;
                      $libelleEdition = $xml->libelleEdition;
                      $libelleAbrege = $xml->libelleAbrege;
                      $libelleAbrev = $xml->libelleAbrev;

                      if (isset($xml->viMoDe->dateDebut) && $xml->viMoDe->dateDebut != "") {
                        $dateDebut = $xml->viMoDe->dateDebut;
                      } else {
                        $dateDebut = NULL;
                      }

                      if (isset($xml->viMoDe->dateFin) && $xml->viMoDe->dateFin != "") {
                        $dateFin = $xml->viMoDe->dateFin;
                      } else {
                        $dateFin = NULL;
                      }

                      if (isset($xml->legislature) && $xml->legislature != "") {
                        $legislature = $xml->legislature;
                      } else {
                        $legislature = NULL;
                      }

                      $positionPolitique = $xml->positionPolitique;
                      $preseance = $xml->preseance;
                      $couleurAssociee = $xml->couleurAssociee;

                      ?>

                      <tr>
                        <td><?= $file ?></td>
                        <td><?= $uid ?></td>
                        <td><?= $codeType ?></td>
                        <td><?= $libelle ?></td>
                        <td><?= $libelleEdition ?></td>
                        <td><?= $libelleAbrege ?></td>
                        <td><?= $libelleAbrev ?></td>
                        <td><?= $dateDebut ?></td>
                        <td><?= $dateFin ?></td>
                        <td><?= $legislature ?></td>
                        <td><?= $positionPolitique ?></td>
                        <td><?= $preseance ?></td>
                        <td><?= $couleurAssociee ?></td>
                      </tr>

                      <?php

                      // PLUS qu'à parser //
                      $sql = $bdd->prepare("INSERT INTO organes (uid, coteType, libelle, libelleEdition, libelleAbrev, libelleAbrege, dateDebut, dateFin, legislature, positionPolitique, preseance, couleurAssociee, dateMaj) VALUES (:uid, :coteType, :libelle, :libelleEdition, :libelleAbrev, :libelleAbrege, :dateDebut, :dateFin, :legislature, :positionPolitique, :preseance, :couleurAssociee, :dateMaj)");
                      $sql->execute(array('uid' => $uid, 'coteType' => $codeType, 'libelle' => $libelle, 'libelleEdition' => $libelleEdition, 'libelleAbrev' => $libelleAbrev, 'libelleAbrege' => $libelleAbrege, 'dateDebut' => $dateDebut, 'dateFin' => $dateFin, 'legislature' => $legislature, 'positionPolitique' => $positionPolitique, 'preseance' => $preseance, 'couleurAssociee' => $couleurAssociee, 'dateMaj' => $dateMaj));

                    }

                  }

                }
              }


              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
