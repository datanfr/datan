<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid">
</div>
<div class="container pg-compte p-5" style="margin-top: -100px; top: 100px;">
  <div class="row">
    <div class="col-12 mb-5">
      <h1><?= $title ?></h1>
      <a href="<?= base_url() ?>mon-compte">Retour vers mon compte</a>
    </div>
    <div class="col-12" >
      <?= validation_errors(); ?>
      <?= form_open('register'); ?>
      <div class="login_form">
        <div class="form-group">
          <label class="font-weight-bold">Nom</label>
          <input type="text" class="form-control" name="name" placeholder="Nom">
        </div>
        <div class="form-group">
          <label class="font-weight-bold">Code postal</label>
          <input type="text" class="form-control" name="zipcode" placeholder="Code postal">
        </div>
        <button type="submit" class="btn btn-primary">Sauvegardez</button>
        <a href="<?= base_url() ?>mon-compte" class="btn btn-secondary">Annuler</a>
      </div>
      <?= form_close(); ?>
    </div>
  </div>
</div>
