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
      line-height: 50px;
      font-size: 80px;
      font-weight: 400;
      padding: 0;
      margin: 0;
      text-align: left;
    }
    .nom{
      font-size: 100px;
      font-weight: 800;
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

  $uid_url = $_GET['uid'];

  $donnees = $bdd->query('
    SELECT d.mpId AS uid, d.nameFirst AS prenom, d.nameLast AS nom, d.active, d.civ,
      d.libelle_2 AS dpt_libelle, d.departementNom, d.departementCode, d.libelle AS groupe, d.libelleAbrev AS groupeAbrev,
      d.couleurAssociee AS groupeColor
    FROM deputes_last d
    WHERE d.mpId = "'.$uid_url.'"
    LIMIT 1
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

      ?>

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
              <h1><span class="prenom"><?= $prenom ?></span> <span class="nom"><?= $nom ?></span> </h1>
              <?php if ($d['active' == 0]): ?>
                <h2 class="dpt"><?= $d['civ'] == 'Mme' ? 'Ancienne députée' : 'Ancien député' ?> <?= $d['dpt_libelle'] ?> <?= $d['departementNom'] ?> (<?= $d['departementCode'] ?>)</h2>
              <?php else: ?>
                <h2 class="dpt"><?= $d['civ'] == 'Mme' ? 'Députée' : 'Député' ?> <?= $d['dpt_libelle'] ?> <?= $d['departementNom'] ?> (<?= $d['departementCode'] ?>)</h2>
              <?php endif; ?>
              <h3 class="groupe"><?= $d['groupe'] ?> (<?= $d['groupeAbrev'] ?>)</h3>
            </div>
          </div>
        </div>
      </div>

      <?php
    }
  ?>

<div class="render" id="render">
</div>

<script>
  $( document ).ready(function() {
    //alert("yes");

    //var elem = $('#element').get(0);
    //var leba = "600";
    //var ting = "400";
    //var type = "bmp";
    //var name = "temp"
    //debugger;


    html2canvas(document.querySelector("#element"), {letterRendering: 1, allowTaint: true, useCORS: true, logging: true} ).then(function (canvas) {
      var dataURL = canvas.toDataURL("image/jpg", 1.0);
      var a = document.createElement('a');
      a.href = dataURL;
      a.download = 'ogp_deputes_<?= $uid ?>.png';
      document.body.appendChild(a);
      document.body.appendChild(canvas)
      a.click();
    })

  });

  </script>


</body>
</html>
