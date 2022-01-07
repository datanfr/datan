<div class="card card-vote>
  <?php if ($vote['vote'] == 'nv'): ?>
    <div class="thumb absent d-flex align-items-center">
      <div class="d-flex align-items-center">
        <span>ABSENT</span>
      </div>
    </div>
    <?php else: ?>
      <div class="thumb d-flex align-items-center <?= $vote['vote'] ?>">
        <div class="d-flex align-items-center">
          <span><?= mb_strtoupper($vote['vote']) ?></span>
        </div>
      </div>
  <?php endif; ?>
  <div class="card-header d-flex flex-row justify-content-between">
    <span class="date"><?= months_abbrev($vote['dateScrutinFR']) ?></span>
  </div>
  <div class="card-body d-flex flex-column justify-content-center">
    <span class="title">
      <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link no-decoration"><?= $vote['vote_titre'] ?></a>
    </span>
    <?php if ($vote['reading']): ?>
      <span class="reading mt-2">
        <?= $vote['reading'] ?>
      </span>
    <?php endif; ?>
  </div>
  <div class="card-footer">
    <span class="field badge badge-primary py-1 px-2"><?= $vote['category_libelle'] ?></span>
  </div>
</div>
