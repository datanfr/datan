<div class="container-fluid bloc-others-container mt-5">
  <div class="container bloc-others">
    <?php if ($groupe['libelleAbrev'] != "NI"): ?>
      <div class="row">
        <div class="col-12">
          <h2>Président du groupe <?= name_group($title) ?></h2>
          <div class="row mt-3">
            <div class="col-6 col-md-3 py-2">
              <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $president['dptSlug'] ?>/depute_<?= $president['nameUrl'] ?>"><?= $president['nameFirst']." ".$president['nameLast'] ?></a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-12">
        <h2>Tous les députés membres du groupe <?= name_group($title) ?></h2>
        <div class="row mt-3">
          <?php foreach ($membres as $key => $membre): ?>
            <div class="col-6 col-md-3 py-2">
              <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $membre['dptSlug'] ?>/depute_<?= $membre['nameUrl'] ?>"><?= $membre['nameFirst']." ".$membre['nameLast'] ?></a>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3">
          <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= $groupe['libelleAbrev'] ?></a>
        </div>
      </div>
    </div>
    <?php if (!empty($apparentes)): ?>
      <div class="row">
        <div class="col-12">
          <h2>Tous les députés apparentés du groupe <?= name_group($title) ?></h2>
          <div class="row mt-3">
            <?php foreach ($apparentes as $key => $mp): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"><?= $mp['nameFirst']." ".$mp['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés apparentés au groupe <?= $groupe['libelleAbrev'] ?></a>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-12">
        <?php if ($groupe['legislature'] == legislature_current()): ?>
          <h2>Tous les groupes parlementaires en activité de la <?= $groupe['legislature'] ?><sup>ème</sup> législature</h2>
        <?php else: ?>
          <h2>Les groupes parlementaires de la <?= $groupe['legislature'] ?><sup>ème</sup> législature</h2>
        <?php endif; ?>
        <div class="row mt-3">
          <?php foreach ($groupesActifs as $value): ?>
            <div class="col-6 col-md-4 py-2">
              <a class="membre no-decoration underline" href="<?= base_url(); ?>groupes/legislature-<?= $value['legislature'] ?>/<?= mb_strtolower($value['libelleAbrev']) ?>"><?= $value['libelle']." (".$value['libelleAbrev'].")" ?></a>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3">
          <a href="<?= base_url() ?>groupes">Voir tous les groupes parlementaires de la <?= $groupe['legislature'] ?><sup>ème</sup> législature</a>
        </div>
      </div>
    </div>
  </div>
</div>
