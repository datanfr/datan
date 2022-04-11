<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid">
</div>
<div class="container pg-compte p-5" style="margin-top: -100px; top: 100px;">
  <div class="row">
    <div class="col-12 mb-5">
      <h1><?= $title ?></h1>
      <a href="<?= base_url() ?>mon-compte">Retour vers mon compte</a>
    </div>
    <div class="col-12" >
      <p class="font-weight-bold mb-4">Êtes-vous sûr de vouloir supprimer votre compte Datan ?</p>
      <a href="<?= base_url() ?>mon-compte" class="btn btn-secondary">Non, je ne supprime pas mon compte</a>
      <a href="<?= base_url() ?>mon-compte/supprimer-compte/confirmed" class="btn btn-danger">Oui, je supprime mon compte</a>
    </div>
  </div>
</div>
