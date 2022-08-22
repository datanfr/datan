<div class="card card-groupe">
  <div class="liseret" style="background-color: <?= $groupe["couleurAssociee"] ?>"></div>
  <div class="card-avatar" style="background-color: <?= groupe_card_background($groupe['legislature'], $groupe['libelleAbrev']) ?>">
    <picture>
      <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
      <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
      <img src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= name_group($groupe['libelle']) ?>">
    </picture>
  </div>
  <div class="card-body d-flex flex-column justify-content-center align-items-center">
    <<?= $tag ?> class="d-block card-title">
      <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>" class="stretched-link no-decoration"><?= name_group($groupe['libelle']) ?></a>
    </<?= $tag ?>>
    <span class="d-block libelle"><?= $groupe["libelleAbrev"] ?></span>
    <?php if (isset($stats)): ?>
      <span class="badge badge-primary badge-stats mt-3"><?= $stats ?></span>
    <?php endif; ?>
    <?php if ($cat): ?>
      <span class="d-block mt-2"><?= $groupe["effectif"] ?> membres</span>
    <?php endif; ?>
  </div>
  <?php if ($cat): ?>
    <div class="mb-3">
      <a class="btn btn-cat btn-primary stretched-link" href="<?= base_url(); ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>" role="button">Découvrez son activité</a>
    </div>
    <?php else: ?>
      <?php if ($active && $legislature === legislature_current()): ?>
      <div class="card-footer d-flex justify-content-center align-items-center">
        <span><?= $groupe["effectif"] ?> membres</span>
      </div>
      <?php endif; ?>
  <?php endif; ?>
</div>
