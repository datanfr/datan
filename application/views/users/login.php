<div class="container-fluid" id="container-always-fluid">
  <div class="row">
    <div class="col-lg-6 col-md-7 col-12">
      <?php echo form_open(); ?>
      <div class="row mt-4">
        <div class="col-lg-4 col-md-5 col-8 offset-2 offset-md-0">
          <a href="<?= base_url() ?>">
            <img src="<?= asset_url() ?>imgs/datan/logo_baseline_color_transp.png" class="img-fluid" alt="Logo Datan">
          </a>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-lg-8 col-md-10 col-10 offset-lg-2 offset-md-1 offset-1">
          <div class="row login_links">
            <a href="<?php echo base_url(); ?>login" class="py-3 active">
              SE CONNECTER
            </a>
            <a href="<?php echo base_url(); ?>register" class="py-3 inactive">
              S'INSCRIRE
            </a>
          </div>
          <div class="row mt-5 d-flex justify-content-center login_form">
            <h1 class="text-center my-4"><?php echo $title ?></h1>
            <div class="form-group">
              <input type="text" name="username" class="form-control" placeholder="Pseudo ou Email" required autofocus>
            </div>
            <div class="form-group">
              <input type="password" name="password" class="form-control" placeholder="Mot de passe" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            <p class="mt-4">Pas encore de compte sur Datan ? <a href="<?php echo base_url(); ?>users/register">S'incrire</a></p>
          </div>
        </div>
        <?php echo form_close(); ?>
        <div class="col-lg-8 col-md-10 col-10 offset-lg-2 offset-md-1 offset-1 login_credits pt-3 mt-4">
          <p>© Datan 2020 - Tous droits réservés</p>
        </div>
      </div>
    </div>
    <div class="col-lg-6 col-md-5 d-none d-md-block m-0 p-0 login_img">
      <div class="img-container">
      </div>
    </div>
  </div>
</div>
