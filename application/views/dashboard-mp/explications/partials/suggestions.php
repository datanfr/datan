<div class="d-flex flex-wrap justify-content-center mt-4" style="width: 100%">
  <?php foreach ($votes_without_suggestion as $key => $value): ?>
    <div class="d-flex my-3 mx-3">
      <div class="card card-vote" style="height: auto!important">
        <div class="thumb d-flex align-items-center <?= $value['vote_depute'] ?>">
          <div class="d-flex align-items-center">
            <span><?= mb_strtoupper($value['vote_depute']) ?></span>
          </div>
        </div>
        <div class="card-header d-flex flex-row justify-content-between">
          <span class="date"><?= months_abbrev($value['dateScrutinFR']) ?></span>
        </div>
        <div class="card-body d-flex flex-column justify-content-center">
          <p class="title text-dark" href="https://datan.fr/votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>"><?= $value['vote_titre'] ?></p>
          <?php if ($value['totalExplication']) : ?>
          <p class="text-dark"><i>
          <?php if ($value['totalExplication'] > 1) : ?>
            <?= $value['totalExplication'] ?> explications de députés.
          <?php else : ?>
            <?= $value['totalExplication'] ?> explication de député.
          <?php endif ?>
          </i></p>
          <?php endif ?>
        </div>
        <div class="card-footer d-flex justify-content-center">
          <a class="btn btn-primary d-flex align-items-center font-weight-bold stretched-link" href="<?= base_url() ?>dashboard/explications/create/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>">
            <?= file_get_contents(asset_url()."imgs/icons/pencil-square.svg") ?>
            <span class="ml-3">Je créé une explication</span>
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
