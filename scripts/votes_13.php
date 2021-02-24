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

  This script creates the table 'class_loyaute_all' using the deputes_loyaute ==> the LAST group

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
					<a class="btn btn-outline-primary" href="./code_vote_10_class_participation_all.php" role="button">Back</a>
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
                  <th scope="col">mpId</th>
                  <th scope="col">score</th>
                  <th scope="col">votesM</th>
                  <th scope="col">dateMaj</th>
                </tr>
              </thead>
              <tbody>
        <?php

        // CONNEXION SQL //
        	include 'bdd-connexion.php';

          $bdd->query('
            DROP TABLE IF EXISTS class_loyaute_all;
            CREATE TABLE class_loyaute_all
              (id INT(5) NOT NULL AUTO_INCREMENT,
              classement INT(5) NOT NULL,
              mpId VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
              score DECIMAL(4,3) NOT NULL,
              votesN INT(15) NOT NULL,
              dateMaj DATE NOT NULL,
              PRIMARY KEY (id));
              ALTER TABLE class_loyaute_all ADD INDEX idx_mpId (mpId);
          ');

          $result = $bdd->query('
          SELECT @s:=@s+1 AS "classement", B.*
          FROM
          (
          /* TO GET THE SCORE OF UNACTIVE MPs */
          SELECT A.mpId, ROUND(AVG(v.scoreLoyaute), 3) AS score, COUNT(v.scoreLoyaute) AS votesN, 0 AS active
          FROM
          (SELECT *
          FROM deputes_all
          WHERE legislature = 15 AND dateFin IS NOT NULL
          ) A
          LEFT JOIN votes_scores v ON A.groupeMandat = v.organeId
          GROUP BY A.mpId
          UNION ALL
          /* TO GET THE SCORE OF ACTIVE MPs */
          SELECT mpId, score, votesN, 1 AS active
          FROM class_loyaute
          ) B,
          (SELECT @s:=0) AS s
          WHERE B.score IS NOT NULL
          ORDER BY B.score DESC, B.votesN DESC
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


            $sql = $bdd->prepare("INSERT INTO class_loyaute_all (classement, mpId, score, votesN, dateMaj) VALUES (:classement, :mpId, :score, :votesN, :dateMaj)");
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
