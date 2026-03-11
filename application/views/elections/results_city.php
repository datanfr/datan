<div class="container pg-resultats-ville">
  <div class="row mt-5">
    <div class="col-lg-10">
      <h1><?= $title ?></h1>
      <p class="mt-4">Découvrez tous les candidats et les résultats des élections municipales 2026 pour <?= $ville['commune_nom'] ?> (<?= $ville_infos['dep_code'] ?>). Le premier tour des élections municipales se tiendra le 15 mars 2026 et le second tour le 22 mars 2026.</p>
      <div class="alert alert-primary mt-4">
        Le premier tour des élections municipales se tiendra le dimanche 15 mars 2026. Les résultats seront diffusés le lendemain sur Datan.
      </div>
      <div class="mt-4 mb-0">
        <?php $this->view('departement/partials/electionFeature.php', array('election' => $deputes, 'city_info' => $ville_infos, 'title' => 'Candidature de députés', 'link' => FALSE)) ?>
      </div>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-lg-8">
      <h2>Listes candidates aux municipales 2026 à <?= $ville['commune_nom'] ?></h2>
      <?php if($isPLM): ?>
        <ul class="nav nav-tabs mt-4" id="scrutinTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active px-3" id="municipal-tab" data-toggle="tab" href="#municipal" role="tab">
              Scrutin municipal
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-3" id="arrondissement-tab" data-toggle="tab" href="#arrondissement" role="tab">
              Arrondissements
            </a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="municipal" role="tabpanel">
            <?php $this->view('elections/partials/_lists_accordion.php', array('arrondissements' => FALSE)) ?>
          </div>
          <div class="tab-pane fade" id="arrondissement" role="tabpanel">
            <p class="text-muted mt-4">À Paris, Lyon et Marseille, chaque électeur dispose de deux bulletins le jour du vote : un pour élire les conseillers de son arrondissement (ou secteur) et un autre pour élire les conseillers municipaux à l’échelle de toute la ville.</p>
            <?php if(!empty($arrondissements)): ?>
              <div class="dropdown mt-3">
                <?php $firstLabel = array_keys($arrondissements)[0]; ?>
                <button type="button" id="arrondissementDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span id="arrondissementDropdownLabel"><?= htmlspecialchars($firstLabel) ?></span>
                    <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                  </svg>
                </button>
                <div class="dropdown-menu shadow-sm" style="max-height: 300px; overflow-y: auto;" aria-labelledby="arrondissementDropdown">
                    <?php foreach($arrondissements as $arrLabel => $lists): ?>
                        <a class="dropdown-item arrondissement-select-item rounded" href="#"
                            data-arr="<?= htmlspecialchars($arrLabel) ?>">
                            <?= $arrLabel ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
              <div id="arrondissementsContent" class="mt-4">
                <?php $first = true; ?>
                <?php foreach($arrondissements as $arrLabel => $lists): ?>
                  <div class="arrondissement-block" data-arr="<?= htmlspecialchars($arrLabel) ?>" <?php if(!$first) echo 'style="display:none"'; ?> >
                    <?php
                      $this->view('elections/partials/_lists_accordion.php', array('listes' => $lists, 'arrondissements' => TRUE));
                    ?>
                  </div>
                  <?php $first = false; ?>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="mt-3">Aucun arrondissement disponible.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <?php $this->view('elections/partials/_lists_accordion.php', array('arrondissements' => FALSE)) ?>
      <?php endif; ?>
      <div class="mt-5 h2">Résultats des élections précédentes à <?= $ville['commune_nom'] ?></div>
      
      <!-- Previous Elections Results -->
      <?php if (!empty($previous_elections)): ?>
        <div class="mt-4" id="previousElectionsAccordion">
          <?php foreach ($previous_elections as $election): ?>
            <div class="liste-election mb-3">
              <div class="d-flex header align-items-center px-4 py-3"
                   data-toggle="collapse"
                   data-target="#collapse<?= $election['election_id'] ?>"
                   aria-controls="collapse<?= $election['election_id'] ?>">
                <div class="flex-grow-1">
                  <div class="title"><?= $election['title'] ?></div>
                </div>
                <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
            </svg>
              </div>
              <div id="collapse<?= $election['election_id'] ?>" class="collapse" data-parent="#previousElectionsAccordion">
                <div class="liste-candidates p-4 previous-election-body">
                  <?php if ($election['has_second_round']): ?>
                    <div class="previous-election-round-switch mb-4" data-election-id="<?= htmlspecialchars($election['election_id']) ?>">
                      <?php foreach ($election_rounds as $key => $round): ?>
                        <button
                          type="button"
                          class="previous-election-round-btn <?= $key === 1 ? 'active' : '' ?>"
                          data-election-id="<?= htmlspecialchars($election['election_id']) ?>"
                          data-round="<?= htmlspecialchars((string) $key) ?>"
                          aria-pressed="<?= $key === 1 ? 'true' : 'false' ?>">
                          <?= $round['title'] ?>
                        </button>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php foreach ($election_rounds as $key => $round): ?>
                    <?php 
                      $roundInfos = $election[$round['data']]['infos'];
                      $roundResults =   $election[$round['data']]['results'];
                    ?>
                    <div
                      class="previous-election-round-content"
                      data-election-id="<?= htmlspecialchars($election['election_id']) ?>"
                      data-round="<?= (string) $key ?>"
                      <?php if ((string) $key !== (string) "1") echo 'style="display:none"'; ?>>
                      <div class="election-stats d-flex mb-3 pb-3">
                        <div class="election-stat">
                          <span class="election-stat-label">Nombre de votants</span>
                          <span class="election-stat-value"><?= formatNumber($roundInfos['votants'] ?? 0) ?></span>
                        </div>
                        <div class="election-stat-divider mx-3"></div>
                        <div class="election-stat">
                          <span class="election-stat-label">Taux d'abstention</span>
                          <span class="election-stat-value"><?= number_format($roundInfos['abstention_pct'] ?? 0, 2, ',', ' ') ?>%</span>
                        </div>
                        <div class="election-stat-divider mx-3"></div>
                        <div class="election-stat">
                          <span class="election-stat-label">Blancs et nuls</span>
                          <span class="election-stat-value"><?= formatNumber($roundInfos['blancs_nuls'] ?? 0) ?></span>
                        </div>
                      </div>
                      <?php if (!empty($roundResults)): ?>
                        <?php foreach ($roundResults as $candidate): ?>
                          <div class="candidate-row d-flex justify-content-between align-items-center py-2">
                            <div>
                              <div class="font-weight-bold"><?= $candidate['prenom'] ?> <?= $candidate['nom'] ?></div>
                              <?php if (!empty($candidate['nuance'])): ?>
                                <small class="text-muted"><?= $candidate['nuance'] ?></small>
                              <?php endif; ?>
                            </div>
                            <div class="text-right ml-3">
                              <div class="font-weight-bold">
                                <?= number_format($candidate['voix_pct'] ?? 0, 2, ',', ' ') ?>%
                              </div>
                              <small class="text-muted">
                                <?= formatNumber($candidate['voix_total'] ?? 0) ?> vote<?= ($candidate['voix_total'] ?? 0) > 1 ? 's' : '' ?>
                              </small>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <p class="text-muted mb-0">Aucun résultat disponible pour ce tour.</p>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-info mt-4">
          <p class="mb-0">Aucun résultat d'élection précédente disponible pour cette commune.</p>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-lg-4">
      <div class="row mt-5 mt-lg-0">
        <div class="col-lg-12 col-md-6">
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
              <?php if($ville['population'] > url_obf_cities()): ?>
                <a href="<?= base_url() ?>deputes/<?= $ville['dpt_slug'] ?>/ville_<?= $ville['commune_slug'] ?>" class="city-item d-flex justify-content-between align-items-center mt-4 no-decoration border-primary text-primary font-weight-bold">
                  <span>Voir la page commune</span>
                  <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
                </a>
              <?php else: ?>
                <a url_obf="<?= url_obfuscation(base_url() . "deputes/" . $ville['dpt_slug'] . "/ville_" . $ville['commune_slug']) ?>" class="city-item d-flex justify-content-between align-items-center mt-4 no-decoration border-primary text-primary font-weight-bold url_obf">
                  <span>Voir la page commune</span>
                  <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-12 col-md-6">
          <div class="card card-nearby border mt-lg-4 mt-md-0 mt-4">            
            <div class="card-body py-3">
              <div class="title">Communes voisines</div>
              <div class="d-flex flex-column mt-3">
                <?php foreach($adjacentes as $city): ?>
                  <a role="button" url_obf="<?= url_obfuscation(base_url() . "elections/resultats/" . $city['slug'] . "/ville_" .  $city['commune_slug']) ?>" class="city-item d-flex justify-content-between align-items-center mb-3 url_obf">
                    <?= $city['commune_nom'] ?>
                    <?= file_get_contents(FCPATH . "assets/imgs/icons/bi-chevron-right.svg") ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php if (!empty($deputes_ville)): ?>
            <div class="card card-nearby mt-5 border">            
              <div class="card-body py-3">
                <div class="title"><?= count($deputes_ville) > 1 ? "Députés" : "Député" ?> à <?= $ville['commune_nom'] ?></div>
                <div class="d-flex flex-column mt-3">
                  <?php foreach($deputes_ville as $mp): ?>
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
<!-- OTHER CITIES FROM THE DEPARTMENT -->
<div class="container-fluid bloc-others-container">
  <div class="container bloc-others">
    <?php if (!in_array($ville['dpt'] , array('099', '975', '75'))): ?>
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
            <a class="membre no-decoration underline" href="<?= base_url() ?>elections/resultats/<?= $commune['slug'] ?>/ville_<?= $commune['commune_slug'] ?>" class="no-decoration underline-blue"><?= $commune['commune_nom'] ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var dropdown = document.getElementById('arrondissementDropdown');

    if (dropdown) {
      // Handle dropdown show/hide for chevron rotation
      $(dropdown).on('show.bs.dropdown', function() {
        this.setAttribute('aria-expanded', 'true');
      });

      $(dropdown).on('hide.bs.dropdown', function() {
        this.setAttribute('aria-expanded', 'false');
      });

      // Handle arrondissement selection
      var items = document.querySelectorAll('.arrondissement-select-item');
      items.forEach(function(item) {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          var selected = this.getAttribute('data-arr');
          document.getElementById('arrondissementDropdownLabel').textContent = selected;
          document.querySelectorAll('.arrondissement-block').forEach(function(block) {
            if (block.getAttribute('data-arr') === selected) {
              block.style.display = '';
            } else {
              block.style.display = 'none';
            }
          });
        });
      });
    }

    // Handle previous elections round switch (Premier tour / Second tour)
    var roundSwitchButtons = document.querySelectorAll('.previous-election-round-btn');
    roundSwitchButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        var electionId = this.getAttribute('data-election-id');
        var round = this.getAttribute('data-round');

        document
          .querySelectorAll('.previous-election-round-btn[data-election-id="' + electionId + '"]')
          .forEach(function(btn) {
            var isActive = btn.getAttribute('data-round') === round;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          });

        document
          .querySelectorAll('.previous-election-round-content[data-election-id="' + electionId + '"]')
          .forEach(function(content) {
            content.style.display = content.getAttribute('data-round') === round ? '' : 'none';
          });
      });
    });
  });
</script>