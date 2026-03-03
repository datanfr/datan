<div class="container pg-resultats-ville">
  <div class="row my-5">
    <div class="col-12">
      <h1><?= $title ?></h1>
    </div>
  </div>
  <div class="row my-5">
    <div class="col-8">
      <p class="mb-0">Découvrez tous les candidats et les résultats des élections municipales 2026 pour <?= $ville['commune_nom'] ?> (<?= $ville_infos['dep_code'] ?>). Le premier tour des élections municipales se tiendra le 15 mars 2026 et le second tour le 22 mars 2026.</p>
      <div class="alert alert-primary mt-4 mb-0" role="alert">
        Le premier tour des élections municipales se tiendra le dimanche 15 mars 2026. Les résultats seront diffusés le lendemain sur Datan.
      </div>
      <div class="mt-4 mb-0">
        <?php $this->view('departement/partials/electionFeature.php', array('election' => $deputes, 'city_info' => $ville_infos, 'title' => 'Candidature de députés', 'link' => FALSE)) ?>
      </div>
      <h2 class="mt-5">Listes candidates aux municipales à <?= $ville['commune_nom'] ?></h2>
      <?php if($isPLM): ?>
        <ul class="nav nav-tabs mt-4" id="scrutinTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="municipal-tab" data-toggle="tab" href="#municipal" role="tab">
              Scrutin municipal
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="arrondissement-tab" data-toggle="tab" href="#arrondissement" role="tab">
              Scrutins par arrondissement
            </a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="municipal" role="tabpanel">
            <?php $this->view('elections/partials/_lists_accordion.php') ?>
          </div>
          <div class="tab-pane fade" id="arrondissement" role="tabpanel">
            <p class="text-muted mt-4">Scrutins par arrondissement à venir.</p>
          </div>
        </div>
      <?php else: ?>
        <?php $this->view('elections/partials/_lists_accordion.php') ?>
      <?php endif; ?>
    </div>
    <div class="col-4">
      <div class="card card-info border">            
        <div class="card-body py-3">
          <div class="title"><?= $ville['commune_nom'] ?></div>
          <div>
            <span class="badge badge-primary"><?= $ville_infos['dep_nom'] ?> - <?= $ville_infos['dep_code'] ?></span>
          </div>
          <div class="label text-uppercase mt-4">👥 Population</div>
          <div class="value"><?= formatNumber($ville_infos['population']) ?></div>
          <?php if (!empty($mayor["nameFirst"])): ?>
            <div class="label text-uppercase mt-3">🏛️ Maire</div>
            <div class="value"><?= $mayor["nameFirst"]." ".ucfirst(mb_strtolower($mayor["nameLast"])) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="card card-nearby mt-5 border">            
        <div class="card-body py-3">
          <div class="title">Communes voisines</div>
          <div class="d-flex flex-column mt-3">
            <?php foreach($adjacentes as $city): ?>
              <a role="button" url_obf="<?= url_obfuscation(base_url() . "elections/resultats/" . $city['slug'] . "/" .  $city['commune_slug']) ?>" class="city-item d-flex justify-content-between align-items-center mb-3 url_obf">
                <?= $city['commune_nom'] ?>
                <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>  
</div>
<?php $this->load->view('partials/follow-us.php') ?>
<!-- OTHER CITIES FROM THE DEPARTMENT -->
<div class="container-fluid bloc-others-container">
  <div class="container bloc-others">
    <?php if ($ville['dpt'] != '099' && $ville['dpt'] != '975'): ?>
      <div class="row">
        <div class="col-12">
          <?php if ($ville['dpt_nom'] == "Nouvelle-Calédonie" || $ville['dpt_nom'] == "Polynésie française"): ?>
            <h2>Communes <?= $ville['libelle_2'] ?><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>)</h2>
          <?php elseif ($ville['dpt_nom'] == "Wallis-et-Futuna"): ?>
            <h2>Commune <?= $ville['libelle_2'] ?><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>)</h2>
          <?php else: ?>
            <h2>Communes les plus peuplées <?= $ville['libelle_2'] ?><?= $ville['dpt_nom'] ?> (<?= strtoupper($ville['dpt'])  ?>)</h2>
          <?php endif; ?>
        </div>
      </div>
      <div class="row">
        <?php foreach ($communes_dpt as $commune): ?>
          <div class="col-6 col-md-3 py-2">
            <a class="membre no-decoration underline" href="<?= base_url() ?>deputes/<?= $commune['slug'] ?>/ville_<?= $commune['commune_slug'] ?>" class="no-decoration underline-blue"><?= $commune['commune_nom'] ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>