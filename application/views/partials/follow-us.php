<div class="container-fluid follow-us mt-5 py-4">
  <div class="container py-5">
    <div class="row">
      <div class="col-12">
        <p class="title text-center">En direct du Palais Bourbon</p>
        <p class="subtitle text-center mt-3">Suivez Datan pour rester informé de l'actualité parlementaire</p>
        <div class="row mt-5">
          <div class="col-md-4 d-flex justify-content-center my-2">
            <a href="<?= base_url() ?>newsletter" class="btn btn-light">Inscrivez-vous à la newsletter</a>
          </div>
          <div class="col-md-4 d-flex justify-content-center my-2">
            <span class="d-flex align-items-center url_obf btn btn-light" url_obf="<?= url_obfuscation("https://www.facebook.com/datanFR/") ?>">
              <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" data-src="<?= asset_url() ?>imgs/logos/facebook.png" alt="Logo Facebook" width="30" height="30">
              <span class="ml-3">Suivez-nous sur Facebook</span>
            </span>
          </div>
          <div class="col-md-4 d-flex justify-content-center my-2">
            <span class="d-flex align-items-center url_obf btn btn-light" url_obf="<?= url_obfuscation("https://twitter.com/datanFR") ?>">
              <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-social-media.png" data-src="<?= asset_url() ?>imgs/logos/twitter.png" alt="Logo Twitter" width="30" height="30">
              <span class="ml-3">Suivez-nous sur Twitter</span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
