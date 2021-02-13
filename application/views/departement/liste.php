    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
      <div class="container d-flex flex-column justify-content-center py-2">
        <h1><?= mb_strtoupper($title) ?></h1>
      </div>
    </div>
    <div class="container pg-departement-liste my-4">
      <div class="row mt-2 mb-3">
        <div class="col-12 d-flex flex-column align-items-center">
          <?php foreach ($departements as $dpt): ?>
            <div class="my-2">
              <a href="<?= base_url()."deputes/".$dpt['slug'] ?>" class="no-decoration underline-blue"><?= $dpt['departement_code'].' - '.$dpt['departement_nom'] ?></a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
