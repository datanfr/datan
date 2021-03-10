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

  This script creates exports all the votes from the XML field
  into the table 'votes_info'

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
        <div class="col-12">
          <h2 class="bg-danger">This script needs to be refreshed until the table below is empty. The scripts automatically is automatically refreshed every 5 seconds.</h2>
          <table class="table">
            <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">voteId</th>
                  <th scope="col">voteNumero</th>
                  <th scope="col">organeRef</th>
                  <th scope="col">legislature</th>
                  <th scope="col">sessionRef</th>
                  <th scope="col">seanceRef</th>
                  <th scope="col">dateScrutin</th>
                  <th scope="col">quantiemeJourSeance</th>
                  <th scope="col">codeTypeVote</th>
                  <th scope="col">libelleTypeVote</th>
                  <th scope="col">typeMajorite</th>
                  <th scope="col">sortCode</th>
                  <th scope="col">titre</th>
                  <th scope="col">demandeur</th>
                  <th scope="col">modePublicationDesVotes</th>
                  <th scope="col">nombreVotants</th>
                  <th scope="col">suffragesExprimes</th>
                  <th scope="col">nbrSuffragesRequis</th>
                  <th scope="col">decomptePour</th>
                  <th scope="col">decompteContre</th>
                  <th scope="col">decompteAbs</th>
                  <th scope="col">decompteNv</th>
                </tr>
              </thead>
              <tbody>
        				<?php
          				ini_set('display_errors', 1);
          				error_reporting(E_ALL);
        					include 'bdd-connexion.php';

        					$reponse_vote = $bdd->query('
        						SELECT voteNumero
        						FROM votes_info
                    WHERE legislature = "'.$legislature_to_get.'"
        						ORDER BY voteNumero DESC
        						LIMIT 1
        					');

                  while ($dernier_vote = $reponse_vote->fetch() ) {
                    $last_vote = $dernier_vote['voteNumero'];
                  }
                  // Last vote
                  if (!isset($last_vote)) {
                    $number_to_import = 1;
                  } else {
                    $number_to_import = $last_vote + 1;
                  }

                  echo "<b>FIRST VOTE TO IMPORT</b> = ".$number_to_import."<br>";
                  $until = $number_to_import + 399;
                  echo "<b>UNTIL WHICH VOTE TO IMPORT</b> = ".$until."<br>";

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

                      $i = 1;

                      foreach (range($number_to_import, $until) as $n) {
                        $file_to_import = 'VTANR5L15V'.$n;
                        $xml_string = $zip->getFromName('xml/'.$file_to_import.'.xml');
                        if ($xml_string != false) {
                          $xml = simplexml_load_string($xml_string);

                          foreach ($xml->xpath("//*[local-name()='scrutin']") as $scrutin) {
                            $voteId = $scrutin->xpath("./*[local-name()='uid']");
                              $item['voteId'] = $voteId[0];

                            $voteNumero = $scrutin->xpath("./*[local-name()='numero']");
                              $item['voteNumero'] = $voteNumero[0];

                            $organeRef = $scrutin->xpath("./*[local-name()='organeRef']");
                              $item['organeRef'] = $organeRef[0];

                            $legislature = $scrutin->xpath("./*[local-name()='legislature']");
                              $item['legislature'] = $legislature[0];

                            $sessionRef = $scrutin->xpath("./*[local-name()='sessionRef']");
                              $item['sessionRef'] = $sessionRef[0];

                            $seanceRef = $scrutin->xpath("./*[local-name()='seanceRef']");
                              $item['seanceRef'] = $seanceRef[0];

                            $dateScrutin = $scrutin->xpath("./*[local-name()='dateScrutin']");
                              $item['dateScrutin'] = $dateScrutin[0];

                            $quantiemeJourSeance = $scrutin->xpath("./*[local-name()='quantiemeJourSeance']");
                              $item['quantiemeJourSeance'] = $quantiemeJourSeance[0];

                            $codeTypeVote = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='codeTypeVote']");
                              $item['codeTypeVote'] = $codeTypeVote[0];

                            $libelleTypeVote = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='libelleTypeVote']");
                              $item['libelleTypeVote'] = $libelleTypeVote[0];

                            $typeMajorite = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='typeMajorite']");
                              $item['typeMajorite'] = $typeMajorite[0];

                            $sortCode = $scrutin->xpath("./*[local-name()='sort']/*[local-name()='code']");
                              $item['sortCode'] = $sortCode[0];

                            $titre = $scrutin->xpath("./*[local-name()='titre']");
                              $item['titre'] = $titre[0];

                            $demandeur = $scrutin->xpath("./*[local-name()='demandeur']/*[local-name()='texte']");
                              $item['demandeur'] = $demandeur[0];

                            $modePublicationDesVotes = $scrutin->xpath("./*[local-name()='modePublicationDesVotes']");
                              $item['modePublicationDesVotes'] = $modePublicationDesVotes[0];

                            $nombreVotants = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='nombreVotants']");
                              $item['nombreVotants'] = $nombreVotants[0];

                            $suffragesExprimes = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='suffragesExprimes']");
                              $item['suffragesExprimes'] = $suffragesExprimes[0];

                            $nbrSuffragesRequis = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='nbrSuffragesRequis']");
                              $item['nbrSuffragesRequis'] = $nbrSuffragesRequis[0];

                            $decomptePour = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='pour']");
                              $item['decomptePour'] = $decomptePour[0];

                            $decompteContre = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='contre']");
                              $item['decompteContre'] = $decompteContre[0];

                            $decompteAbs = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='abstentions']");
                              $item['decompteAbs'] = $decompteAbs[0];

                            $decompteNv = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='nonVotants']");
                              $item['decompteNv'] = $decompteNv[0];


                              ?>

                              <tr>
                                <td><?= $i ?></td>
                                <td><?= $item['voteId'] ?></td>
                                <td><?= $item['voteNumero'] ?></td>
                                <td><?= $item['organeRef'] ?></td>
                                <td><?= $item['legislature'] ?></td>
                                <td><?= $item['sessionRef'] ?></td>
                                <td><?= $item['seanceRef'] ?></td>
                                <td><?= $item['dateScrutin'] ?></td>
                                <td><?= $item['quantiemeJourSeance'] ?></td>
                                <td><?= $item['codeTypeVote'] ?></td>
                                <td><?= $item['libelleTypeVote'] ?></td>
                                <td><?= $item['typeMajorite'] ?></td>
                                <td><?= $item['sortCode'] ?></td>
                                <td><?= $item['titre'] ?></td>
                                <td><?= $item['demandeur'] ?></td>
                                <td><?= $item['modePublicationDesVotes'] ?></td>
                                <td><?= $item['nombreVotants'] ?></td>
                                <td><?= $item['suffragesExprimes'] ?></td>
                                <td><?= $item['nbrSuffragesRequis'] ?></td>
                                <td><?= $item['decomptePour'] ?></td>
                                <td><?= $item['decompteContre'] ?></td>
                                <td><?= $item['decompteAbs'] ?></td>
                                <td><?= $item['decompteNv'] ?></td>
                              </tr>

                              <?php

                              $sql = $bdd->prepare("INSERT INTO votes_info (voteId, voteNumero, organeRef, legislature, sessionREF, seanceRef, dateScrutin, quantiemeJourSeance, codeTypeVote, libelleTypeVote, typeMajorite, sortCode, titre, demandeur, modePublicationDesVotes, nombreVotants, suffragesExprimes, nbrSuffragesRequis, decomptePour, decompteContre, decompteAbs, decompteNv) VALUES (:voteId, :voteNumero, :organeRef, :legislature, :sessionREF, :seanceRef, :dateScrutin, :quantiemeJourSeance, :codeTypeVote, :libelleTypeVote, :typeMajorite, :sortCode, :titre, :demandeur, :modePublicationDesVotes, :nombreVotants, :suffragesExprimes, :nbrSuffragesRequis, :decomptePour, :decompteContre, :decompteAbs, :decompteNv)");

                      				$sql->execute(array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'organeRef' => $item['organeRef'], 'legislature' => $item['legislature'], 'sessionREF' => $item['sessionRef'], 'seanceRef' => $item['seanceRef'], 'dateScrutin' => $item['dateScrutin'], 'quantiemeJourSeance' => $item['quantiemeJourSeance'], 'codeTypeVote' => $item['codeTypeVote'], 'libelleTypeVote' => $item['libelleTypeVote'], 'typeMajorite' => $item['typeMajorite'], 'sortCode' => $item['sortCode'], 'titre' => $item['titre'], 'demandeur' => $item['demandeur'], 'modePublicationDesVotes' => $item['modePublicationDesVotes'], 'nombreVotants' => $item['nombreVotants'], 'suffragesExprimes' => $item['suffragesExprimes'], 'nbrSuffragesRequis' => $item['nbrSuffragesRequis'], 'decomptePour' => $item['decomptePour'], 'decompteContre' => $item['decompteContre'], 'decompteAbs' => $item['decompteAbs'], 'decompteNv' => $item['decompteNv']));

                              $i++;
                          }
                        }
                      }
                    }

                  } elseif ($legislature_to_get == 14) {

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

                        foreach ($xml->xpath('./scrutin[(numero>='. $number_to_import .') and (numero<='. $until.')]') as $scrutin) {
                          $voteId = $scrutin->xpath("./*[local-name()='uid']");
                            $item['voteId'] = $voteId[0];

                          $voteNumero = $scrutin->xpath("./*[local-name()='numero']");
                            $item['voteNumero'] = $voteNumero[0];

                          $organeRef = $scrutin->xpath("./*[local-name()='organeRef']");
                            $item['organeRef'] = $organeRef[0];

                          $sessionRef = $scrutin->xpath("./*[local-name()='sessionRef']");
                            $item['sessionRef'] = $sessionRef[0];

                          $seanceRef = $scrutin->xpath("./*[local-name()='seanceRef']");
                            $item['seanceRef'] = $seanceRef[0];

                          $dateScrutin = $scrutin->xpath("./*[local-name()='dateScrutin']");
                            $item['dateScrutin'] = $dateScrutin[0];

                          $quantiemeJourSeance = $scrutin->xpath("./*[local-name()='quantiemeJourSeance']");
                            $item['quantiemeJourSeance'] = $quantiemeJourSeance[0];

                          $codeTypeVote = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='codeTypeVote']");
                            $item['codeTypeVote'] = $codeTypeVote[0];

                          $libelleTypeVote = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='libelleTypeVote']");
                            $item['libelleTypeVote'] = $libelleTypeVote[0];

                          $typeMajorite = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='typeMajorite']");
                            $item['typeMajorite'] = $typeMajorite[0];

                          $sortCode = $scrutin->xpath("./*[local-name()='sort']/*[local-name()='code']");
                            $item['sortCode'] = $sortCode[0];

                          $titre = $scrutin->xpath("./*[local-name()='titre']");
                            $item['titre'] = $titre[0];

                          $demandeur = $scrutin->xpath("./*[local-name()='demandeur']/*[local-name()='texte']");
                            $item['demandeur'] = $demandeur[0];

                          $modePublicationDesVotes = $scrutin->xpath("./*[local-name()='modePublicationDesVotes']");
                            $item['modePublicationDesVotes'] = $modePublicationDesVotes[0];

                          $nombreVotants = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='nombreVotants']");
                            $item['nombreVotants'] = $nombreVotants[0];

                          $suffragesExprimes = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='suffragesExprimes']");
                            $item['suffragesExprimes'] = $suffragesExprimes[0];

                          $nbrSuffragesRequis = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='nbrSuffragesRequis']");
                            $item['nbrSuffragesRequis'] = $nbrSuffragesRequis[0];

                          $decomptePour = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='pour']");
                            $item['decomptePour'] = $decomptePour[0];

                          $decompteContre = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='contre']");
                            $item['decompteContre'] = $decompteContre[0];

                          $decompteAbs = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='abstentions']");
                            $item['decompteAbs'] = $decompteAbs[0];

                          $decompteNv = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='nonVotants']");
                            $item['decompteNv'] = $decompteNv[0];


                            ?>

                            <tr>
                              <td><?= $i ?></td>
                              <td><?= $item['voteId'] ?></td>
                              <td><?= $item['voteNumero'] ?></td>
                              <td><?= $item['organeRef'] ?></td>
                              <td><?= $legislature_to_get ?></td>
                              <td><?= $item['sessionRef'] ?></td>
                              <td><?= $item['seanceRef'] ?></td>
                              <td><?= $item['dateScrutin'] ?></td>
                              <td><?= $item['quantiemeJourSeance'] ?></td>
                              <td><?= $item['codeTypeVote'] ?></td>
                              <td><?= $item['libelleTypeVote'] ?></td>
                              <td><?= $item['typeMajorite'] ?></td>
                              <td><?= $item['sortCode'] ?></td>
                              <td><?= $item['titre'] ?></td>
                              <td><?= $item['demandeur'] ?></td>
                              <td><?= $item['modePublicationDesVotes'] ?></td>
                              <td><?= $item['nombreVotants'] ?></td>
                              <td><?= $item['suffragesExprimes'] ?></td>
                              <td><?= $item['nbrSuffragesRequis'] ?></td>
                              <td><?= $item['decomptePour'] ?></td>
                              <td><?= $item['decompteContre'] ?></td>
                              <td><?= $item['decompteAbs'] ?></td>
                              <td><?= $item['decompteNv'] ?></td>
                            </tr>

                            <?php

                            $sql = $bdd->prepare("INSERT INTO votes_info (voteId, voteNumero, organeRef, legislature, sessionREF, seanceRef, dateScrutin, quantiemeJourSeance, codeTypeVote, libelleTypeVote, typeMajorite, sortCode, titre, demandeur, modePublicationDesVotes, nombreVotants, suffragesExprimes, nbrSuffragesRequis, decomptePour, decompteContre, decompteAbs, decompteNv) VALUES (:voteId, :voteNumero, :organeRef, :legislature, :sessionREF, :seanceRef, :dateScrutin, :quantiemeJourSeance, :codeTypeVote, :libelleTypeVote, :typeMajorite, :sortCode, :titre, :demandeur, :modePublicationDesVotes, :nombreVotants, :suffragesExprimes, :nbrSuffragesRequis, :decomptePour, :decompteContre, :decompteAbs, :decompteNv)");

                            $sql->execute(array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'organeRef' => $item['organeRef'], 'legislature' => $legislature_to_get, 'sessionREF' => $item['sessionRef'], 'seanceRef' => $item['seanceRef'], 'dateScrutin' => $item['dateScrutin'], 'quantiemeJourSeance' => $item['quantiemeJourSeance'], 'codeTypeVote' => $item['codeTypeVote'], 'libelleTypeVote' => $item['libelleTypeVote'], 'typeMajorite' => $item['typeMajorite'], 'sortCode' => $item['sortCode'], 'titre' => $item['titre'], 'demandeur' => $item['demandeur'], 'modePublicationDesVotes' => $item['modePublicationDesVotes'], 'nombreVotants' => $item['nombreVotants'], 'suffragesExprimes' => $item['suffragesExprimes'], 'nbrSuffragesRequis' => $item['nbrSuffragesRequis'], 'decomptePour' => $item['decomptePour'], 'decompteContre' => $item['decompteContre'], 'decompteAbs' => $item['decompteAbs'], 'decompteNv' => $item['decompteNv']));

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
