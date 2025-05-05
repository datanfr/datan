</div>
    </main>
    <!-- JS files -->
    <script src="<?= asset_url(); ?>js/libraries/jquery/jquery-3.5.1.min.js"></script>
    <script src="<?= asset_url(); ?>js/libraries/jquery/jquery-ui.min.js"></script>
    <script src="<?= asset_url(); ?>js/libraries/popper/popper.min.js"></script>
    <script type="text/javascript" src="<?= asset_url() ?>js/libraries/bootstrap/bootstrap.min.js"></script>
    <?php if (isset($js_to_load_before_datan)) : ?>
      <?php foreach ($js_to_load_before_datan as $file) : ?>
        <script src="<?= asset_url(); ?>js/<?= $file ?>.js?v=<?= get_version() ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>
    <script type="text/javascript" src="<?= asset_url() ?>js/libraries/jquery/jquery.unveil.min.js"></script>

    <script type="text/javascript">
      var tarteaucitronForceLanguage = "fr";
    </script>
    <script src="https://cdn.jsdelivr.net/gh/AmauriC/tarteaucitron.js@20210329/tarteaucitron.min.js"></script>
    <script type="text/javascript">
      tarteaucitron.init({
        "privacyUrl": "<?= base_url() ?>mentions-legales",
        /* Privacy policy url */

        "hashtag": "#tarteaucitron",
        /* Open the panel with this hashtag */
        "cookieName": "tarteaucitron",
        /* Cookie name */

        "orientation": "bottom",
        /* Banner position (top - bottom - middle) */
        "showAlertSmall": false,
        /* Show the small banner on bottom right */
        "cookieslist": true,
        /* Show the cookie list */

        "adblocker": false,
        /* Show a Warning if an adblocker is detected */
        "AcceptAllCta": true,
        /* Show the accept all button when highPrivacy on */
        "highPrivacy": true,
        /* Disable auto consent */
        "handleBrowserDNTRequest": false,
        /* If Do Not Track == 1, disallow all */

        "removeCredit": false,
        /* Remove credit link */
        "moreInfoLink": true,
        /* Show more info link */
        "useExternalCss": false,
        /* If false, the tarteaucitron.css file will be loaded */

        //"cookieDomain": ".my-multisite-domaine.fr", /* Shared cookie for multisite */

        "readmoreLink": "/cookiespolicy" /* Change the default readmore link */
      });
    </script>

    <script type="text/javascript">
      tarteaucitron.user.googletagmanagerId = 'GTM-K3QQNK2';
      (tarteaucitron.job = tarteaucitron.job || []).push('googletagmanager');
    </script>

    <?php if (isset($js_to_load)) : ?>
      <?php foreach ($js_to_load as $file) : ?>
        <script type="text/javascript" src="<?= asset_url() ?>js/<?= $file ?>.js?v=<?= get_version() ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>

    <script type="text/javascript" src="<?= asset_url() ?>js/main.min.js?v=<?= get_version() ?>"></script>
    <script type="text/javascript" src="<?= asset_url() ?>js/datan/url_obf2.min.js"></script>

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K3QQNK2" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    </body>

    </html>
