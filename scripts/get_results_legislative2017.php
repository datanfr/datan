<?php
  include "lib/simplehtmldom_1_9/simple_html_dom.php";
  include "bdd-connexion.php";

  ini_set('memory_limit', '2048M');

  $bdd->query('TRUNCATE TABLE elect_2017_leg_results');
  $bdd->query('TRUNCATE TABLE elect_2017_leg_infos');

  $circos = $bdd->query('SELECT departementCode, circo
    FROM deputes_all
    WHERE legislature = 15
    GROUP BY departementCode, circo
    ORDER BY departementCode, circo
  ');

  while ($x = $circos->fetch()) {
    echo "<br><hr><br>";
    print_r($x);
    $dpt = $x['departementCode'];
    $dpt_url = $dpt;
    $circo = $x['circo'];
    $circo_url = $circo;

    if (in_array($dpt_url, array("2a", "2b"))) {
      $dpt_url = mb_strtoupper($dpt_url);
    }

    if ($dpt_url < 99) {
      $dpt_url = "0".$dpt_url;
    }

    if ($circo_url < 10) {
      $circo_url = "0".$circo_url;
    }

    $url = "https://www.interieur.gouv.fr/Elections/Les-resultats/Legislatives/elecresult__legislatives-2017/(path)/legislatives-2017/".$dpt_url."/".$dpt_url."".$circo_url.".html";

    echo $url;

    $html = file_get_html($url);

    ?>

    <h2>Departement = <?= $dpt ?> // circo = <?= $circo ?></h2>

    <h3>resultsCandidates</h3>
    <table>
      <tbody>

      <?php

      $round = $html->find('h3', 1)->plaintext;

      if (strpos($round, '2d') !== false) { /* 2ND TOUR */
        $round = 2;
        $resultsCandidates = $html->find('.table-bordered', 0)->find('tbody', 0);
        $circosInfos = NULL;
        $circosInfos = $html->find('.table-bordered', 1)->find('tbody', 0);
      }

      if (strpos($round, '1er') !== false) { /* 1er tour */
        $round = 1;
        $resultsCandidates = $html->find('.table-bordered', 0)->find('tbody', 0);
        $circosInfos = NULL;
        $circosInfos = $html->find('.table-bordered', 1)->find('tbody', 0);
      }

      foreach ($resultsCandidates->find('tr') as $row) {
        $name = $row->find('td', 0)->plaintext;
        $nuance = str_replace(' ', '', $row->find('td', 1)->plaintext);
        $voix = str_replace(' ', '', $row->find('td', 2)->plaintext);
        $pct_inscrits = $row->find('td', 3)->plaintext;
        $pct_inscrits = str_replace(',', '.', $pct_inscrits);
        $pct_exprimes = $row->find('td', 4)->plaintext;
        $pct_exprimes = str_replace(',', '.', $pct_exprimes);
        $elected = $row->find('td', 5)->plaintext;
        if ($elected == "Oui") {
          $elected = 1;
        } else {
          $elected = 0;
        }

        ?>

        <tr>
          <td><?= $round ?></td>
          <td><?= $dpt ?></td>
          <td><?= $circo ?></td>
          <td><?= $name ?></td>
          <td><?= $nuance ?></td>
          <td><?= $voix ?></td>
          <td><?= $pct_inscrits ?></td>
          <td><?= $pct_exprimes ?></td>
          <td><?= $elected ?></td>
        </tr>

        <?php

        // Insert into database
        $updateResults = $bdd->prepare("INSERT INTO elect_2017_leg_results (dpt, dpt_url, circo, circo_url, tour, nuance, candidat, voix, pct_inscrits, pct_exprimes, elected) VALUES (:dpt, :dpt_url, :circo, :circo_url, :tour, :nuance, :candidat, :voix, :pct_inscrits, :pct_exprimes, :elected)");
        $updateResultsArray = array(
          'dpt' => $dpt,
          'dpt_url' => $dpt_url,
          'circo' => $circo,
          'circo_url' => $circo_url,
          'tour' => $round,
          'nuance' => $nuance,
          'voix' => $voix,
          'candidat' => $name,
          'pct_inscrits' => $pct_inscrits,
          'pct_exprimes' => $pct_exprimes,
          'elected' => $elected
        );
        $updateResults->execute($updateResultsArray);

      }

      ?>

      </tbody>
    </table>

    <h3>circoInfos</h3>

    <?php

    $info = [];
    foreach ($circosInfos->find('tr') as $row) {
      $info[] = str_replace(' ', '', $row->find('td', 1)->plaintext);
    }
    $inscrits = $info[0];
    $abstentions = $info[1];
    $votants = $info[2];
    $blancs = $info[3];
    $nuls = $info[4];
    $exprimes = $info[5];

    echo "round => ".$round."<br>";
    echo "inscrits => ".$inscrits."<br>";
    echo "abstentions => ".$abstentions."<br>";
    echo "votants => ".$votants."<br>";
    echo "blancs => ".$blancs."<br>";
    echo "nuls => ".$nuls."<br>";
    echo "exprimes => ".$exprimes."<br>";

    // Insert into database
    $updateInfos = $bdd->prepare("INSERT INTO elect_2017_leg_infos (dpt, dpt_url, circo, circo_url, tour, inscrits, abstentions, votants, blancs, nuls, exprimes) VALUES (:dpt, :dpt_url, :circo, :circo_url, :tour, :inscrits, :abstentions, :votants, :blancs, :nuls, :exprimes)");
    $updateInfosArray = array(
      'dpt' => $dpt,
      'dpt_url' => $dpt_url,
      'circo' => $circo,
      'circo_url' => $circo_url,
      'tour' => $round,
      'inscrits' => $inscrits,
      'abstentions' => $abstentions,
      'votants' => $votants,
      'blancs' => $blancs,
      'nuls' => $nuls,
      'exprimes' => $exprimes
    );
    $updateInfos->execute($updateInfosArray);

}
