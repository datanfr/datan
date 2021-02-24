<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>11_deputes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the table 'deputes_all'

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
				<h1>11. Create deputes_all table</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_deputes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th>mpId</th>
                <th>legislature</th>
                <th>mandatId</th>
                <th>libelle</th>
                <th>libelleAbrev</th>
                <th>groupeId</th>
                <th>couleurAssociee</th>
                <th>dateFin</th>
                <th>dateMaj</th>
              </tr>
            </thead>
            <tbody>
  				<?php
            include 'bdd-connexion.php';
            $dateMaj = date('Y-m-d');
            $bdd->exec('TRUNCATE TABLE deputes_all');

            $query = $bdd->query('
              SELECT mpId, legislature, datePriseFonction, causeFin
              FROM mandat_principal
              GROUP BY mpId, legislature
            ');

            $i = 1;

            while ($data = $query->fetch()) {
              $mpId = $data['mpId'];
              $legislature = $data['legislature'];

              // Get the mandat_principal

              $mandatPrincipal = $bdd->query('
                SELECT *
                FROM mandat_principal
                WHERE mpId = "'.$mpId.'" AND preseance = 50 AND legislature = '.$legislature.'
                ORDER BY !ISNULL(dateFin), dateFin DESC
                LIMIT 1
              ');

              if ($mandatPrincipal->rowCount() > 0) {
                while ($mandat = $mandatPrincipal->fetch()) {
                  $dateFin = $mandat['dateFin'];
                  $mandatId = $mandat['mandatId'];
                }
              } else {
                echo "ERROR";
              }

              $mandatGroupes = $bdd->query('
              SELECT o.libelle, o.libelleAbrev, o.uid AS groupeId, o.couleurAssociee
              FROM mandat_groupe mg
              LEFT JOIN organes o ON o.uid = mg.organeRef
              WHERE mg.mpId = "'.$mpId.'" AND mg.legislature = '.$legislature.' AND mg.preseance >= 20
              ORDER BY !ISNULL(mg.dateFin), mg.dateFin DESC
              LIMIT 1
              ');

              if ($mandatGroupes->rowCount() > 0) {
                while ($mandatGroupe = $mandatGroupes->fetch()) {
                  $libelle = $mandatGroupe['libelle'];
                  $libelleAbrev = $mandatGroupe['libelleAbrev'];
                  $groupeId = $mandatGroupe['groupeId'];
                  $couleurAssociee = $mandatGroupe['couleurAssociee'];
                }
              } else {
                echo "ERROR -- ";
                echo $mpId." -- ".$legislature."<br>";
                $libelle = NULL;
                $libelleAbrev = NULL;
                $groupeId = NULL;
                $couleurAssociee = NULL;
              }


              ?>

              <tr>
                <th scope="row"><?= $i ?></th>
                <td scope="row"><?= $mpId ?></td>
                <td><?= $legislature ?></td>
                <td><?= $mandatId ?></td>
                <td><?= $libelle ?></td>
                <td><?= $libelleAbrev ?></td>
                <td><?= $groupeId ?></td>
                <td><?= $couleurAssociee ?></td>
                <td><?= $dateFin ?></td>
                <td><?= $dateMaj ?></td>
              </tr>

              <?php

              // SQL //
              $sql = $bdd->prepare("INSERT INTO deputes_all (mpId, legislature, mandatId, libelle, libelleAbrev, groupeId, couleurAssociee, dateFin, dateMaj) VALUES (:mpId, :legislature, :mandatId, :libelle, :libelleAbrev, :groupeId, :couleurAssociee, :dateFin, :dateMaj)");
              $sql->execute(array('mpId' => $mpId, 'legislature' => $legislature, 'mandatId' => $mandatId, 'libelle' => $libelle, 'libelleAbrev' => $libelleAbrev, 'groupeId' => $groupeId, 'couleurAssociee' => $couleurAssociee, 'dateFin' => $dateFin, 'dateMaj' => $dateMaj));
              $i++;
            }

  				?>
            </tbody>
          </table>
        </div>
			</div>
		</div>
	</body>
</html>
