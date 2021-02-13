<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="15">
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

  This script updates the table 'groupes_accord'

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
        <?php

        // CONNEXION SQL //
        	include 'bdd-connexion.php';

          $reponse_last_vote = $bdd->query('
          SELECT voteNumero AS lastVote
          FROM groupes_accord
          ORDER BY voteNumero DESC
          LIMIT 1
          ');

        while ($donnees_last_vote = $reponse_last_vote->fetch()) {
          echo '<p>Last vote: '.$donnees_last_vote['lastVote'].'</p><br>';
          $lastVote = $donnees_last_vote['lastVote'] + 1;
          $untilVote = $donnees_last_vote['lastVote'] + 50;
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
        $reponse_groupes = $bdd->prepare('
          SELECT uid
          FROM organes
          WHERE coteType = "GP" AND legislature = 15
        ');
        $reponse_groupes->execute();
        $groupes = $reponse_groupes->fetchAll();

        foreach ($groupes as $key => $value) {
          //echo $value['uid'];
        }

        $reponse_1 = $bdd->query('
        SELECT voteNumero, organeRef, positionGroupe, o.libelle
        FROM groupes_cohesion t1
        JOIN organes o ON t1.organeRef = o.uid
        WHERE voteNUmero >= "'.$lastVote.'" AND voteNumero <= "'.$untilVote.'" AND t1.legislature = 15
        ');

        ?>


        <table>
          <thead>
            <td>#</td>
            <td>voteNumero</td>
            <td>organe</td>
            <td>libelle</td>
            <td>positionGroupe</td>
            <?php foreach ($groupes as $key => $value): ?>
              <td>check <?= $value['uid'] ?></td>
              <td>PO<?= $value['uid'] ?></td>
              <td>acc<?= $value['uid'] ?></td>
            <?php endforeach; ?>
          </thead>

        <?php

        $i = 1;
        //echo "<br><br>";
        //echo "nbr of groups => ";
        //echo count($groupes);
        //echo "<br><br>";


        while ($data_1 = $reponse_1->fetch()) {

          $query_var_1 = 'SELECT voteNumero,';
          $query_var_2 = '';
          foreach ($groupes as $key => $value) {
            if ($key+1 < count($groupes)) {
              $query_var_2 = $query_var_2.' SUM(CASE WHEN organeRef = "'.$value['uid'].'" THEN positionGroupe ELSE NULL END) AS "'.$value['uid'].'",';
            } else {
              $query_var_2 = $query_var_2.' SUM(CASE WHEN organeRef = "'.$value['uid'].'" THEN positionGroupe ELSE NULL END) AS "'.$value['uid'].'"';
            }
          }
          $query_var_3 = '
            FROM groupes_cohesion
            WHERE voteNUmero = "'.$data_1['voteNumero'].'" AND legislature = 15
            GROUP BY voteNumero';

          $query_var = $query_var_1.' '.$query_var_2.' '.$query_var_3;

          $reponse_2 = $bdd->query($query_var);

                 while ($data_2 = $reponse_2->fetch()) {

                 //print_r($data_2);

                 foreach ($groupes as $key => $value) {
                   $groupe_id = $value["uid"];
                   $groupes[$key]["score"] = NULL;
                   if ($data_2[$groupe_id] == NULL) {
                     $groupes[$key]["score"] = NULL;
                   } elseif ($data_2[$groupe_id] === $data_1["positionGroupe"]) {
                     $groupes[$key]["score"] = 1;
                   } else {
                     $groupes[$key]["score"] = 0;
                   }
                 }

                echo '<tr>';
                echo '<td>'.$i.'</td>';
                echo '<td>'.$data_1['voteNumero'].'</td>';
                echo '<td>'.$data_1['organeRef'].'</td>';
                echo '<td>'.$data_1['libelle'].'</td>';
                echo '<td>'.$data_1['positionGroupe'].'</td>';

                foreach ($groupes as $key => $value) {
                  $groupe_id = $value["uid"];
                  echo '<td>'.$groupe_id.'</td>';
                  echo '<td>'.$data_2[$groupe_id].'</td>';
                  echo '<td>'.$value["score"].'</td>';
                }

                echo '<tr>';

                $i++;


                $sql_prepare_1 = '
                INSERT INTO groupes_accord (voteNumero, organeRef, positionGroupe,
                ';
                $sql_prepare_2 = '';
                foreach ($groupes as $key => $value) {
                  if ($key+1 < count($groupes)) {
                    $sql_prepare_2 = $sql_prepare_2.' '.$value['uid'].',';
                  } else {
                    $sql_prepare_2 = $sql_prepare_2.' '.$value['uid'];
                  }
                }
                $sql_prepare_3 = '
                ) VALUES (:voteNumero, :organeRef, :positionGroupe,
                ';
                $sql_prepare_4 = '';
                foreach ($groupes as $key => $value) {
                  if ($key+1 < count($groupes)) {
                    $sql_prepare_4 = $sql_prepare_4.' :'.$value['uid'].',';
                  } else {
                    $sql_prepare_4 = $sql_prepare_4.' :'.$value['uid'].')';
                  }
                }

                $sql_prepare = $sql_prepare_1." ".$sql_prepare_2." ".$sql_prepare_3." ".$sql_prepare_4;

                $sql_array = array(
                  'voteNumero' => $data_1['voteNumero'],
                  'organeRef' => $data_1['organeRef'],
                  'positionGroupe' => $data_1['positionGroupe'],
                );
                foreach ($groupes as $key => $value) {
                  $groupe_id = $value['uid'];
                  $score = $value['score'];
                  $sql_array += [$groupe_id => $score];
                }

                //print_r($sql_array);

                $sql = $bdd->prepare($sql_prepare);
                $sql->execute($sql_array);


                }
        }

        ?>
        </table>
      </div>
    </div>
  </body>
</html>
