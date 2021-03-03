<?php
include '../bdd-connexion.php';

$row = 1;
if (($handle = fopen("reseaux_sociaux_deputes.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);

        $dbDeputeContacts = $bdd->prepare('SELECT * FROM deputes WHERE nameLast LIKE "%' . array_pop(explode(' ', $data[3])) . '%"
        AND nameFirst LIKE "%' . array_shift(explode(' ', $data[3])) . '%"');
        $dbDeputeContacts->execute();
        if($dbDeputeContacts->rowCount() >= 2){
            echo '<pre>', var_dump($data), '</pre>';

        }
        // echo "<p>". $data[3]."</p>";
            // echo "<p> $num champs à la ligne $row: <br /></p>\n";
        $row++;
        // for ($c=0; $c < $num; $c++) {
        //     echo $data[$c] . "<br />\n";
        // }
    }
    fclose($handle);
}