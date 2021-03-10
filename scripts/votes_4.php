<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="5">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script updates the table 'votes_groupes'

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", "votes_", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
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
        <h2 class="bg-danger">This script needs to be refreshed until the table below is empty. The scripts automatically is automatically refreshed every 5 seconds.</h2>
        <table class="table">
          <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">uidVote</th>
                <th scope="col">numero</th>
                <th scope="col">legislature</th>
                <th scope="col">organeRef</th>
                <th scope="col">nombreMembresGroupe</th>
                <th scope="col">positionMajoritaire</th>
                <th scope="col">nombrePours</th>
                <th scope="col">nombreContres</th>
                <th scope="col">nombreAbstentions</th>
                <th scope="col">nonVotants</th>
                <th scope="col">nonVotantsVolontaires</th>
              </tr>
            </thead>
            <tbody>
              <?php

                include 'bdd-connexion.php';

                $reponse_vote = $bdd->query('
                  SELECT voteNumero
                  FROM votes_groupes
                  WHERE legislature = "'.$legislature_to_get.'"
                  ORDER BY voteNumero DESC
                  LIMIT 1
                ');
                while ($dernier_vote = $reponse_vote->fetch() ) {
                  $last_vote = $dernier_vote['voteNumero'];
                }
                //$last_vote = 0;
                if (!isset($last_vote)) {
                  $number_to_import = 1;
                } else {
                  $number_to_import = $last_vote + 1;
                }

                echo "FIRST VOTE TO IMPORT = ".$number_to_import;
                $until = $number_to_import + 300;
                echo "UNTIL WHICH VOTE TO IMPORT = ".$until;

                // SCRAPPING DEPENDING ON LEGISLATURE
                if ($legislature_to_get == 15) {

                  // Online File
                  $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/scrutins/Scrutins_XV.xml.zip';
                  $file = trim($file);
                  $newfile = 'tmp_Scrutins_XV.xml.zip';
          				if (!copy($file, $newfile)) {
          					echo "failed to copy $file...\n";
          				}

                  $zip = new ZipArchive();
          				if ($zip->open($newfile)!==TRUE) {
          						exit("cannot open <$filename>\n");
          					} else {

                      foreach (range($number_to_import, $until) as $n) {
                        $file_to_import = 'VTANR5L15V'.$n;
                        $xml_string = $zip->getFromName('xml/'.$file_to_import.'.xml');
                        if ($xml_string != false) {
                          $xml = simplexml_load_string($xml_string);

                          $i = 1;

                          foreach ($xml->xpath("//*[local-name()='groupe']") as $groupe) {
                            $voteId = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='uid']");
                            $item['voteId'] = $voteId[0];

                            $voteNumero = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='numero']");
                            $item['voteNumero'] = $voteNumero[0];

                            $legislature = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='legislature']");
                            $item['legislature'] = $legislature[0];

                            $organeRef = $groupe->xpath("./*[local-name()='organeRef']");
                            $item['organeRef'] = $organeRef[0];

                            $nombreMembresGroupe = $groupe->xpath("./*[local-name()='nombreMembresGroupe']");
                            $item['nombreMembresGroupe'] = $nombreMembresGroupe[0];

                            $positionMajoritaire = $groupe->xpath("./*[local-name()='vote']/*[local-name()='positionMajoritaire']");
                            $item['positionMajoritaire'] = $positionMajoritaire[0];

                            $nombrePours = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='pour']");
                            $item['nombrePours'] = $nombrePours[0];

                            $nombreContres = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='contre']");
                            $item['nombreContres'] = $nombreContres[0];

                            $nombreAbstentions = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='abstentions']");
                            $item['nombreAbstentions'] = $nombreAbstentions[0];

                            $nonVotants = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotants']");
                            $item['nonVotants'] = $nonVotants[0];

                            $nonVotantsVolontaires = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotantsVolontaires']");
                            $item['nonVotantsVolontaires'] = $nonVotantsVolontaires[0];

                            $total_votant = $item['nombrePours']+$item['nombreContres']+$item['nombreAbstentions'];
                            if ($total_votant == '0') {
                              $positionMajoritaire = 'nv';
                            } else {
                              $positionMajoritaire = $item['positionMajoritaire'];
                            }

                              ?>
                              <tr>
                                <td><?= $i ?></td>
                                <td><?= $item['voteId'] ?></td>
                                <td><?= $item['voteNumero'] ?></td>
                                <td><?= $item['legislature'] ?></td>
                                <td><?= $item['organeRef'] ?></td>
                                <td><?= $item['nombreMembresGroupe'] ?></td>
                                <td><?= $positionMajoritaire ?></td>
                                <td><?= $item['nombrePours'] ?></td>
                                <td><?= $item['nombreContres'] ?></td>
                                <td><?= $item['nombreAbstentions'] ?></td>
                                <td><?= $item['nonVotants'] ?></td>
                                <td><?= $item['nonVotantsVolontaires'] ?></td>
                              </tr>
                              <?php

                              $sql = $bdd->prepare("INSERT INTO votes_groupes (voteId, voteNumero, legislature, organeRef, nombreMembresGroupe, positionMajoritaire, nombrePours, nombreContres, nombreAbstentions, nonVotants, nonVotantsVolontaires) VALUES (:voteId, :voteNumero, :legislature, :organeRef, :nombreMembresGroupe, :positionMajoritaire, :nombrePours, :nombreContres, :nombreAbstentions, :nonVotants, :nonVotantsVolontaires)");
                              $sql->execute(array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'legislature' => $item['legislature'] ,'organeRef' => $item['organeRef'], 'nombreMembresGroupe' => $item['nombreMembresGroupe'], 'positionMajoritaire' => $positionMajoritaire, 'nombrePours' => $item['nombrePours'], 'nombreContres' => $item['nombreContres'], 'nombreAbstentions' => $item['nombreAbstentions'], 'nonVotants' => $item['nonVotants'], 'nonVotantsVolontaires' => $item['nonVotantsVolontaires']));

                              $i++;
                          }
                        }
                      }
                      $zip->close();
                    }

                } else {

                  $file = 'http://data.assemblee-nationale.fr/static/openData/repository/14/loi/scrutins/Scrutins_XIV.xml.zip';
                  $file = trim($file);
                  $newfile = 'tmp_Scrutins_XIV.xml.zip';
                  if (!copy($file, $newfile)) {
                    echo "failed to copy $file...\n";
                  }
                  $zip = new ZipArchive();

                  if ($zip->open($newfile)!==TRUE) {
                    exit("cannot open <$filename>\n");
                  } else {

                    $i = 1;

                    $xml_string = $zip->getFromName('Scrutins_XIV.xml');
                    if ($xml_string != false) {

                      $xml = simplexml_load_string($xml_string);

                      foreach ($xml->xpath('//groupe/ancestor::scrutin[(numero>='. $number_to_import .') and (numero<='. $until.')]') as $xml2) {

                        foreach ($xml2->xpath('.//groupe') as $groupe) {

                          $voteId = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='uid']");
                          $item['voteId'] = $voteId[0];

                          $voteNumero = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='numero']");
                          $item['voteNumero'] = $voteNumero[0];

                          $organeRef = $groupe->xpath("./*[local-name()='organeRef']");
                          $item['organeRef'] = $organeRef[0];

                          $nombreMembresGroupe = $groupe->xpath("./*[local-name()='nombreMembresGroupe']");
                          $item['nombreMembresGroupe'] = $nombreMembresGroupe[0];

                          $positionMajoritaire = $groupe->xpath("./*[local-name()='vote']/*[local-name()='positionMajoritaire']");
                          $item['positionMajoritaire'] = $positionMajoritaire[0];

                          $nombrePours = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='pour']");
                          $item['nombrePours'] = $nombrePours[0];

                          $nombreContres = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='contre']");
                          $item['nombreContres'] = $nombreContres[0];

                          $nombreAbstentions = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='abstention']");
                          $item['nombreAbstentions'] = $nombreAbstentions[0];

                          $nonVotants = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotant']");
                          if (isset($nonVotants[0])) {
                            $item['nonVotants'] = $nonVotants[0];
                          } else {
                            $nonVotants = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotants']");
                            if (isset($nonVotants[0])) {
                              $item['nonVotants'] = $nonVotants[0];
                            } else {
                              $item['nonVotants'] = null;
                            }
                          }

                          $total_votant = $item['nombrePours']+$item['nombreContres']+$item['nombreAbstentions'];
                          if ($total_votant == '0') {
                            $positionMajoritaire = 'nv';
                          } else {
                            $positionMajoritaire = $item['positionMajoritaire'];
                          }

                          ?>

                          <tr>
                            <td><?= $i ?></td>
                            <td><?= $item['voteId'] ?></td>
                            <td><?= $item['voteNumero'] ?></td>
                            <td><?= $legislature_to_get ?></td>
                            <td><?= $item['organeRef'] ?></td>
                            <td><?= $item['nombreMembresGroupe'] ?></td>
                            <td><?= $positionMajoritaire ?></td>
                            <td><?= $item['nombrePours'] ?></td>
                            <td><?= $item['nombreContres'] ?></td>
                            <td><?= $item['nombreAbstentions'] ?></td>
                            <td><?= $item['nonVotants'] ?></td>
                            <td></td>
                          </tr>

                          <?php

                          $sql = $bdd->prepare("INSERT INTO votes_groupes (voteId, voteNumero, legislature, organeRef, nombreMembresGroupe, positionMajoritaire, nombrePours, nombreContres, nombreAbstentions, nonVotants, nonVotantsVolontaires) VALUES (:voteId, :voteNumero, :legislature, :organeRef, :nombreMembresGroupe, :positionMajoritaire, :nombrePours, :nombreContres, :nombreAbstentions, :nonVotants, null)");
                          $sql->execute(array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'legislature' => $legislature_to_get ,'organeRef' => $item['organeRef'], 'nombreMembresGroupe' => $item['nombreMembresGroupe'], 'positionMajoritaire' => $positionMajoritaire, 'nombrePours' => $item['nombrePours'], 'nombreContres' => $item['nombreContres'], 'nombreAbstentions' => $item['nombreAbstentions'], 'nonVotants' => $item['nonVotants']));
                          $i++;

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
