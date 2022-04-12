<div class="container-fluid" id="container-always-fluid">
  <div class="row">
    <div class="col-lg-6 col-md-7 col-12">
      <?= form_open(); ?>
      <div class="row mt-4">
        <div class="col-lg-4 col-md-5 col-8 offset-2 offset-md-0">
          <a href="<?= base_url() ?>">
            <img src="<?= asset_url() ?>imgs/datan/logo_baseline_color_transp.png" class="img-fluid" alt="Logo Datan">
          </a>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-lg-8 col-md-10 col-10 offset-lg-2 offset-md-1 offset-1">
          <div class="row mt-5 d-flex flex-column justify-content-center login_form">
            <h1 class="text-center mt-4 mb-5"><?= $title ?></h1>
            <?php if (validation_errors()): ?>
              <div class="mt-2">
                <div class="alert alert-danger text-center">
                  <?= validation_errors(); ?>
                </div>
              </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('success')): ?>
              <div class="alert alert-success mb-4 text-center" role="alert">
                <p class="font-weight-bold"><?= $this->session->flashdata('success') ?></p>
                <a href="<?= base_url() ?>login">Connectez-vous à Datan</a>
              </div>
            <?php endif; ?>
            <div class="form-group">
              <label class="font-weight-bold">Nouveau mot de passe</label>
              <input type="password" class="form-control" name="new" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Confirmez le mot de passe</label>
              <input type="password" class="form-control" name="new_confirmation" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Valider</button>
          </div>
        </div>
        <?= form_close(); ?>
        <div class="col-lg-8 col-md-10 col-10 offset-lg-2 offset-md-1 offset-1 login_credits pt-3 mt-4">
          <p>© Datan 2020 - Tous droits réservés</p>
        </div>
      </div>
    </div>
    <div class="col-lg-6 col-md-5 d-none d-md-block m-0 p-0 login_img">
      <div class="img-container"></div>
    </div>
  </div>
</div>
