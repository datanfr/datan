    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
      <div class="container d-flex flex-column justify-content-center py-2">
        <h1><?= $title ?></h1>
      </div>
    </div>
    <div class="container pg-departement my-5">
      <div class="row">
        <div class="col-12">
          <h2>Découvez les députés <?= $departement['libelle_2'] ?><?= $departement['departement_nom'] ?> à l'Assemblée nationale</h2>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-12 d-flex flex-wrap justify-content-around">
          <?php foreach ($deputes as $depute): ?>
            <div class="card card-depute">
              <div class="liseret" style="background-color: <?= $depute["couleurAssociee"] ?>"></div>
              <div class="card-avatar">
                <img src="<?= base_url(); ?>assets/imgs/deputes/depute_<?= substr($depute["mpId"], 2) ?>.png" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>">
              </div>
              <div class="card-body">
                <h3 class="d-block card-title">
                  <a href="<?php echo base_url(); ?>deputes/<?php echo $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
                </h3>
                <span class="d-block"><?= $depute["departementNom"] ?> (<?= $depute["departementCode"] ?>)</span>
              </div>
              <div class="card-footer d-flex justify-content-center align-items-center">
                <span><?= $depute["libelle"] ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- OTHER CITIES FROM THE DEPARTMENT -->
    <div class="container-fluid bloc-others-container">
      <div class="container bloc-others">
        <?php if ($departement['departement_code'] != '099' && $departement['departement_code'] != '975'): ?>
          <div class="row">
            <div class="col-12">
              <?php if ($departement['departement_nom'] == "Nouvelle-Calédonie" || $departement['departement_nom'] == "Polynésie française"): ?>
                <h2>Communes <?= $departement['libelle_2'] ?><?= $departement['departement_nom'] ?> (<?= $departement['departement_code'] ?>)</h2>
              <?php elseif ($departement['departement_nom'] == "Wallis-et-Futuna"): ?>
                <h2>Commune <?= $departement['libelle_2'] ?><?= $departement['departement_nom'] ?> (<?= $departement['departement_code'] ?>)</h2>
              <?php else: ?>
                <h2>Communes les plus peuplées <?= $departement['libelle_2'] ?><?= $departement['departement_nom'] ?> (<?= $departement['departement_code'] ?>)</h2>
              <?php endif; ?>
            </div>
          </div>
          <div class="row">
            <?php foreach ($communes as $commune): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url() ?>deputes/<?= $commune['slug'] ?>/ville_<?= $commune['commune_slug'] ?>" class="no-decoration underline-blue"><?= $commune['commune_nom'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
