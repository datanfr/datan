<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>2.2_deputes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script downloads the OGP photos

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_next = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>2.2. OGP photos 'deputes_webp'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./3_deputes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <p>This script downloads the OGP photos.</p>
          <p>You need to click on all the MPs below. Once the photos are downloaded on your computer, you need to manually move them to the DATAN 'assets/imgs/deputes_ogp' folder.</p>
          <p>If too many MPs need to be updated, and you do not have time, drop us a line and we'll send you the photos :) </p>
          <h2 class="my-5">MPs TO DOWNLOAD</h2>

          <?php

          include 'bdd-connexion.php';

          $donnees = $bdd->query('
            SELECT d.mpId AS uid, d.nameFirst AS prenom, d.nameLast AS nom
            FROM deputes_last d
            WHERE d.legislature IN (14, 15)
          ');

          while ($mp = $donnees->fetch()) {
            $mpId = str_replace("PA", "", $mp['uid']);

            if (file_exists("../assets/imgs/deputes_original/depute_" . $mpId . ".png")) {
              if (!file_exists("../assets/imgs/deputes_ogp/ogp_deputes_PA" . $mpId . ".png")) {

                ?>

                <p>
                  <a href="http://localhost/datan/scripts/code_photos_ogp_export.php?uid=<?= $mp['uid'] ?>" target="_blank"><?= $mp['prenom'] ?> <?= $mp['nom'] ?> - <?= $mp['uid'] ?></a>
                </p>

                <?php
              }
            }

            ?>


            <?php
          }

          ?>

        </div>
      </div>
    </div>
  </body>
</html>
