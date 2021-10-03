<?php
  include 'bdd-connexion.php';
  include_once "lib/simplehtmldom_1_9_1/simple_html_dom.php";

  // Context for 403 security on the website
  $context = stream_context_create(
      array(
          "http" => array(
              "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
          )
      )
  );

  $bdd->query('TRUNCATE TABLE hatvp');

  $query_mps = $bdd->query('SELECT d.mpId, d.hatvp
    FROM deputes d
    LEFT JOIN deputes_last dl ON d.mpId = dl.mpId
    WHERE hatvp != "" AND dl.legislature = 15
  ');

  $array = [];
  $fields = array('mpId', 'url', 'category', 'value', 'dateDebut', 'dateFin');
  $i = 1;

  while ($mp = $query_mps->fetch()) {
    $mpId = $mp['mpId'];
    $hatvpUrl = $mp['hatvp'];
    //echo $hatvpUrl . '<br>';

    // Scrapping
    $content = file_get_contents($hatvpUrl, false, $context);
    $html = str_get_html($content);

    // Check if the button to the XML file exists
    if (!empty($html->find('.download-declaration-buttons', 0))) {
      $xmlUrl = $html->find('.download-declaration-buttons', 0)->find('a', 1)->href;
      //echo $xmlUrl . '<br>';

      // Check if the XML file exists
      $xml = @simplexml_load_file($xmlUrl);
      if ($xml) {
        $xml = simplexml_load_file($xmlUrl);

        $activities = $xml->activProfCinqDerniereDto->items;
        if (count($activities) > 0) {
          foreach ($activities->items as $activity) {
            $description = (string) $activity->description;
            $dateDebut = (string) $activity->dateDebut;
            $dateDebut = substr($dateDebut, 3, 4) . '-' . substr($dateDebut, 0, 2). '-01';
            $dateFin = (string) $activity->dateFin;
            $dateFin = substr($dateFin, 3, 4) . '-' . substr($dateFin, 0, 2). '-01';

            //echo $description . '<br>';
            //echo $dateDebut . '<br>';
            //echo $dateFin . '<br>';

            $item = array(
              'mpId' => $mpId,
              'url' => $xmlUrl,
              'category' => 'activProf',
              'value' => $description,
              'dateDebut' => $dateDebut,
              'dateFin' => $dateFin
            );

            // INSERT INTO DATABSSE //
            $sql = $bdd->prepare('INSERT INTO hatvp (mpId, url, category, value, dateDebut, dateFin) VALUES (:mpId, :url, :category, :value, :dateDebut, :dateFin)');
            $sql->execute($item);
            echo "Data inserted <br>";

            $i++;
          }
        }
      }
    }
  }


?>
