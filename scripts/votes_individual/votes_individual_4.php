<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <body>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>Votes_4 individual</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
			</div>
			<div class="row mt-3">
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

                include '../bdd-connexion.php';

                if (isset($_GET["vote"])) {
                  $number_to_get = $_GET["vote"];

                  //DELETE FROM TABLE
                  $sql_delete = "DELETE FROM votes_groupes WHERE numero = :number_to_get";
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

                echo "VOTE TO GET = ".$number_to_get;


                //https://stackoverflow.com/questions/2600105/need-php-script-to-decompress-and-loop-through-zipped-file

                $zip = new ZipArchive();
        				if ($zip->open($newfile)!==TRUE) {
        						exit("cannot open <$filename>\n");
        					} else {

                    foreach (range($number_to_get, $number_to_get) as $n) {
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

              ?>
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
