<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>8_mandats</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script exports from the Open Data XML date regarding MPs' mandates in
  parliamentary groups ('GP'). The data is stored into 'mandat_group'.
  It also truncates the table 'mandat_group' first.

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_next = $url_current + 1;
    ?>
		<div class="container-fluid" style="background-color: #e9ecef;">
			<div class="row">
				<h1>8. Mise à jour base 'mandat_group'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_mandats.php" role="button">Next</a>
				</div>
			</div>
			<div class="row mt-3">
        <table class="table">
          <thead>
              <tr>
                <th scope="col">file</th>
                <th scope="col">mandatId</th>
                <th scope="col">mpId</th>
                <th scope="col">legislature</th>
                <th scope="col">typeOrgane</th>
                <th scope="col">dateDebut</th>
                <th scope="col">dateFin</th>
                <th scope="col">preseance</th>
                <th scope="col">nominPrincipale</th>
                <th scope="col">codeQualite</th>
                <th scope="col">libQualiteSex</th>
                <th scope="col">organe</th>
                <th scope="col">dateMaj</th>
              </tr>
            </thead>
            <tbody>
              <?php
              //$starttime = microtime(true);
              ini_set('memory_limit','500M');
              $dateMaj = date('Y-m-d');
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
                // AJOUTE SQL ICI //
                include 'bdd-connexion.php';
                $bdd->query("TRUNCATE TABLE mandat_groupe");
                $i = 1;
                $until = $zip->numFiles;

                for ($i=0; $i < $until ; $i++) {
                  $filename = $zip->getNameIndex($i);
                  //echo 'Filename: ' . $filename . '<br />';
                  $sub = substr($filename, 0, 13);
                  if ($sub == 'xml/acteur/PA') {
                    $xml_string = $zip->getFromName($filename);
                    if ($xml_string != false) {
                      $xml = simplexml_load_string($xml_string);
                      $mpId = str_replace("xml/acteur/", "", $filename);
                      $mpId = str_replace(".xml", "", $mpId);

                      foreach ($xml->mandats->mandat as $mandat) {
                        if ($mandat->typeOrgane == "GP") {
                          $mandatId = $mandat->uid;
                          $legislature = $mandat->legislature;
                          $typeOrgane = $mandat->typeOrgane;
                          $dateDebut = $mandat->dateDebut;
                          $dateFin = $mandat->dateFin;

                          if ($dateFin == "") {
                            $dateFin = NULL;
                          } else {
                            $dateFin = $dateFin;
                          }

                          $preseance = $mandat->preseance;
                          $nominPrincipale = $mandat->nominPrincipale;
                          $codeQualite = $mandat->infosQualite->codeQualite;
                          $libQualiteSex = $mandat->infosQualite->libQualiteSex;
                          $organe = $mandat->organes->organeRef;
                          ?>

                          <tr>
                            <td><?= $file ?></td>
                            <td><?= $mandatId ?></td>
                            <td><?= $mpId ?></td>
                            <td><?= $legislature ?></td>
                            <td><?= $typeOrgane ?></td>
                            <td><?= $dateDebut ?></td>
                            <td><?= $dateFin ?></td>
                            <td><?= $preseance ?></td>
                            <td><?= $nominPrincipale ?></td>
                            <td><?= $codeQualite ?></td>
                            <td><?= $libQualiteSex ?></td>
                            <td><?= $organe ?></td>
                            <td><?= $dateMaj ?></td>
                          </tr>

                          <?php

                          // AJOUTE SQL ICI //
                          include 'bdd-connexion.php';
                          $sql = $bdd->prepare("INSERT INTO mandat_groupe (
                            mandatId,
                            mpId,
                            legislature,
                            typeOrgane,
                            dateDebut,
                            dateFin,
                            preseance,
                            nominPrincipale,
                            codeQualite,
                            libQualiteSex,
                            organeRef,
                            dateMaj)
                            VALUES (
                              :mandatId,
                              :mpId,
                              :legislature,
                              :type_organe,
                              :date_debut,
                              :date_fin,
                              :preseance,
                              :nomin_principale,
                              :code_qualite,
                              :libQualiteSex,
                              :organeRef,
                              :dateMaj)");

                          $sql->execute(array(
                            'mandatId' => $mandatId,
                            'mpId' => $mpId,
                            'legislature' => $legislature,
                            'type_organe' => $typeOrgane,
                            'date_debut' => $dateDebut,
                            'date_fin' => $dateFin,
                            'preseance' => $preseance,
                            'nomin_principale' => $nominPrincipale,
                            'code_qualite' => $codeQualite,
                            'libQualiteSex' => $libQualiteSex,
                            'organeRef' => $organe,
                            'dateMaj' => $dateMaj
                          ));

                        }
                      }
                    }
                  }
                }
              }

              ?>
            </tbody>
        </table>
			</div>
		</div>
	</body>
</html>
