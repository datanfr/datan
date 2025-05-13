<!-- AUTRES DEPUTES -->
<div class="container-fluid pg-depute-individual bloc-others-container">
  <div class="container bloc-others">
    <?php if (isset($other_deputes)) : ?>
      <div class="row mb-5">
        <div class="col-12">
          <?php if ($depute['legislature'] != legislature_current()) : ?>
            <h2>Les autres députés de la <?= $depute['legislature'] ?><sup>e</sup> législature</h2>
          <?php elseif ($active) : ?>
            <h2>Les autres députés <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>)</h2>
          <?php else : ?>
            <h2>Les autres députés plus en activité</h2>
          <?php endif; ?>
          <div class="row mt-3">
            <?php foreach ($other_deputes as $mp) : ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'] . ' ' . $mp['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <?php if ($depute['legislature'] != legislature_current()) : ?>
              <a href="<?= base_url(); ?>deputes/legislature-<?= $depute['legislature'] ?>">Tous les députés de la législature <?= $depute['legislature'] ?></a>
            <?php elseif ($active) : ?>
              <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($depute['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>)</a>
            <?php else : ?>
              <a href="<?= base_url(); ?>deputes/inactifs">Tous les députés plus en activité</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-12">
        <h2>Les députés en activité du département <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></h2>
        <div class="row mt-3">
          <?php foreach ($other_deputes_dpt as $mp) : ?>
            <div class="col-6 col-md-3 py-2">
              <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'] . ' ' . $mp['nameLast'] ?></a>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3">
          <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>">Voir tous les députés élus dans le département <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] ?></a>
        </div>
      </div>
    </div>
  </div>
</div>