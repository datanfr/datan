<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="150">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <body>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>Votes_1 individual</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col-12">
          <table class="table">
            <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">mpId</th>
                  <th scope="col">mandatId</th>
                  <th scope="col">parDelegation</th>
                  <th scope="col">causeDelegationVote</th>
                  <th scope="col">voteMp</th>
                  <th scope="col">vote</th>
                  <th scope="col">voteId</th>
                  <th scope="col">voteNumero</th>
                  <th scope="col">legislature</th>
                  <th scope="col">voteType</th>
                </tr>
              </thead>
              <tbody>
        				<?php
        				ini_set('display_errors', 1);
        				error_reporting(E_ALL);
        					include '../bdd-connexion.php';

                  if (isset($_GET["vote"])) {
                    $number_to_get = $_GET["vote"];

                    //DELETE FROM TABLE
                    $sql_delete = "DELETE FROM votes WHERE voteNumero = :number_to_get";
                    $stmt = $bdd->prepare($sql_delete);
                    $stmt->execute(array('number_to_get' => $number_to_get));

                  } else {
                    exit("Please indicate the number of the vote in the url (?vote=xx)");
                  }

                  // Online File
                  $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/scrutins/Scrutins_XV.xml.zip';
                  $file = trim($file);
                  $newfile = 'tmp_Scrutins_XV.xml.zip';
                  if (!copy($file, $newfile)) {
                    echo "failed to copy $file...\n";
                  }

                  echo "VOTE TO GET = ".$number_to_get."<br>";

                  $zip = new ZipArchive();
                  if ($zip->open($newfile)!==TRUE) {
                    exit("cannot open <$filename>\n");
                  } else {

                    $i = 1;

                    foreach (range($number_to_get, $number_to_get) as $n) {
                      $file_to_import = 'VTANR5L15V'.$n;
                      $xml_string = $zip->getFromName('xml/'.$file_to_import.'.xml');
                      if ($xml_string != false) {
                        $xml = simplexml_load_string($xml_string);

                        foreach ($xml->xpath("//*[local-name()='votant']") as $votant) {
                          $mpId = $votant->xpath("./*[local-name()='acteurRef']");
                          $item['mpId'] = $mpId[0];

                          $mandatId = $votant->xpath("./*[local-name()='mandatRef']");
                          $item['mandatId'] = $mandatId[0];

                          $parDelegation = $votant->xpath("./*[local-name()='parDelegation']");
                            if (isset($parDelegation[0])) {
                              $item['parDelegation'] = $parDelegation[0];
                            } else {
                              $item['parDelegation'] = NULL;
                            }

                          $causePosition = $votant->xpath("./*[local-name()='causePositionVote']");
                            if (isset($causePosition[0])) {
                              $item['causePosition'] = $causePosition[0];
                            } else {
                              $item['causePosition'] = NULL;
                            }

                          $voteMp = $votant->xpath("./parent::*");
                          $item['voteMp'] = $voteMp[0]->getName();


                          if ($item['voteMp'] == 'pours') {
                            $vote = 1;
                          } elseif ($item['voteMp'] == 'contres') {
                            $vote = -1;
                          } elseif ($item['voteMp'] == 'abstentions') {
                            $vote = 0;
                          } elseif ($item['voteMp'] == 'nonVotants') {
                            $vote = 'nv';
                          } else {
                            $vote = 99;
                          }

                          $voteId = $votant->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='uid']");
                          $item['voteId'] = $voteId[0];

                          $voteNumero = $votant->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='numero']");
                          $item['voteNumero'] = $voteNumero[0];

                          $legislature = $votant->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='legislature']");
                          $item['legislature'] = $legislature[0];

                          $miseaupoint = $votant->xpath("./../..");
                          $item['voteType'] = $miseaupoint[0]->getName();


                            ?>

                            <tr>
                              <td><?= $i ?></td>
                              <td><?= $item['mpId'] ?></td>
                              <td><?= $item['mandatId'] ?></td>
                              <td><?= $item['parDelegation'] ?></td>
                              <td><?= $item['causePosition'] ?></td>
                              <td><?= $item['voteMp'] ?></td>
                              <td><?= $vote ?></td>
                              <td><?= $item['voteId'] ?></td>
                              <td><?= $item['voteNumero'] ?></td>
                              <td><?= $item['legislature'] ?></td>
                              <td><?= $item['voteType'] ?></td>
                            </tr>

                            <?php

                            //INSERT INTO TABLE
                            $sql = $bdd->prepare("INSERT INTO votes (mpId, vote, voteNumero, voteId, legislature, mandatId, parDelegation, causePosition, voteType) VALUES (:mpId, :vote, :voteNumero, :voteId, :legislature, :mandatId, :parDelegation, :causePosition, :voteType)");

                            $sql->execute(array('mpId' => $item['mpId'], 'vote' => $vote, 'voteNumero' => $item['voteNumero'], 'voteId' => $item['voteId'], 'legislature' => $item['legislature'], 'mandatId' => $item['mandatId'], 'parDelegation' => $item['parDelegation'], 'causePosition' => $item['causePosition'], 'voteType' => $item['voteType']));

                            $i++;
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
