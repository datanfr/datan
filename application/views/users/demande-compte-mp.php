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
            <h1 class="text-center my-4"><?= $title ?></h1>
            <?php if ($this->session->flashdata('error')): ?>
              <div class="alert alert-danger mb-4 text-center" role="alert">
                <?= ($this->session->flashdata('error')) ?>
              </div>
            <?php endif; ?>
            <div class="mt-2">
              <?= validation_errors(); ?>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Email de l'Assemblée nationale se terminant par : @assemblee-nationale.fr</label>
              <input type="email" name="email" class="form-control" placeholder="Email" required autofocus>
            </div>
            <?php $this->view('captcha/index') ?>
            <div class="alert alert-danger mb-4 text-center">Attention, le lien d'activation ne sera valide que 24 heures.</div>
            <button type="submit" class="btn btn-primary btn-block">Demandez un lien d'activation</button>
            <p class="mt-4">Déjà un compte Datan ? <a href="<?= base_url(); ?>login">Se connecter</a></p>
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
