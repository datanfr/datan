<?php
include('../bdd-connexion.php');
$dpts = array(
    array("new" => "01", "old" => "1"),
    array("new" => "02", "old" => "2"),
    array("new" => "03", "old" => "3"),
    array("new" => "04", "old" => "4"),
    array("new" => "05", "old" => "5"),
    array("new" => "06", "old" => "6"),
    array("new" => "07", "old" => "7"),
    array("new" => "08", "old" => "8"),
    array("new" => "09", "old" => "9"),
);

try {
    foreach ($dpts as $dpt) {
      $bdd->query('UPDATE `circos` SET `dpt`= "' . $dpt['new'] . '" WHERE dpt="' . $dpt['old'] . '"');
      $bdd->query('UPDATE `elect_2017_pres_2` SET `dpt`= "' . $dpt['new'] . '" WHERE dpt="' . $dpt['old'] . '"');
    }

} catch (Exception $e) {
    echo '<pre>', var_dump($e->getMessage()), '</pre>';
}
