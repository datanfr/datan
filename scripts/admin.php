<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex,nofollow">
    <title>Scripts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <body>
    <main>
      <?php
        include 'bdd-connexion.php';
        error_reporting( E_ALL );

        $all_v_installed = $bdd->query('SELECT * FROM mysql_v ORDER BY version DESC');
        $all_v_installed = $all_v_installed->fetchAll(PDO::FETCH_ASSOC);

        $last_v_installed = $all_v_installed[0];

        $file = file_get_contents("database/versions.json");
        $existing_versions = json_decode($file, TRUE);
        $existing_versions = array_reverse($existing_versions);
        $last_existing_version = $existing_versions[0];

      ?>
        <div class="container py-5">
          <div class="row">
            <div class="col-12">
              <h1>Mise à jour de la base</h1>
              <div class="alert alert-<?= $last_existing_version['version'] > $last_v_installed['version'] ? 'danger' : 'success' ?> mt-4" role="alert">
                <p>The current version of your installed database is: <?= $last_v_installed['version'] ?></p>
                <p>The latest version of the project's database is: <?= $last_existing_version['version'] ?></p>
                <p class="font-weight-bold">Your own installed version is <?= $last_existing_version['version'] > $last_v_installed['version'] ? 'not' : NULL ?> up to date.</p>
              </div>
              <table class="table mt-5">
                <thead>
                  <tr>
                    <th scope="col">Version</th>
                    <th scope="col">Object</th>
                    <th scope="col">Installed</th>
                    <th scope="col">Date of installation</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($existing_versions as $key => $value): ?>

                    <?php $installed = FALSE ?>

                    <?php foreach ($all_v_installed as $item): ?>
                      <?php if ($value['version'] == $item['version']): ?>
                        <?php $installed = $item ?>
                      <?php endif; ?>
                    <?php endforeach; ?>

                    <tr>
                      <th scope="row"><?= $value['version'] ?></th>
                      <td><?= $value['object'] ?></td>
                      <td><?= $installed ? "Yes" : "No" ?></td>
                      <td><?= $installed ? $installed['updated_at'] : NULL  ?></td>
                      <?php if (!$installed): ?>
                        <td><a type="button" href="database/update/v_<?= $value['version'] ?>.php" target="_blank" class="btn btn-secondary font-weight-bold">Install</a></td>
                      <?php else: ?>
                        <td></td>
                      <?php endif; ?>

                    </tr>

                  <?php endforeach; ?>
                </tbody>
              </table>
              <div class="alert alert-secondary mt-5" role="alert">
                <p class="font-weight-bold">Information about database updates</p>
                <p>All changes to the database should be made on the basis of the example file located in 'scripts/database/update/example.php'.</p>
                <p>Duplicate the file, rename it (ex. v_2.php for creating the version 2 of the database), and make your changes to the database.</p>
                <p>When a change is made to the database, the file 'scripts/database/versions.json' should be changed as well before the commit.</p>
              </div>
            </div>
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
