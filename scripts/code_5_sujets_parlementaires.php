<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>code_5_sujets_parlementaires</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <body>
		<div class="container" style="background-color: #e9ecef;">
      <div class="row">
        <h1>Code_5_sujets_parlementaires</h1>
      </div>

      <div class="row">
        <div class="col-12">

          <?php

          include 'bdd-connexion.php';
          $bdd->query('SET SESSION SQL_BIG_SELECTS=1');

          if(!isset($_GET['commission'], $_GET['category'])){

            $query_id = $bdd->query('
            SELECT t1.libelle, t1.libelleAbrev, t1.coteType, t1.legislature, t1.dateFin
            FROM organes t1
            LEFT JOIN sujets t2 on t1.libelleAbrev = t2.libelleAbrev
            WHERE t2.libelleAbrev IS NULL AND t1.coteType NOT IN("DELEGBUREAU", "ASSEMBLEE", "CMP", "ORGEXTPARL", "API", "COMSENAT", "GP", "MINISTERE", "GROUPESENAT", "DELEGSENAT", "CJR") AND ((t1.legislature IS NULL) OR (t1.legislature = 15)) AND ((t1.dateFin < 2010-01-01) OR (t1.dateFin IS NULL))
            LIMIT 1
            ');
            while ($commission = $query_id->fetch()) {
              $libelle = $commission['libelle'];
              $libelleAbbrev = $commission['libelleAbrev'];
              $legislature = $commission['legislature'];
              $dateFin = $commission['dateFin'];
              echo 'codeType = '.$commission['coteType'].'<br>';
              echo 'libelle = '.$libelle.'<br>';
              echo 'libelleAbbrev ='.$libelleAbbrev.'<br>';
              echo 'legislature ='.$legislature.'<br>';
              echo 'dateFin ='.$dateFin.'<br>';
            }
            $query_categories = $bdd->query('
              SELECT id, name
              FROM categories
              ORDER BY name
            ');

            ?>
            <form action="code_5_sujets_parlementaires.php" method="get">
              libelleAbrev: <input type="text" name="commission" value="<?= $libelleAbbrev ?>" readonly="readonly"><br>
              categorie:
              <select class="" name="category">
                <option value="null">NULL</option>
                <?php
                  while ($category = $query_categories->fetch()) {
                    ?>
                    <option value="<?php echo $category['id'] ?>"><?php echo $category['name'] ?></option>
                    <?php
                  }
                ?>
              </select>
              <br>
              <input type="submit" value="Submit">
            </form>

          <?php

          } else {
            echo '<h1 class="text-center">Insert categorie</h1>';
            echo '<h3 class="text-center"1>commssion = '.$_GET['commission'].'</h3>';
            echo '<h3 class="text-center"1>categorie = '.$_GET['category'].'</h3>';
            echo '<p class="text-center"><a href="code_5_sujets_parlementaires.php" class="btn btn-outline-primary">BACK</a></p>';
            echo '<br>';

            // SQL insert
            if ($_GET['category'] == "null") {
              $sujet = null;
            } else {
              $sujet = $_GET['category'];
            }
            $import = [
              'libelleAbrev' => $_GET['commission'],
              'sujet' => $sujet
            ];
            $sql = "INSERT INTO sujets (libelleAbrev, sujet) VALUES (:libelleAbrev, :sujet)";
            $stmt = $bdd->prepare($sql);
            $stmt->execute($import);
            print_r($stmt->errorInfo());
          }
        ?>
        </div>
      </div>



    </div>
  </body>
</html>
