<script type="text/javascript">
  tarteaucitron.user.googletagmanagerId = 'GTM-K3QQNK2';
  (tarteaucitron.job = tarteaucitron.job || []).push('googletagmanager');
</script>

<!DOCTYPE html>
<html lang="fr" dir="ltr" prefix="og: http://ogp.me/ns#">
  <head>
    <script type="text/javascript" src="<?= base_url() ?>assets/js/tarteaucitron.js-1.3/tarteaucitron.min.js" ></script>
    <script type="text/javascript">
    tarteaucitron.init({
    "privacyUrl": "", /* Privacy policy url */

    "hashtag": "#tarteaucitron", /* Open the panel with this hashtag */
    "cookieName": "tarteaucitron", /* Cookie name */

    "orientation": "bottom", /* Banner position (top - bottom - middle) */
    "showAlertSmall": true, /* Show the small banner on bottom right */
    "cookieslist": true, /* Show the cookie list */

    "adblocker": false, /* Show a Warning if an adblocker is detected */
    "AcceptAllCta" : true, /* Show the accept all button when highPrivacy on */
    "highPrivacy": true, /* Disable auto consent */
    "handleBrowserDNTRequest": false, /* If Do Not Track == 1, disallow all */

    "removeCredit": false, /* Remove credit link */
    "moreInfoLink": true, /* Show more info link */
    "useExternalCss": false, /* If false, the tarteaucitron.css file will be loaded */

    //"cookieDomain": ".my-multisite-domaine.fr", /* Shared cookie for multisite */

    "readmoreLink": "/cookiespolicy" /* Change the default readmore link */
    });
    </script>
    <meta charset="utf-8">
    <meta name="robots" content="index,follow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title_meta ?></title>
    <meta name="title" content="<?= $title_meta ?>">
    <?php if (isset($description_meta)): ?>
      <meta name="description" content="<?= $description_meta ?>">
    <?php endif; ?>
    <!-- ANALYSE SEO -->
    <?php
    /* © 2020 Seolyzer.io */
    require_once($_SERVER['DOCUMENT_ROOT'].'/seolyzer-46f6b30a24a20580c90a90c52f9e84e6.php');
    ?>
    <!-- Open Graph -->
    <!-- https://ogp.me/ -->
    <?php if (isset($ogp)): ?>
    <meta property="og:title" content="<?= $ogp['title'] ?>" />
    <meta property="og:description" content="<?= $ogp['description'] ?>" />
    <meta property="og:type" content="<?= $ogp['type'] ?>" />
    <meta property="og:url" content="<?= $ogp['url'] ?>" />
    <meta property="og:image" content="<?= $ogp['img'] ?>" />
    <meta property="og:image:secure_url" content="<?= $ogp['img'] ?>" />
    <meta property="og:image:width" content="<?= $ogp['img_width'] ?>">
    <meta property="og:image:height" content="<?= $ogp['img_height'] ?>">
    <meta property="og:image:type" content="<?= $ogp['img_type'] ?>" />
    <meta property="og:site_name" content="<?= $ogp['site_name'] ?>" />
    <meta property="og:locale" content="fr_FR" />
    <meta name="twitter:card" content="<?= $ogp['twitter_card'] ?>">
    <meta name="twitter:site" content="@datanFR" />
    <meta name="twitter:title" content="<?= $ogp['twitter_title'] ?>" />
    <meta name="twitter:description" content="<?= $ogp['twitter_description'] ?>" />
    <meta name="twitter:image" content="<?= $ogp['twitter_img'] ?>" />
    <meta name="twitter:creator" content="@awenig">
    <?php if (isset($ogp['type_first_name'])): ?>
      <meta property="profile:first_name" content="<?= $ogp['type_first_name'] ?>" />
      <meta property="profile:last_name" content="<?= $ogp['type_last_name'] ?>" />
    <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($breadcrumb_json)): ?>
      <script type="application/ld+json">
        <?= json_encode($breadcrumb_json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE); ?>
      </script>
    <?php endif; ?>
    <?php if (isset($person_schema)): ?>
      <script type="application/ld+json">
        <?= json_encode($person_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE); ?>
      </script>
    <?php endif; ?>
    <script type='application/ld+json'>
    {
      "@context" : "http://schema.org",
      "@type" : "Organization",
      "name" : "Datan",
      "url" : "https://datan.fr",
      "logo": "https://datan.fr/assets/imgs/datan/logo_datan.png",
      "sameAs" : ["https://www.facebook.com/datanFR","https://twitter.com/datanFR"],
      "email" : "contact@datan.fr"
    }
    </script>

    <link rel="canonical" href="<?= $url ?>">
    <link rel="icon" type="image/png" href="<?= asset_url() ?>/imgs/icons/datan_favicon.ico" />
    <!-- PRELOAD -->
    <?php if (isset($preloads)): ?>
      <?php foreach ($preloads as $preload): ?>
        <link rel=preload href="<?= $preload['href'] ?>" as="<?= $preload['as'] ?>" imagesrcset="<?= $preload['imagesrcset'] ?>">
      <?php endforeach; ?>
    <?php endif; ?>
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="<?= asset_url() ?>css/main.css">
    <!-- OLDER SYSTEM PRELOAD -->
    <!--
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript>
      <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;800&display=swap" rel="stylesheet"/>
    </noscript>
    -->
    <!-- NEW SYSTEM FONT TEST -->
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/opensans/v17/mem8YaGs126MiZpBA-UFVZ0bf8pkAg.woff2" as="font" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/opensans/v17/mem5YaGs126MiZpBA-UNirkOUuhpKKSTjw.woff2" as="font" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/opensans/v17/mem5YaGs126MiZpBA-UN8rsOUuhpKKSTjw.woff2" as="font" crossorigin>


    <?php if (isset($css_to_load)): ?>
      <?php foreach ($css_to_load as $file): ?>
        <link type="text/css" <?php if ($file['async']): ?> rel="stylesheet" media="print" onload="this.media='all';this.onload=null;" <?php else: ?> rel="stylesheet" <?php endif; ?> href="<?= $file['url'] ?>"/>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- JS_UP -->
    <?php if (isset($js_to_load_up)): ?>
      <?php foreach ($js_to_load_up as $file): ?>
        <script src="<?= asset_url().'js/'.$file ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>
    <style media="screen">
      body {
        position: relative;
      }
      @font-face {
        font-family: 'Open Sans';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: local('Open Sans Regular'), local('OpenSans-Regular'), url(https://fonts.gstatic.com/s/opensans/v17/mem8YaGs126MiZpBA-UFVZ0bf8pkAg.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
      }
      @font-face {
        font-family: 'Open Sans';
        font-style: normal;
        font-weight: 600;
        font-display: swap;
        src: local('Open Sans SemiBold'), local('OpenSans-SemiBold'), url(https://fonts.gstatic.com/s/opensans/v17/mem5YaGs126MiZpBA-UNirkOUuhpKKSTjw.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
      }
      @font-face {
        font-family: 'Open Sans';
        font-style: normal;
        font-weight: 800;
        font-display: swap;
        src: local('Open Sans ExtraBold'), local('OpenSans-ExtraBold'), url(https://fonts.gstatic.com/s/opensans/v17/mem5YaGs126MiZpBA-UN8rsOUuhpKKSTjw.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
      }
    </style>
    <!-- TWAK CHATBOX -->
    <script type="text/javascript">
      var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
      (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src='https://embed.tawk.to/5f5637ebf0e7167d000e19cd/default';
        s1.charset='UTF-8';
        s1.setAttribute('crossorigin','*');
        s0.parentNode.insertBefore(s1,s0);
      })();
    </script>

    <!-- Hotjar -->
    <!-- Hotjar Tracking Code for https://datan.fr -->
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:1981564,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>

  </head>
  <?php
  if (!isset($mentions)) {
    $mentions = NULL;
  }
  ?>
  <body class="<?= $mentions ?>" data-spy="scroll" data-target="#navScrollspy" data-offset="90">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K3QQNK2"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <header>
      <section>
        <nav class="navbar fixed-top navbar-expand-lg navbar-light" id="navbar-datan">
          <div class="container p-0">
            <a class="navbar-brand mx-auto p-0 no-decoration" href="<?= base_url(); ?>" style="text-align: center">
              <img src="<?= asset_url() ?>imgs/datan/logo_svg.svg" alt="Logo Datan">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar_datan" aria-controls="navbar_datan" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar_datan">
              <ul class="nav navbar-nav ml-auto mt-2 mt-lg-0">
                <li class="nav-item">
                  <a class="nav-link no-decoration" href="<?= base_url(); ?>deputes">Députés</span></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link no-decoration" href="<?= base_url() ?>groupes">Groupes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link no-decoration" href="<?= base_url() ?>votes">Votes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link no-decoration" href="<?= base_url() ?>blog">Actualités</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link no-decoration" href="<?= base_url(); ?>a-propos">À propos</a>
                </li>

                <?php if (($this->session->userdata('type') == 'admin') || ($this->session->userdata('type') == 'writer')): ?>
                  <li class="nav-item">
                    <a class="nav-link no-decoration" href="<?= base_url() ?>classements">Classements</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle no-decoration" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      Admin
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                      <?php if (($this->session->userdata('type') == 'admin') || ($this->session->userdata('type') == 'writer')): ?>
                        <a class="dropdown-item no-decoration" href="<?= base_url(); ?>admin/">Dashboard</a>
                      <?php endif; ?>
                      <?php if (($this->session->userdata('type') == 'admin')): ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item no-decoration" href="<?= base_url(); ?>scripts/">Scripts</a>
                      <?php endif; ?>
                    </div>
                  </li>
                  <?php endif; ?>
                  <?php if ($this->session->userdata('logged_in')): ?>
                    <li class="nav-item">
                      <a href="<?= base_url(); ?>users/logout" class="nav-link no-decoration">Déconnexion</a>
                    </li>
                  <?php endif; ?>
              </ul>
            </div>
          </div>
        </nav>
      </section>
    </header>
    <main>
