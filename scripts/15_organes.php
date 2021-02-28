<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>15_parties</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table legislatures

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 2);
      $url_next = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>15. Create legislatures table</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./" role="button">END</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <p>Create table legislatures</p>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">organeRef</th>
                <th scope="col">libelle</th>
                <th scope="col">libelleAbrev</th>
                <th scope="col">name</th>
                <th scope="col">number</th>
                <th scope="col">startDate</th>
                <th scope="col">endDate</th>
                <th scope="col">dateMaj</th>
              </tr>
            </thead>
            <tbody>
          <?php
            include 'bdd-connexion.php';
            $dateMaj = date('Y-m-d');

            $bdd->exec('
              CREATE TABLE IF NOT EXISTS legislature (
                id INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                organeRef VARCHAR(30) NOT NULL,
                libelle VARCHAR(255) NOT NULL,
                libelleAbrev VARCHAR(30) NOT NULL,
                name VARCHAR(255) NOT NULL,
                legislatureNumber TINYINT(1) NOT NULL,
                dateDebut DATE NOT NULL,
                dateFin DATE NULL,
                dateMaj DATE NOT NULL
              );

            ');

            $bdd->query('TRUNCATE TABLE legislature');

            $response = $bdd->query('
              SELECT *
              FROM organes
              WHERE coteType = "ASSEMBLEE"
            ');

            while ($data = $response->fetch()) {
              $organeRef = $data['uid'];
              $libelle = $data['libelle'];
              $libelleAbrev = $data['libelleAbrev'];
              $number = $data['legislature'];
              $dateDebut = $data['dateDebut'];
              $dateFin = $data['dateFin'];

              $name = $number."ème législature";

              ?>

              <tr>
                <td><?= $organeRef ?></td>
                <td><?= $libelle ?></td>
                <td><?= $libelleAbrev ?></td>
                <td><?= $name ?></td>
                <td><?= $number ?></td>
                <td><?= $dateDebut ?></td>
                <td><?= $dateFin ?></td>
                <td><?= $dateMaj ?></td>
              </tr>


              <?php

              // INSERT INTO DATABSSE //
              $sql = $bdd->prepare('INSERT INTO legislature (organeRef, libelle, libelleAbrev, name, legislatureNumber, dateDebut, dateFin, dateMaj) VALUES (:organeRef, :libelle, :libelleAbrev, :name, :legislatureNumber, :dateDebut, :dateFin, :dateMaj)');
              $sql -> execute(array('organeRef' => $organeRef, 'libelle' => $libelle, 'libelleAbrev' => $libelleAbrev, 'name' => $name, 'legislatureNumber' => $number, 'dateDebut' => $dateDebut, 'dateFin' => $dateFin, 'dateMaj' => $dateMaj));


            }


            /*
            $bdd->exec('
            CREATE INDEX idx_uid ON parties (uid);
            '); */


          ?>
            </tbody>
          </table>
        </div>
			</div>
		</div>
	</body>
</html>
