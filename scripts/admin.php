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

        $issueMarch2021 = $bdd->query('
        SELECT distinct(voteNumero)
        FROM votes
        WHERE vote = 99
        ');

      ?>
        <div class="container">
          <div class="row">
            <h1>Liens de mise à jour de la base</h1>
            <h2> COUCOU</h2>
          </div>
          <div class="row">
            <div class="col">
              <a class="btn btn-outline-primary my-3" href="./" role="button">Back</a>
              <br>
              <h3 class="mt-3">Delete caching of webpages+SQL queries</h3>
              <a class="btn btn-primary my-3" href="../cache/delete_all" role="button">Delete all caching</a>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Update database : update elections</h5>
                  <h6 class="card-subtitle text-muted">June 21, 2021</h6>
                  <p class="card-text">
                    Update election
                    <a class="btn btn-danger my-3" href="update_dataset/20210621_update_elections.php" role="button">UPDATE DATABASE</a>
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Update database : update table for FAQ</h5>
                  <h6 class="card-subtitle text-muted">June 14, 2021</h6>
                  <p class="card-text">
                    Add two new tables for FAQ: faq_categories & faq_posts
                    <a class="btn btn-danger my-3" href="update_dataset/20210614_faq.php" role="button">UPDATE DATABASE</a>
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Update database : update table deputesAll</h5>
                  <h6 class="card-subtitle text-muted">May 5, 2021</h6>
                  <p class="card-text">
                    Add two new fields: jobs and catSocPro
                    <a class="btn btn-danger my-3" href="update_dataset/20210505_deputesAll.php" role="button">UPDATE DATABASE</a>
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Update database : two new tables for 2017 electoral results</h5>
                  <h6 class="card-subtitle text-muted">April 29, 2021</h6>
                  <p class="card-text">
                    Two new table for electoral results, and drop one unused table
                    <a class="btn btn-danger my-3" href="update_dataset/20210409_new_election_tables.php" role="button">UPDATE DATABASE</a>
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Update elections date</h5>
                  <h6 class="card-subtitle text-muted">April 16, 2021</h6>
                  <p class="card-text">
                    Update the date for regional and department elections
                    <a class="btn btn-danger my-3" href="update_dataset/20210416_elections.php" role="button">UPDATE DATABASE</a>
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Database structure update - Newsletter</h5>
                  <h6 class="card-subtitle text-muted">March 31, 2021</h6>
                  <p class="card-text">
                    New table for newsletter
                    <a class="btn btn-danger my-3" href="update_dataset/addNewsletter.php" role="button">UPDATE DATABASE</a>
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Database structure update - Fix issues with departements / circos table</h5>
                  <h6 class="card-subtitle text-muted">March 30, 2021</h6>
                  <p class="card-text">
                    Run this script to fix issues with the "circos" tables. Departements such as 1, 2, 3 has been replaced by "01", "02", "03"
                    The same for the table elect_2017_pres_2 (presidential elections).
                  </p>
                  <p>
                    <a class="btn btn-danger my-3" href="update_dataset/20210330_update_dpts_circos.php" role="button">UPDATE DATABASE</a>
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Database structure update - New legislature</h5>
                  <h6 class="card-subtitle text-muted">March 12, 2021</h6>
                  <p class="card-text">
                    First run the following script to update the database structure ==> <a class="btn btn-danger my-3" href="update_dataset/20210312_update_database.php" role="button">UPDATE DATABASE</a>
                  </p>
                  <p class="card-text">
                    Second, updat the 1. below (Députés/Mandats/Organes).
                  </p>
                  <p>
                    Finally, you can update the votes scripts. First, for the 15th legislature, use the 2. Votes button below. Second, for the 14th legislature, the button is below, in the "Past legislature" section.
                  </p>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Database structure update - Fix '99' issues in the 'votes' table</h5>
                  <h6 class="card-subtitle text-muted">March 7, 2021</h6>
                  <p class="card-text">
                    First, update the database. This script allows the 'vote' field in the 'votes' table to take NULL values ==> <a class="btn btn-danger my-3" href="update_dataset/20210307_update_database.php" role="button">UPDATE DATABASE</a>
                  </p>
                  <p class="card-text">
                    As for the 39 rows in the 'votes' table who took for the field 'vote' the value 99, you can re-run some the scripts to remove these votes and update the new value. This is not mandatory (this will be done online, but for local, 39 rows does not change much of the scores).
                  </p>
                  <p>
                    If you want to change the tables, please click on the following links, and "NEXT" when running the diferent scripts.
                  </p>
                  <div class="d-flex flex-wrap">
                    <?php
                    while ($x = $issueMarch2021->fetch()) {
                      ?>
                      <a class="btn btn-outline-primary m-1" href="votes_individual/votes_individual_1.php?vote=<?= $x['voteNumero'] ?>" target="_blank">
                        <?= $x['voteNumero'] ?>
                      </a>
                      <?php
                    }
                     ?>
                  </div>
                </div>
              </div>
              <br>
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Old database updates</h5>
                  <a class="btn btn-warning my-3" href="update_dataset/add_twitter_facebook.php" role="button">Add Social Network</a>
                  <a class="btn btn-warning my-3" href="update_dataset/20210309_update_department.php" role="button">Update departments</a>
                  <a class="btn btn-warning my-3" href="update_dataset/20210322_update_primary_key.php" role="button">Add primary keys</a>
                  <a class="btn btn-warning my-3" href="update_dataset/addNewsletter.php" role="button">Add table newsletter</a>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="my-4 p-3" style="border: 5px solid #00B794">
                <h3>Data update</h3>
                <div class="list-group">
                  <a href="cities_mayors_update.php" class="list-group-item list-group-item-action list-group-item-primary">Update cities_mayors (the open data source is updated every three month)</a>
                </div>
              </div>
              <h3 class="mt-3">Photos OGP</h2>
              <div class="list-group">
                <a href="code_photos_ogp.php" class="list-group-item list-group-item-action">Code photos OGP</a>
              </div>
              <h3 class="mt-3">Database backup</h2>
              <div class="list-group">
                <a href="exportSql.php" class="list-group-item list-group-item-action">All the database</a>
                <a href="exportSql-votes_datan.php" class="list-group-item list-group-item-action">Only votes_datan</a>
              </div>
              <h3 class="mt-3">Unused scripts</h2>
              <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action">Code communes_slug (code_communes_slug.php)</a>
                <a href="#" class="list-group-item list-group-item-action">Cities (cities.php)</a>
              </div>
              <h3 class="mt-3">Deleted scripts + get individual votes</h3>
              <div class="list-group">
                <a href="supp.php" class="list-group-item list-group-item-action list-group-item-danger">Suppression de bases</a>
                <a href="votes_individual" class="list-group-item list-group-item-action list-group-item-danger">Get individual votes</a>
              </div>
            </div>
          </div>
        </div>
    </main>
  </body>
</html>
