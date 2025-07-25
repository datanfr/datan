<!-- Election Feature -->
<?php if ($voteFeature): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['voteText'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Proposition de loi Duplomb sur l’agriculture</h2>
      <?php if ($voteFeature['vote'] == 1): ?>
        <p><?= $title ?> <span>a voté pour</span> la proposition de loi visant à lever les contraintes à l’exercice du métier d’agriculteur, dite loi Duplomb.</p>
      <?php elseif($voteFeature['vote'] == -1): ?>
        <p><?= $title ?> <span>a voté contre</span> la proposition de loi visant à lever les contraintes à l’exercice du métier d’agriculteur, dite loi Duplomb.</p>
      <?php elseif($voteFeature['vote'] == 0): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> sur la proposition de loi visant à lever les contraintes à l’exercice du métier d’agriculteur, dite loi Duplomb.</p>
      <?php endif; ?>
      <p>Cette proposition de loi <b>a été adoptée</b> par l'Assemblée nationale, avec 316 voix pour et 223 voix contre.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= is_congress_numero($voteFeature['voteNumero']) ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
