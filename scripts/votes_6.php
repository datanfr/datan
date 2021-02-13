<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <meta http-equiv="refresh" content="15">
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

  This script updates the table 'groupes_cohesion'

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
					<a class="btn btn-outline-success" href="./votes_<?= $url_second ?>.php" role="button">Next</a>
				</div>
			</div>
			<div class="row mt-3">
        <?php

        // formulaire ici //


        // CONNEXION SQL //


        	include 'bdd-connexion.php';

          $reponse_last_vote = $bdd->query('
          SELECT voteNumero AS lastVote
          FROM groupes_cohesion
          ORDER BY voteNumero DESC
          LIMIT 1
          ');

        while ($donnees_last_vote = $reponse_last_vote->fetch()) {
          echo 'Last vote: '.$donnees_last_vote['lastVote'].'<br>';
          $lastVote = $donnees_last_vote['lastVote'] + 1;
                $untilVote = $donnees_last_vote['lastVote'] + 100;
          echo 'last vote = '.$lastVote.'<br>';
          echo 'until vote = '.$untilVote.'<br>';
        }

          if (!isset($lastVote)) {
            echo 'rien dans la base';
            $lastVote = 0;
            $untilVote = 2;
          }


        $bdd->query('SET SQL_BIG_SELECTS=1');

        $reponseVote = $bdd->query('
        SELECT A.*,
        CASE
          WHEN A.positionGroup = A.voteResult THEN 1
          ELSE 0
        END AS scoreGagnant
        FROM
        (
          SELECT vg.voteNumero, vg.organeRef, o.libelle, vg.legislature, vg.nombrePours, vg.nombreContres, vg.nombreAbstentions,
              ROUND((GREATEST(vg.nombrePours,nombreContres, nombreAbstentions)-0.5*((nombrePours + nombreContres + nombreAbstentions)-GREATEST(vg.nombrePours, vg.nombreContres, vg.nombreAbstentions)))/(vg.nombrePours + vg.nombreContres + vg.nombreAbstentions),3) as cohesion,
          CASE
            WHEN vg.positionMajoritaire = "pour" THEN 1
            WHEN vg.positionMajoritaire = "abstention" THEN 0
            WHEN vg.positionMajoritaire = "contre" THEN -1
            WHEN vg.positionMajoritaire = "nv" THEN "nv"
            ELSE "error"
          END AS positionGroup,
          CASE
            WHEN vi.sortCode = "adopté" THEN 1
            WHEN vi.sortCode = "rejeté" THEN -1
            ELSE vi.sortCode
          END AS voteResult
          FROM votes_groupes vg
          JOIN organes o ON vg.organeRef = o.uid
          JOIN votes_info vi ON vi.voteNumero = vg.voteNumero
          WHERE vg.legislature = 15 AND (vg.voteNumero BETWEEN "'.$lastVote.'" AND "'.$untilVote.'")
        ) A
        ');

        ?>

        <table>
          <thead>
            <td>#</td>
            <td>voteNumero</td>
            <td>legislature</td>
            <td>organe</td>
            <td>oganeName</td>
            <td>pours</td>
            <td>contres</td>
            <td>abstentions</td>
            <td>cohesion</td>
            <td>positionGroup</td>
            <td>voteSort</td>
            <td>gagnant</td>
          </thead>

        <?php

        $i = 1;

        while ($donneesVote = $reponseVote->fetch()) {

            echo '<tr>';
            echo '<td>'.$i.'</td>';
            echo '<td>'.$donneesVote['voteNumero'].'</td>';
            echo '<td>'.$donneesVote['legislature'].'</td>';
            echo '<td>'.$donneesVote['organeRef'].'</td>';
            echo '<td>'.$donneesVote['libelle'].'</td>';
            echo '<td>'.$donneesVote['nombrePours'].'</td>';
            echo '<td>'.$donneesVote['nombreContres'].'</td>';
            echo '<td>'.$donneesVote['nombreAbstentions'].'</td>';
            echo '<td>'.$donneesVote['cohesion'].'</td>';
            echo '<td>'.$donneesVote['positionGroup'].'</td>';
            echo '<td>'.$donneesVote['voteResult'].'</td>';
            echo '<td>'.$donneesVote['scoreGagnant'].'</td>';
            echo '<tr>';

            $i++;


            $sql = $bdd->prepare("INSERT INTO groupes_cohesion (voteNumero, legislature, organeRef, cohesion, positionGroupe, voteSort, scoreGagnant) VALUES (:voteNumero, :legislature, :organeRef, :cohesion, :positionGroupe, :voteSort, :scoreGagnant)");
            $sql->execute(array('voteNumero' => $donneesVote['voteNumero'], 'legislature' => $donneesVote['legislature'], 'organeRef' => $donneesVote['organeRef'] ,'cohesion' => $donneesVote['cohesion'], 'positionGroupe' => $donneesVote['positionGroup'], 'voteSort' => $donneesVote['voteResult'], 'scoreGagnant' => $donneesVote['scoreGagnant']));

        }

        ?>
      </table>
      </div>
    </div>
  </body>
</html>
