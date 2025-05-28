<!-- Election Feature -->
<?php if ($voteFeature): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['vote'] == "absent" ? "contre" : $voteFeature['vote'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Motion de censure du gouvernement Barnier du 4 décembre 2024</h2>
      <?php if ($voteFeature['vote'] == "pour"): ?>
        <p><?= $title ?> <span>a voté pour</span> la motion de censure contre le gouvernement du Premier ministre Michel Barnier.</p>
      <?php elseif($voteFeature['vote'] == "contre"): ?>
        <p><?= $title ?> <span>a voté contre</span> la motion de censure contre le gouvernement du Premier ministre Michel Barnier.</p>
      <?php elseif($voteFeature['vote'] == "abstention"): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> sur la motion de censure contre le gouvernement du Premier ministre Michel Barnier.</p>
      <?php elseif($voteFeature['vote'] == "absent"): ?>
        <p><?= $title ?> <span class="text-danger">n'a pas voté</span> la motion de censure contre le gouvernement du Premier ministre Michel Barnier.</p>
        <p>En raison de la nécessité d'une majorité absolue pour faire passer une motion, son abstention peut être interprétée comme un soutien tacite au gouvernement.
      <?php endif; ?>
      <p>La motion de censure <b>a été adoptée</b>, recueillant 331 voix, soit davantage que la majorité absolue. Ce vote, qui marque la chute du gouvernement, fait suite à la décision de Michel Barnier d'engager la responsabilité de son exécutif en recourant à l'article 49.3 sur le projet de loi de financement de la Sécurité sociale.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= is_congress_numero($voteFeature['voteNumero']) ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
