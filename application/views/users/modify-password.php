<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid">
</div>
<div class="container pg-compte p-5" style="margin-top: -100px; top: 100px;">
  <div class="row">
    <div class="col-12 mb-5">
      <h1><?= $title ?></h1>
      <a href="<?= base_url() ?>mon-compte">Retour vers mon compte</a>
    </div>
    <div class="col-12" >
      <?php if ($this->session->flashdata('login_failed')): ?>
        <div class="alert alert-danger mb-4 text-center" role="alert">
          <?= ($this->session->flashdata('login_failed')) ?>
        </div>
      <?php endif; ?>    
      <?= validation_errors(); ?>
      <?= form_open('mon-compte/modifier-password'); ?>
      <div class="login_form">
        <div class="form-group">
          <label class="font-weight-bold">Mot de passe actuel</label>
          <input type="password" class="form-control" name="current" required>
        </div>
        <div class="form-group">
          <label class="font-weight-bold">Nouveau mot de passe</label>
          <input type="password" class="form-control" name="new" required>
        </div>
        <div class="form-group">
          <label class="font-weight-bold">Confirmez le mot de passe</label>
          <input type="password" class="form-control" name="new_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary">Sauvegardez</button>
        <a href="<?= base_url() ?>mon-compte" class="btn btn-secondary">Annuler</a>
      </div>
      <?= form_close(); ?>
    </div>
  </div>
</div>
