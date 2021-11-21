<div class="card card-vote">
  <div class="thumb d-flex align-items-center <?= $vote['sortCode'] ?>">
    <div class="d-flex align-items-center">
      <span><?= mb_strtoupper($vote['sortCode']) ?></span>
    </div>
  </div>
  <div class="card-header d-flex flex-row justify-content-between">
    <span class="date"><?= months_abbrev($vote['dateScrutinFR']) ?></span>
  </div>
  <div class="card-body d-flex flex-column justify-content-center">
    <span class="title">
      <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link"></a>
      <?= $vote['voteTitre'] ?>
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
