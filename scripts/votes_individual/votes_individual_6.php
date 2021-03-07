<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>votes_6 individual</title>
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
    if (isset($_GET["vote"])) {
      $vote = $_GET["vote"];
    }
     ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>votes_6 individual</h1>
			</div>
			<div class="row">
        <div class="col-4">
  				<a class="btn btn-outline-success" href="votes_individual_7.php?vote=<?= $vote ?>" role="button">Next</a>
  			</div>
			</div>
			<div class="row mt-3">
        <?php

        	include '../bdd-connexion.php';

          if (isset($_GET["vote"])) {
            $number_to_get = $_GET["vote"];

            //DELETE FROM TABLE
            $sql_delete = "DELETE FROM groupes_cohesion WHERE voteNumero = :number_to_get";
            $stmt = $bdd->prepare($sql_delete);
            $stmt->execute(array('number_to_get' => $number_to_get));

          } else {
            exit("Please indicate the number of the vote in the url (?vote=xx)");
          }

          echo "VOTE TO GET = ".$number_to_get."<br>";

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
          WHERE vg.legislature = 15 AND (vg.voteNumero = "'.$number_to_get.'")
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
