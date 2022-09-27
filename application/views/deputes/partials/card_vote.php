<div class="card card-vote" style="width: 310px">
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
      <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link* no-decoration"><?= $vote['vote_titre'] ?></a>
    </span>
    <?php if ($vote['reading']): ?>
      <span class="reading mt-2">
        <?= $vote['reading'] ?>
      </span>
    <?php endif; ?>
  </div>
  <?php if ($vote['explication']): ?>
    <div class="card-footer d-flex justify-content-between">
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#explication-l<?= $vote['legislature'] ?>-v<?= $vote['voteNumero'] ?>">
        L'avis du député
      </button>
      <a type="button" class="btn btn-primary" href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>">+ d'infos</a>
    </div>
  <?php else: ?>
    <div class="card-footer">
      <span class="field badge badge-primary py-1 px-2"><?= $vote['category_libelle'] ?></span>
    </div>
  <?php endif; ?>
  <?php if (!$vote['explication']): ?>
    <div class="bg-primary py-2 d-flex justify-content-center align-items-center" style="border-bottom-left-radius: 5px; border-bottom-right-radius: 5px">
      <div class="" style="color: #fff">
        <?= file_get_contents(asset_url()."imgs/icons/box-arrow-up-right.svg") ?>
      </div>
      <a href="#" class="no-decoration text-center font-weight-bold ml-2" style="color: #fff">L'avis du député</a>
    </div>
  <?php endif; ?>
</div>
