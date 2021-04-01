<div class="container-fluid pg-depute-all" id="container-always-fluid">
    <div class="row">
        <div class="container">
            <div class="row row-grid bloc-titre">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1><?= $title ?></h1>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <h2>Abonnement de <?= $newsletter['email'] ?></h2>
                    <br>
                    <?= form_open('newsletter/edit/' . $newsletter['email']) ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="general" id="general" <?= !$newsletter['general'] ?: 'checked' ?>>
                        <label for="general">Inscription à la newsletter principale</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Mettre à jour </button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>