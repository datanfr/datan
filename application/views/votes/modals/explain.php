<div class="modal modalExplication fade" id="<?= $id ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex flex-row align-items-center">
          <div class="depute-img-circle depute-img-circle-explication mr-3">
            <?php if ($img != NULL) : ?>
              <picture>
                <source srcset="<?= asset_url(); ?>imgs/deputes_nobg_webp/depute_<?= $img ?>_webp.webp" type="image/webp">
                <source srcset="<?= asset_url(); ?>imgs/deputes_nobg/depute_<?= $img ?>.png" type="image/png">
                <img src="<?= asset_url(); ?>imgs/deputes_original/depute_<?= $img ?>.png" width="150" height="192" alt="Photo du député">
              </picture>
            <?php else : ?>
              <picture>
                <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" type="image/png">
                <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" alt="<?= $title ?>">
              </picture>
            <?php endif; ?>
          </div>
          <p class="modal-title"><?= $title ?></p>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-2 font-italic font-weight-bold"><?= $vote_titre ?></p>
        <p class="quoted">
          <?= $explication ?>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>
