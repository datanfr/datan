<div class="col-lg-4 col-md-6 py-3">
  <div class="card card-groupe">
    <div class="liseret" style="background-color: <?= $groupe["couleurAssociee"] ?>"></div>
    <div class="card-avatar group">
      <picture>
        <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
        <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
        <img src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groupe['libelle'] ?>">
      </picture>
    </div>
    <div class="card-body d-flex flex-column justify-content-center align-items-center">
      <<?= $tag ?> class="d-block card-title">
        <a href="<?= base_url(); ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>" class="stretched-link no-decoration"><?= $groupe['libelle'] ?></a>
      </<?= $tag ?>>
      <span class="d-block"><?= $groupe["libelleAbrev"] ?></span>
    </div>
    <?php if ($active): ?>
    <div class="card-footer d-flex justify-content-center align-items-center">
      <span><?= $groupe["effectif"] ?> membres</span>
    </div>
    <?php endif; ?>
  </div>
</div>
