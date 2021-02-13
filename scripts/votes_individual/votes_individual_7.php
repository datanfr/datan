<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="">
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
  <body>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>vote_7 individual</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
			</div>
			<div class="row mt-3">
        <?php

        // CONNEXION SQL //
        	include '../bdd-connexion.php';

          if (isset($_GET["vote"])) {
            $number_to_get = $_GET["vote"];

            //DELETE FROM TABLE
            $sql_delete = "DELETE FROM groupes_accord WHERE num = :number_to_get";
            $stmt = $bdd->prepare($sql_delete);
            $stmt->execute(array('number_to_get' => $number_to_get));

          } else {
            exit("Please indicate the number of the vote in the url (?vote=xx)");
          }


        echo 'VOTE TO GET => '.$number_to_get.'<br>';

        $bdd->query('SET SQL_BIG_SELECTS=1');

        echo '<br><br>';

        $reponse_1 = $bdd->query('
        SELECT voteNumero, organeRef, positionGroupe, o.libelle
        FROM groupes_cohesion t1
        JOIN organes o ON t1.organeRef = o.uid
        WHERE voteNUmero = "'.$number_to_get.'" AND t1.legislature = 15
        ');

        ?>


        <table>
          <thead>
            <td>#</td>
            <td>voteNumero</td>
            <td>organe</td>
            <td>libelle</td>
            <td>positionGroupe</td>
            <td>PO730964</td>
            <td>accPO730964</td>
            <td>PO730934</td>
            <td>accPO730934</td>
            <td>PO730970</td>
            <td>accPO730970</td>
            <td>PO730952</td>
            <td>accPO730952</td>
            <td>PO730946</td>
            <td>accPO730946</td>
            <td>PO723569</td>
            <td>accPO723569</td>
            <td>PO730958</td>
            <td>accPO730958</td>
            <td>PO730940</td>
            <td>accPO730940</td>
            <td>PO744425</td>
            <td>accPO744425</td>
            <td>PO758835</td>
            <td>accPO758835</td>
            <td>PO759900</td>
            <td>accPO759900</td>
            <td>PO765636</td>
            <td>accPO765636</td>
            <td>PO767217</td>
            <td>accPO767217</td>
            <td>PO771789</td>
            <td>PO771789</td>
            <td>PO771889</td>
            <td>PO771889</td>
            <td>PO771923</td>
            <td>PO771923</td>
          </thead>

        <?php

        $i = 1;


        while ($data_1 = $reponse_1->fetch()) {






/*
PO765636
PO767217
*/

          $reponse_2 = $bdd->query('
              SELECT voteNumero,
              SUM(CASE WHEN organeRef = "PO730964" THEN positionGroupe ELSE NULL END) AS "PO730964",
              SUM(CASE WHEN organeRef = "PO730934" THEN positionGroupe ELSE NULL END) AS "PO730934",
              SUM(CASE WHEN organeRef = "PO730970" THEN positionGroupe ELSE NULL END) AS "PO730970",
              SUM(CASE WHEN organeRef = "PO730952" THEN positionGroupe ELSE NULL END) AS "PO730952",
              SUM(CASE WHEN organeRef = "PO730946" THEN positionGroupe ELSE NULL END) AS "PO730946",
              SUM(CASE WHEN organeRef = "PO723569" THEN positionGroupe ELSE NULL END) AS "PO723569",
              SUM(CASE WHEN organeRef = "PO730958" THEN positionGroupe ELSE NULL END) AS "PO730958",
              SUM(CASE WHEN organeRef = "PO730940" THEN positionGroupe ELSE NULL END) AS "PO730940",
              SUM(CASE WHEN organeRef = "PO744425" THEN positionGroupe ELSE NULL END) AS "PO744425",
              SUM(CASE WHEN organeRef = "PO758835" THEN positionGroupe ELSE NULL END) AS "PO758835",
              SUM(CASE WHEN organeRef = "PO759900" THEN positionGroupe ELSE NULL END) AS "PO759900",
              SUM(CASE WHEN organeRef = "PO765636" THEN positionGroupe ELSE NULL END) AS "PO765636",
              SUM(CASE WHEN organeRef = "PO767217" THEN positionGroupe ELSE NULL END) AS "PO767217",
              SUM(CASE WHEN organeRef = "PO771789" THEN positionGroupe ELSE NULL END) AS "PO771789",
              SUM(CASE WHEN organeRef = "PO771889" THEN positionGroupe ELSE NULL END) AS "PO771889",
              SUM(CASE WHEN organeRef = "PO771923" THEN positionGroupe ELSE NULL END) AS "PO771923"
              FROM groupes_cohesion
              WHERE voteNUmero = "'.$data_1['voteNumero'].'" AND legislature = 15
              GROUP BY voteNumero
          ');

                 while ($data_2 = $reponse_2->fetch()) {

                   if ($data_2['PO730964'] == NULL) {
                     $PO730964 = NULL;
                   } elseif ($data_2['PO730964'] === $data_1['positionGroupe']) {
                    $PO730964 = 1;
                  } else {
                    $PO730964 = 0;
                  }

                  if ($data_2['PO730934'] == NULL) {
                    $PO730934 = NULL;
                  } elseif ($data_2['PO730934'] === $data_1['positionGroupe']) {
                    $PO730934 = 1;
                  } else {
                    $PO730934 = 0;
                  }

                  if ($data_2['PO730970'] == NULL) {
                    $PO730970 = NULL;
                  } elseif ($data_2['PO730970'] === $data_1['positionGroupe']) {
                    $PO730970 = 1;
                  } else {
                    $PO730970 = 0;
                  }

                  if ($data_2['PO730952'] == NULL) {
                    $PO730952 = NULL;
                  } elseif ($data_2['PO730952'] === $data_1['positionGroupe']) {
                    $PO730952 = 1;
                  } else {
                    $PO730952 = 0;
                  }

                  if ($data_2['PO730946'] == NULL) {
                    $PO730946 = NULL;
                  } elseif ($data_2['PO730946'] === $data_1['positionGroupe']) {
                    $PO730946 = 1;
                  } else {
                    $PO730946 = 0;
                  }

                  if ($data_2['PO723569'] == NULL) {
                    $PO723569 = NULL;
                  } elseif ($data_2['PO723569'] === $data_1['positionGroupe']) {
                    $PO723569 = 1;
                  } else {
                    $PO723569 = 0;
                  }

                  if ($data_2['PO730958'] == NULL) {
                    $PO730958 = NULL;
                  } elseif ($data_2['PO730958'] === $data_1['positionGroupe']) {
                    $PO730958 = 1;
                  } else {
                    $PO730958 = 0;
                  }

                  if ($data_2['PO730940'] == NULL) {
                    $PO730940 = NULL;
                  } elseif ($data_2['PO730940'] === $data_1['positionGroupe']) {
                    $PO730940 = 1;
                  } else {
                    $PO730940 = 0;
                  }

                  if ($data_2['PO744425'] == NULL) {
                    $PO744425 = NULL;
                  } elseif ($data_2['PO744425'] === $data_1['positionGroupe']) {
                    $PO744425 = 1;
                  } else {
                    $PO744425 = 0;
                  }

                  if ($data_2['PO758835'] == NULL) {
                    $PO758835 = NULL;
                  } elseif ($data_2['PO758835'] === $data_1['positionGroupe']) {
                    $PO758835 = 1;
                  } else {
                    $PO758835 = 0;
                  }

                  if ($data_2['PO759900'] == NULL) {
                    $PO759900 = NULL;
                  } elseif ($data_2['PO759900'] === $data_1['positionGroupe']) {
                    $PO759900 = 1;
                  } else {
                    $PO759900 = 0;
                  }

                  if ($data_2['PO765636'] == NULL) {
                    $PO765636 = NULL;
                  } elseif ($data_2['PO765636'] === $data_1['positionGroupe']) {
                    $PO765636 = 1;
                  } else {
                    $PO765636 = 0;
                  }

                  if ($data_2['PO767217'] == NULL) {
                    $PO767217 = NULL;
                  } elseif ($data_2['PO767217'] === $data_1['positionGroupe']) {
                    $PO767217 = 1;
                  } else {
                    $PO767217 = 0;
                  }

                  if ($data_2['PO771789'] == NULL) {
                    $PO771789 = NULL;
                  } elseif ($data_2['PO771789'] === $data_1['positionGroupe']) {
                    $PO771789 = 1;
                  } else {
                    $PO771789 = 0;
                  }

                  if ($data_2['PO771889'] == NULL) {
                    $PO771889 = NULL;
                  } elseif ($data_2['PO771889'] === $data_1['positionGroupe']) {
                    $PO771889 = 1;
                  } else {
                    $PO771889 = 0;
                  }

                  if ($data_2['PO771923'] == NULL) {
                    $PO771923 = NULL;
                  } elseif ($data_2['PO771923'] === $data_1['positionGroupe']) {
                    $PO771923 = 1;
                  } else {
                    $PO771923 = 0;
                  }

                  echo '<tr>';
                  echo '<td>'.$i.'</td>';
                  echo '<td>'.$data_1['voteNumero'].'</td>';
                  echo '<td>'.$data_1['organeRef'].'</td>';
                  echo '<td>'.$data_1['libelle'].'</td>';
                  echo '<td>'.$data_1['positionGroupe'].'</td>';

                  echo '<td>'.$data_2['PO730964'].'</td>';
                  echo '<td>'.$PO730964.'</td>';

                  echo '<td>'.$data_2['PO730934'].'</td>';
                  echo '<td>'.$PO730934.'</td>';

                  echo '<td>'.$data_2['PO730970'].'</td>';
                  echo '<td>'.$PO730970.'</td>';

                  echo '<td>'.$data_2['PO730952'].'</td>';
                  echo '<td>'.$PO730952.'</td>';

                  echo '<td>'.$data_2['PO730946'].'</td>';
                  echo '<td>'.$PO730946.'</td>';

                  echo '<td>'.$data_2['PO723569'].'</td>';
                  echo '<td>'.$PO723569.'</td>';

                  echo '<td>'.$data_2['PO730958'].'</td>';
                  echo '<td>'.$PO730958.'</td>';

                  echo '<td>'.$data_2['PO730940'].'</td>';
                  echo '<td>'.$PO730940.'</td>';

                  echo '<td>'.$data_2['PO744425'].'</td>';
                  echo '<td>'.$PO744425.'</td>';

                  echo '<td>'.$data_2['PO758835'].'</td>';
                  echo '<td>'.$PO758835.'</td>';

                  echo '<td>'.$data_2['PO759900'].'</td>';
                  echo '<td>'.$PO759900.'</td>';

                  echo '<td>'.$data_2['PO765636'].'</td>';
                  echo '<td>'.$PO765636.'</td>';

                  echo '<td>'.$data_2['PO767217'].'</td>';
                  echo '<td>'.$PO767217.'</td>';

                  echo '<td>'.$data_2['PO771789'].'</td>';
                  echo '<td>'.$PO771789.'</td>';

                  echo '<td>'.$data_2['PO771889'].'</td>';
                  echo '<td>'.$PO771889.'</td>';

                  echo '<td>'.$data_2['PO771923'].'</td>';
                  echo '<td>'.$PO771923.'</td>';

                  echo '<tr>';

                  $i++;

                  $sql = $bdd->prepare("INSERT INTO groupes_accord (voteNumero, organeRef, positionGroupe, PO730964, PO730934, PO730970, PO730952, PO730946, PO723569, PO730958, PO730940, PO744425, PO758835, PO759900, PO765636, PO767217, PO771789, PO771889, PO771923) VALUES (:voteNumero, :organeRef, :positionGroupe, :PO730964, :PO730934, :PO730970, :PO730952, :PO730946, :PO723569, :PO730958, :PO730940, :PO744425, :PO758835, :PO759900, :PO765636, :PO767217, :PO771789, :PO771889, :PO771923)");
                  $sql->execute(array('voteNumero' => $data_1['voteNumero'], 'organeRef' => $data_1['organeRef'], 'positionGroupe' => $data_1['positionGroupe'],'PO730964' => $PO730964, 'PO730934' => $PO730934, 'PO730970' => $PO730970, 'PO730952' => $PO730952, 'PO730946' => $PO730946, 'PO723569' => $PO723569, 'PO730958' => $PO730958, 'PO730940' => $PO730940, 'PO744425' => $PO744425, 'PO758835' => $PO758835, 'PO759900' => $PO759900, 'PO765636' => $PO765636, 'PO767217' => $PO767217, 'PO771789' => $PO771789, 'PO771889' => $PO771889, 'PO771923' => $PO771923));

                }
        }

        ?>
        </table>
      </div>
    </div>
  </body>
</html>
