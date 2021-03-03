<?php
include '../bdd-connexion.php';

$row = 1;
$fields = array("twitter", "facebook");
if (($handle = fopen("reseaux_sociaux_deputes.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        $dbDeputeContacts = $bdd->prepare('SELECT * FROM deputes RIGHT JOIN deputes_contacts ON deputes.mpId=deputes_contacts.mpId 
        WHERE mailAn="' . $data[5] . '"');
        $dbDeputeContacts->execute();
        if ($dbDeputeContacts->rowCount() == 0) {
            if ($data[3]) {
                $arrayName = explode(' ', $data[3]);
                $dbDeputeContacts = $bdd->prepare('SELECT * FROM deputes  
                RIGHT JOIN deputes_contacts ON deputes.mpId=deputes_contacts.mpId 
                WHERE nameLast LIKE "%' . array_pop($arrayName) . '%"
                AND nameFirst LIKE "%' . array_shift($arrayName) . '%" ORDER BY deputes.mpId DESC LIMIT 1');
                $dbDeputeContacts->execute();
            }
        }
        if ($dbDeputeContacts->rowCount() == 1) {
            $depute = $dbDeputeContacts->fetch();
            if ($data[6] != 'Pas de compte Facebook'){
                $facebooks = explode('/',$data[6]);
                while(count($facebooks) > 0 && strlen($facebook = array_pop($facebooks)) <= 1){
                }
            }
            else {
                $facebook = $depute['facebook'];
            }
            if ($data[7] == 'Pas de compte Twitter'){
                $data[7] = $depute['twitter'];
            }
            echo "updated {$depute['mpId']} {$depute['nameFirst']} {$depute['nameLast']} with his twitter {$data[7]} and his facebook {$facebook}<br>";
            $sql = $bdd->prepare('UPDATE deputes_contacts SET facebook=:facebook, twitter=:twitter, dateMaj=CURDATE() WHERE mpId = "' . $depute["mpId"] . '"');
            $sql->execute(array("facebook" => $facebook, "twitter" => $data[7]));
        } elseif ($dbDeputeContacts->rowCount() > 2) {
            echo 'Aye I dont know which one is this guy : <pre>', var_dump($data), '</pre>';
        }
        $row++;
    }
    fclose($handle);
}
