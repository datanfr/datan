<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!--<meta http-equiv="refresh" content="10">-->
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table 'dossiers'

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
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
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
          <p>Legislature to get = <?= $legislature_to_get ?></p>
          <table class="table">
            <thead>
                <tr>
                  <th scope="col">#</th>
                  <th>dossierId</th>
                  <th>legislature</th>
                  <th>titre</th>
                  <th>titreChemin</th>
                  <th>senatChemin</th>
                  <th>procedureParlementaireCode</th>
                  <th>procedureParlementaireLibelle</th>
                  <th>jo</th>
                  <th>commissionFond</th>
                </tr>
              </thead>
              <tbody>
        				<?php
          				ini_set('display_errors', 1);
                  ini_set('memory_limit','500M');
          				error_reporting(E_ALL);
          				include 'bdd-connexion.php';

                  $bdd->query('
                    DELETE FROM dossiers WHERE legislature = "'.$legislature_to_get.'"
                  ');

                  if ($legislature_to_get == 15) {

                    // Online file
                    $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/dossiers_legislatifs/Dossiers_Legislatifs_XV.xml.zip';
                    $file = trim($file);
                    $newfile = 'tmp_dossiers.zip';
                    if (!copy($file, $newfile)) {
                      echo "failed to copy $file...\n";
                    }

                    $zip = new ZipArchive();
                    if ($zip->open($newfile)!==TRUE) {
                      exit("cannot open <$filename>\n");
                    } else {

                      for ($i=0; $i < $zip->numFiles ; $i++) {
                        $filename = $zip->getNameIndex($i);
                        //echo 'Filename: ' . $filename . '<br />';
                        $sub = substr($filename, 0, 13);

                        if ($sub == 'xml/dossierPa') {
                          $xml_string = $zip->getFromName($filename);

                          if ($xml_string != false) {
                            $xml = simplexml_load_string($xml_string);

                            $dossierId = $xml->uid;
                            $legislature = $xml->legislature;
                            $titre = $xml->titreDossier->titre;
                            $titreChemin = $xml->titreDossier->titreChemin;
                            $senatChemin = $xml->titreDossier->senatChemin;
                            $procedureParlementaireCode = $xml->procedureParlementaire->code;
                            $procedureParlementaireLibelle = $xml->procedureParlementaire->libelle;

                            $commissionFond = $xml->xpath("//*[text()='AN1-COM-FOND']/parent::*[local-name()='acteLegislatif']/*[local-name()='organeRef']");
                            if (!empty($commissionFond)) {
                              $commissionFond = $commissionFond[0];
                            } else {
                              $commissionFond = NULL;
                            }


                            //INITIATEUR (BUT DIFFERENT ONES?)
                            //GET REPORT TEXT
                            //GET FINAL TEXT / JO promulgation.;

                            ?>

                            <tr>
                              <td><?= $i ?></td>
                              <td><?= $dossierId ?></td>
                              <td><?= $legislature ?></td>
                              <td><?= $titre ?></td>
                              <td><?= $titreChemin ?></td>
                              <td><?= $senatChemin ?></td>
                              <td><?= $procedureParlementaireCode ?></td>
                              <td><?= $procedureParlementaireLibelle ?></td>
                              <td>jo</td>
                              <td><?= $commissionFond ?></td>
                            </tr>

                            <?php

                            // INSERT INTO SQLiteDatabase
                            $sql = $bdd->prepare("INSERT INTO dossiers (dossierId, legislature, titre, titreChemin, senatChemin, procedureParlementaireCode, procedureParlementaireLibelle, commissionFond) VALUES (:dossierId, :legislature, :titre, :titreChemin, :senatChemin, :procedureParlementaireCode, :procedureParlementaireLibelle, :commissionFond)");
                            $sql->execute(array('dossierId' => $dossierId, 'legislature' => $legislature, 'titre' => $titre, 'titreChemin' => $titreChemin, 'senatChemin' => $senatChemin, 'procedureParlementaireCode' => $procedureParlementaireCode, 'procedureParlementaireLibelle' => $procedureParlementaireLibelle, 'commissionFond' => $commissionFond));

                          }
                        }
                      }
                    }
                  } elseif ($legislature_to_get == 14) {

                    // Online file
                    $file = 'https://data.assemblee-nationale.fr/static/openData/repository/14/loi/dossiers_legislatifs/Dossiers_Legislatifs_XIV.xml.zip';
                    $file = trim($file);
                    $newfile = 'tmp_dossiers_14.zip';
                    if (!copy($file, $newfile)) {
                      echo "failed to copy $file...\n";
                    }

                    $zip = new ZipArchive();
                    if ($zip->open($newfile)!==TRUE) {
                      exit("cannot open <$filename>\n");
                    } else {
                      $xml_string = $zip->getFromName("Dossiers_Legislatifs_XIV.xml");
                      if ($xml_string != false) {
                        $xml = simplexml_load_string($xml_string);

                        $i = 1;

                        foreach ($xml->xpath("//*[local-name()='dossierParlementaire']") as $dossier) {
                          $dossierId = $dossier->uid;
                          $legislature = $dossier->legislature;
                          $titre = $dossier->titreDossier->titre;
                          $titreChemin = $dossier->titreDossier->titreChemin;
                          $senatChemin = $dossier->titreDossier->senatChemin;
                          $procedureParlementaireCode = $dossier->procedureParlementaire->code;
                          $procedureParlementaireLibelle = $dossier->procedureParlementaire->libelle;

                          $commissionFond = $dossier->xpath(".//*[text()='AN1-COM-FOND']/parent::*[local-name()='acteLegislatif']/*[local-name()='organeRef']");
                          if (!empty($commissionFond)) {
                            $commissionFond = $commissionFond[0];
                          } else {
                            $commissionFond = NULL;
                          }

                          ?>

                          <tr>
                            <td><?= $i ?></td>
                            <td><?= $dossierId ?></td>
                            <td><?= $legislature ?></td>
                            <td><?= $titre ?></td>
                            <td><?= $titreChemin ?></td>
                            <td><?= $senatChemin ?></td>
                            <td><?= $procedureParlementaireCode ?></td>
                            <td><?= $procedureParlementaireLibelle ?></td>
                            <td>jo</td>
                            <td><?= $commissionFond ?></td>
                          </tr>

                          <?php

                          $i++;

                          // INSERT INTO SQLiteDatabase
                          $sql = $bdd->prepare("INSERT INTO dossiers (dossierId, legislature, titre, titreChemin, senatChemin, procedureParlementaireCode, procedureParlementaireLibelle, commissionFond) VALUES (:dossierId, :legislature, :titre, :titreChemin, :senatChemin, :procedureParlementaireCode, :procedureParlementaireLibelle, :commissionFond)");
                          $sql->execute(array('dossierId' => $dossierId, 'legislature' => $legislature, 'titre' => $titre, 'titreChemin' => $titreChemin, 'senatChemin' => $senatChemin, 'procedureParlementaireCode' => $procedureParlementaireCode, 'procedureParlementaireLibelle' => $procedureParlementaireLibelle, 'commissionFond' => $commissionFond));


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
