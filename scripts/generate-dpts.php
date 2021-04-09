<?php
  include 'bdd-connexion.php';

  $regions = $bdd->query('SELECT distinct(region) FROM departement WHERE region != "" ORDER BY region ASC');
  $string = NULL;

  $string .= '{<br>name: "';

  while ($region = $regions->fetch()) {
    $reg = $region[0];
    $string .= $reg.'",<br>color: "#fff",<br> departements: [';

    $dpts = $bdd->query('SELECT departement_code FROM departement WHERE region = "'.$reg.'" AND departement_code != "" ORDER BY departement_code ASC');

    while ($dpt = $dpts->fetch()) {
      $dptId = $dpt[0];
      $string .= '"fr-'.$dptId.'", ';
    }

    $string .= ']<br>},<br>{<br>';


  }

  echo $string;


?>
