    <div class="container-fluid pg-groupe-all mb-5" id="container-always-fluid">
      <div class="row">
        <div class="container">
          <div class="row row-grid bloc-titre">
            <div class="col-lg-6 mb-4 mb-lg-0">
              <h1><?= $title ?></h1>
            </div>
            <div class="col-lg-5 offset-lg-1">
              <p>
                Les <b>groupes politiques</b> rassemblent des députés selon leur affinité politique. Un groupe doit être composé au minimum de 15 députés.
              </p>
              <?php if ($active): ?>
                <p>
                  Les groupes jouent un rôle important dans l'Assemblée. Ils permettent de structurer le débat sur la base de lignes politiques (gauche, droite, centre). Les groupes reçoivent également des ressources politiques (temps de parole) et financières non négligeables.
                </p>
              <?php endif; ?>
              <p>
                <?php if ($active == TRUE): ?>
                  Cette page présente les <?= $number ?> groupes parlementaires en activité dans l'Assemblée nationale. Pour découvrir les <?= $number_groupes_inactive ?> groupes qui ne sont plus en activité, <a href="<?= base_url() ?>groupes/inactifs">cliquez ici</a>.
                </p>
                <p>
                  Au total, <?= $number_in_groupes ?> députés sont membres ou apparentés à un groupe, tandis que <?= $number_unattached ?> sont "non-inscrits".
                  <?php else: ?>
                  Cette page présente les <?= $number_groupes_inactive ?> groupes de la 15<sup>e</sup> qui ne sont plus en activités. Ce sont des groupes qui ont été dissous depuis leur création.
                </p>
                <p>
                  Pour découvrir les <?= $number_groupes_active ?> groupes politiques en activité à l'Assemblée nationale, <a href="<?= base_url() ?>groupes">cliquez ici</a>.
                <?php endif; ?>
              </p>
            </div>
          </div>
          <div class="row mt-4">
            <?php foreach ($groupes as $groupe): ?>
              <div class="col-lg-4 col-md-6 py-3">
                <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupe, 'tag' => 'h2')) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="row d-flex justify-content-center mt-5 py-2">
        <?php if ($active == TRUE): ?>
          <a class="btn btn-outline-primary d-none d-md-block" href="<?= base_url() ?>groupes/inactifs">Liste des groupes <b>plus en activité</b></a>
          <?php else: ?>
          <a class="btn btn-outline-primary d-none d-md-block" href="<?= base_url() ?>groupes">Liste des groupes <b>en activité</b></a>
        <?php endif; ?>
      </div>
    </div>
