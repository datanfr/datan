<div class="container-fluid" id="container-always-fluid">
  <div class="row">
    <div class="col-lg-6 col-md-7 col-12" >
      <?= form_open($token ? 'register/' . $token : 'register'); ?>
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
            <a href="<?= base_url(); ?>login" class="py-3 inactive">
              SE CONNECTER
            </a>
            <a href="<?= base_url(); ?>register" class="py-3 active">
              S'INSCRIRE
            </a>
          </div>
          <?php if ($this->session->flashdata('registered') == 1): ?>
            <div class="row mt-5">
              <div class="alert alert-success">
                Vous êtes désormais inscrit. Vous pouvez vous connecter en <a href="<?= base_url() ?>login">cliquant ici.</a>
              </div>
            </div>
            <?php else: ?>
              <div class="row mt-5 d-flex justify-content-center login_form">
                <h3 class="text-center my-4"><?= $title ?></h3>
                <?php if (!$mp): ?>
                  <div class="alert alert-danger">
                    Attention, <b>si vous êtes député</b>, veuillez d'aborder demander un lien d'activation en <a href="<?= base_url() ?>demande-compte-depute">cliquant ici.</a>
                  </div>
                <?php endif; ?>
                <div class="mt-2">
                  <?= validation_errors(); ?>
                </div>
                <div class="form-group">
                  <label class="font-weight-bold">Nom</label>
                  <input type="text" class="form-control" name="name" <?= $mp ? 'value="'.$depute['name'].'"' : 'placeholder="Jean Dupont"' ?>>
                </div>
                <div class="form-group">
                  <label class="font-weight-bold">Code postal</label>
                  <input type="text" class="form-control" name="zipcode" <?= !$mp ? 'placeholder="7500"' : '' ?>>
                </div>
                <div class="form-group">
                  <label class="font-weight-bold">Adresse email</label>
                  <input type="text" class="form-control" name="email" <?= $mp ? 'value="' . $depute['contacts']['mailAn'] .'" readonly' : 'placeholder="jean.dupont@gmail.com"' ?>>
                </div>
                <div class="form-group">
                  <label class="font-weight-bold"><span class="font-weight-bold">Pseudo</span> <span class="font-italic">(maximum 10 characters)</span></label>
                  <input type="text" class="form-control" name="username" <?= !$mp ? 'placeholder="jeandupont"' : '' ?>>
                </div>
                <div class="form-group">
                  <label class="font-weight-bold">Mot de passe</label>
                  <input type="password" class="form-control" name="password">
                </div>
                <div class="form-group">
                  <label class="font-weight-bold">Confirmez le mot de passe</label>
                  <input type="password" class="form-control" name="password2">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Confirmez</button>
                <p class="mt-4">Déjà un compte sur Datan ? <a href="<?= base_url(); ?>login">Connectez-vous</a></p>
                <p>Vous êtes député et vous n'avez pas encore un compte Datan ? <a href="<?= base_url(); ?>demande-compte-depute">Demandez-le !</a></p>
              </div>
          <?php endif; ?>
        </div>
        <?= form_close(); ?>
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
