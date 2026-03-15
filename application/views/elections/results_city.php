<div class="container pg-resultats-ville">
  <div class="row mt-5">
    <div class="col-lg-10">
      <h1><?= $title ?></h1>
      <p class="mt-4">Découvrez tous les candidats et les résultats des élections municipales 2026 pour <?= $ville['commune_nom'] ?> (<?= $ville_infos['dep_code'] ?>). Le premier tour des élections municipales se tiendra le 15 mars 2026 et le second tour le 22 mars 2026.</p>
      <div class="mt-4 mb-0">
        <?php $this->view('departement/partials/electionFeature.php', array('election' => $deputes, 'city_info' => $ville_infos, 'title' => 'Candidature de députés', 'link' => FALSE)) ?>
      </div>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-lg-8">
      <h2>Résultats aux municipales 2026 à <?= $ville['commune_nom'] ?></h2>
      <?php if (!empty($municipales_ministry_results)): ?>
        <div class="card border mt-4">
          <div class="card-body py-3">
            <?php foreach ($municipales_ministry_results as $candidate): ?>
              <?php $score_pct = max(0, min(100, (float)($candidate['voix_pct'] ?? 0))); ?>

              <div class="candidate-row d-flex flex-wrap align-items-start py-2">
                <div class="col-8 order-1 col-lg-4 order-lg-1 px-0 mb-1 mb-lg-0" style="min-width: 0;">
                  <div class="font-weight-bold"><?= trim(($candidate['prenom'] ?? '') . ' ' . ($candidate['nom'] ?? '')) ?></div>
                  <?php if (!empty($candidate['nuance'])): ?>
                    <small class="text-muted"><?= $candidate['nuance'] ?></small>
                  <?php endif; ?>
                </div>

                <div class="col-4 order-2 col-lg-3 order-lg-3 px-0 ml-auto text-right mb-1 mb-md-0">
                  <div class="font-weight-bold">
                    <?= number_format($candidate['voix_pct'] ?? 0, 2, ',', ' ') ?>%
                  </div>
                  <small class="text-muted">
                    <?= formatNumber($candidate['voix'] ?? 0) ?> vote<?= ($candidate['voix'] ?? 0) > 1 ? 's' : '' ?>
                  </small>
                </div>

                <div class="col-12 order-3 col-lg-5 px-0 order-lg-2 ml-lg-0 px-lg-5 align-self-lg-center">
                  <div class="progress" style="height: 8px; background-color: #e9ecef">
                    <div
                      class="progress-bar bg-primary"
                      role="progressbar"
                      style="width: <?= number_format($score_pct, 2, '.', '') ?>%;"
                      aria-valuenow="<?= number_format($score_pct, 2, '.', '') ?>"
                      aria-valuemin="0"
                      aria-valuemax="100"></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-info mt-4">
          Les bureaux de votes ferment entre 18h et 20h selon les communes. Les premiers résultats seront communiqués une fois qu'ils seront mis en ligne par le Ministère de l'Intérieur.
        </div>
      <?php endif; ?>

      <h2 class="mt-5">Listes candidates aux municipales 2026 à <?= $ville['commune_nom'] ?></h2>
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
                <button type="button" id="arrondissementDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span id="arrondissementDropdownLabel"><?= htmlspecialchars($arrondissements_first_label) ?></span>
                    <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                  </svg>
                </button>
                <div class="dropdown-menu shadow-sm" style="max-height: 300px; overflow-y: auto;" aria-labelledby="arrondissementDropdown">
                    <?php foreach($arrondissements_view as $arrondissement): ?>
                        <a class="dropdown-item arrondissement-select-item rounded" href="#"
                            data-arr="<?= htmlspecialchars($arrondissement['label']) ?>">
                            <?= $arrondissement['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
              <div id="arrondissementsContent" class="mt-4">
                <?php foreach($arrondissements_view as $arrondissement): ?>
                  <div class="arrondissement-block" data-arr="<?= htmlspecialchars($arrondissement['label']) ?>" <?= $arrondissement['label'] !== $arrondissements_first_label ? 'style="display:none"' : '' ?> >
                    <?php
                      $this->view('elections/partials/_lists_accordion.php', array('listes' => $arrondissement['lists'], 'arrondissements' => TRUE));
                    ?>
                  </div>
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
      <?php $this->view('partials/campaign.php', array('wrapper_classes' => array('mt-5'))) ?>
      <div class="mt-5 h2">Résultats des élections précédentes à <?= $ville['commune_nom'] ?></div>

      <!-- Previous Elections Results -->
      <?php if (!empty($previous_elections_ui)): ?>
        <div class="mt-4" id="previousElectionsAccordion">
          <?php foreach ($previous_elections_ui as $election): ?>
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
                  <?php if ($election['has_multiple_circos']): ?>
                    <div class="previous-election-circo-switch mb-3" data-election-id="<?= htmlspecialchars($election['election_id']) ?>">
                      <?php foreach ($election['circos_ui'] as $circo): ?>
                        <button
                          type="button"
                          class="previous-election-circo-btn <?= $circo['is_default'] ? 'active' : '' ?>"
                          data-election-id="<?= htmlspecialchars($election['election_id']) ?>"
                          data-circo-id="<?= htmlspecialchars($circo['id']) ?>"
                          aria-pressed="<?= $circo['is_default'] ? 'true' : 'false' ?>">
                          <?= htmlspecialchars($circo['label']) ?>
                        </button>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php foreach ($election['circos_ui'] as $circo): ?>
                    <div
                      class="previous-election-circo-content"
                      data-election-id="<?= htmlspecialchars($election['election_id']) ?>"
                      data-circo-id="<?= htmlspecialchars($circo['id']) ?>"
                      <?= !$circo['is_default'] ? 'style="display:none"' : '' ?>>
                      <?php if ($election['has_second_round']): ?>
                        <div class="previous-election-round-switch mb-4" data-election-id="<?= htmlspecialchars($election['election_id']) ?>" data-circo-id="<?= htmlspecialchars($circo['id']) ?>">
                          <?php foreach ($circo['rounds'] as $round): ?>
                            <button
                              type="button"
                              class="previous-election-round-btn <?= $round['is_default'] ? 'active' : '' ?>"
                              data-election-id="<?= htmlspecialchars($election['election_id']) ?>"
                              data-circo-id="<?= htmlspecialchars($circo['id']) ?>"
                              data-round="<?= htmlspecialchars($round['key']) ?>"
                              aria-pressed="<?= $round['is_default'] ? 'true' : 'false' ?>">
                              <?= $round['title'] ?>
                            </button>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>

                      <?php foreach ($circo['rounds'] as $round): ?>
                        <div
                          class="previous-election-round-content"
                          data-election-id="<?= htmlspecialchars($election['election_id']) ?>"
                          data-circo-id="<?= htmlspecialchars($circo['id']) ?>"
                          data-round="<?= $round['key'] ?>"
                          <?= !$round['is_default'] ? 'style="display:none"' : '' ?>>
                          <?php if ($round['show_no_second_round_alert']): ?>
                            <div class="alert alert-info mb-0">
                              Il n'y a pas de second tour car l'élection a été remportée dès le premier tour.
                            </div>
                          <?php else: ?>
                            <div class="election-stats d-flex mb-3 pb-3">
                              <div class="election-stat">
                                <span class="election-stat-label">Nombre de votants</span>
                                <span class="election-stat-value"><?= formatNumber($round['infos']['votants'] ?? 0) ?></span>
                              </div>
                              <div class="election-stat-divider mx-3"></div>
                              <div class="election-stat">
                                <span class="election-stat-label">Taux d'abstention</span>
                                <span class="election-stat-value"><?= number_format($round['infos']['abstention_pct'] ?? 0, 2, ',', ' ') ?>%</span>
                              </div>
                              <div class="election-stat-divider mx-3"></div>
                              <div class="election-stat">
                                <span class="election-stat-label">Blancs et nuls</span>
                                <span class="election-stat-value"><?= formatNumber($round['infos']['blancs_nuls'] ?? 0) ?></span>
                              </div>
                            </div>
                            <?php if (!empty($round['results'])): ?>
                              <?php foreach ($round['results'] as $candidate): ?>
                                <?php $score_pct = max(0, min(100, (float)($candidate['voix_pct'] ?? 0))); ?>
                                
                                <div class="candidate-row d-flex flex-wrap align-items-start py-2">
  
                                  <!-- Candidate name + nuance -->
                                  <div class="col-8 order-1 col-lg-4 order-lg-1 px-0 mb-1 mb-lg-0" style="min-width: 0;">
                                    <div class="font-weight-bold"><?= $candidate['prenom'] ?> <?= $candidate['nom'] ?></div>
                                    <?php if (!empty($candidate['nuance'])): ?>
                                      <small class="text-muted"><?= $candidate['nuance'] ?></small>
                                    <?php endif; ?>
                                  </div>

                                  <!-- Score (mobile: right of candidate / desktop: far right) -->
                                  <div class="col-4 order-2 col-lg-3 order-lg-3 px-0 ml-auto text-right mb-1 mb-md-0">
                                    <div class="font-weight-bold">
                                      <?= number_format($candidate['voix_pct'] ?? 0, 2, ',', ' ') ?>%
                                    </div>
                                    <small class="text-muted">
                                      <?= formatNumber($candidate['voix_total'] ?? 0) ?> vote<?= ($candidate['voix_total'] ?? 0) > 1 ? 's' : '' ?>
                                    </small>
                                  </div>

                                  <!-- Progress bar -->
                                  <div class="col-12 order-3 col-lg-5 px-0 order-lg-2 ml-lg-0 px-lg-5 align-self-lg-center">
                                    <div class="progress" style="height: 8px; background-color: #e9ecef">
                                      <div
                                        class="progress-bar bg-primary"
                                        role="progressbar"
                                        style="width: <?= number_format($score_pct, 2, '.', '') ?>%;"
                                        aria-valuenow="<?= number_format($score_pct, 2, '.', '') ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                    </div>
                                  </div>

                                </div>
                                
                              <?php endforeach; ?>
                            <?php else: ?>
                              <p class="text-muted mb-0">Aucun résultat disponible pour ce tour.</p>
                            <?php endif; ?>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
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
              <?php if($ville_infos['population']): ?>
                <div class="label text-uppercase mt-4">👥 Population</div>
                <div class="value"><?= formatNumber($ville_infos['population']) ?></div>
              <?php endif; ?>
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
          <?php if($adjacentes): ?>
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
          <?php endif; ?>
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

    // Handle circo switch when a city has multiple circonscriptions
    var circoSwitchButtons = document.querySelectorAll('.previous-election-circo-btn');
    circoSwitchButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        var electionId = this.getAttribute('data-election-id');
        var circoId = this.getAttribute('data-circo-id');

        document
          .querySelectorAll('.previous-election-circo-btn[data-election-id="' + electionId + '"]')
          .forEach(function(btn) {
            var isActive = btn.getAttribute('data-circo-id') === circoId;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          });

        document
          .querySelectorAll('.previous-election-circo-content[data-election-id="' + electionId + '"]')
          .forEach(function(content) {
            content.style.display = content.getAttribute('data-circo-id') === circoId ? '' : 'none';
          });
      });
    });

    // Handle previous elections round switch (Premier tour / Second tour)
    var roundSwitchButtons = document.querySelectorAll('.previous-election-round-btn');
    roundSwitchButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        var electionId = this.getAttribute('data-election-id');
        var circoId = this.getAttribute('data-circo-id');
        var round = this.getAttribute('data-round');

        if (!round) {
          return;
        }

        document
          .querySelectorAll('.previous-election-round-btn[data-election-id="' + electionId + '"][data-circo-id="' + circoId + '"]')
          .forEach(function(btn) {
            var isActive = btn.getAttribute('data-round') === round;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          });

        document
          .querySelectorAll('.previous-election-round-content[data-election-id="' + electionId + '"][data-circo-id="' + circoId + '"]')
          .forEach(function(content) {
            content.style.display = content.getAttribute('data-round') === round ? '' : 'none';
          });
      });
    });
  });
</script>