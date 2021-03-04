<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>2_deputes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script downloads the photos in the folder 'deputes_original'
  These are the non-optimised photos

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_next = $url_current + 1;
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>2. Photos in folder 'deputes_original'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./2.1_deputes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <p>These are the non-optimised photos.</p>
          <?php

          include 'bdd-connexion.php';

          $donnees = $bdd->query('
            SELECT d.mpId AS uid, d.legislature
            FROM deputes_last d
            WHERE legislature IN (14, 15)
          ');

          while ($d = $donnees->fetch()) {
            $uid = substr($d['uid'], 2);
            $filename = "../assets/imgs/deputes_original/depute_" . $uid .".png";

            if (!file_exists($filename)) {
              $legislature = $d['legislature'];
        			$url = 'http://www2.assemblee-nationale.fr/static/tribun/'.$legislature.'/photos/'.$uid.'.jpg';


              if (substr(get_headers($url)[12], 9, 3) != '404' && substr(get_headers($url)[0], 9, 3) != '404') {
                $content = file_get_contents($url);
                if ($content){
                  $img = imagecreatefromstring($content);
            			$width = imagesx($img);
            			$height = imagesy($img);
            			$newwidth = $width;
            			$newheight = $height;
            			$quality = 0;
            			$thumb = imagecreatetruecolor($newwidth, $newheight);
            			imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            			imagepng($thumb, '../assets/imgs/deputes_original/depute_'.$uid.'.png', $quality);
                  echo "one image was just downloaded<br>";
                }
              }
            }
          }

           ?>
        </div>
      </div>
    </div>
  </body>
</html>
