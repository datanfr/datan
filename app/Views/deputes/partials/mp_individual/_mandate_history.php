<!-- BLOC HISTORIQUE MANDATS -->
<div class="bloc-mandats mt-5">
  <h2 class="mb-4 title-center">Historique des mandats</h2>
  <?php if (count($mandats) > 1): ?>
    <p>
      <?= $title ?> a été élu<?= $gender['e'] ?> à plusieurs reprises. Au total, <?= $gender['pronom'] ?> a été <?= $gender['depute'] ?> à l'Assemblée nationale pendant <?= $depute['lengthEdited'] ?>.
    </p>
    <p>
      <?= $title ?> a été député pendant les législatures suivantes :
      <?php $i = 1; ?>
      <?php foreach ($mandatsReversed as $mandat): ?>
        <?php if ($mandat['legislature']): ?>
          <i><?= $mandat['legislature'] ?>ème législature</i><?php if ($i < count($mandats)): ?>,<?php else: ?>.<?php endif; ?>
        <?php endif; ?>
        <?php $i++; ?>
      <?php endforeach; ?>
    </p>
    <p>
      Cette page publie les statistiques de <?= $title ?> pour sa législature la plus récente (<?= $depute['legislature'] ?><sup>ème</sup> législature). Nous publions sur Datan l'historique de tous les mandats depuis la 14<sup>ème</sup> législature.
    </p>
    <div class="row">
      <?php foreach ($mandats as $mandat): ?>
        <?php if ($mandat['legislature'] >= 14 && $mandat['legislature'] != $depute['legislature']): ?>
          <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
            <?php if ($mandat['legislature'] == legislature_current()): ?>
              <a class="btn d-flex align-items-center justify-content-center" href="<?= base_url() ?>deputes/<?= $mandat['dptSlug'] ?>/depute_<?= $mandat['nameUrl'] ?>">
                <?= $mandat['legislature'] ?>ème législature
              </a>
            <?php else: ?>
              <a class="btn d-flex align-items-center justify-content-center" href="<?= base_url() ?>deputes/<?= $mandat['dptSlug'] ?>/depute_<?= $mandat['nameUrl'] ?>/legislature-<?= $mandat['legislature'] ?>">
                <?= $mandat['legislature'] ?>ème législature
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>
      <?= $title ?> n'a été élu<?= $gender['e'] ?> <?= $gender['depute'] ?> qu'une fois. Au total, <?= $gender['pronom'] ?> a été <?= $gender['depute'] ?> à l'Assemblée nationale pendant <?= $depute['lengthEdited'] ?>.
    </p>
    <p>
      <?= $title ?> a été député pendant les législatures suivantes :
      <?php $i = 1; ?>
      <?php foreach ($mandatsReversed as $mandat): ?>
        <?php if ($mandat['legislature']): ?>
          <i><?= $mandat['legislature'] ?>ème législature</i><?php if ($i < count($mandats)): ?>,<?php else: ?>.<?php endif; ?>
        <?php endif; ?>
        <?php $i++; ?>
      <?php endforeach; ?>
    </p>
  <?php endif; ?>
</div> <!-- // END BLOC HISTORIQUE MANDAT -->