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
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
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
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      width: 100%;
      height: 100%;
    }
    .bottom{
      display: flex;
    }
    .left{
      width: 20%;
      display: flex;
      justify-content: flex-start;
      align-items: flex-start;
    }
    .right{
      width: 80%;
      display: flex;
      justify-content: flex-start;
      align-items: flext-start;
    }
    .inside{
      margin-top: 30px;
      width: 100%;
    }
    .inside_right{
      display: flex;
      flex-direction: row;
      justify-content: space-around;
      align-items:center;
      padding-right: 100px;
    }
    #logo{
      width: 200px;
      height: auto;
      margin-left: 30px;
    }
    #photo{
      width: 300px;
      height: auto;
      border-radius: 15px;
    }
    .titre{
      padding: 75px;
      display: flex;
      flex-direction: column;
      align-items: flex-end;

    }
    .prenom{
      color: white;
      font-size: 60px;
      display: block;
      font-weight: 400;
      padding: 0;
      margin: 0;
      text-align: right;
    }
    .nom{
      color: white;
      font-size: 60px;
      display: block;
      font-weight: 800;
      padding: 0;
      margin: 0;
      text-align: right;
    }
  </style>
</head>

<?php

  include 'bdd-connexion.php';

  $uid_url = $_GET['uid'];

  $donnees = $bdd->query('
    SELECT d.mpId AS uid, d.nameFirst AS prenom, d.nameLast AS nom
    FROM deputes_last d
    WHERE d.mpId = "'.$uid_url.'"
    GROUP BY d.mpId
    LIMIT 1
  ');

  // PA332228

?>

<body>

  <?php
    while ($d = $donnees->fetch()) {

      // Check if exists in the file.

      $uid = $d['uid'];
      $prenom = $d['prenom'];
      $nom = $d['nom'];
      echo $uid;
      echo substr($uid, 2);

      ?>

      <div id="element">
        <div class="container">
          <div class="left">
            <div class="inside">
              <img src="../assets/imgs/datan/logo_white_transp.png" alt="" id="logo">
            </div>
          </div>
          <div class="right">
            <div class="inside inside_right">
              <div class="titre">
                <h1 class="prenom"><?= $prenom ?></h1>
                <h1 class="nom"><?= $nom ?></h1>
              </div>
              <div class="image">
                <img src="http://localhost/datan/assets/imgs/deputes_original/depute_<?= substr($uid, 2) ?>.png" alt="img" id="photo">
              </div>
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
