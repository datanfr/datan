<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>votes_9.1 individual</title>
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
    if (isset($_GET["vote"])) {
      $vote = $_GET["vote"];
    }
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>votes_9.1 individual</h1>
			</div>
			<div class="row">
        <div class="col-4">
  				<a class="btn btn-outline-success" href="../admin.php" role="button">END</a>
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
            $sql_delete = "DELETE FROM votes_participation_commission WHERE voteNumero = :number_to_get";
            $stmt = $bdd->prepare($sql_delete);
            $stmt->execute(array('number_to_get' => $number_to_get));

          } else {
            exit("Please indicate the number of the vote in the url (?vote=xx)");
          }

        $bdd->query('SET SQL_BIG_SELECTS=1');

        echo "VOTE TO GET = ".$number_to_get."<br>";

        echo '<br>';

        $votes = $bdd->query('
          SELECT vi.voteNumero, vi.legislature, vi.dateScrutin, d.*, o.libelleAbrev
          FROM votes_info vi
          LEFT JOIN votes_dossiers vd ON vi.voteNumero = vd.voteNumero
          LEFT JOIN dossiers d ON vd.dossier = d.titreChemin
          LEFT JOIN organes o ON d.commissionFond = o.uid
          WHERE vi.voteNumero = "'.$number_to_get.'" AND vi.legislature = 15
          ORDER BY vi.voteNumero ASC
        ');

        while ($vote = $votes->fetch()) {
          echo '<h1>Chercher députés pour = '.$vote['voteNumero'].'</h1>';
          $voteNumero = $vote['voteNumero'];
          $voteDate = $vote['dateScrutin'];
          $commissionFond = $vote['commissionFond'];
          $commissionFondAbrev = $vote['libelleAbrev'];
          $legislature = $vote['legislature'];
          echo 'date = '.$voteDate.'<br>';
          echo 'voteNumero = '.$voteNumero.'<br>';
          echo 'commissionFond = '.$commissionFond.'<br>';
          echo 'commissionLibelleAbev = '.$commissionFondAbrev.'<br>';


          if ($commissionFond == NULL) {
            echo "pas de commission parlementaire";
            $legislature = 15;

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

        ?>
        </div>
      </div>
    </div>
  </body>
</html>
