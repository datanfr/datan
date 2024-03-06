<!-- Election Feature -->
<?php if ($voteFeature['participation'] == 1): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['vote'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Vote au <b>Congrès</b> sur la constitutionnalisation de l'IVG</h2>
      <?php if ($voteFeature['vote'] == "pour"): ?>
        <p><?= $title ?> <span>a voté pour</span> l'inscription de l'interruption volontaire de grossesse (IVG) dans la Constitution.</p>
      <?php elseif($voteFeature['vote'] == "contre"): ?>
        <p><?= $title ?> <span>a voté contre</span> l'inscription de l'interruption volontaire de grossesse (IVG) dans la Constitution.</p>
      <?php elseif($voteFeature['vote'] == "abstention"): ?>
        <p><?= $title ?> <span>s'est abstenu<?= $gender['e'] ?></span> sur le vote sur l'inscription de l'interruption volontaire de grossesse (IVG) dans la Constitution.</p>
      <?php endif; ?>
      <p>Le projet de loi constitutionnelle relatif à la liberté de recourir à l'interruption volontaire de grossesse (IVG) a été adopté par le Congrès réunissant députés et sénateurs le 4 mars 2024.</p>
      <p>Le texte a été adopté par 780 voix contre 72. Cinquante parlementaires se sont abstenus.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= is_congress_numero($voteFeature['voteNumero']) ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
