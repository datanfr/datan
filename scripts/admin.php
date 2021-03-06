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
    <header>
    </header>
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
            <h1>Liens de mise à jour de la base</h1>
          </div>
          <div class="row">
            <div class="col">
              <a class="btn btn-outline-primary my-3" href="./" role="button">Back</a>
              <br>
              <h3 class="mt-3">Delete caching of webpages+SQL queries</h3>
              <a class="btn btn-primary my-3" href="../cache/delete_all" role="button">Delete all caching</a>
              <br>
              <h3 class="mt-3">Database structure update</h3>
              <a class="btn btn-danger my-3" href="update_dataset/20210305_update_database.php" role="button">UPDATE DATABASE (March 5, 2021)</a>
              <a class="btn btn-warning my-3" href="update_dataset/add_twitter_facebook.php" role="button">Add Social Network</a>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="my-4 p-3" style="border: 5px solid #00B794">
                <h3>Data update</h3>
                <div class="list-group">
                  <a href="1_deputes.php" class="list-group-item list-group-item-action list-group-item-primary">1. Députés/Mandats/Oranes - Dernière mise à jour => <b><?php echo $maj_depute ?></b></a>
                  <hr>
                  <a href="votes_1.php" class="list-group-item list-group-item-action list-group-item-primary">2. Votes (update point 1 before)</a>
                  <hr>
                  <a href="code_photos.php" class="list-group-item list-group-item-action list-group-item-primary">3. Code photos RESMUSH</a>
                </div>
              </div>
              <h3 class="mt-3">Unused scripts</h2>
              <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action">Code communes_slug (code_communes_slug.php)</a>
                <a href="#" class="list-group-item list-group-item-action">Code communes_dpt (code_communes_slug.php)</a>
                <a href="#" class="list-group-item list-group-item-action">Cities (code_communes_slug.php)</a>
                <a href="#" class="list-group-item list-group-item-action">Code get_twitter (code_communes_slug.php)</a>
              </div>
              <h3 class="mt-3">Delete sripts + get individual votes</h3>
              <div class="list-group">
                <a href="supp.php" class="list-group-item list-group-item-action list-group-item-danger">Suppression de bases</a>
                <a href="votes_individual" class="list-group-item list-group-item-action list-group-item-danger">Get individual votes</a>
              </div>
            </div>
          </div>
        </div>


    </main>
    <footer>

    </footer>

</body>
</html>
