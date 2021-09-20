<div class="container-fluid pg-depute-all" id="container-always-fluid">
    <div class="row">
        <div class="container">
            <div class="row row-grid bloc-titre">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1><?= $title ?></h1>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-3">Abonnement(s) de <?= $newsletter['email'] ?></h2>
                    <?= form_open() ?>
                    <?php foreach ($lists as $list): ?>
                      <div class="form-check">
                          <input type="checkbox" class="form-check-input" name="<?= $list['name'] ?>" id="<?= $list['name'] ?>" <?= !$newsletter[$list['name']] ?: 'checked' ?>>
                          <label for="<?= $list['name'] ?>"><?= $list['label'] ?></label>
                      </div>
                    <?php endforeach; ?>
                    <button type="submit" name="submit" class="btn btn-primary my-3">Mettre à jour</button>
                    <?= form_close() ?>
                    <h2 class="mt-5">Se désabonner de toutes les newsletters</h2>
                    <p>Si vous souhaitez vous désabonnez de toutes les newsletters, <a href="<?= base_url() ?>newsletter/delete/<?= urlencode($newsletter['email']) ?>">cliquez ici</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
