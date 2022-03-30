<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid">
</div>
<div class="container pg-compte p-5" style="margin-top: -100px; top: 100px;">
  <div class="row">
    <div class="col-12">
      <h1 class="mb-5"><?= $title ?></h1>
    </div>
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title font-weight-bold">Mes données personnelles</h5>
          <p class="mb-0">Username : <?= $user['username'] ?></p>
          <p class="mb-0">Code psotal : <?= $user['zipcode'] ?></p>
          <p>Type de compte : <?= $user['type'] ?></p>
          <div class="mt-3">
            <a href="<?= base_url() ?>mon-compte/modifier-donnees-personnelles" class="card-link">Modifier</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 mt-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title font-weight-bold">Mes données de connexion</h5>
          <p class="mb-0">Email : <?= $user['email'] ?></p>
          <p class="mb-0">●●●●●●●●●</p>
          <div class="mt-3">
            <a href="<?= base_url() ?>mon-compte/modifier-donnees-connexion" class="card-link">Modifier</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 mt-4">
      <a class="btn btn-danger" href="<?= base_url() ?>mon-compte/supprimer-compte">Supprimer mon compte</a>
    </div>
  </div>
</div>
