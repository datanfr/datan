<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>votes_9 individual</title>
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
    if (isset($_GET["vote"])) {
      $vote = $_GET["vote"];
    }
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>votes_9 individual</h1>
			</div>
			<div class="row">
        <div class="col-4">
  				<a class="btn btn-outline-success" href="votes_individual_9.1.php?vote=<?= $vote ?>" role="button">Next</a>
  			</div>
			</div>
			<div class="row mt-3">
        <div class="col-12">
        <?php

        // CONNEXION SQL //
        	include '../bdd-connexion.php';

          if (isset($_GET["vote"])) {
            $number_to_get = $_GET["vote"];

            //DELETE FROM TABLE
            $sql_delete = "DELETE FROM votes_participation WHERE voteNumero = :number_to_get";
            $stmt = $bdd->prepare($sql_delete);
            $stmt->execute(array('number_to_get' => $number_to_get));

          } else {
            exit("Please indicate the number of the vote in the url (?vote=xx)");
          }

        $bdd->query('SET SQL_BIG_SELECTS=1');

        echo "VOTE TO GET = ".$number_to_get."<br>";
        echo '<br>';

        $votes = $bdd->query('
          SELECT voteNumero, legislature, dateScrutin
          FROM votes_info
          WHERE voteNumero = "'.$number_to_get.'" AND legislature = 15
        ');

        while ($vote = $votes->fetch()) {
          echo '<h1>Chercher députés pour = '.$vote['voteNumero'].'</h1>';
          $voteNumero = $vote['voteNumero'];
          $voteDate = $vote['dateScrutin'];
          $legislature = $vote['legislature'];
          echo 'date = '.$voteDate.'<br>';
          echo 'voteNumero = '.$voteNumero.'<br>';
          $deputes = $bdd->query('
            SELECT *
            FROM mandat_principal m
            LEFT JOIN deputes d ON d.mpId = m.mpId
            WHERE ((m.datePriseFonction < "'.$voteDate.'" AND m.dateFin > "'.$voteDate.'") OR (m.datePriseFonction < "'.$voteDate.'" AND m.dateFin IS NULL)) AND m.legislature = 15 AND m.typeOrgane = "ASSEMBLEE" AND m.codeQualite = "membre" AND m.preseance = 50
          ');

          $i = 1;
          while ($depute = $deputes->fetch()) {
            echo $i.' - '.$depute['mpId'].' - '.$vote['voteNumero'].' - voteId : '.$voteNumero.' - ';
            $mpId = $depute['mpId'];
            $voted = $bdd->query('
              SELECT vote
              FROM votes
              WHERE mpId = "'.$mpId.'" AND legislature = 15 AND voteNumero = "'.$voteNumero.'"
            ');

            if ($voted->rowCount() > 0) {
              while ($x = $voted->fetch()) {
                $v = $x["vote"];
              }
              if ($v == "nv") {
                $participation = NULL;
              } else {
                $participation = 1;
              }
            } else {
              $participation = 0;
            }

            echo 'participation = '.$participation.'<br>';
            $i++;

            $sql = $bdd->prepare("INSERT INTO votes_participation (legislature, voteNumero, mpId, participation) VALUES (:legislature, :voteNumero, :mpId, :participation)");
            $sql->execute(array('legislature' => $legislature, 'voteNumero' => $voteNumero, 'mpId' => $mpId, 'participation' => $participation));
          }
        }

        ?>
        </div>
      </div>
    </div>
  </body>
</html>
