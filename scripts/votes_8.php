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
          <h2 class="bg-danger">This script needs to be refreshed until the table below is empty. The scripts automatically is automatically refreshed every 5 seconds.</h2>
          <?php
          // CONNEXION SQL //
        	include 'bdd-connexion.php';

          $reponse_last_vote = $bdd->query('
          SELECT voteNumero AS lastVote
          FROM deputes_accord
          ORDER BY voteNumero DESC
          LIMIT 1
          ');

          while ($donnees_last_vote = $reponse_last_vote->fetch()) {
            echo '<p>Last vote: '.$donnees_last_vote['lastVote'].'</p>';
            $lastVote = $donnees_last_vote['lastVote'] + 1;
            $untilVote = $donnees_last_vote['lastVote'] + 10;
            echo 'last vote = '.$lastVote.'<br>';
            echo 'until vote = '.$untilVote.'<br>';
          }

          if (!isset($lastVote)) {
            echo 'rien dans la base';
            $lastVote = 0;
            $untilVote = 2;
          }

          echo 'LAST VOTE = '.$lastVote.'<br>';
          echo 'NEW VOTE ='.$untilVote.'<br>';

          $bdd->query('SET SQL_BIG_SELECTS=1');

          echo '<br><br>';

          $queryGroupes = $bdd->query('
            SELECT gc.*, o.libelleAbrev
            FROM groupes_cohesion gc
            LEFT JOIN organes o ON gc.organeRef = o.uid
            WHERE gc.voteNumero >= "'.$lastVote.'" AND gc.voteNumero <= "'.$untilVote.'" AND gc.legislature = 15
          ');

          ?>

          <table>
            <thead>
              <td>#</td>
              <td>voteNumero</td>
              <td>legislature</td>
              <td>mpId</td>
              <td>vote</td>
              <td>organeRef</td>
              <td>groupe</td>
              <td>positionGroupe</td>
              <td>accord</td>
            </thead>

            <?php

            $i = 1;

            while ($groupe = $queryGroupes->fetch()) {
              $voteNumero = $groupe['voteNumero'];
              $legislature = $groupe['legislature'];
              $organeRef = $groupe['organeRef'];
              $libelleAbrev = $groupe['libelleAbrev'];
              $positionGroupe = $groupe['positionGroupe'];

              $queryMPs = $bdd->query('
                SELECT *
                FROM votes_scores
                WHERE voteNumero = "'.$voteNumero.'"
              ');

              while ($mp = $queryMPs->fetch()) {
                $mpId = $mp['mpId'];
                $vote = $mp['vote'];
                $accord = NULL;

                if ($vote === $positionGroupe) {
                  $accord = 1;
                } else {
                  $accord = 0;
                }

                ?>

                <tr>
                  <td><?= $i ?></td>
                  <td><?= $voteNumero ?></td>
                  <td><?= $legislature ?></td>
                  <td><?= $mpId ?></td>
                  <td><?= $vote ?></td>
                  <td><?= $organeRef ?></td>
                  <td><?= $libelleAbrev ?></td>
                  <td><?= $positionGroupe ?></td>
                  <td><?= $accord ?></td>
                </tr>

                <?php

                $sql = $bdd->prepare("INSERT INTO deputes_accord (voteNumero, legislature, mpId, organeRef, accord) VALUES (:voteNumero, :legislature, :mpId, :organeRef, :accord)");
                $sql->execute(array('voteNumero' => $voteNumero, 'legislature' => $legislature, 'mpId' => $mpId, 'organeRef' => $organeRef, 'accord' => $accord));

                $i++;

              }


            }


            ?>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
