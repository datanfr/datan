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
      width: 250px;
      height: auto;
      border-radius: 15px;
    }
    .titre{
      padding: 20px;
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
    }
    .nom{
      color: white;
      font-size: 60px;
      display: block;
      font-weight: 800;
      padding: 0;
      margin: 0;
    }
  </style>
</head>

<?php

  include 'bdd-connexion.php';

  $donnees = $bdd->query('
    SELECT d.mpId AS uid, d.nameFirst AS prenom, d.nameLast AS nom
    FROM deputes d
    LEFT JOIN mandat_principal mp ON d.mpId = mp.mpId
    WHERE mp.legislature = 15
    GROUP BY d.mpId
    ORDER BY d.mpId ASC
  ');

  // PA332228

?>

<body style="padding: 20px; text-align: center">

<?php

$i = 1;

while ($d = $donnees->fetch()) {

  ?>

  <p>
    <a href="http://localhost/datan/scripts/code_photos_ogp_export.php?uid=<?= $d['uid'] ?>" target="_blank"><?= $i ?> - <?= $d['prenom'] ?> <?= $d['nom'] ?> - <?= $d['uid'] ?></a>
  </p>

  <?php

  $i++;

}

 ?>


</body>
</html>
