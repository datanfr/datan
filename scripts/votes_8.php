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

  This script updates the table 'deputes_accord'

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

          $reponse_last_vote = $bdd->query('
          SELECT voteNumero AS lastVote
          FROM deputes_accord
          WHERE legislature = "'.$legislature_to_get.'"
          ORDER BY voteNumero DESC
          LIMIT 1
          ');

          $donnees_last_vote = $reponse_last_vote->fetch();
          $lastVote = $donnees_last_vote['lastVote'] + 1;
          $untilVote = $donnees_last_vote['lastVote'] + 10;
          echo '<p>First vote to get = '.$lastVote.'</p>';
          echo '<p>Until vote = '.$untilVote.'</p>';

          if (!isset($lastVote)) {
            echo 'rien dans la base';
            $lastVote = 1;
            $untilVote = 2;
          }

          $bdd->query('SET SQL_BIG_SELECTS=1');

          $query = $bdd->query('
          SELECT vs.voteNumero, vs.legislature, vs.mpId, gc.organeRef,
          CASE WHEN vs.vote = gc.positionGroupe THEN 1 ELSE 0 END AS accord
          FROM votes_scores vs
          LEFT JOIN groupes_cohesion gc ON vs.voteNumero = gc.voteNumero AND vs.legislature = gc.legislature
          WHERE vs.legislature = "'.$legislature_to_get.'" AND vs.voteNumero BETWEEN "'.$lastVote.'" AND "'.$untilVote.'"
          ');

          ?>

          <table>
            <thead>
              <td>#</td>
              <td>voteNumero</td>
              <td>legislature</td>
              <td>mpId</td>
              <td>organeRef</td>
              <td>accord</td>
            </thead>

            <?php

            $i = 1;

            while ($group = $query->fetch()) {

              ?>

              <tr>
                <td><?= $i ?></td>
                <td><?= $group['voteNumero'] ?></td>
                <td><?= $group['legislature'] ?></td>
                <td><?= $group['mpId'] ?></td>
                <td><?= $group['organeRef'] ?></td>
                <td><?= $group['accord'] ?></td>
              </tr>

              <?php

              $sql = $bdd->prepare("INSERT INTO deputes_accord (voteNumero, legislature, mpId, organeRef, accord) VALUES (:voteNumero, :legislature, :mpId, :organeRef, :accord)");
              $arraySql = array(
                'voteNumero' => $group['voteNumero'],
                'legislature' => $group['legislature'],
                'mpId' => $group['mpId'],
                'organeRef' => $group['organeRef'],
                'accord' => $group['accord']
              );
              $sql->execute($arraySql);

              $i++;


            }


            ?>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
