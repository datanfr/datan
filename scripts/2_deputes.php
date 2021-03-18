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
        <a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME'] . '' . $_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
      </div>
      <div class="col-4">
        <a class="btn btn-outline-success" href="./2.1_deputes.php" role="button">NEXT</a>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col">
        <p>These are the non-optimised photos.</p>
        <?php
        set_time_limit(5000);
        include 'bdd-connexion.php';
        $donnees = $bdd->query('
            SELECT d.mpId AS uid, d.legislature
            FROM deputes_last d
            WHERE legislature IN (14, 15)
          ');

        if (!isset($_SERVER['API_KEY_NOBG'])) {
        ?>
          try adding API_KEY_NOBG env variable to download image from paying website
          <a href="https://www.remove.bg/dashboard#api-key">https://www.remove.bg/dashboard#api-key</a><br>
          50 img/month free</br></br>
        <?php
        }
        $originalFolder = "../assets/imgs/deputes_original/";
        if (!file_exists($originalFolder)) mkdir($originalFolder);
        while ($d = $donnees->fetch()) {
          $uid = substr($d['uid'], 2);
          $filename = "../assets/imgs/deputes_original/depute_" . $uid . ".png";
          $legislature = $d['legislature'];
          $url = 'http://www2.assemblee-nationale.fr/static/tribun/' . $legislature . '/photos/' . $uid . '.jpg';

          if (!file_exists($filename)) {
            if (substr(get_headers($url)[12], 9, 3) != '404' && substr(get_headers($url)[0], 9, 3) != '404') {
              $content = file_get_contents($url);
              if ($content) {
                $img = imagecreatefromstring($content);
                $width = imagesx($img);
                $height = imagesy($img);
                $newwidth = $width;
                $newheight = $height;
                $quality = 0;
                $thumb = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagepng($thumb, '../assets/imgs/deputes_original/depute_' . $uid . '.png', $quality);
                echo "one image was just downloaded<br>";
              }
            }
          }
          //$nobg => no background
          $nobgFolder = "../assets/imgs/deputes_nobg_import/";
          if (!file_exists($nobgFolder)) mkdir($nobgFolder);
          $liveUrl = 'https://datan.fr/assets/imgs/deputes_nobg_import/depute_' . $uid . '.png';
          $nobgfilename = '../assets/imgs/deputes_nobg_import/depute_' . $uid . '.png';
          if (!file_exists($nobgfilename)) {
            $nobgLive = file_get_contents($liveUrl);
            if ($nobgLive) {
              file_put_contents($nobgfilename, $nobgLive);
              echo "one nobg image was just downloaded from datan.fr<br>";
            } else if (isset($_SERVER['API_KEY_NOBG'])) {
              $ch = curl_init('https://api.remove.bg/v1.0/removebg');
              curl_setopt($ch, CURLOPT_HEADER, false);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              echo "URL:" . $url . "<br>";
              curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Api-Key:' . $_SERVER['API_KEY_NOBG']
              ]);
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'image_url' => $url,
                'size' => 'preview'
              ));
              $nobg = curl_exec($ch);
              $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
              $version = curl_getinfo($ch, CURLINFO_HTTP_VERSION);
              echo "VERSON" . $version . "<br>";
              if ($nobg && $httpCode == 200) {
                file_put_contents($nobgfilename, $nobg);
                echo "one nobg image was just downloaded from remove.bg<br>";
              } else {
                echo "Error while downloading from remove.bg httpCode:" . $httpCode . "<br>";
                echo "<pre>";
                echo curl_error($ch);
                echo "</pre>";
                var_dump($nobg);
              }
              curl_close($ch);
            } else {
              echo "API_KEY_NOBG not set nothing was downloaded</br>";
            }
          }
        }

        ?>
      </div>
    </div>
  </div>
</body>

</html>