    <div class="container-fluid pg-groupe-all mb-5" id="container-always-fluid">
      <div class="row">
        <div class="container">
          <div class="row row-grid bloc-titre">
            <div class="col-lg-8">
              <h1><?= $title ?></h1>
            </div>
            <div class="col-lg-8 mt-4">
              <p>Les <b>groupes politiques</b> rassemblent des députés selon leur affinité politique. Un groupe doit être composé au minimum de 15 députés.</p>
              <?php if ($active): ?>
                <p>Les groupes jouent un rôle important dans l'Assemblée. Ils permettent de structurer le débat sur la base de lignes politiques (gauche, droite, centre). Les groupes reçoivent également des ressources politiques (temps de parole) et financières non négligeables.</p>
                <p>Cette page présente les <?= $number ?> groupes parlementaires en activité dans l'Assemblée nationale. Pour découvrir les <?= $number_groupes_inactive ?> groupes qui ne sont plus en activité, <a href="<?= base_url() ?>groupes/inactifs">cliquez ici</a>.</p>
                <p>Au total, <?= $number_in_groupes ?> députés sont membres ou apparentés à un groupe, tandis que <?= $number_unattached ?> sont "non-inscrits".</p>
              <?php endif; ?>
              <?php if ($active === FALSE): ?>
                <p>Cette page présente les <?= $number_groupes_inactive ?> groupes de la 15<sup>e</sup> qui ne sont plus en activités. Ce sont des groupes qui ont été dissous depuis leur création.</p>
                <p>Pour découvrir les <?= $number_groupes_active ?> groupes politiques en activité à l'Assemblée nationale, <a href="<?= base_url() ?>groupes">cliquez ici</a>.</p>
              <?php endif; ?>
              <div class="d-flex flex-wrap justify-content-around mt-5">
                <a href="<?= base_url() ?>deputes/inactifs" class="btn btn-primary my-2">Anciens groupes (15<sup>ème</sup> législature)</a>
                <a href="<?= base_url() ?>deputes/legislature-14" class="btn btn-primary my-2">Groupes de la 14<sup>ème</sup> législature</a>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <?php foreach ($groupes as $groupe): ?>
              <div class="col-lg-4 col-md-6 py-3">
                <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupe, 'tag' => 'h2', 'cat' => false)) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
