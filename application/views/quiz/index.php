<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
    <div class="container d-flex flex-column justify-content-center py-2">
        <div class="row">
            <div class="col-12">
                <h1><?= mb_strtoupper($title) ?></h1>
            </div>
        </div>
    </div>
</div>
<div class="container my-3 pg-posts">
    <div class="row row-grid">
        <div class="col-md-9">
            <?= form_open('questionnaire/resultat', 'post');  ?>
            <?php foreach ($votes as $vote) : ?>
                <h2><?= $vote['voteTitre'] ?></h2>
                <h3>Auriez-vous été pour ou contre ?</h3>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="prochoice[<?= $vote['vote_id'] ?>]" value="pour" name="<?= $vote['vote_id'] ?>[choice]" class="custom-control-input">
                    <label class="custom-control-label" for="prochoice[<?= $vote['vote_id'] ?>]" >Pour</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="conchoice[<?= $vote['vote_id'] ?>]" value="contre" name="<?= $vote['vote_id'] ?>[choice]" class="custom-control-input">
                    <label class="custom-control-label" for="conchoice[<?= $vote['vote_id'] ?>]" >Contre</label>
                </div>
                <div class="form-group">
                    <label for="weight<?= $vote['vote_id'] ?>"><h3>Etait-il important de s'exprimer sur ce vote ?</h3></label>
                    <input type="number" max="5" min="0" name="<?= $vote['vote_id'] ?>[weigth]" class="form-control" id="weight<?= $vote['vote_id'] ?>">
                </div>
            <?php endforeach ?>
            <button type="submit" class="btn btn-primary">Calculer</button>
            <?= form_close() ?>
        </div>
    </div>
</div>