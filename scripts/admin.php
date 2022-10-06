<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex,nofollow">
    <title>Scripts_imports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <body>
    <main>
      <?php
        include 'bdd-connexion.php';
        error_reporting( E_ALL );

        $maj_deputes = $bdd->query('
        SELECT dateMaj
        FROM deputes
        ORDER BY dateMaj
        LIMIT 1
        ');
        while ($data = $maj_deputes->fetch()) {
          $maj_depute = $data['dateMaj'];
          $maj_depute = date("d-m-Y", strtotime($maj_depute));
        }
      ?>
        <div class="container">
          <div class="row">
            <h1>Mise à jour de la base</h1>
          </div>
          <div class="row mt-5">
            <div class="col">
              <div class="my-4 p-3" style="border: 5px solid #00B794">
                <h3>Data update</h3>
                <div class="list-group">
                  <a href="cities_mayors_update.php" class="list-group-item list-group-item-action list-group-item-primary">Update cities_mayors (the open data source is updated every three month)</a>
                </div>
              </div>
            </div>
          </div>
        </div>
    </main>
  </body>
</html>
