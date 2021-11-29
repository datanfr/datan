<!DOCTYPE html>
<html lang="fr" dir="ltr" prefix="og: http://ogp.me/ns#">
  <head>
    <meta charset="utf-8">
    <meta name="robots" content="index,follow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title_meta ?></title>
    <meta name="title" content="<?= $title_meta ?>">
    <?php if (isset($description_meta)): ?>
      <meta name="description" content="<?= $description_meta ?>">
    <?php endif; ?>
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
    <?php if (isset($schema)): ?>
      <script type="application/ld+json">
        <?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE); ?>
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
      "email" : "info@datan.fr"
    }
    </script>
    <?php if (isset($vote_schema)): ?>
      <script type="application/ld+json">
        <?= json_encode($vote_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE); ?>
      </script>
    <?php endif; ?>

    <link rel="canonical" href="<?= $url ?>">
    <link rel="icon" type="image/png" href="<?= asset_url() ?>imgs/icons/datan_favicon.ico" />
    <!-- PRELOAD -->
    <?php if (isset($preloads)): ?>
      <?php foreach ($preloads as $preload): ?>
        <link rel=preload href="<?= $preload['href'] ?>" as="<?= $preload['as'] ?>" <?php if (isset($preload['imagesrcset'])): ?>imagesrcset="<?= $preload['imagesrcset'] ?>"<?php endif; ?> <?php if (isset($preload['media'])): ?>media="<?= $preload['media'] ?>"<?php endif; ?>>
      <?php endforeach; ?>
    <?php endif; ?>
    <link rel="preload" href="https://matomo.datan.fr/1337.js" onload="embedTracker()" type="script" crossorigin>
    <?php
    if (isset($critical_css)): ?>
      <link rel="stylesheet" media="screen and (max-width: 480px)" href="<?= asset_url() ?>css/critical/<?= $critical_css ?>-mobile.css" />
      <link rel="stylesheet" media="screen and (min-width: 481px)" href="<?= asset_url() ?>css/critical/<?= $critical_css ?>.css" />
      <link rel="stylesheet" href="<?= css_url() ?>main.css?v=<?= get_version() ?>" media="print" onload="this.media='all'; this.onload=null;">
      <noscript><link rel="stylesheet" type="text/css" href="<?= css_url() ?>main.css?v=<?= get_version() ?>"></noscript>
      <?php else: ?>
      <link rel="stylesheet" type="text/css" href="<?= css_url() ?>main.css?v=<?= get_version() ?>">
    <?php endif; ?>
    <!-- GOOGLE FONTS -->
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
    <?php if (isset($js_to_load_up_defer)): ?>
      <?php foreach ($js_to_load_up_defer as $file): ?>
        <script defer src="<?= asset_url().'js/'.$file ?>"></script>
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
    <!-- Matomo -->
    <script type="text/javascript">
      // https://github.com/0x11DFE/Matomo-Anti-Adblock
      // Static way - Need to redo it after updating Matomo
      var _paq = window._paq = window._paq || [];
      // CODE CNIL
      _paq.push([function() {
          var self = this;
          function getOriginalVisitorCookieTimeout() {
              var now = new Date(),
              nowTs = Math.round(now.getTime() / 1000),
              visitorInfo = self.getVisitorInfo();
              var createTs = parseInt(visitorInfo[2]);
              var cookieTimeout = 33696000; // 13 mois en secondes
              var originalTimeout = createTs + cookieTimeout - nowTs;
              return originalTimeout;
          }
          this.setVisitorCookieTimeout( getOriginalVisitorCookieTimeout() );
      }]);
      // FIN CODE CNIL
      /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
      _paq.push(['trackPageView']);
      _paq.push(['enableLinkTracking']);
      (function() {
        var u="//matomo.datan.fr/";
        _paq.push(['setTrackerUrl', u+'1337.php']);
        _paq.push(['setSiteId', '1']);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.src=u+'1337.js'; s.parentNode.insertBefore(g,s);
      })();
    </script>
    <noscript><p><img src="//matomo.datan.fr/1337.php?idsite=1&amp;rec=1" style="border:0;" alt="" /></p></noscript>
    <!-- End Matomo Code -->

  </head>
  <?php
  if (!isset($mentions)) {
    $mentions = NULL;
  }
  ?>
  <body class="<?= $mentions ?>" data-spy="scroll" data-target="#navScrollspy" data-offset="90">
    <header>
      <section>
        <nav class="navbar fixed-top navbar-expand-lg navbar-light" id="navbar-datan">
          <div class="container p-0">
            <a class="navbar-brand mx-auto p-0 no-decoration" href="<?= base_url(); ?>" style="text-align: center">
              <img src="<?= asset_url() ?>imgs/datan/logo_svg.svg" width="937" height="204" alt="Logo Datan">
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
                  <a class="nav-link no-decoration" href="<?= base_url() ?>statistiques">En chiffres</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link no-decoration" href="<?= base_url() ?>elections">Élections</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link no-decoration" href="<?= base_url(); ?>a-propos">À propos</a>
                </li>
                <li class="nav-item dropdown follow-us">
                  <a class="nav-link dropdown-toggle no-decoration" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Suivez-nous
                  </a>
                  <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item no-decoration" href="https://twitter.com/datanFR" target="_blank" rel="noopener">Twitter</a>
                    <a class="dropdown-item no-decoration" href="https://www.facebook.com/datanFR/" target="_blank" rel="noopener">Facebook</a>
                    <span class="dropdown-item no-decoration cursor-pointer" data-toggle="modal" data-target="#newsletter">Newsletter</span>
                  </div>
                </li>
                <?php if (($this->session->userdata('type') == 'admin') || ($this->session->userdata('type') == 'writer')): ?>
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
                      <?php if ($this->session->userdata('logged_in')): ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url(); ?>logout" class="dropdown-item no-decoration">Déconnexion</a>
                      <?php endif; ?>
                    </div>
                  </li>
                  <?php endif; ?>
              </ul>
            </div>
          </div>
        </nav>
      </section>
    </header>
    <main>
