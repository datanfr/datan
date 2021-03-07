<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>votes_7 individual</title>
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
    if (isset($_GET["vote"])) {
      $vote = $_GET["vote"];
    }
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>votes_7 individual</h1>
			</div>
			<div class="row">
        <div class="col-4">
  				<a class="btn btn-outline-success" href="votes_individual_8.php?vote=<?= $vote ?>" role="button">Next</a>
  			</div>
			</div>
			<div class="row mt-3">
        <?php

        // CONNEXION SQL //
        include '../bdd-connexion.php';
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

        if (isset($_GET["vote"])) {
          $number_to_get = $_GET["vote"];

          //DELETE FROM TABLE
          $sql_delete = "DELETE FROM groupes_accord WHERE voteNumero = :number_to_get";
          $stmt = $bdd->prepare($sql_delete);
          $stmt->execute(array('number_to_get' => $number_to_get));

        } else {
          exit("Please indicate the number of the vote in the url (?vote=xx)");
        }

        echo "VOTE TO GET = ".$number_to_get."<br>";

        $bdd->query('SET SQL_BIG_SELECTS=1');

        echo '<br><br>';

        $response = $bdd->query('
        SELECT A.*,
        CASE WHEN positionGroupe = positionGroupeAccord THEN 1 ELSE 0 END AS accord
        FROM
        (
          SELECT t1.voteNumero, t1.legislature, t1.organeRef, t1.positionGroupe, t2.organeRef AS organeRefAccord, t2.positionGroupe AS positionGroupeAccord
          FROM groupes_cohesion t1
          LEFT JOIN groupes_cohesion t2 ON t1.voteNumero = t2.voteNumero AND t1.legislature = t2.legislature
          WHERE t1.voteNUmero = "'.$number_to_get.'" AND t1.legislature = 15
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

            }

            ?>
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
