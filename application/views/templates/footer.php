    </div>
  </main>
  <footer>
    <div class="container-fluid footer">
      <div class="container p-0">
        <div class="row row-grid py-3">
          <div class="col-md-4 d-flex flex-column justify-content-center">
            <img class="img-lazy img-fluid" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media-big.png" data-src="<?= asset_url(); ?>imgs/datan/logo_baseline_white_transp.png" alt="Logo Facebook Datan">
          </div>
          <!--
          <div class="col-md-8 d-flex flex-column justify-content-center">
            <div>
              PHP //form_open()
                <div class="d-flex">
                    <div class="input-group">
                      <input type="text" class="form-control" id="inlineFormInputGroupUsername" placeholder="Inscrivez-vous à notre newsletter!">
                    </div>
                    <button class="btn" type="submit" id="button-addon2"><i class="fa fa-paper-plane"></i></button>
                </div>
              PHP //form_close()
            </div>
          </div>
          -->
          <div class="col-md-4 d-flex flex-column justify-content-center">
            <p>© Datan 2020 - Tous droits réservés</p>
            <p>Nous contacter : <a href="mailto:contact@datan.fr" class="no-decoration underline">contact@datan.fr</a></p>
            <div class="social-media">
              <a href="https://www.facebook.com/datanFR/" target="_blank" class="no-decoration" rel="noopener">
                <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" data-src="<?= asset_url() ?>imgs/logos/facebook_datan.png" alt="Logo Facebook Datan">
              </a>
              <a href="https://twitter.com/datanFR" target="_blank" class="no-decoration" rel="noopener">
                <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" data-src="<?= asset_url() ?>imgs/logos/twitter_datan.png" alt="Logo Twitter Datan">
              </a>
            </div>
          </div>
          <div class="col-md-4 d-flex flex-column justify-content-center mt-md-0">
            <div class="d-flex flex-row liste justify-content-center">
              <div class="d-flex flex-column mx-1">
                <p>
                  <a href="<?= base_url(); ?>a-propos" class="no-decoration underline">À propos</a>
                </p>
                <p>
                  <a href="<?= base_url(); ?>login" class="no-decoration underline">Connexion</a>
                </p>
                <p>
                  <a href="<?= base_url(); ?>statistiques/aide" class="no-decoration underline">Nos statistiques</a>
                </p>
              </div>
              <div class="d-flex flex-column mx-1">
                <p>
                  <a href="<?= base_url(); ?>blog" class="no-decoration underline">Actualités</a>
                </p>
                <p>
                  <a href="<?= base_url(); ?>mentions-legales" class="no-decoratio underline">Mentions légales</a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid footer-data">
      <div class="container">
        <div class="row py-3">
          <div class="col-12 text-center">
            Nos données sont disponibles sur le site <a href="https://www.data.gouv.fr/fr/organizations/datan/" target="_blank" rel="noopener">data.gouv.fr</a>
          </div>
        </div>
      </div>
    </div>
  </footer>
    <!-- JS files -->
    <script src="<?= asset_url(); ?>js/jquery-3.5.1.min.js"></script>
    <script src="<?= asset_url(); ?>js/jquery-ui.min.js"></script>
    <?php if (isset($js_to_load_before_bootstrap)): ?>
      <?php foreach ($js_to_load_before_bootstrap as $file): ?>
        <script src="<?= asset_url(); ?>js/<?= $file ?>.js"></script>
      <?php endforeach; ?>
    <?php endif; ?>
    <script type="text/javascript" src="<?= asset_url() ?>js/bootstrap.min.js"></script>
    <?php if (isset($js_to_load_before_datan)): ?>
      <?php foreach ($js_to_load_before_datan as $file): ?>
        <script src="<?= asset_url(); ?>js/<?= $file ?>.js"></script>
      <?php endforeach; ?>
    <?php endif; ?>
    <script type="text/javascript" src="<?= asset_url() ?>js/jquery.unveil.min.js"></script>


    <script type="text/javascript">
      tarteaucitron.user.googletagmanagerId = 'GTM-K3QQNK2';
      (tarteaucitron.job = tarteaucitron.job || []).push('googletagmanager');
    </script>

    <?php if (isset($js_to_load)): ?>
      <?php foreach ($js_to_load as $file): ?>
        <script type="text/javascript" src="<?= asset_url() ?>js/<?= $file ?>.js"></script>
      <?php endforeach; ?>
    <?php endif; ?>

    <script type="text/javascript" src="<?= asset_url() ?>js/<?= js_file() ?>.js"></script>

  </body>
</html>
