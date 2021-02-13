<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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

  This script creates the table 'class_participation_six'
  Now his is over the last twelve months
  Has changed the script the 2020-31-01 in order to get participation_commission rate + only those with more than 5 votes

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
                  <th scope="col">classement</th>
                  <th scope="col">depute_id</th>
                  <th scope="col">score_participation</th>
                  <th scope="col">nombre_votes</th>
                  <th scope="col">date_maj</th>
                </tr>
              </thead>
              <tbody>
        <?php

        // CONNEXION SQL //
        	include 'bdd-connexion.php';

          $bdd->query('
            DROP TABLE IF EXISTS class_participation_six;
            CREATE TABLE class_participation_six
              (id INT(5) NOT NULL AUTO_INCREMENT,
              classement INT(5) NOT NULL,
              mpId VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
              score DECIMAL(3,2) NOT NULL,
              votesN INT(15) NOT NULL,
              dateMaj DATE NOT NULL,
              PRIMARY KEY (id));
              ALTER TABLE class_participation_six ADD INDEX idx_mpId (mpId);
          ');

          $result = $bdd->query('
          SELECT @s:=@s+1 AS "classement", C.*
          FROM
          (
          	SELECT B.*
          	FROM
          	(
          		SELECT A.mpId, ROUND(AVG(A.participation),2) AS score, COUNT(A.participation) AS votesN, ROUND(COUNT(A.participation)/10) AS "index"
          		FROM
          		(
          			SELECT v.mpId, v.participation, vi.dateScrutin
          			FROM votes_participation_commission v
          			LEFT JOIN votes_info vi ON v.voteNumero = vi.voteNumero
          			WHERE vi.dateScrutin >= CURDATE() - INTERVAL 12 MONTH
          		) A
          		WHERE A.participation IS NOT NULL
          		GROUP BY A.mpId
          		ORDER BY ROUND(COUNT(A.participation)/10) DESC, AVG(A.participation) DESC
          	) B
          	WHERE B.mpId IN (
          		SELECT mpId
          		FROM deputes_actifs
          	)
          ) C,
          (SELECT @s:= 0) AS s
          WHERE C.votesN > 5
          ORDER BY C.score DESC, C.votesN DESC
          ');

          while ($depute = $result->fetch()) {
            $classement = $depute["classement"];
            $mpId = $depute["mpId"];
            $score = $depute["score"];
            $votesN = $depute["votesN"];
            $dateMaj = date('Y-m-d');

            echo "<tr>";
            echo "<td>".$classement."</td>";
            echo "<td>".$mpId."</td>";
            echo "<td>".$score."</td>";
            echo "<td>".$votesN."</td>";
            echo "<td>".$dateMaj."</td>";
            echo "</tr>";


            $sql = $bdd->prepare("INSERT INTO class_participation_six (classement, mpId, score, votesN, dateMaj) VALUES (:classement, :mpId, :score, :votesN, :dateMaj)");
            $sql->execute(array('classement' => $classement, 'mpId' => $mpId, 'score' => $score, 'votesN' => $votesN, 'dateMaj' => $dateMaj));
          }
        ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
