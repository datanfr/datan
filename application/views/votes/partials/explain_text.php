
<div class="d-flex align-items-start my-4">
    <div class="depute-img-circle">
        <?php if ($exp['img']) : ?>
            <picture>
                <source srcset="<?= asset_url(); ?>imgs/deputes_nobg_webp/depute_<?= $exp['idImage'] ?>_webp.webp" type="image/webp">
                <source srcset="<?= asset_url(); ?>imgs/deputes_nobg/depute_<?= $exp['idImage'] ?>.png" type="image/png">
                <img src="<?= asset_url(); ?>imgs/deputes_original/depute_<?= $exp['idImage'] ?>.png" width="150" height="192" alt="<?= $title ?>">
            </picture>
        <?php else : ?>
            <picture>
                <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" type="image/png">
                <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" alt="<?= $title ?>">
            </picture>
        <?php endif; ?>
    </div>
    <div class="ml-3">
        <p>
            <a class="font-weight-bold no-decoration underline" href="<?= base_url() . "deputes/" . $exp['dptSlug'] . '/depute_' . $exp['nameUrl'] ?>" class="pg-depute-all">
                <?= $exp['nameFirst'] . ' ' . $exp['nameLast'] ?> -
            </a>
            <?= $exp['text'] ?>
        </p>
    </div>
</div>