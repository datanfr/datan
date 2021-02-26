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

        $maj_principal = $bdd->query('
        SELECT dateMaj
        FROM mandat_principal
        ORDER BY dateMaj
        LIMIT 1
        ');
        while ($data_princ = $maj_principal->fetch()) {
          $maj_mandat_principal = $data_princ['dateMaj'];
          $maj_mandat_principal = date("d-m-Y", strtotime($maj_mandat_principal));
        }

        $maj_groupes = $bdd->query('
        SELECT dateMaj
        FROM organes
        ORDER BY dateMaj
        LIMIT 1
        ');
        while ($data_groupes = $maj_groupes->fetch()) {
          $maj_groupe = $data_groupes['dateMaj'];
          $maj_groupe = date("d-m-Y", strtotime($maj_groupe));
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
              <a class="btn btn-primary my-3" href="votes_individual" role="button">Votes individual</a>
              <br>
              <a class="btn btn-primary my-3" href="../cache/delete_all" role="button">Delete all caching</a>
              <br>
              <a class="btn btn-danger my-3" href="update_dataset/20210226.php" role="button">UPDATE THE DATABASE (26 February 2021)</a>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="list-group">

                  <hr>
                  <a href="votes_1.php" class="list-group-item list-group-item-action"><b>1. Votes</b></a>
                  <hr>
                  <a href="1_deputes.php" class="list-group-item list-group-item-action list-group-item-primary">1. Députés - Dernière mise à jour => <b><?php echo $maj_depute ?></b></a>
                  <!--<a href="social_media.php" class="list-group-item list-group-item-action list-group-item-primary disabled">1-3. Social Media</a>-->
                  <a href="7_mandats.php" class="list-group-item list-group-item-action list-group-item-secondary">2. Mandats principaux - Dernière mise à jour => <b><?php echo $maj_mandat_principal ?></b></a>
                  <a href="10_organes.php" class="list-group-item list-group-item-action list-group-item-success">4. Organes - Dernière mise à jour => <b><?php echo $maj_groupe ?></b></a>
                  <hr>
                  <a href="code_communes_slug.php" class="list-group-item list-group-item-action">Code communes_slug</a>
                  <a href="code_communes_dpt.php" class="list-group-item list-group-item-action">Code communes_dpt</a>
                  <hr>
                  <!-- <a href="code_departements.php" class="list-group-item list-group-item-action list-group-item-warning disabled">8. Départements</a>
                  <hr>
                  <a href="code_5_sujets_parlementaires.php" class="list-group-item list-group-item-action">5. Sujets des commissions parlementaires</a>
                  <hr>-->
                  <a href="code_photos_original.php" class="list-group-item list-group-item-action">1. Code photos ORIGINAL (only localhost)</a>
                  <a href="code_photos.php" class="list-group-item list-group-item-action">2. Code photos RESMUSH (only localhost)</a>
                  <a href="code_photos_wepb.php" class="list-group-item list-group-item-action">3. Code photos webp (only localhost)</a>
                  <a href="code_photos_ogp.php" class="list-group-item list-group-item-action">4. Code photos_ogp (only localhost)</a>
                  <hr>
                  <a href="cities_1.php" class="list-group-item list-group-item-action">Cities</a>
                  <hr>
                  <a href="code_get_twitter.php" class="list-group-item list-group-item-action">Code get_twitter (online)</a>
                  <hr>
                  <a href="supp.php" class="list-group-item list-group-item-action list-group-item-danger">Suppression de bases</a>
              </div>
            </div>
          </div>
        </div>


    </main>
    <footer>

    </footer>

</body>
</html>
