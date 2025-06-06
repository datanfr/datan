<!DOCTYPE html>
<html lang="fr" dir="ltr" prefix="og: http://ogp.me/ns#">
  <head>
    <meta charset="utf-8">
    <?php if (isset($seoNoFollow) && $seoNoFollow === TRUE): ?>
      <meta name="robots" content="noindex, nofollow">
      <?php else: ?>
      <meta name="robots" content="index,follow">
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title_meta ?></title>
    <meta name="title" content="<?= $title_meta ?>">
    <?php if (isset($description_meta)): ?>
      <meta name="description" content="<?= $description_meta ?>">
    <?php endif; ?>
    <!-- Color theme -->
    <meta name="theme-color" content="#00668e">
    
    <!-- PRELOAD -->
    <?php if (isset($preloads)): ?>
      <?php foreach ($preloads as $preload): ?>
        <link rel=preload href="<?= $preload['href'] ?>" as="<?= $preload['as'] ?>" <?php if (isset($preload['imagesrcset'])): ?>imagesrcset="<?= $preload['imagesrcset'] ?>"<?php endif; ?> <?php if (isset($preload['media'])): ?>media="<?= $preload['media'] ?>"<?php endif; ?>>
      <?php endforeach; ?>
    <?php endif; ?>
    <link rel="preload" href="https://matomo.datan.fr/1337.js" onload="embedTracker()" type="script" crossorigin>
    <?php
    if (isset($critical_css)): ?>
      <link rel="stylesheet" media="screen and (max-width: 480px)" href="<?= asset_url() ?>css/critical/<?= $critical_css ?>-mobile.css?v=<?= get_version() ?>" />
      <link rel="stylesheet" media="screen and (min-width: 481px)" href="<?= asset_url() ?>css/critical/<?= $critical_css ?>.css?v=<?= get_version() ?>" />
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
        <link type="text/css" <?php if ($file['async']): ?> rel="stylesheet" media="print" onload="this.media='all';this.onload=null;" <?php else: ?> rel="stylesheet" <?php endif; ?> href="<?= $file['url'] ?>?v=<?= get_version() ?>"/>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- JS_UP -->
    <?php if (isset($js_to_load_up)): ?>
      <?php foreach ($js_to_load_up as $file): ?>
        <script src="<?= asset_url().'js/'.$file ?>?v=<?= get_version() ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($js_to_load_up_defer)): ?>
      <?php foreach ($js_to_load_up_defer as $file): ?>
        <script defer src="<?= asset_url().'js/'.$file ?>?v=<?= get_version() ?>"></script>
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

    <main>