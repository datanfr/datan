<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Code get_twitter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <body>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>Code get_twitter</h1>
			</div>


      <?php
        include 'bdd-connexion.php';
        $query = $bdd->query('
          SELECT twitter
          FROM deputes_contacts
          WHERE 1
          ORDER BY twitter ASC
        ');

      ?>


			<div class="row mt-3">
        <div class="col-12">
          <?php
          $i = 1;
          while ($x = $query->fetch()) {
            ?>

            <p>
              <?= $i ?>
              --
              <a href="https://twitter.com/<?= $x['twitter'] ?>" target="_blank">
                <?= $x['twitter'] ?>
              </a>
            </p>

            <?php
            $i++;
          }
           ?>
        </div>
			</div>
		</div>
	</body>
</html>
