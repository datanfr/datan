<?php
include('../bdd-connexion.php');
$dpts = array(
    array("new" => "2a", "old" => "2A"),
    array("new" => "2b", "old" => "2B"),
    array("new" => "971", "old" => "ZA"),
    array("new" => "972", "old" => "ZB"),
    array("new" => "973", "old" => "ZC"),
    array("new" => "974", "old" => "ZD"),
    array("new" => "976", "old" => "ZM"),
    array("new" => "988", "old" => "ZN"),
    array("new" => "987", "old" => "ZP"),
    array("new" => "975", "old" => "ZS"),
    array("new" => "986", "old" => "ZW"),
    array("new" => "977", "old" => "ZX"),
    array("new" => "099", "old" => "ZZ")
);

try {
    foreach ($dpts as $dpt) {
        $bdd->query('UPDATE `circos` SET `dpt`= "' . $dpt['new'] . '" WHERE dpt="' . $dpt['old'] . '"');
        $bdd->query('UPDATE `elect_2017_pres_2` SET `dpt`= "' . $dpt['new'] . '" WHERE dpt="' . $dpt['old'] . '"');
        $bdd->query('UPDATE `elect_2019_europe_clean` SET `dpt`= "' . $dpt['new'] . '" WHERE dpt="' . $dpt['old'] . '"');
        $bdd->query('UPDATE `cities_mayors` SET `dpt`= "' . $dpt['new'] . '" WHERE dpt="' . $dpt['old'] . '"');
        $bdd->query('UPDATE `circos` SET `commune`=CONCAT("6", RIGHT(commune, 2)) WHERE dpt="976"');
        $bdd->query('UPDATE `elect_2017_pres_2` SET `commune`=CONCAT("6", RIGHT(commune, 2)) WHERE dpt="976"');
    }
    $bdd->query('ALTER TABLE `circos` DROP `dpt_edited`;');
    $bdd->query('UPDATE `insee` SET `dpt`= LEFT(postal, 3) WHERE insee.dpt = "97"');

} catch (Exception $e) {
    echo '<pre>', var_dump($e->getMessage()), '</pre>';
}
