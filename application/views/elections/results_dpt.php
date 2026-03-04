<div class="container pg-resultats-dpt">
  <div class="row mt-5">
    <div class="col-lg-10">
      <h1><?= $title ?></h1>
      <p class="mt-4">Découvrez tous les candidats et les résultats des élections municipales 2026 <?= $dpt['libelle_1'] ?><?= $dpt['departement_nom'] ?> (<?= $dpt['region'] ?>). Le premier tour des élections municipales se tiendra le 15 mars 2026 et le second tour le 22 mars 2026.</p>
      <div class="alert alert-primary mt-4">
        Le premier tour des élections municipales se tiendra le dimanche 15 mars 2026. Les résultats seront diffusés le lendemain sur Datan.
      </div>
      <h2 class="mt-5">Les résultats des élections commune par commune</h2>
      <!-- TOP CITIES -->
      <div class="d-flex justify-content-start flex-wrap mt-4">
        <?php foreach($big_communes as $commune): ?>
          <a href="<?= base_url() ?>elections/resultats/<?= url_election_paris($commune['slug'] . "/ville_" . $commune['commune_slug']) ?>" class="top-city-pill"><?= $commune['commune_nom'] ?></a>
        <?php endforeach; ?>
      </div>
      <div class="letter-index mt-4">
        <?php foreach($communes as $letter => $commune_by_letter): ?>
          <a href="#letter-<?= $letter ?>" class="letter-anchor"><?= $letter ?></a>
        <?php endforeach; ?>
      </div>
      <div class="communes-list mt-4">
        <?php foreach($communes as $letter => $commune_by_letter): ?>
          <div class="letter-block anchor" id="letter-<?= $letter ?>">
            <div class="letter-heading"><?= $letter ?></div>
            <div class="row">
              <?php foreach($commune_by_letter as $commune): ?>
                <div class="col-lg-4 col-6">
                  <?php if($commune['population'] > url_obf_cities_election() && !in_array($commune['commune_slug'], $big_slugs)): ?>
                    <a role="button" href="<?= base_url() ?>elections/resultats/<?= url_election_paris($commune['slug'] . "/ville_" . $commune['commune_slug']) ?>" class="city-item d-flex justify-content-between align-items-center mb-3">
                      <?= $commune['commune_nom'] ?>
                      <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
                    </a>
                  <?php else: ?>
                    <a role="button" url_obf="<?= url_obfuscation(base_url() . "elections/resultats/" . url_election_paris($commune['slug'] . "/ville_" . $commune['commune_slug'])) ?>" class="city-item d-flex justify-content-between align-items-center mb-3 url_obf">
                      <?= $commune['commune_nom'] ?>
                      <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
                    </a>
                  <?php endif; ?>  
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('partials/follow-us.php') ?>