<?php
  include "lib/simplehtmldom_1_9/simple_html_dom.php";
  include "bdd-connexion.php";

  ini_set('memory_limit', '2048M');

  $circos = $bdd->query('SELECT departementCode, circo
    FROM deputes_all
    WHERE legislature = 15
    GROUP BY departementCode, circo
    ORDER BY departementCode, circo
    LIMIT 50
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
        $name = $row->find('td', 0);
        $nuance = $row->find('td', 1);
        $voix = str_replace(' ', '', $row->find('td', 2));
        $pct_inscrits = $row->find('td', 3);
        $pct_exprimes = $row->find('td', 4);
        $elected = $row->find('td', 5);

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

}
