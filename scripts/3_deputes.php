<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>3_deputes</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
</head>
<!--

  This script inserts into the table 'deputes_contacts' the contact infos
  of each MP. It also truncates the table before importing data.

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
      <h1>3. Mise à jour base 'deputes_contacts'</h1>
    </div>
    <div class="row">
      <div class="col-4">
        <a class="btn btn-outline-primary" href="./" role="button">Back</a>
      </div>
      <div class="col-4">
        <a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME'] . '' . $_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
      </div>
      <div class="col-4">
        <a class="btn btn-outline-success" href="./<?= $url_next ?>_deputes.php" role="button">NEXT</a>
      </div>
    </div>
    <div class="row mt-3">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">mpId</th>
            <th scope="col">file</th>
            <th scope="col">uid</th>
            <th scope="col">type</th>
            <th scope="col">typeLibelle</th>
            <th scope="col">poids</th>
            <th scope="col">adresseRattachement</th>
            <th scope="col">intitule</th>
            <th scope="col">numeroRue</th>
            <th scope="col">nomRue</th>
            <th scope="col">complementAdresse</th>
            <th scope="col">codePostal</th>
            <th scope="col">ville</th>
            <th scope="col">valElec</th>
            <th scope="col">dateMaj</th>
          </tr>
        </thead>
        <tbody>
          <?php
          ini_set('memory_limit', '500M');
          include 'bdd-connexion.php';
          $dateMaj = date('Y-m-d');
          //Online file
          $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
          $file = trim($file);
          $newfile = 'tmp_acteurs_organes.zip';
          if (!copy($file, $newfile)) {
            echo "failed to copy $file...\n";
          }
          $zip = new ZipArchive();
          if ($zip->open($newfile) !== TRUE) {
            exit("cannot open <$filename>\n");
          } else {
            $deputeContacts = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
              $filename = $zip->getNameIndex($i);
              $sub = substr($filename, 0, 13);
              if ($sub == 'xml/acteur/PA') {
                $xml_string = $zip->getFromName($filename);
                if ($xml_string != false) {
                  $xml = simplexml_load_string($xml_string);
                  $mpId = str_replace("xml/acteur/", "", $filename);
                  $mpId = str_replace(".xml", "", $mpId);
                  foreach ($xml->adresses->adresse as $adresses) {
                    $uid = $adresses->uid;
                    $type = $adresses->type;
                    $typeLibelle = $adresses->typeLibelle;
                    $poids = $adresses->poids;
                    $adresseRattachement = $adresses->adresseDeRattachement;
                    $intitule = $adresses->intitule;
                    $numeroRue = $adresses->numeroRue;
                    $nomRue = $adresses->nomRue;
                    $complementAdresse = $adresses->complementAdresse;
                    $codePostal = $adresses->codePostal;
                    $ville = $adresses->ville;
                    $valElec = $adresses->valElec;
          ?>

                    <tr>
                      <td><?= $mpId ?></td>
                      <td><?= $file ?></td>
                      <td><?= $uid ?></td>
                      <td><?= $type ?></td>
                      <td><?= $typeLibelle ?></td>
                      <td><?= $poids ?></td>
                      <td><?= $adresseRattachement ?></td>
                      <td><?= $intitule ?></td>
                      <td><?= $numeroRue ?></td>
                      <td><?= $nomRue ?></td>
                      <td><?= $complementAdresse ?></td>
                      <td><?= $codePostal ?></td>
                      <td><?= $ville ?></td>
                      <td><?= $valElec ?></td>
                      <td><?= $dateMaj ?></td>
                    </tr>

          <?php
                    //add different contacts to array $deputeContacts
                    if (!isset($deputeContacts[$mpId])) {
                      $deputeContacts[$mpId] = array();
                    }
                    switch ($type) {
                      case 22:
                        $deputeContacts[$mpId]["website"] = $valElec;
                        break;
                      case 15:
                        if (strpos($valElec, '@assemblee-nationale.fr') !== false) {
                          $deputeContacts[$mpId]["mailAn"] = $valElec;
                        } else {
                          $deputeContacts[$mpId]["mailPerso"] = $valElec;
                        }
                        break;
                      case 24:
                        $deputeContacts[$mpId]["twitter"] = $valElec;
                        break;
                      case 25:
                        $deputeContacts[$mpId]["facebook"] = $valElec;
                        break;
                    }
                  }
                }
              }
            }
            // Get depute contacts for database
            $dbDeputeContacts = $bdd->prepare('SELECT * FROM deputes_contacts');
            $dbDeputeContacts->execute();
            $fields = array("website", "mailAn", "mailPerso", "twitter", "facebook");

            // update table depute contact from array $deputeContacts
            while ($dbDeputeContact = $dbDeputeContacts->fetch()) {
              $updateFields = [];
              $updateValues = [];
              $toUpdate = false;
              foreach ($fields as $field) {
                if (
                  isset($deputeContacts[$dbDeputeContact["mpId"]][$field])
                  && $deputeContacts[$dbDeputeContact["mpId"]][$field]
                  && $deputeContacts[$dbDeputeContact["mpId"]][$field] != $dbDeputeContact[$field]
                ) {
                  $updateFields[] = $field;
                  $updateValues[] = $deputeContacts[$dbDeputeContact["mpId"]][$field];
                  $toUpdate = true;
                }
              }
              if ($toUpdate) {
                $set = "";
                for ($i = 0; count($updateFields) > $i; $i++) {
                  $set .= "{$updateFields[$i]} = \"{$updateValues[$i]}\",";
                }
                $set = substr($set, 0, -1);
                $sql = $bdd->prepare('UPDATE deputes_contacts SET ' . $set . ', dateMaj=CURDATE() WHERE mpId = "' . $dbDeputeContact["mpId"] . '"');
                $sql->execute();
              }
              unset($deputeContacts[$dbDeputeContact["mpId"]]);
            }
            // if new deputes add contact
            foreach ($deputeContacts as $key => $deputeContact) {
              $sql = $bdd->prepare("INSERT INTO deputes_contacts (
                mpId,
                website,
                mailAn,
                mailPerso,
                twitter,
                facebook,
                dateMaj)
                VALUES (
                :mpId,
                :website,
                :mailAn,
                :mailPerso,
                :twitter,
                :facebook,
                CURDATE()
                )");


              $sql->execute(array(
                'mpId' => $key,
                'website' => isset($deputeContact['website']) ? $deputeContact['website'] : null,
                'mailAn' => isset($deputeContact['mailAn']) ? $deputeContact['mailAn'] : null,
                'mailPerso' => isset($deputeContact['mailPerso']) ? $deputeContact['mailPerso'] : null,
                'twitter' => isset($deputeContact['twitter']) ? $deputeContact['twitter'] : null,
                'facebook' => isset($deputeContact['facebook']) ? $deputeContact['facebook'] : null,
              ));
            }
          }


          ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>