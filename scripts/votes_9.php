<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <meta http-equiv="refresh" content="5">
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

  This script updates the table 'votes_participation'

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
  					<a class="btn btn-outline-success" href="./votes_9.1.php" role="button">Next</a>
  					<?php else: ?>
  					<a class="btn btn-outline-success" href="./votes_9.1.php?legislature=<?= $legislature_to_get ?>" role="button">Next</a>
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

            if ($legislature_to_get == 14) {
              $votesLeft = $bdd->query('
                SELECT voteNumero
                FROM votes_info
                WHERE legislature = 14 AND codeTypeVote IN ("SAT", "SPS", "MOC") AND voteNumero NOT IN (
                  SELECT DISTINCT(voteNumero)
                  FROM votes_participation
                  WHERE legislature = 14 AND voteNumero
                )
                ORDER BY voteNumero ASC
                LIMIT 3
              ');
            } elseif ($legislature_to_get == 15) {
              $votesLeft = $bdd->query('
                SELECT voteNumero
                FROM votes_info
                WHERE legislature = 15 AND voteNumero NOT IN (
                  SELECT DISTINCT(voteNumero)
                  FROM votes_participation
                  WHERE legislature = 15 AND voteNumero
                )
                ORDER BY voteNumero ASC
                LIMIT 3
              ');
            }

            $i = 1;


            while ($vote = $votesLeft->fetch()) {

              $voteQuery = $bdd->query('
                SELECT A.*, v.vote,
                CASE
                  WHEN vote IN ("1", "0", "-1") THEN 1
                    WHEN vote = "nv" THEN NULL
                    ELSE 0
                END AS participation
                FROM
                (
                SELECT vi.voteNumero, vi.legislature, mp.mpId
                FROM votes_info vi
                LEFT JOIN mandat_principal mp ON ((vi.dateScrutin BETWEEN mp.datePriseFonction AND mp.dateFin) OR (mp.datePriseFonction < vi.dateScrutin AND mp.dateFin IS NULL))
                WHERE vi.legislature = "'.$legislature_to_get.'" AND vi.voteNumero = "'.$vote['voteNumero'].'"
                ) A
                LEFT JOIN votes v ON A.mpId = v.mpId AND A.legislature = v.legislature AND A.voteNumero = v.voteNumero
              ');

              while ($mp = $voteQuery->fetch()) {
                echo $i." - ".$mp['legislature']." - ".$mp['voteNumero']." - ".$mp['mpId']." - ".$mp['participation']."<br>";

                $sql = $bdd->prepare("INSERT INTO votes_participation (legislature, voteNumero, mpId, participation) VALUES (:legislature, :voteNumero, :mpId, :participation)");
                $sql->execute(array('legislature' => $mp['legislature'], 'voteNumero' => $mp['voteNumero'], 'mpId' => $mp['mpId'], 'participation' => $mp['participation']));

                $i++;
              }

            }


          ?>
        </div>
      </div>
    </div>
  </body>
</html>
