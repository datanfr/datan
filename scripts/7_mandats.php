<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>7_mandats</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates get the data from the 'AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml'
  file and stores the data in the table 'mandat_principal'. It takes only the mandates 'ASSEMBLEE'
  It also truncate the table 'mandat_principal' first.

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
				<h1>7. Mise à jour base 'mandat_principal'</h1>
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
                <th scope="col">electionRegion</th>
                <th scope="col">electionRegionType</th>
                <th scope="col">electionDepartement</th>
                <th scope="col">electionDepartementNumero</th>
                <th scope="col">eletionCirco</th>
                <th scope="col">datePriseFonction</th>
                <th scope="col">causeFin</th>
                <th scope="col">premiereElection</th>
                <th scope="col">placeHemicycle</th>
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
                $bdd->query("TRUNCATE TABLE mandat_principal");
                $i = 1;
                $until = $zip->numFiles;
                for ($i=0; $i < $until ; $i++) {
                  $filename = $zip->getNameIndex($i);
                  $sub = substr($filename, 0, 13);
                  if ($sub == 'xml/acteur/PA') {
                    $xml_string = $zip->getFromName($filename);
                    if ($xml_string != false) {
                      $xml = simplexml_load_string($xml_string);
                      $mpId = str_replace("xml/acteur/", "", $filename);
                      $mpId = str_replace(".xml", "", $mpId);
                      foreach ($xml->mandats->mandat as $mandat) {
                        if ($mandat->typeOrgane == "ASSEMBLEE") {
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

                          if (isset($mandat->election->lieu->region)) {
                            $electionRegion = $mandat->election->lieu->region;
                          } else {
                            $electionRegion = NULL;
                          }

                          if (isset($mandat->election->lieu->regionType)) {
                            $electionRegionType = $mandat->election->lieu->regionType;
                          } else {
                            $electionRegionType = NULL;
                          }

                          if (isset($mandat->election->lieu->departement)) {
                            $departement = $mandat->election->lieu->departement;
                            $numDepartement = $mandat->election->lieu->numDepartement;
                            $numCirco = $mandat->election->lieu->numCirco;
                          } else {
                            $departement = NULL;
                            $numDepartement = NULL;
                            $numCirco = NULL;
                          }

                          if (isset($mandat->mandature)) {
                            $datePriseFonction = $mandat->mandature->datePriseFonction;
                            $causeFin = $mandat->mandature->causeFin;
                            $premiereElection = $mandat->mandature->premiereElection;
                          } else {
                            $datePriseFonction = NULL;
                            $causeFin = NULL;
                            $premiereElection = NULL;
                          }

                          if (isset($mandat->mandature->placeHemicycle)) {
                            $placeHemicycle = $mandat->mandature->placeHemicycle;
                          } else {
                            $placeHemicycle = NULL;
                          }

                          if ($datePriseFonction == "") {
                            $datePriseFonction = NULL;
                          } else {
                            $datePriseFonction = $datePriseFonction;
                          }

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
                            <td><?= $electionRegion ?></td>
                            <td><?= $electionRegionType ?></td>
                            <td><?= $departement ?></td>
                            <td><?= $numDepartement ?></td>
                            <td><?= $numCirco ?></td>
                            <td><?= $datePriseFonction ?></td>
                            <td><?= $causeFin ?></td>
                            <td><?= $premiereElection ?></td>
                            <td><?= $placeHemicycle ?></td>
                            <td><?= $dateMaj ?></td>
                          </tr>

                          <?php

                          // AJOUTE SQL ICI //
                          include 'bdd-connexion.php';
                          $sql = $bdd->prepare("INSERT INTO mandat_principal (
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
                            organe,
                            electionRegion,
                            electionRegionType,
                            electionDepartement,
                            electionDepartementNumero,
                            electionCirco,
                            datePriseFonction,
                            causeFin,
                            premiereElection,
                            placeHemicyle,
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
                              :organe,
                              :election_region,
                              :election_region_type,
                              :election_departement,
                              :election_departement_numero,
                              :election_circo,
                              :prise_fonction,
                              :causeFin,
                              :premiere_election,
                              :placeHemicyle,
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
                            'organe' => $organe,
                            'election_region' => $electionRegion,
                            'election_region_type' => $electionRegionType,
                            'election_departement' => $departement,
                            'election_departement_numero' => $numDepartement,
                            'election_circo' => $numCirco,
                            'prise_fonction' => $datePriseFonction,
                            'causeFin' => $causeFin,
                            'premiere_election' => $premiereElection,
                            'placeHemicyle' => $placeHemicycle,
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
