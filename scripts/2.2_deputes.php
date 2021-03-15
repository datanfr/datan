<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>code_photos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script downloads the photos in the folder 'deputes'
  These are the optimised photos (with RESMUSH)

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
				<h1>Photos in folder 'deputes' (resmushed)</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
        <div class="col-4">
					<a class="btn btn-outline-success" href="./2.3_deputes.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <p>These are the optimised photos. </p>
          <?php

          include 'bdd-connexion.php';

      		$donnees = $bdd->query('
      			SELECT d.mpId AS uid, d.legislature
      			FROM deputes_last d
      			WHERE legislature IN (14, 15)
      		');

          while ($d = $donnees->fetch()) {
            $uid = substr($d['uid'], 2);
            $filename = "../assets/imgs/deputes/depute_" . $uid .".png";

            if (!file_exists($filename)) {
              $legislature = $d['legislature'];
        			$url = 'http://www2.assemblee-nationale.fr/static/tribun/'.$legislature.'/photos/'.$uid.'.jpg';

              if (substr(get_headers($url)[12], 9, 3) != '404' && substr(get_headers($url)[0], 9, 3) != '404') {
                $content = file_get_contents($url);
                if ($content) {


                  $img = imagecreatefromstring($content);
                  $width = imagesx($img);
                  $height = imagesy($img);
                  $newwidth = '130';
                  $newheight = '166.4';
                  $quality = 0;
                  $thumb = imagecreatetruecolor($newwidth, $newheight);
                  imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                  imagepng($thumb, '../assets/imgs/deputes/depute_'.$uid.'.png', $quality);

                  // Start RESMUSH CODE
                  // 1.
                  $file = "C:/wamp64/www/datan/assets/imgs/deputes/depute_".$uid.".png";
                  $mime = mime_content_type($file);
                  $info = pathinfo($file);
                  $name = $info['basename'];
                  $output = new CURLFile($file, $mime, $name);
                  $data = array(
                    "files" => $output,
                  );
                  // 2.
                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/?qlty=80');
                  curl_setopt($ch, CURLOPT_POST,1);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                  $result = curl_exec($ch);
                  if (curl_errno($ch)) {
                    $result = curl_error($ch);
                  }
                  curl_close($ch);

                  $arr_result = json_decode($result);

                  print_r($arr_result);

                  // store the optimized version of the image
                  /*
                  $ch = curl_init($arr_result->dest);
                  $dest = "C:/wamp64/www/datan/assets/imgs/deputes/resmushed_".$name;
                  $fp = fopen($dest, "wb");
                  curl_setopt($ch, CURLOPT_FILE, $fp);
                  curl_setopt($ch, CURLOPT_HEADER, 0);
                  curl_exec($ch);
                  curl_close($ch);
                  fclose($fp);
                  */
                  $url = $arr_result->dest;
                  $img = "C:/wamp64/www/datan/assets/imgs/deputes/".$name;
                  file_put_contents($img, file_get_contents($url));

                  // END RESMUSH

                  echo '<p>'.$uid.' = <img src="../assets/imgs/deputes/depute_'.$uid.'.png"></p>';
                  echo '<br>';



                  echo "<br>";

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
