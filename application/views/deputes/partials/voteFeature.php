<!-- Election Feature -->
<?php if ($voteFeature['participation'] == 1): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['vote'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Vote sur la motion de rejet du projet de loi immigration</h2>
      <?php if ($voteFeature['vote'] == "pour"): ?>
        <p><?= $title ?> <span>a voté pour</span> la motion de rejet au projet de loi immigration.</p>
      <?php elseif($voteFeature['vote'] == "contre"): ?>
        <p><?= $title ?> <span>a voté contre</span> la motion de rejet au projet de loi immigration.</p>
      <?php elseif($voteFeature['vote'] == "abstention"): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> la motion de rejet au projet de loi immigration.</p>
      <?php endif; ?>
      <p>Cette motion de rejet a été adoptée le 11 décembre 2023. Elle a entraîné le rejet du projet de loi sur l'immigration. Le gouvernement a annoncé renvoyer le texte en commission mixte paritaire (CMP), où députés et sénateurs doivent désormais trouver un compromis.</p>
      <p>La motion de rejet a été adoptée par 270 voix contre 265.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= $voteFeature['voteNumero'] ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
