<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>2.1_deputes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script downloads the photos in the folder 'deputes_webp'

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
				<h1>2.1. Photos in folder 'deputes_webp'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./2.2_deputes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <p>These are the webp.</p>
          <?php

          $dir = "../assets/imgs/deputes_original/";
          $newdir = "../assets/imgs/deputes_webp/";
          $files = scandir($dir);
          unset($files[0]);
      		unset($files[1]);
          echo "Number of photos in the deputes_original ==> ".count($files)."<br>";

          foreach ($files as $file) {
            $newfile = str_replace(".png", "", $file);
      			$newfile = $newfile."_webp.webp";

            if (!file_exists($newdir . "" . $newfile)) {
              $img = imagecreatefrompng($dir . $file);
              imagepalettetotruecolor($img);
              imagealphablending($img, true);
              imagesavealpha($img, true);
              imagewebp($img, $newdir . $newfile, 80);
              imagedestroy($img);
              echo "an image was just downloaded<br>";
            }


          }





          ?>
        </div>
      </div>
    </div>
  </body>
</html>
