<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row my-4">
                <div class="col-sm-6">
                    <h1 class="m-0 text-primary font-weight-bold" style="font-size: 2rem"><?= $title ?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid mb-4">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <?= form_open('admin/campagnes/edit/' . $campaign['id'], ['method' => 'post']) ?>
                    <fieldset class="mb-4">
                        <legend class="fs-5 mb-3 sr-only">Période de la campagne</legend>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" value="<?= $campaign['start_date'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" value="<?= $campaign['end_date'] ?>" required>
                        </div>
                    </fieldset>
                    <fieldset class="mb-4">
                        <legend class="fs-5 mb-3 sr-only">Message de la campagne</legend>
                        <div class="mb-3">
                            <label for="campaignMessage" class="form-label">Message de la campagne</label>
                            <textarea id="editor" class="form-control" name="message" rows="5"><?= $campaign['text'] ?></textarea>
                        </div>
                    </fieldset>
                    <fieldset class="mb-4">
                        <legend class="fs-5 mb-3 sr-only">Position de la bannière</legend>
                        <div class="mb-3">
                            <label class="form-label d-block">Position</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                    type="radio"
                                    name="position"
                                    id="positionTop"
                                    value="haut" <?= $campaign['position'] === 'haut' ? 'checked' : '' ?>
                                    required>
                                <label class="form-check-label" for="positionTop">Haut</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                    type="radio"
                                    name="position"
                                    id="positionBottom"
                                    value="bas" <?= $campaign['position'] === 'bas' ? 'checked' : '' ?>
                                    required>
                                <label class="form-check-label" for="positionBottom">Bas</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="page" class="form-label">Page cible (optionnel)</label>
                            <input type="text" class="form-control" id="page" name="page" value="<?= $campaign['page'] ?>" placeholder="ex : /deputes">
                        </div>
                    </fieldset>
                    <button type="submit" class="btn btn-primary">Modifier la campagne</button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="importmap">
    {
  "imports": {
    "ckeditor5": "<?= asset_url() ?>js/libraries/ckeditor/ckeditor5.js",
    "ckeditor5/": "<?= asset_url() ?>js/libraries/ckeditor/"
  }
}
</script>

<script type="module" src="<?= asset_url() ?>js/dashboard/init-ckeditor.js"></script>