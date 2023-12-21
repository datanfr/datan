<!-- Election Feature -->
<?php if ($voteFeature['participation'] == 1): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['vote'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Vote sur le projet de loi immigration</h2>
      <?php if ($voteFeature['vote'] == "pour"): ?>
        <p><?= $title ?> <span>a voté pour</span> le projet de loi immigration.</p>
      <?php elseif($voteFeature['vote'] == "contre"): ?>
        <p><?= $title ?> <span>a voté contre</span> le projet de loi immigration.</p>
      <?php elseif($voteFeature['vote'] == "abstention"): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> le projet de loi immigration.</p>
      <?php endif; ?>
      <p>Le projet de loi immigration a été adopté le 19 décembre 2023.</p>
      <p>Le texte a été adopté par 349 voix contre 186. Les groupes de la majorité (Renaissance, Horizons, et Démocrate), ainsi que les Républicains et le Rassemblement national ont voté en faveur.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= $voteFeature['voteNumero'] ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
