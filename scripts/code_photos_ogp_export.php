<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="description" content="Webpage description goes here" />
  <meta charset="utf-8">
  <title>Html/image</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@600;800&display=swap" rel="stylesheet">
  <style media="screen">
    body{
      font-family: 'Open Sans', sans-serif;
    }
    #element{
      width: 1200px;
      height: 630px;
      background: linear-gradient(90deg, #246B96 17.99%, #00B794 86.12%);
      padding: 0px;
      display: flex;
    }
    .container{
      position: relative;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      width: 100%;
      height: 100%;
    }
    .bottom{
      display: flex;
    }
    .content {
      width: 100%;
      height: 100%;
      position: absolute;
    }
    .logo-container {
      position: absolute;
      right: 0px;
      top: 0px;
      background-color: #fff;
      border-bottom-left-radius: 35px;
    }
    #logo{
      width: 240px;
      height: auto;
      margin-left: 30px;
      margin-top: 10px;
    }
    .inside{
      width: 100%;
      display: flex;
      flex-direction: row;
      justify-content: start;
      align-items:center;
      padding-left: 100px;
    }
    #photo{
      width: 300px;
      height: auto;
      border-radius: 25px;
    }
    .titre{
      margin-left: 50px;
      color: white;
    }
    .prenom {
      display: block;
      line-height: 1;
      font-weight: 400;
      padding: 0;
      margin: 0;
      text-align: left;
    }
    .nom{
      font-weight: 800;
      line-height: 1;
      padding: 0;
      margin: 0;
      text-align: left;
    }
    .dpt {
      font-size: 35px;
      margin: 0;
    }
    .groupe {
      font-size: 25px;
      margin-top: 15px;
    }
    .groupeBorder{
      position: absolute;
      bottom: 0;
      width: 100%;
      height: 30px;
    }
  </style>
</head>

<?php

  include 'bdd-connexion.php';

  $donnees = $bdd->query('
    SELECT d.mpId AS uid, d.nameFirst AS prenom, d.nameLast AS nom, d.active, d.civ,
      d.libelle_2 AS dpt_libelle, d.departementNom, d.departementCode, d.libelle AS groupe, d.libelleAbrev AS groupeAbrev,
      d.couleurAssociee AS groupeColor
    FROM deputes_last d
    ORDER BY RAND()
  ');

?>

<body>

  <?php
    while ($d = $donnees->fetch()) {

      //print_r($d);

      // Check if exists in the file.

      $uid = $d['uid'];
      $prenom = $d['prenom'];
      $nom = $d['nom'];

      $filename = __DIR__ . "/../assets/imgs/deputes_ogp/ogp_deputes_" . $uid . ".png";

      ?>

      <?php if (!file_exists($filename)): ?>
        <div id="element">
          <div class="container">
            <div class="logo-container">
              <img src="../assets/imgs/datan/logo_datan.png" alt="" id="logo" style="top: 0">
            </div>
            <?php if ($d['groupeColor']): ?>
              <div class="groupeBorder" style="background-color: <?= $d['groupeColor'] ?>"></div>
            <?php endif; ?>
            <div class="inside">
              <div class="image">
                <img src="../assets/imgs/deputes_original/depute_<?= substr($uid, 2) ?>.png" alt="img" id="photo">
              </div>
              <div class="titre">
                <h1>
                  <span class="prenom" style="font-size: <?= 3.5 / (strlen($nom) ** 0.30) ?>em;"><?= $prenom ?></span>
                  <span class="nom" style="font-size: <?= 5 / (strlen($nom) ** 0.30) ?>em;"><?= $nom ?></span>
                </h1>
                <?php if ($d['active' == 0]): ?>
                  <h2 class="dpt"><?= $d['civ'] == 'Mme' ? 'Ancienne députée' : 'Ancien député' ?> <?= $d['dpt_libelle'] ?><?= $d['departementNom'] ?> (<?= $d['departementCode'] ?>)</h2>
                <?php else: ?>
                  <h2 class="dpt"><?= $d['civ'] == 'Mme' ? 'Députée' : 'Député' ?> <?= $d['dpt_libelle'] ?> <?= $d['departementNom'] ?> (<?= $d['departementCode'] ?>)</h2>
                <?php endif; ?>
                <h3 class="groupe"><?= $d['groupe'] ?> (<?= $d['groupeAbrev'] ?>)</h3>
              </div>
            </div>
          </div>
        </div>
        <?php break; ?>
      <?php else: ?>
        Og img already exists.
      <?php endif; ?>


      <?php
    }
  ?>

<div class="render" id="render">
</div>


<script>
  let div = document.getElementById('element'); // getting special div
  let uid = '<?php echo $uid ?>';

  html2canvas(div).then(canvas => {

    // create a new AJAX object
    var ajax = new XMLHttpRequest();

    // set the method to use, the backend file and set it requst as asynchrounous
    ajax.open('POST', 'save_og_img.php', true);

    // set the request headers
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    // set the image type to "img/jpeg" and quality to 1 (100%)
    ajax.send("image=" + canvas.toDataURL("image/png", 1) + "&mpId=" + uid);

    ajax.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {

        // display the response sent from server
        console.log(this.responseText);

      }
    }

  })

  </script>


</body>
</html>
