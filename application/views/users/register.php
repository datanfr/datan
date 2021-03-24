<div class="container-fluid" id="container-always-fluid">
  <div class="row">
    <div class="col-lg-6 col-md-7 col-12" >
      <?php echo validation_errors(); ?>
      <?php echo form_open('/register'); ?>
      <div class="row mt-4">
        <div class="col-lg-4 col-md-5 col-8 offset-2 offset-md-0">
          <a href="<?= base_url() ?>">
            <img src="<?= asset_url() ?>imgs/datan/logo_baseline_color_transp.png" class="img-fluid" alt="">
          </a>
        </div>
      </div>
        <div class="row mt-5">
          <div class="col-lg-8 col-md-10 col-10 offset-lg-2 offset-md-1 offset-1">
            <div class="row login_links">
              <a href="<?php echo base_url(); ?>login" class="py-3 inactive">
                SE CONNECTER
              </a>
              <a href="<?php echo base_url(); ?>register" class="py-3 active">
                S'INSCRIRE
              </a>
            </div>
            <div class="row mt-5 d-flex justify-content-center login_form">
              <h3 class="text-center my-4"><?php echo $title ?></h3>
              <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" name="name" placeholder="Nom">
              </div>
              <div class="form-group">
                <label>Code postal</label>
                <input type="text" class="form-control" name="zipcode" placeholder="Code postal">
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="text" class="form-control" name="email" placeholder="Email">
              </div>
              <div class="form-group">
                <label>Pseudo</label>
                <input type="text" class="form-control" name="username" placeholder="Pseudo">
              </div>
              <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" class="form-control" name="password" placeholder="Mot de passe">
              </div>
              <div class="form-group">
                <label>Confirmez le mot de passe</label>
                <input type="password" class="form-control" name="password2" placeholder="Mot de passe">
              </div>
              <button type="submit" class="btn btn-primary btn-block">Confirmez</button>
              <p class="mt-4">Déjà un compte sur Datan ? <a href="<?php echo base_url(); ?>/login">Se connnecter</a></p>
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
