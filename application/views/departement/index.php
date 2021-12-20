    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="height: 13em">
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
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute, 'tag' => 'h3', 'cat' => true, 'logo' => true)) ?>
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
