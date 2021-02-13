<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table 'votes_dossiers'

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", "votes_", ".php"), "", $url);
      $url_current = substr($url, 0, 2);
      $url_second = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1><?= $_SERVER['REQUEST_URI'] ?></h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col-12">
          <table class="table">
            <thead>
                <tr>
                  <th scope="col">#</th>
                  <th>offset</th>
                  <th>num</th>
                  <th>href</th>
                  <th>dossier</th>
                </tr>
              </thead>
              <tbody>
        				<?php
                include "lib/simplehtmldom_1_9/simple_html_dom.php";
                include "bdd-connexion.php";
                ini_set('memory_limit', '1024M'); // or you could use 1G

                $bdd->query('
                  TRUNCATE TABLE votes_dossiers
                ');

                //Until where to go?
                $until_html = file_get_html("http://www2.assemblee-nationale.fr/scrutins/liste/(legislature)/15/(type)/TOUS/(idDossier)/TOUS");
                $pagination = $until_html->find('.pagination-bootstrap ul', 0);
                $last = $pagination->find('li', -2)->plaintext;
                $until = ($last-1)*100;
                echo $until;

                //array urls to get
                $offsets = range(0, $until, 100);
                $i = 1;

                foreach ($offsets as $offset) {
                  $url = "http://www2.assemblee-nationale.fr/scrutins/liste/(offset)/".$offset."/(legislature)/15/(type)/TOUS/(idDossier)/TOUS";
                  //echo "<br>Offset = ".$offset;
                  //echo "<br>URL = ".$url;

                  $html = file_get_html($url);
                  foreach ($html->find('tbody tr') as $x) {
                    //echo $x;
                    $voteNumero = $x->find('.denom', 0)->plaintext;
                    $voteNumero = str_replace("*", "", $voteNumero);
                    $href = "";
                    $dossier = "";
                    foreach ($x->find('a') as $a) {
                      if ($a->plaintext == "dossier") {
                        $href = $a->href;
                        if (strpos($href, "/14/") !== false) {
                          if (strpos($href, ".asp") !== false) {
                            //echo "1";
                            $dossier1 = str_replace('https://www.assemblee-nationale.fr/14/dossiers/', '', $href);
                            $dossier = str_replace('.asp', '', $dossier1);
                          } else {
                            //echo "2";
                            $dossier = str_replace('https://www.assemblee-nationale.fr/dyn/14/dossiers/', '', $href);
                          }
                        } else {
                          if (strpos($href, ".asp") !== false) {
                            //echo "3";
                            $dossier1 = str_replace('https://www.assemblee-nationale.fr/15/dossiers/', '', $href);
                            $dossier = str_replace('.asp', '', $dossier1);
                          } else {
                            //echo "4";
                            $dossier = str_replace('https://www.assemblee-nationale.fr/dyn/15/dossiers/', '', $href);
                          }
                        }
                      }
                    }

                    $dossier = !empty($dossier) ? "$dossier" : NULL;
                    $href = !empty($href) ? "$href" : NULL;


                    ?>
                    <tr>
                      <td><?= $i ?></td>
                      <td><?= $offset ?></td>
                      <td><?= $voteNumero ?></td>
                      <td><?= $href ?></td>
                      <td><?= $dossier ?></td>
                    </tr>
                    <?php

                    // INSERT INTO THE DATABASE HERE
                    $sql = $bdd->prepare("INSERT INTO votes_dossiers (offset_num, voteNumero, href, dossier) VALUES (:offset_num, :voteNumero, :href, :dossier)");
                    $sql->execute(array('offset_num' => $offset, 'voteNumero' => $voteNumero, 'href' => $href, 'dossier' => $dossier));

                    $i++;
                  }

                }

                ?>
            </tbody>
          </table>
        </div>
			</div>
		</div>
	</body>
</html>
