<div class="card card-vote">
  <div class="thumb d-flex align-items-center <?= $vote['vote_depute'] ?>">
    <div class="d-flex align-items-center">
      <span><?= mb_strtoupper($vote['vote_depute']) ?></span>
    </div>
  </div>
  <div class="card-header d-flex flex-row justify-content-between">
    <span class="date"><?= months_abbrev($vote['dateScrutinFR']) ?></span>
  </div>
  <div class="card-body d-flex flex-column justify-content-center">
    <span class="title">
      <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link underline no-decoration"><?= $vote['vote_titre'] ?></a>
    </span>
    <?php if ($vote['reading']): ?>
      <span class="reading mt-2">
        <?= $vote['reading'] ?>
      </span>
    <?php endif; ?>
  </div>
  <?php if ($vote['explication']): ?>
    <button class="explication py-2 d-flex justify-content-center align-items-center" data-toggle="modal" data-target="#explication-l<?= $vote['legislature'] ?>-v<?= $vote['voteNumero'] ?>" style="z-index: 2">
      <div class="icon d-flex justify-content-center align-items-center">
        <?= file_get_contents(asset_url()."imgs/icons/eye-fill.svg") ?>
      </div>
      <span class="ml-2">L'avis <?= $gender['du'] ?> <?= $gender['depute'] ?></span>
    </button>
  <?php endif; ?>
</div>
