<div class="modal fade modalDatan" id="modalExplain" tabindex="-1" role="dialog" aria-labelledby="modalExplainLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title" id="modalExplainLabel">L'explication par <?= $explication['nameFirst'] . ' ' . $explication['nameLast'] ?></span>
                <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </span>
            </div>
            <div class="modal-body">
                <div class="p-2 row">
                    <div class="col-md-3">
                        <?php if ($explication['img']) : ?>
                            <a href="<?= base_url() . "deputes/" . $explication['dptSlug'] . '/depute_' . $explication['nameUrl'] ?>">
                                <picture>
                                    <source srcset="<?= asset_url(); ?>imgs/deputes_nobg_webp/depute_<?= $explication['idImage'] ?>_webp.webp" type="image/webp">
                                    <source srcset="<?= asset_url(); ?>imgs/deputes_nobg/depute_<?= $explication['idImage'] ?>.png" type="image/png">
                                    <img src="<?= asset_url(); ?>imgs/deputes_original/depute_<?= $explication['idImage'] ?>.png" width="150" height="192" alt="<?= $title ?>">
                                </picture>
                            </a>
                        <?php else : ?>
                            <picture>
                                <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" type="image/png">
                                <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" alt="<?= $title ?>">
                            </picture>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9">
                        <h2 class="mb-0" id="collapse<?= $explication['idImage'] ?>">
                            <a class="font-weight-bold no-decoration underline" href="<?= base_url() . "deputes/" . $explication['dptSlug'] . '/depute_' . $explication['nameUrl'] ?>" class="pg-depute-all">
                                <?= $explication['nameFirst'] . ' ' . $explication['nameLast'] ?>
                            </a>
                        </h2>
                        <h3>du groupe <span style="color: <?= $explication['couleurAssociee'] ?>"><?= $explication['libelle'] ?></span></h3>
                    </div>
                </div>
                <div>
                    <div class="quote-img"><?= file_get_contents(asset_url()."imgs/svg/quote.svg") ?></div>
                    <p>
                        <?= $explication['text'] ?>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
</script>