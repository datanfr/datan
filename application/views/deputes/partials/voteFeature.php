<!-- Election Feature -->
<?php if ($voteFeature): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['voteText'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Proposition de loi sur l'aide à mourir</h2>
      <?php if ($voteFeature['vote'] == 1): ?>
        <p><?= $title ?> <span>a voté pour</span> la proposition de loi relative au droit à l’aide à mourir. </p>
      <?php elseif($voteFeature['vote'] == -1): ?>
        <p><?= $title ?> <span>a voté contre</span> la proposition de loi relative au droit à l’aide à mourir.</p>
      <?php elseif($voteFeature['vote'] == 0): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> sur la proposition de loi relative au droit à l’aide à mourir.</p>
      <?php endif; ?>
      <p>La proposition de loi <b>a été adoptée</b> en première lecture, avec 305 voix pour et 199 voix contre.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= is_congress_numero($voteFeature['voteNumero']) ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
