<div class="container-fluid pg-depute-all" id="container-always-fluid">
    <div class="row">
        <div class="container">
            <div class="row row-grid bloc-titre">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1><?= $title ?></h1>
                </div>
            </div>
            <div class="row mb-4">
              <div class="col-lg-6 mb-4 mb-lg-0">
                <p><b>Félicitations !</b> Vous avez mis à jour vos abonnements aux newsletters de Datan.</p>
                <div class="d-flex justify-content-around my-5">
                  <a href="<?= base_url() ?>/newsletter/edit/<?= urlencode($newsletter['email']) ?>" class="btn btn-warning">Retour</a>
                  <a href="<?= base_url() ?>" class="btn btn-primary">Découvrez Datan !</a>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>
