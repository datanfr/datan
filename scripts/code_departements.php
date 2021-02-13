<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>code_departements_slug</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <body>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>8. Ajout des 'slug' pour la bdd 'departement'</h1>
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
          <?php

          	include 'bdd-connexion.php';

          	$reponses = $bdd->query('
          		SELECT *
          		FROM departement
          		WHERE slug is null
              ORDER BY departement_id ASC
              LIMIT 50
          		');

          	echo '<hr>';

          	while ($reponse = $reponses->fetch()) {
              $code = $reponse['departement_code'];
          		echo 'Code : '.$code.'<br>';

              $slug = $reponse['departement_slug'];
              echo 'Slug actuel : '.$slug.'<br>';

              $new_slug = $slug.'-'.$code;

              echo 'Nouveau slug : '.$new_slug.'<br>';

              echo '<br>';

              //IMPORT SQL

          		$sql = $bdd->prepare('UPDATE departement SET slug = :slug WHERE departement_code = "'.$code.'"');
          		$sql -> execute(array('slug' => $new_slug));
          	}

          ?>
        </div>
      </div>
    </div>
  </body>
</html>
