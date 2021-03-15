<div class="row bloc-carousel-votes-flickity">
    <div class="col-12 carousel-cards">
        <?php foreach ($votes as $vote) : ?>
            <div class="card card-vote">
                <div class="thumb d-flex align-items-center <?= $vote['sortCode'] ?>">
                    <div class="d-flex align-items-center">
                        <span><?= mb_strtoupper($vote['sortCode']) ?></span>
                    </div>
                </div>
                <div class="card-header d-flex flex-row justify-content-between">
                    <span class="date"><?= $vote['dateScrutinFR'] ?></span>
                </div>
                <div class="card-body d-flex align-items-center">
                    <span class="title">
                        <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link"></a>
                        <?= $vote['voteTitre'] ?></span>
                </div>
                <div class="card-footer">
                    <span class="field badge badge-primary py-1 px-2"><?= $vote['category_libelle'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="card card-vote see-all">
            <div class="card-body d-flex align-items-center justify-content-center">
                <a href="<?= base_url() ?>votes/decryptes" class="stretched-link no-decoration">VOIR TOUS</a>
            </div>
        </div>
    </div>
</div>