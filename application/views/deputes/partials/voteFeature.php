<!-- Election Feature -->
<?php if ($voteFeature): ?>
  <div class="card card-vote-feature mt-5 py-2 <?= $voteFeature['vote'] ?>">
    <div class="card-body py-4">
      <h2 class="mb-4">🗳️ Vote sur la motion de censure transpartisane</h2>
      <?php if ($voteFeature['participation'] == 1): ?>
        <p><?= $title ?> <span>a voté <?= $voteFeature['vote'] ?></span> la motion de censure transpartisane.</p>
      <?php else: ?>
        <p>Le député <span><?= $title ?> n'a pas participé au vote</span> de la motion de censure transpartisane. Parce qu'il n'est pas possible de voter contre une motion, s'abstenir équivaut à un soutien au gouvernement.</p>
      <?php endif; ?>
      <p>Cette motion, déposée par le groupe LIOT et soutenue par la NUPES, fait suite à l'utilisation par le gouvernement de l'article 49-3, qui a fait adopter la réforme des retraites sans vote.</p>
      <p>Les députés ont voté sur cette motion de censure le lundi 21 mars. Au total, 278 députés ont voté pour. La censure du gouvernement a donc échoué à 9 voix près.</p>
      <p>Pour rappel, si une motion de censure est adoptée, le Premier ministre doit remettre la démission de son gouvernement.</p>
      <a class="mt-2 btn btn-light" href="<?= base_url() ?>votes/legislature-<?= $voteFeature['legislature'] ?>/vote_<?= $voteFeature['voteNumero'] ?>">Découvrir le détail du vote</a>
    </div>
  </div>
<?php endif; ?>
