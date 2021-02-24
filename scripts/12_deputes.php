<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>12_deputes (json)</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script creates the JSON with all MPs.

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
				<h1>12. Create deputes_json.json</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_organes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <p>Create deputes_json.json</p>
          <?php
            include 'bdd-connexion.php';

            $reponse = $bdd->query('
            SELECT da.mpId, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug
            FROM deputes_all da
            WHERE da.legislature = 15
            ');

            $i = 1;
            $array = array();
            while ($data = $reponse->fetch()) {
              $id = $data['mpId'];
              $name = $data['nameFirst'].' '.$data['nameLast'];
              $slug = $data['nameUrl'];
              $dpt = $data['dptSlug'];

              echo '<p>'.$i.'</p>';
              echo '<p>id = '.$id.'</p>';
              echo '<p>name = '.$name.'</p>';
              echo '<p>slug = '.$slug.'</p>';
              echo '<p>dpt = '.$dpt.'</p>';

              $array[] = [
                "id" => $id,
                "name" => $name,
                "slug" => $slug,
                "dpt" => $dpt
              ];

              echo '<br>';
              $i++;
            }

            print_r($array);
            $json = json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            echo $json;

            // save file
            $dir = __DIR__;
            $dir = str_replace(array("/", "scripts", ".php"), "", $dir);
            $dir = $dir."assets/data/";
            $dir = "../assets/data/";
            echo $dir;
            $fp = fopen($dir."deputes_json.json", 'w');
            fwrite($fp, $json);
            fclose($fp);
          ?>
        </div>
			</div>
		</div>
	</body>
</html>
