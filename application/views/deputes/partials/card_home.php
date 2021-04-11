<div class="card card-depute mx-2">
  <div class="liseret" style="background-color: <?= $depute["couleurAssociee"] ?>"></div>
  <div class="card-avatar">
    <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" data-src="<?= base_url(); ?>assets/imgs/deputes_nobg/depute_<?= substr($depute["mpId"], 2) ?>.png" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>">
  </div>
  <div class="card-body d-flex align-items-center justify-content-center">
    <div>
      <?php if ($tag == "span"): ?>
        <span class="d-block card-title">
          <a href="<?php echo base_url(); ?>deputes/<?php echo $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
        </span>
      <?php endif; ?>
      <?php if ($tag == "h2"): ?>
        <h2 class="d-block card-title">
          <a href="<?php echo base_url(); ?>deputes/<?php echo $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
        </h2>
      <?php endif; ?>
      <?php if ($tag == "h3"): ?>
        <h3 class="d-block card-title">
          <a href="<?php echo base_url(); ?>deputes/<?php echo $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
        </h3>
      <?php endif; ?>
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
