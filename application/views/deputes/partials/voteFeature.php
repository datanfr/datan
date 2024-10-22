<!-- Election Feature -->
<?php if ($voteFeature): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['vote'] == "absent" ? "contre" : $voteFeature['vote'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Vote sur la motion de censure contre le gouvernement Barnier</h2>
      <?php if ($voteFeature['vote'] == "pour"): ?>
        <p><?= $title ?> <span>a voté pour</span> la motion de censure des groupes du Nouveau Front populaire (NFP) contre le gouvernement du Premier ministre Michel Barnier.</p>
      <?php elseif($voteFeature['vote'] == "contre"): ?>
        <p><?= $title ?> <span>a voté contre</span> la motion de censure des groupes du Nouveau Front populaire (NFP) contre le gouvernement du Premier ministre Michel Barnier.</p>
      <?php elseif($voteFeature['vote'] == "abstention"): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> sur la motion de censure des groupes du Nouveau Front populaire (NFP) contre le gouvernement du Premier ministre Michel Barnier.</p>
      <?php elseif($voteFeature['vote'] == "absent"): ?>
        <p><?= $title ?> <span class="text-danger">n'a pas voté</span> la motion de censure des groupes du Nouveau Front populaire (NFP) contre le gouvernement du Premier ministre Michel Barnier.</p>
        <p>En raison de la nécessité d'une majorité absolue pour faire passer une motion, son abstention peut être interprétée comme un soutien tacite au gouvernement.
      <?php endif; ?>
      <p>La motion de censure contre le gouvernement Barnier n'a pas été adoptée. Elle a recueilli 197 voix pour, alors qu'il fallait un minimum de 289 voix.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= is_congress_numero($voteFeature['voteNumero']) ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
