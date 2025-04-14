    </div>
    </main>
    <!-- Modal Newsletter -->
    <div class="modal fade" id="newsletter" tabindex="-1" role="dialog" aria-labelledby="newsletterLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <?= form_open('newsletter', array('id'=> 'newsletterForm')); ?>
            <div class="modal-header">
              <span class="modal-title h2" id="newsletterLabel">Newsletter</span>
              <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </span>
            </div>
            <div class="modal-body">
              <p>Souhaitez-vous vous inscrire et recevoir les informations parlementaires et les nouvelles de Datan.fr <b>une fois par mois</b> ?</p>
              <input type="email" name="email" required class="form-control" placeholder="Votre email">
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Recevoir la newsletter</button>
            </div>
            <?= form_close() ?>
          <div id="modalSubscription">
            <div class="modal-header">
              <span class="modal-title h2">Félicitations</span>
              <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </span>
            </div>
            <div class="modal-body">
              <p>Vous serez maintenant informé des actualités politiques de nos députés et un peu du site aussi :)</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
          </div>
          <div id="modalFail">
            <div class="modal-header">
              <span class="modal-title h2">Quelque chose s'est mal passé</span>
              <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </span>
            </div>
            <div class="modal-body">
              <p>Vous êtes sans doute déjà inscrit !</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <footer>
      <div class="container-fluid footer">
        <div class="container p-0">
          <div class="row row-grid py-3">
            <div class="col-md-5 d-flex flex-column justify-content-center">
              <img class="img-lazy img-fluid" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media-big.png" width="4500" height="1975" data-src="<?= asset_url(); ?>imgs/datan/logo_baseline_white_transp.png" alt="Logo Datan">
            </div>
            <div class="col-md-4 pt-md-4">
              <div class="d-flex flex-row liste justify-content-center">
                <div class="d-flex flex-column mx-1">
                  <p>
                    <?php if ($this->router->fetch_class() == "home"): ?> 
                      <a href="<?= base_url(); ?>a-propos" class="no-decoration underline">À propos</a>
                    <?php else: ?>
                      <span class="url_obf no-decoration underline text-white" url_obf="<?= url_obfuscation(base_url() . "a-propos") ?>">À propos</span>
                    <?php endif; ?>
                  </p>
                  <p>
                    <?php if ($this->router->fetch_class() == "home"): ?> 
                      <a href="<?= base_url(); ?>newsletter" class="no-decoration underline">Newsletter</a>
                    <?php else: ?>
                      <span class="url_obf no-decoration underline text-white" url_obf="<?= url_obfuscation(base_url() . "newsletter") ?>">Newsletter</span>
                    <?php endif; ?>
                  </p>
                  <p>
                    <a href="<?= base_url(); ?>statistiques/aide" class="no-decoration underline">Nos statistiques</a>
                  </p>
                  <p>
                    <a href="<?= base_url(); ?>partis-politiques" class="no-decoration underline">Partis politiques</a>
                  </p>
                  <p>
                    <a href="https://www.helloasso.com/associations/datan/formulaires/1" target="_blank" rel="noopener" class="no-decoration underline">Dons</a>
                  </p>
                </div>
                <div class="d-flex flex-column mx-1">
                  <p>
                    <a href="<?= base_url(); ?>blog" class="no-decoration underline">Actualités</a>
                  </p>
                  <p>
                    <a href="<?= base_url(); ?>parrainages-2022" class="no-decoration underline">Parrainages 2022</a>
                  </p>
                  <p>
                    <a href="<?= base_url(); ?>faq" class="no-decoration underline">Foire aux questions</a>
                  </p>
                  <p>
                    <?php if ($this->router->fetch_class() == "home"): ?> 
                      <a href="<?= base_url(); ?>login" class="no-decoratio underline">Connexion</a>
                    <?php else: ?>
                      <span class="url_obf no-decoration underline text-white" url_obf="<?= url_obfuscation(base_url() . "login") ?>">Connexion</span>
                    <?php endif; ?>
                  </p>
                  <p>
                    <?php if ($this->router->fetch_class() == "home"): ?> 
                      <a href="<?= base_url(); ?>mentions-legales" class="no-decoratio underline">Mentions légales</a>
                    <?php else: ?>
                      <span class="url_obf no-decoration underline text-white" url_obf="<?= url_obfuscation(base_url() . "mentions-legales") ?>">Mentions légales</span>
                    <?php endif; ?>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-3 social-media pt-md-3">
              <div class="row">
                <div class="col-6 col-md-12 d-flex flex-column">
                  <p class="font-weight-bold mb-1">Nous suivre</p>
                  <?php if ($this->router->fetch_class() == "home"): ?>
                    <a href="https://www.facebook.com/datanFR/" target="_blank" class="no-decoration my-1" rel="noreferrer">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/facebook_datan.png" alt="Logo Facebook">
                      Facebook
                    </a>
                    <a href="https://twitter.com/datanFR" target="_blank" class="no-decoration my-1" rel="noreferrer">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/twitter_datan.png" alt="Logo Twitter">
                      Twitter
                    </a>
                    <a href="https://www.linkedin.com/company/datanfr" target="_blank" class="no-decoration my-1" rel="noreferrer">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/linkedin_datan.png" alt="Logo Linkedin">
                      Linkedin
                    </a>
                    <a href="https://www.instagram.com/datanfr/" target="_blank" class="no-decoration my-1" rel="noreferrer">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/instagram_datan.png" alt="Logo Instagram">
                      Instagram
                    </a>
                  <?php else: ?>
                    <span class="url_obf no-decoration text-white my-1" url_obf="<?= url_obfuscation("https://www.facebook.com/datanFR/") ?>">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/facebook_datan.png" alt="Logo Facebook">
                      Facebook
                    </span>
                    <span class="url_obf no-decoration text-white my-1" url_obf="<?= url_obfuscation("https://twitter.com/datanFR") ?>">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/twitter_datan.png" alt="Logo Twitter">
                      Twitter
                    </span>
                    <span class="url_obf no-decoration text-white my-1" url_obf="<?= url_obfuscation("https://www.linkedin.com/company/datanfr/") ?>">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/linkedin_datan.png" alt="Logo Linkedin">
                      Linkedin
                    </span>
                    <span class="url_obf no-decoration text-white my-1" url_obf="<?= url_obfuscation("https://www.instagram.com/datanfr/") ?>">
                      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="542" height="542" data-src="<?= asset_url() ?>imgs/logos/instagram_datan.png" alt="Logo Instagram">
                      Instagram
                    </span>
                  <?php endif; ?>
                </div>
                <div class="col-6 col-md-12 d-flex flex-column mt-0">
                  <p class="font-weight-bold mt-md-2 mb-1">Nous contacter</p>
                  info[at]datan.fr
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <p>© Datan 2024 - Tous droits réservés</p>
            </div>
          </div>
        </div>
      </div>
      <div class="container-fluid footer-data">
        <div class="container">
          <div class="row py-3">
            <div class="col-md-6 text-center">
              <?php if ($this->router->fetch_class() == "home"): ?>
                Nos données sont disponibles sur le site <a href="https://www.data.gouv.fr/fr/organizations/datan/" target="_blank" rel="noreferrer">data.gouv.fr</a>
                <?php else: ?>
                Nos données sont disponibles sur le site <span class="url_obf" url_obf="<?= url_obfuscation("https://www.data.gouv.fr/fr/organizations/datan/") ?>">data.gouv.fr</span>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              Notre projet est disponible sur
              <?php if ($this->router->fetch_class() == "home"): ?>
                <a href="https://github.com/datanFR/datan" target="_blank" rel="noreferrer" class="no-decoration underline">
                  <img src="<?= asset_url() ?>imgs/logos/GitHub-Mark.png" width="25" height="25" alt="Logo GitHub">
                  GitHub
                </a>
                <?php else: ?>
                  <span class="url_obf no-decoration" url_obf="<?= url_obfuscation("https://github.com/datanFR/datan") ?>">
                    <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" width="25" height="25" data-src="<?= asset_url() ?>imgs/logos/GitHub-Mark.png" alt="Logo GitHub">
                    GitHub
                  </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </footer>
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
