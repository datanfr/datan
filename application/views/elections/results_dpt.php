<div class="container pg-resultats-dpt">
  <div class="row mt-5">
    <div class="col-lg-10">
      <h1><?= $title ?></h1>
      <p class="mt-4">Découvrez tous les candidats et les résultats des élections municipales 2026 <?= $dpt['libelle_1'] ?><?= $dpt['departement_nom'] ?> (<?= $dpt['region'] ?>). Le premier tour des élections municipales se tiendra le 15 mars 2026 et le second tour le 22 mars 2026.</p>
      <div class="alert alert-primary mt-4">
        Le premier tour des élections municipales se tiendra le dimanche 15 mars 2026. Les résultats seront diffusés le lendemain sur Datan.
      </div>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-lg-8">
      <h2>Les résultats des élections commune par commune</h2>
      <div class="letter-index mt-3">
        <?php foreach($communes as $letter => $commune_by_letter): ?>
          <a href="#letter-<?= $letter ?>" class="letter-anchor"><?= $letter ?></a>
        <?php endforeach; ?>
      </div>
      <div class="communes-list mt-4">
        <?php foreach($communes as $letter => $commune_by_letter): ?>
          <div class="letter-block anchor mb-5" id="letter-<?= $letter ?>">
            <div class="letter-heading"><?= $letter ?></div>
            <div class="row">
              <?php foreach($commune_by_letter as $commune): ?>
                <div class="col-6">
                  <?php if($commune['pop2017'] > $url_obf): ?>
                    <a role="button" href="<?= base_url() ?>elections/resultats/<?= $commune['slug'] ?>/<?= $commune['commune_slug'] ?>" class="city-item d-flex justify-content-between align-items-center mb-3">
                      <?= $commune['commune_nom'] ?>
                      <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
                    </a>
                  <?php else: ?>
                    <a role="button" url_obf="<?= url_obfuscation(base_url() . "elections/resultats/" . $commune['slug'] . "/" . $commune['commune_slug']) ?>" class="city-item d-flex justify-content-between align-items-center mb-3 url_obf">
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
    <div class="col-lg-4">
      <div class="row">
        <div class="col-lg-12 col-md-6">
          <?php if($big_communes): ?>
            <div class="card card-nearby border">            
              <div class="card-body py-3">
                <div class="title">Les plus grandes communes</div>
                <div class="d-flex flex-column mt-3">
                  <?php foreach($big_communes as $city): ?>
                    <a role="button" href="<?= base_url() . "elections/resultats/" . $city['slug'] . "/" .  $city['commune_slug'] ?>" class="city-item d-flex justify-content-between align-items-center mb-3 url_obf">
                      <?= $city['commune_nom'] ?>
                      <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-lg-12 col-md-6">
          <?php if($deputes): ?>
            <div class="card card-nearby border mt-lg-4 mt-md-0 mt-4">            
              <div class="card-body py-3">
                <div class="title">Députés candidats <?= $dpt['libelle_1'] ?><?= $dpt['departement_nom'] ?></div>
                <div class="d-flex flex-column mt-3">
                  <?php foreach($deputes as $mp): ?>
                    <a href="<?= base_url() ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>" class="city-item d-flex justify-content-between align-items-center mb-3 no-decoration">
                      <span>
                        <?= $mp['nameFirst'] ?> <?= $mp['nameLast'] ?> -
                        <span style="color: <?= $mp['couleurAssociee'] ?>;"><?= $mp['libelleAbrev'] ?></span>
                      </span>
                      <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>                  
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <?php if(!empty($mps_dpt)): ?>
            <div class="card card-nearby border mt-4">            
              <div class="card-body py-3">
                <div class="title">Députés <?= $dpt['libelle_1'] ?><?= $dpt['departement_nom'] ?></div>
                <div class="d-flex flex-column mt-3">
                  <?php foreach($mps_dpt as $mp): ?>
                    <a href="<?= base_url() ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>" class="city-item d-flex justify-content-between align-items-center mb-3 no-decoration">
                      <span>
                        <?= $mp['nameFirst'] ?> <?= $mp['nameLast'] ?> -
                        <span style="color: <?= $mp['couleurAssociee'] ?>;"><?= $mp['libelleAbrev'] ?></span>
                      </span>
                      <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>                  
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>      
    </div>
  </div>  
</div>
<?php $this->load->view('partials/follow-us.php') ?>