    <div class="container-fluid pg-groupe-all mb-5" id="container-always-fluid">
      <div class="row">
        <div class="container">
          <div class="row bloc-titre">
            <div class="col-12">
              <h1><?= $title ?></h1>
            </div>
          </div>
          <div class="row row-grid mt-5">
            <div class="col-md-7">
              <p>Les <b>groupes politiques</b> rassemblent des députés selon leur affinité politique. Un groupe doit être composé au minimum de 15 députés.</p>
              <p>Les groupes jouent un rôle important dans l'Assemblée. Ils permettent de structurer le débat sur la base de lignes politiques (gauche, droite, centre). Les groupes reçoivent également des ressources politiques (temps de parole) et financières non négligeables.</p>
              <?php if ($active && $legislature == legislature_current()): ?>
                <?php if ($number > 0): ?>
                  <p>Le groupe le plus important est <a href="<?= base_url() ?>groupes/legislature-<?= $groupes[0]['legislature'] ?>/<?= mb_strtolower($groupes[0]['libelleAbrev']) ?>"><?= $groupes[0]['libelle'] ?></a>. Il compte <?= $groupes[0]['effectif'] ?> députés membres.</p>
                  <p>Cette page présente les <?= $number ?> groupes parlementaires en activité dans l'Assemblée nationale. Pour découvrir les groupes qui ne sont plus en activité, <a href="<?= base_url() ?>groupes/inactifs">cliquez ici</a>.</p>
                <?php endif; ?>
                <?php if ($number_in_groupes == 0): ?>
                  <p>Tous les députés sont "non-inscrits".</p>
                  <?php else: ?>
                  <p>Au total, <?= $number_in_groupes ?> députés sont membres ou apparentés à un groupe, tandis que <?= $number_unattached ?> sont "non-inscrits".</p>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($active === FALSE): ?>
                <p>Cette page présente les groupes de la <?= $legislature ?><sup>ème</sup> législature qui ne sont plus en activités. Ce sont des groupes qui ont été dissous depuis leur création.</p>
                <p>Pour découvrir les <?= $number_groupes_active ?> groupes politiques en activité à l'Assemblée nationale, <a href="<?= base_url() ?>groupes">cliquez ici</a>.</p>
              <?php endif; ?>
              <?php if ($legislature < legislature_current()): ?>
                <p>Cette page présente les <?= $number ?> groupes politiques de la <?= $legislature ?><sup>ème</sup> législature.</p>
              <?php endif; ?>
            </div>
            <div class="col-md-3 offset-md-1">
              <h3><?= $legislature == legislature_current() ? 'Historique' : 'Toutes les législatures' ?></h3>
              <p>La législature actuelle est la 16<sup>ème</sup> législature. Elle a débuté en 2022, à la suite des <a href="<?= base_url() ?>elections/legislatives-2022">élections législatives</a>, et se terminera en 2027.</p>
              <?php if ($legislature == legislature_current()): ?>
                <p>Découvrez les groupes des législatures précédentes.</p>
              <?php else: ?>
                <p>Découvrez les groupes de toutes les législatures.</p>
              <?php endif; ?>
              <div class="d-flex flex-wrap">
                <?php if ($legislature != legislature_current()): ?>
                  <a href="<?= base_url() ?>groupes/legislature-16" class="btn btn-secondary my-2">16<sup>ème</sup> législature</a>
                <?php endif; ?>
                <a href="<?= base_url() ?>groupes/legislature-15" class="btn btn-secondary my-2">15<sup>ème</sup> législature</a>
                <a href="<?= base_url() ?>groupes/legislature-14" class="btn btn-secondary my-2">14<sup>ème</sup> législature</a>
              </div>
            </div>
          </div>
          <?php if ($number > 0): ?>
            <div class="row mt-5">
              <div class="col-12">
                <?php if ($active && $legislature == legislature_current()): ?>
                  <h2>Les <span class="text-primary"><?= $number ?> groupes</span> parlementaires de l'Assemblée nationale</h2>
                <?php endif; ?>
                <?php if ($active === FALSE): ?>
                  <?php if (count($groupes) > 1): ?>
                    <h2>Les <span class="text-primary"><?= count($groupes) ?> anciens groupes</span> parlementaires de la <?= $legislature ?><sup>ème</sup> législature</h2>
                    <?php else: ?>
                    <h2>Les <span class="text-primary">anciens groupes</span> parlementaires de la <?= $legislature ?><sup>ème</sup> législature</h2>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($legislature != legislature_current()): ?>
                  <h2>Les <span class="text-primary"><?= count($groupes) ?> groupes</span> parlementaires de la <?= $legislature ?><sup>ème</sup> législature</h2>
                <?php endif; ?>
              </div>
            </div>
            <div class="row mt-4">
              <?php foreach ($groupes as $groupe): ?>
                <div class="col-lg-4 col-md-6 py-3">
                  <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupe, 'tag' => 'h3', 'cat' => false)) ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="alert alert-danger mt-5" role="alert">
              Il y a actuellement aucun groupe politique pour cette législature.
            </div>
          <?php endif; ?>
          <div class="row mt-5">
            <div class="col-12 d-flex justify-content-center">
              <a href="<?= base_url() ?>groupes/legislature-<?= $legislature ?>/ni" class="btn btn-primary">Députés non-inscrits</a>
            </div>
          </div>
        </div>
      </div>
    </div>
