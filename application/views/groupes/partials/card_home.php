<div class="card card-groupe">
  <div class="liseret" style="background-color: <?= $groupe["couleurAssociee"] ?>"></div>
  <?php if ($groupe['legislature'] >= 14): ?>
    <div class="card-avatar" style="background-color: #fff">
      <picture>
        <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
        <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
        <img src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= name_group($groupe['libelle']) ?>">
      </picture>
    </div>
  <?php endif; ?>
  <div class="card-body d-flex flex-column justify-content-center align-items-center">
    <<?= $tag ?> class="d-block card-title">
      <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>" class="stretched-link no-decoration"><?= name_group($groupe['libelle']) ?></a>
    </<?= $tag ?>>
    <span class="d-block libelle"><?= $groupe['libelleAbrev'] ?></span>
    <?php if (isset($stats)): ?>
      <span class="badge badge-primary badge-stats mt-3"><?= $stats ?></span>
    <?php endif; ?>
    <?php if ($cat): ?>
      <span class="d-block mt-2"><?= $cat ?></span>
    <?php endif; ?>
  </div>
  <?php if ($cat): ?>
    <div class="mb-3">
      <a class="btn btn-cat btn-primary stretched-link" href="<?= base_url(); ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>" role="button">Découvrez son activité</a>
    </div>
  <?php else: ?>
    <?php if ($active && $groupe['legislature'] == legislature_current()): ?>
      <div class="card-footer d-flex justify-content-center align-items-center">
        <span><?= $groupe['effectif'] ?> membres</span>
      </div>
      <?php endif; ?>
  <?php endif; ?>
</div>
