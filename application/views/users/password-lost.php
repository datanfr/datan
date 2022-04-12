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
            <p>Indiquez l'e-mail avec lequel vous êtes inscrit. Vous recevrez dans quelques instants un e‑mail avec un lien permettant de créer un nouveau mot de passe.</p>
            <?php if (validation_errors()): ?>
              <div class="mt-2">
                <div class="alert alert-danger text-center">
                  <?= validation_errors(); ?>
                </div>
              </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('flash')): ?>
              <div class="alert alert-danger mb-4 text-center" role="alert">
                <?= ($this->session->flashdata('flash')) ?>
              </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('success')): ?>
              <div class="alert alert-success mb-4 text-center" role="alert">
                <p class="font-weight-bold">Un e-mail vient de vous être envoyé.</p>
                <p><b>ATTENTION :</b> Le lien sera désactivé dans vingt-quatre heures.</p>
              </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('failure')): ?>
              <div class="alert alert-danger mb-4 text-center" role="alert">
                <p class="font-weight-bold">Nous ne trouvons pas votre email.</p>
                <p>Essayez de rentrer un nouvel email.</p>
              </div>
            <?php endif; ?>
            <div class="form-group">
              <input type="email" name="email" class="form-control" placeholder="Email" required autofocus>
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
