<?php
  include "lib/simplehtmldom_1_9/simple_html_dom.php";

  ini_set('memory_limit', '2048M');

  $dpt = "035";
  $circos = array("01", "02", "03", "04", "05", "06", "07", "08", "09");

  foreach ($circos as $circo) {


    $url = "https://www.interieur.gouv.fr/Elections/Les-resultats/Legislatives/elecresult__legislatives-2017/(path)/legislatives-2017/".$dpt."/".$dpt."".$circo.".html";

    echo "dpt ==> ". $dpt ."<br>";
    echo "circo ==> ".$circo."<br>";
    echo "url => ". $url . "<br>";

    $html = file_get_html($url);

    ?>

    <table>
      <tbody>

      <?php

      foreach($html->find('.table-bordered') as $table) {
        $legend = $table->find('th', 0);
        if (strpos($legend, 'candidats') !== false) {
          foreach ($table->find('tr') as $row) {
            $name = $row->find('td', 0);
            $voix = $row->find('td', 2);
            $pct_inscrits = $row->find('td', 3);
            $pct_exprimes = $row->find('td', 4);
            $elected = $row->find('td', 5);

            ?>
            <tr>
              <td><?= $dpt ?></td>
              <td><?= $circo ?></td>
              <td><?= $name ?></td>
              <td><?= $voix ?></td>
              <td><?= $pct_inscrits ?></td>
              <td><?= $pct_exprimes ?></td>
              <td><?= $elected ?></td>
            </tr>

            <?php

          }
        }

      }

      ?>
      </tbody>
    </table>

    <br><br>

    <?php
  }
