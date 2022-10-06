<?php
include '../bdd-connexion.php';

$row = 1;
$fields = array("twitter", "facebook");
if (($handle = fopen("reseaux_sociaux_deputes_2022.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
      $num = count($data);
      echo $data[2];
      $dbDeputeContacts = $bdd->prepare('SELECT * FROM deputes_contacts WHERE mpId="' . $data[2] . '"');
      $dbDeputeContacts->execute();
      if ($dbDeputeContacts->rowCount() == 1) {
        if ($data[3]) {
          echo 'twitter =>' .$data[3];
          if ($data[3] != "NULL") {
            $sql = $bdd->prepare('UPDATE deputes_contacts SET twitter=:twitter, dateMaj=CURDATE() WHERE mpId = "' . $data[2] . '"');
            $sql->execute(array("twitter" => $data[3]));
          }
        } else {
          echo 'no twitter';
        }
      }
    }
    fclose($handle);
}
