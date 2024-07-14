<div class="d-flex align-items-start my-4">
    <div class="depute-img depute-img-<?= $exp['legislature_last'] >= 17 ? 'square' : 'circle' ?>">
        <?php if ($exp['img'] && $this->config->item('mp_photos')) : ?>
            <a href="<?= base_url() . "deputes/" . $exp['dptSlug'] . '/depute_' . $exp['nameUrl'] ?>">
              <picture>
                  <source srcset="<?= asset_url(); ?>imgs/<?= $exp['legislature_last'] >= 17 ? 'deputes_webp/' : 'deputes_nobg_webp/' ?>depute_<?= $exp['idImage'] ?>_webp.webp" type="image/webp">
                  <source srcset="<?= asset_url(); ?>imgs/<?= $exp['legislature_last'] >= 17 ? 'deputes_original/' : 'deputes_nobg/' ?>depute_<?= $exp['idImage'] ?>.png" type="image/png">
                  <img src="<?= asset_url(); ?>imgs/deputes_original/depute_<?= $exp['idImage'] ?>.png" width="150" height="192" alt="<?= $title ?>">
              </picture>
            </a>
        <?php else : ?>
            <picture>
                <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" type="image/png">
                <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" alt="<?= $title ?>">
            </picture>
        <?php endif; ?>
    </div>
    <div class="ml-3">
        <p class="mb-0 collapse" id="collapse<?= $exp['idImage'] ?>">
            <a class="font-weight-bold no-decoration underline" href="<?= base_url() . "deputes/" . $exp['dptSlug'] . '/depute_' . $exp['nameUrl'] ?>" class="pg-depute-all">
                <?= $exp['nameFirst'] . ' ' . $exp['nameLast'] ?>
            </a>
            - <span style="color: <?= $exp['couleurAssociee'] ?>"><?= $exp['libelleAbrev'] ?></span>
            - <?= $exp['text'] ?>
        </p>
        <a class="read collapsed" data-toggle="collapse" href="#collapse<?= $exp['idImage'] ?>" aria-expanded="false" aria-controls="collapse<?= $exp['idImage'] ?>"></a>
    </div>
</div>
