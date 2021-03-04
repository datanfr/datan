<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>10_deputes</title>
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
				<h1>10. Create deputes_all table</h1>
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
                <th>nameUrl</th>
                <th>civ</th>
                <th>nameFirst</th>
                <th>nameLast</th>
                <th>age</th>
                <th>mandatId</th>
                <th>dptSlug</th>
                <th>departementNom</th>
                <th>departementCode</th>
                <th>circo</th>
                <th>libelle</th>
                <th>libelleAbrev</th>
                <th>groupeId</th>
                <th>groupeMandat</th>
                <th>couleurAssociee</th>
                <th>datePriseFonction</th>
                <th>dateFin</th>
                <th>causeFin</th>
                <th>dateMaj</th>
              </tr>
            </thead>
            <tbody>
  				<?php
            include 'bdd-connexion.php';
            $dateMaj = date('Y-m-d');
            $bdd->exec('TRUNCATE TABLE deputes_all');

            $query = $bdd->query('
              SELECT mp.mpId, mp.legislature, d.nameUrl, d.nameFirst, d.nameLast, d.civ,
              YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age
              FROM mandat_principal mp
              LEFT JOIN deputes d ON d.mpId = mp.mpId
              GROUP BY mp.mpId, mp.legislature
            ');

            $i = 1;

            while ($data = $query->fetch()) {
              $mpId = $data['mpId'];
              $legislature = $data['legislature'];
              $nameUrl = $data['nameUrl'];
              $nameFirst = $data['nameFirst'];
              $nameLast = $data['nameLast'];
              $civ = $data['civ'];
              $age = $data['age'];

              if (file_exists("../assets/imgs/deputes_webp/depute_" . str_replace("PA", "", $mpId) ."_webp.webp")) {
                $img = 1;
              } else {
                $img = 0;
              }

              if (file_exists("../assets/imgs/deputes_ogp/ogp_deputes_" . $mpId .".png")) {
                $imgOgp = 1;
              } else {
                $imgOgp = 0;
              }


              // Get the mandat_principal

              $mandatPrincipal = $bdd->query('
                SELECT mp.*, dpt.slug AS dptSlug, dpt.departement_nom AS departementNom, dpt.departement_code AS departementCode, mp.electionCirco AS circo, mp.causeFin, mp.datePriseFonction
                FROM mandat_principal mp
                LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
                WHERE mp.mpId = "'.$mpId.'" AND mp.preseance = 50 AND mp.legislature = '.$legislature.'
                ORDER BY !ISNULL(mp.dateFin), mp.dateFin DESC
                LIMIT 1
              ');

              if ($mandatPrincipal->rowCount() > 0) {
                while ($mandat = $mandatPrincipal->fetch()) {
                  $dateFin = $mandat['dateFin'];
                  $mandatId = $mandat['mandatId'];
                  $dptSlug = $mandat['dptSlug'];
                  $departementNom = $mandat['departementNom'];
                  $departementCode = $mandat['departementCode'];
                  $circo = $mandat['circo'];
                  $causeFin = $mandat['causeFin'];
                  $datePriseFonction = $mandat['datePriseFonction'];
                }
              } else {
                echo "ERROR";
              }

              $mandatGroupes = $bdd->query('
              SELECT o.libelle, o.libelleAbrev, o.uid AS groupeId, o.couleurAssociee, mg.mandatId AS groupeMandat
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
                  $groupeMandat = $mandatGroupe['groupeMandat'];
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
                <td><?= $nameUrl ?></td>
                <td><?= $civ ?></td>
                <td><?= $nameFirst ?></td>
                <td><?= $nameLast ?></td>
                <td><?= $age ?></td>
                <td><?= $mandatId ?></td>
                <td><?= $dptSlug ?></td>
                <td><?= $departementNom ?></td>
                <td><?= $departementCode ?></td>
                <td><?= $circo ?></td>
                <td><?= $libelle ?></td>
                <td><?= $libelleAbrev ?></td>
                <td><?= $groupeId ?></td>
                <td><?= $groupeMandat ?></td>
                <td><?= $couleurAssociee ?></td>
                <td><?= $datePriseFonction ?></td>
                <td><?= $dateFin ?></td>
                <td><?= $causeFin ?></td>
                <td><?= $dateMaj ?></td>
              </tr>

              <?php

              // SQL //
              $sql = $bdd->prepare("INSERT INTO deputes_all (mpId, legislature, nameUrl, civ, nameFirst, nameLast, age, dptSlug, departementNom, departementCode, circo, mandatId, libelle, libelleAbrev, groupeId, groupeMandat, couleurAssociee, datePriseFonction, dateFin, causeFin, img, imgOgp, dateMaj) VALUES (:mpId, :legislature, :nameUrl, :civ, :nameFirst, :nameLast, :age, :dptSlug, :departementNom, :departementCode, :circo, :mandatId, :libelle, :libelleAbrev, :groupeId, :groupeMandat, :couleurAssociee, :datePriseFonction, :dateFin, :causeFin, :img, :imgOgp, :dateMaj)");
              $sql->execute(array('mpId' => $mpId, 'legislature' => $legislature, 'nameUrl' => $nameUrl, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'age' => $age, 'dptSlug' => $dptSlug, 'departementNom' => $departementNom, 'departementCode' => $departementCode, 'circo' => $circo, 'mandatId' => $mandatId, 'libelle' => $libelle, 'libelleAbrev' => $libelleAbrev, 'groupeId' => $groupeId, 'groupeMandat' => $groupeMandat, 'couleurAssociee' => $couleurAssociee, 'datePriseFonction' => $datePriseFonction, 'dateFin' => $dateFin, 'causeFin' => $causeFin, 'img' => $img, 'imgOgp' => $imgOgp, 'dateMaj' => $dateMaj));
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
