<!-- Election Feature -->
<?php if ($voteFeature): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['voteText'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Suspension de la réforme des retraites</h2>
      <?php if ($voteFeature['vote'] == 1): ?>
        <p><?= $title ?> <span>a voté pour</span> la suspension de la réforme des retraites jusqu'au 1er janvier 2028.</p>
      <?php elseif($voteFeature['vote'] == -1): ?>
        <p><?= $title ?> <span>a voté contre</span> la suspension de la réforme des retraites jusqu'au 1er janvier 2028.</p>
      <?php elseif($voteFeature['vote'] == 0): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> sur le vote sur la suspension de la réforme des retraites.</p>
      <?php endif; ?>
      <p>La suspension de la réforme des retraites a été adoptée par l'Assemblée nationale le 12 novembre 2025. Au total, 255 députés ont voté en faveur et 146 ont voté contre.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= is_congress_numero($voteFeature['voteNumero']) ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
