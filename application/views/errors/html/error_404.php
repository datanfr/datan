<!doctype html>
<html lang="fr">
<meta charset="utf-8">
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<link rel="icon" type="image/png" href="<?= asset_url() ?>/imgs/favicon/datan_favicon.ico" />
<head>
  <title>404 Page non trouvée</title>
  <style>
    body {
      width: 99%;
      height: 100%;
      background-color: mediumturquoise;
      color: white;
      font-family: sans-serif;
    }

    div {
      position: absolute;
      width: 400px;
      height: 300px;
      z-index: 15;
      top: 45%;
      left: 50%;
      margin: -100px 0 0 -200px;
      text-align: center;
    }

    h1,
    h2 {
      text-align: center;
    }

    h1 {
      font-size: 60px;
      margin-bottom: 10px;
      border-bottom: 1px solid white;
      padding-bottom: 10px;
    }

    h2 {
      margin-bottom: 40px;
    }

    a {
      margin-top: 10px;
      text-decoration: none;
      padding: 10px 25px;
      background-color: ghostwhite;
      color: black;
      margin-top: 20px;
    }
  </style>
</head>

<body>
  <div>
    <h1>404</h1>
    <h2>Page non trouvée</h2>
    <a href='<?=base_url()?>'>Page d'accueil</a>
  </div>
</body>

</html>
