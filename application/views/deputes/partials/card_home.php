<div class="card card-depute mx-2">
  <div class="liseret" style="background-color: <?= $depute["couleurAssociee"] ?>"></div>
  <div class="card-avatar">
    <?php if ($depute['img']): ?>
      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" width="130" height="166" data-src="<?= base_url(); ?>assets/imgs/deputes_nobg/depute_<?= substr($depute["mpId"], 2) ?>.png" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>">
      <?php else: ?>
      <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" width="130" height="166" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>">
    <?php endif; ?>
  </div>
  <div class="card-body d-flex align-items-center justify-content-center">
    <div>
      <<?= $tag ?> class="d-block card-title">
        <a href="<?= base_url(); ?>deputes/<?= $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?= $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
      </<?= $tag ?>>
      <?php if (isset($stats)): ?>
        <span class="badge badge-primary badge-stats mb-3"><?= $stats ?></span>
      <?php endif; ?>
      <span class="d-block"><?= $depute["cardCenter"] ?></span>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-center align-items-center">
    <span><?= $depute["groupLibelle"] ?> (<?= $depute["groupLibelleAbrev"] ?>)</span>
  </div>
</div>
