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
        <a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME'] . '' . $_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
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
          $output_filename = "../assets/imgs/deputes_nobg/depute_" . $uid . ".png";
          $input_filename = "../assets/imgs/deputes_nobg_import/depute_" . $uid . ".png";

          if (!file_exists($output_filename)) {
            $content = file_get_contents($input_filename);
            if ($content) {
              $img = imagecreatefromstring($content);
              $width = imagesx($img);
              $height = imagesy($img);

              $filename = realpath($input_filename);
              $mime = mime_content_type($input_filename);
              $info = pathinfo($input_filename);
              $name = $info['basename'];
              $output = new CURLFile($filename, $mime, $name);
              $data = array(
                "files" => $output,
              );
              // 2.
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/');
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
              $result = curl_exec($ch);

              if (curl_errno($ch)) {
                $result = curl_error($ch);
              }
              curl_close($ch);

              $arr_result = json_decode($result);
              echo "<pre>";
              print_r($arr_result);
              echo "</pre>";

              if ($arr_result->dest) {
                file_put_contents($output_filename, file_get_contents($arr_result->dest));
                $reducedBy = ($arr_result->src_size - $arr_result->dest_size) / $arr_result->src_size * 100;
                echo "file size reduced by ".$reducedBy."% = (src_size-dest_size)/src_size";
              }

              // END RESMUSH
            }
          }
          echo '<p>' . $uid . ' = <img src=' . $input_filename . '>'.'=> <img src=' . $output_filename . '></p>';
          echo '<br>';
          echo "<br>";
        }

        ?>
      </div>
    </div>
  </div>
</body>

</html>
