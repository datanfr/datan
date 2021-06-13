<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Page en construction</title>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K3QQNK2');</script>
    <!-- End Google Tag Manager -->

    <link rel="stylesheet" href="<?= asset_url(); ?>css/main.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans" />


    <style>
    body, html {
        height: 100%;
        margin: 0;
    }

    .bgimg {
      background: linear-gradient(
        rgba(0, 0, 0, 0.45),
        rgba(0, 0, 0, 0.45)
        ), url('<?= base_url(); ?>assets/imgs/hemycicle.jpg');
        height: 100%;
        background-position: center;
        background-size: cover;
        position: relative;
        color: white;
        font-size: 30px;
    }

    .topleft {
        position: absolute;
        top: 0;
        left: 16px;
    }

    .topleft img {
      width: 10em;
      height: auto;
    }

    .bottomleft {
        position: absolute;
        bottom: 0;
        left: 16px;
    }

    .middle {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    hr {
        margin: auto;
        width: 40%;
    }
    </style>
  </head>
  <body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K3QQNK2"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="bgimg">
      <div class="topleft">
        <a href="<?= base_url(); ?>">
          <img src="<?= asset_url(); ?>imgs/logo_white_transp.png" alt="">
        </a>
      </div>
      <div class="middle">
        <h1>PAGE EN CONSTRUCTION</h1>
        <hr>
      </div>
      <div class="bottomleft">
        <i class="fab fa-facebook-square"></i>
        <i class="fab fa-twitter-square"></i>
      </div>
    </div>
  </body>
</html>
