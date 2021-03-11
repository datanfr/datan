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

  This script updates the table 'votes_participation_commission'

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
  					<a class="btn btn-outline-success" href="./votes_10.php" role="button">Next</a>
  					<?php else: ?>
  					<a class="btn btn-outline-success" href="./votes_10.php?legislature=<?= $legislature_to_get ?>" role="button">Next</a>
  				<?php endif; ?>
        </div>
			</div>
			<div class="row mt-3">
        <div class="col-12">
          <h2 class="bg-danger">This script needs to be refreshed until the table below is empty. The scripts automatically is automatically refreshed every 5 seconds.</h2>
          <p>Legislature to get = <?= $legislature_to_get ?></p>

          <?php

          if ($legislature_to_get == 14) {
            echo '<p class="bg-danger">This script is not run for the legislature 14, please launch the following script.</p>';
          } else {

            include 'bdd-connexion.php';

            $result = $bdd->query('
            SELECT voteNumero
            FROM votes_participation_commission
            ORDER BY voteNumero DESC
            LIMIT 1
            ');

            while ($last = $result->fetch()) {
              echo '<p>Last vote: '.$last['voteNumero'].'</p>';
              $last_vote = $last['voteNumero'];
              $until_vote = $last_vote + 10;
              echo 'until vote = '.$until_vote.'<br>';
              $legislature = 15;
            }

            if (!isset($until_vote)) {
              echo 'rien dans la base';
              $last_vote = 0;
              $until_vote = 2;
              $legislature = 15;
            }

            $bdd->query('SET SQL_BIG_SELECTS=1');

            $votes = $bdd->query('
              SELECT vi.voteNumero, vi.legislature, vi.dateScrutin, d.*, o.libelleAbrev
              FROM votes_info vi
              LEFT JOIN votes_dossiers vd ON vi.voteNumero = vd.voteNumero
              LEFT JOIN dossiers d ON vd.dossier = d.titreChemin
              LEFT JOIN organes o ON d.commissionFond = o.uid
              WHERE vi.voteNumero > "'.$last_vote.'" AND vi.voteNumero <= "'.$until_vote.'" AND vi.legislature = 15
              ORDER BY vi.voteNumero ASC
            ');

            if ($votes->rowCount() == 0) {
              $new_vote = $last_vote + 1;
              $until_vote = $new_vote + 2;
              echo "Need to jump a vote.<br>";
              echo "NEW VOTE = ".$new_vote;
              $votes = $bdd->query('
                SELECT vi.voteNumero, vi.legislature, vi.dateScrutin, d.*, o.libelleAbrev
                FROM votes_info vi
                LEFT JOIN votes_dossiers vd ON vi.voteNumero = vd.voteNumero
                LEFT JOIN dossiers d ON vd.dossier = d.titreChemin
                LEFT JOIN organes o ON d.commissionFond = o.uid
                WHERE vi.voteNumero > "'.$last_vote.'" AND vi.voteNumero <= "'.$until_vote.'" AND vi.legislature = 15
                ORDER BY vi.voteNumero ASC
              ');
            }

            while ($vote = $votes->fetch()) {
              echo '<h1>Chercher députés pour = '.$vote['voteNumero'].'</h1>';
              $voteNumero = $vote['voteNumero'];
              $voteDate = $vote['dateScrutin'];
              $commissionFond = $vote['commissionFond'];
              $commissionFondAbrev = $vote['libelleAbrev'];
              echo 'date = '.$voteDate.'<br>';
              echo 'voteNumero = '.$voteNumero.'<br>';
              echo 'commissionFond = '.$commissionFond.'<br>';
              echo 'commissionLibelleAbev = '.$commissionFondAbrev.'<br>';


              if ($commissionFond == NULL) {
                echo "pas de commission parlementaire";

                // Insert into database
                $sql = $bdd->prepare("INSERT INTO votes_participation_commission (legislature, voteNumero, mpId, participation) VALUES (:legislature, :voteNumero, :mpId, :participation)");
                $sql->execute(array('legislature' => $legislature, 'voteNumero' => $voteNumero, 'mpId' => NULL, 'participation' => NULL));

              } else {

                $deputes = $bdd->query('
                  SELECT *
                  FROM votes_participation vp
                  LEFT JOIN mandat_secondaire ms ON vp.mpId = ms.mpId
                  WHERE vp.voteNumero = "'.$voteNumero.'" AND ms.typeOrgane = "COMPER" AND ms.codeQualite = "Membre" AND ms.organeRef = "'.$commissionFond.'" AND ((DATE_ADD(ms.dateDebut, INTERVAL 1 MONTH) <= "'.$voteDate.'" AND ms.dateFin >= "'.$voteDate.'") OR (DATE_ADD(ms.dateDebut, INTERVAL 1 MONTH) <= "'.$voteDate.'" AND ms.dateFin IS NULL)) AND vp.participation IS NOT NULL
                ');

                $i = 1;

                if ($deputes->rowCount() > 0) {

                  while ($depute = $deputes->fetch()) {
                    $legislature = $depute['legislature'];
                    $voteNumero = $depute['voteNumero'];
                    $mpId = $depute['mpId'];
                    $participation = $depute['participation'];
                    $organeRef = $depute['organeRef'];

                    echo $i." / ".$legislature." / ".$voteNumero." / ".$mpId." / ".$participation." / ".$organeRef."<br>";
                    $i++;

                    // Insert into database
                    $sql = $bdd->prepare("INSERT INTO votes_participation_commission (legislature, voteNumero, mpId, participation) VALUES (:legislature, :voteNumero, :mpId, :participation)");
                    $sql->execute(array('legislature' => $legislature, 'voteNumero' => $voteNumero, 'mpId' => $mpId, 'participation' => $participation));

                  }

                } else {

                  // Insert into database
                  $sql = $bdd->prepare("INSERT INTO votes_participation_commission (legislature, voteNumero, mpId, participation) VALUES (:legislature, :voteNumero, :mpId, :participation)");
                  $sql->execute(array('legislature' => $legislature, 'voteNumero' => $voteNumero, 'mpId' => NULL, 'participation' => NULL));

                }
              }
            }
          }
          ?>
        </div>
      </div>
    </div>
  </body>
</html>
