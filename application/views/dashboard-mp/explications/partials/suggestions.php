<div class="d-flex flex-wrap justify-content-center mt-4" style="width: 100%">
  <?php foreach ($votes_without_suggestion as $key => $value): ?>
    <div class="d-flex my-3 mx-3">
      <div class="card card-vote">
        <div class="thumb d-flex align-items-center <?= $value['vote_depute'] ?>">
          <div class="d-flex align-items-center">
            <span><?= mb_strtoupper($value['vote_depute']) ?></span>
          </div>
        </div>
        <div class="card-header d-flex flex-row justify-content-between">
          <span class="date"><?= months_abbrev($value['dateScrutinFR']) ?></span>
        </div>
        <div class="card-body d-flex flex-column justify-content-center">
          <p class="title" href="https://datan.fr/votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>"><?= $value['vote_titre'] ?></p>
          <?php if ($value['totalExplication']) : ?>
          <p><i><?= $value['totalExplication'] ?> députés ont déjà donné leur raison.</i></p>
          <?php endif ?>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <a class="btn btn-primary d-flex align-items-center font-weight-bold stretched-link" href="<?= base_url() ?>dashboard-mp/explications/create/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>">
            <?= file_get_contents(asset_url()."imgs/icons/pencil-square.svg") ?>
            <span class="ml-3">Explication</span>
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
