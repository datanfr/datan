<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="5">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>

    <style>
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
  </style>
  </head>
  <!--

  This script updates the table 'groupes_accord'
  It has been updated on March 3, 2020

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", "votes_", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_second = $url_current + 1;

      include "include/legislature.php";
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
          <?php if ($legislature_to_get == 15): ?>
  					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php" role="button">Next</a>
  					<?php else: ?>
  					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php?legislature=<?= $legislature_to_get ?>" role="button">Next</a>
  				<?php endif; ?>
        </div>
			</div>
			<div class="row mt-3">
        <div class="col-12">
          <h2 class="bg-danger">This script needs to be refreshed until the table below is empty. The scripts automatically is automatically refreshed every 5 seconds.</h2>
          <p>Legislature to get = <?= $legislature_to_get ?></p>
          <?php

          // CONNEXION SQL //
          include 'bdd-connexion.php';
          $dateMaj = date('Y-m-d');
          // Create table if not exists
          $bdd->query('
            CREATE TABLE IF NOT EXISTS groupes_accord (
              id INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              voteNumero INT(6) NOT NULL,
              legislature TINYINT(2) NOT NULL,
              organeRef VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
              organeRefAccord VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
              accord TINYINT(2) NULL,
              dateMaj DATE
            );
            CREATE INDEX idx_organeRef ON groupes_accord(organeRef);
            CREATE INDEX idx_organeRefAccord ON groupes_accord(organeRefAccord);
          ');

          $reponse_last_vote = $bdd->query('
          SELECT voteNumero AS lastVote
          FROM groupes_accord
          WHERE legislature = "'.$legislature_to_get.'"
          ORDER BY voteNumero DESC
          LIMIT 1
          ');

          while ($donnees_last_vote = $reponse_last_vote->fetch()) {
            $lastVote = $donnees_last_vote['lastVote'] + 1;
            $untilVote = $donnees_last_vote['lastVote'] + 99;
            echo '<p>First vote to get = '.$lastVote.'</p>';
            echo '<p>Until vote = '.$untilVote.'</p>';
          }

          if (!isset($lastVote)) {
            echo 'rien dans la base<br>';
            $lastVote = 0;
            $untilVote = 2;
          }

          $bdd->query('SET SQL_BIG_SELECTS=1');

          $response = $bdd->query('
          SELECT A.*,
          CASE WHEN positionGroupe = positionGroupeAccord THEN 1 ELSE 0 END AS accord
          FROM
          (
            SELECT t1.voteNumero, t1.legislature, t1.organeRef, t1.positionGroupe, t2.organeRef AS organeRefAccord, t2.positionGroupe AS positionGroupeAccord
            FROM groupes_cohesion t1
            LEFT JOIN groupes_cohesion t2 ON t1.voteNumero = t2.voteNumero AND t1.legislature = t2.legislature
            WHERE t1.voteNUmero BETWEEN "'.$lastVote.'" AND "'.$untilVote.'" AND t1.legislature = "'.$legislature_to_get.'"
          ) A

          ');

          ?>


          <table>
            <thead>
              <tr>
                <td>#</td>
                <td>voteNumero</td>
                <td>legislature</td>
                <td>organeRef</td>
                <td>organeRefAccord</td>
                <td>accord</td>
              </tr>
            </thead>
            <tbody>

              <?php

              $i = 1;

              while ($data = $response->fetch()) {
                ?>

                <tr>
                  <td><?= $i ?></td>
                  <td><?= $data['voteNumero'] ?></td>
                  <td><?= $data['legislature'] ?></td>
                  <td><?= $data['organeRef'] ?></td>
                  <td><?= $data['organeRefAccord'] ?></td>
                  <td><?= $data['accord'] ?></td>
                </tr>

                <?php

                $sql = $bdd->prepare("INSERT INTO groupes_accord (voteNumero, legislature, organeRef, organeRefAccord, accord, dateMaj) VALUES (:voteNumero, :legislature, :organeRef, :organeRefAccord, :accord, :dateMaj)");
                $sql->execute(array('voteNumero' => $data['voteNumero'], 'legislature' => $data['legislature'], 'organeRef' => $data['organeRef'] ,'organeRefAccord' => $data['organeRefAccord'], 'accord' => $data['accord'], 'dateMaj' => $dateMaj));

                $i++;

              }

              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
