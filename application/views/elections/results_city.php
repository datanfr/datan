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
      <?php
        $municipalesRounds = isset($municipales_ministry_rounds) && is_array($municipales_ministry_rounds) ? $municipales_ministry_rounds : array();
        $hasMunicipalesRoundData = false;
        foreach ($municipalesRounds as $roundData) {
          if (!empty($roundData['results']) || !empty($roundData['infos']) || !empty($roundData['listes'])) {
            $hasMunicipalesRoundData = true;
            break;
          }
        }
        $municipalesDefaultRound = $municipales_ministry_default_round ?? 't1';
        $showMunicipalesRoundToggle = !empty($municipales_ministry_show_round_toggle);
        $municipalesListFusionsDebug = isset($municipales_list_fusions_debug) && is_array($municipales_list_fusions_debug)
          ? $municipales_list_fusions_debug
          : array();
      ?>
      <?php if ($hasMunicipalesRoundData): ?>
        <?php
          $summaryRound = $municipalesRounds['t1'] ?? reset($municipalesRounds);
          $summaryRoundKey = $summaryRound['key'] ?? '';
          $summaryResults = $summaryRound['results'] ?? array();
          $summaryInfos = $summaryRound['infos'] ?? array();
          $summaryQualifiedLeaders = $summaryRound['qualified_leaders'] ?? array();
          $summaryQualifiedLeadersText = $summaryRound['qualified_leaders_text'] ?? '';
          $summaryQualifiedLeadersDisplay = '';

          if (!empty($summaryQualifiedLeaders)) {
            $summaryLeaderParts = array();
            foreach ($summaryQualifiedLeaders as $leader) {
              $leaderName = trim((string) ($leader['name'] ?? ''));
              if ($leaderName === '') {
                continue;
              }

              $leaderNuance = trim((string) ($leader['nuance'] ?? ''));
              $part = '<b>' . htmlspecialchars($leaderName) . '</b>';
              if ($leaderNuance !== '') {
                $part .= ' (' . htmlspecialchars($leaderNuance) . ')';
              }
              $summaryLeaderParts[] = $part;
            }

            $summaryLeadersCount = count($summaryLeaderParts);
            if ($summaryLeadersCount === 1) {
              $summaryQualifiedLeadersDisplay = $summaryLeaderParts[0];
            } elseif ($summaryLeadersCount === 2) {
              $summaryQualifiedLeadersDisplay = $summaryLeaderParts[0] . ' et ' . $summaryLeaderParts[1];
            } elseif ($summaryLeadersCount > 2) {
              $lastSummaryLeader = array_pop($summaryLeaderParts);
              $summaryQualifiedLeadersDisplay = implode(', ', $summaryLeaderParts) . ' et ' . $lastSummaryLeader;
            }
          }

          if ($summaryQualifiedLeadersDisplay === '' && $summaryQualifiedLeadersText !== '') {
            $summaryQualifiedLeadersDisplay = '<b>' . htmlspecialchars($summaryQualifiedLeadersText) . '</b>';
          }
        ?>
        <?php if (!empty($summaryResults)): ?>
          <?php if ($summaryRoundKey === 't1' && ($summaryInfos['pourvu'] ?? null) === 'T1'): ?>
            <?php $winningCandidate = $summaryResults[0]; ?>
            <div class="card border-primary my-4">
              <div class="card-body py-3">
                La liste dirigée par <b><?= trim(($winningCandidate['prenom'] ?? '') . ' ' . ($winningCandidate['nom'] ?? '')) ?></b> a remporté les élections municipales 2026 <?= $ville_infos['nom_a'] ?> avec <u><?= number_format($winningCandidate['voix_pct'], 2, ',', ' ') ?>%</u> des voix.
              </div>
            </div>
          <?php elseif ($summaryRoundKey === 't1'): ?>
            <div class="card border-primary my-4">
              <div class="card-body py-3">
                Un second tour sera organisé à <?= $ville['commune_nom'] ?> le 22 mars.
                <?php if ($summaryQualifiedLeadersDisplay !== ''): ?>
                  Les listes portées par <?= $summaryQualifiedLeadersDisplay ?> sont qualifiées.
                <?php endif; ?>
                <div class="mt-3">
                  <div class="font-weight-bold mb-1">Fusions</div>
                  <?php if (!empty($municipalesListFusionsDebug)): ?>
                    <?php foreach ($municipalesListFusionsDebug as $fusion): ?>
                      <p class="mb-1">
                        La liste menée par <b><?= htmlspecialchars($fusion['head_name']) ?></b><?php if (!empty($fusion['first_round_nuance'])): ?> (<?= htmlspecialchars($fusion['first_round_nuance']) ?>)<?php endif; ?> a fusionné avec la liste menée par <b><?= htmlspecialchars($fusion['second_round_head']) ?></b><?php if (!empty($fusion['second_round_nuance'])): ?> (<?= htmlspecialchars($fusion['second_round_nuance']) ?>)<?php endif; ?>.
                      </p>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="mb-0 text-muted">Aucune fusion de listes dans cette commune.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php else: ?>
            <?php $winningCandidate = $summaryResults[0]; ?>
            <div class="card border-primary mt-4">
              <div class="card-body py-3">
                Au second tour, la liste dirigée par <b><?= trim(($winningCandidate['prenom'] ?? '') . ' ' . ($winningCandidate['nom'] ?? '')) ?></b> est arrivée en tête à <?= $ville['commune_nom'] ?> avec <u><?= number_format($winningCandidate['voix_pct'], 2, ',', ' ') ?>%</u> des voix.
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <div class="alert alert-light border mt-3 mb-3">
          <strong>DEBUG fusions listes (T1 &gt; 5% vers T2)</strong>
          <pre class="mb-0 mt-2"><?php print_r($municipalesListFusionsDebug); ?></pre>
        </div>

        <?php if ($showMunicipalesRoundToggle): ?>
          <div class="previous-election-round-switch" data-municipales-switch="true">
            <?php foreach ($municipalesRounds as $round): ?>
              <button
                type="button"
                class="previous-election-round-btn municipales-round-btn <?= (($round['key'] ?? '') === $municipalesDefaultRound) ? 'active' : '' ?>"
                data-round="<?= htmlspecialchars($round['key'] ?? '') ?>"
                aria-pressed="<?= (($round['key'] ?? '') === $municipalesDefaultRound) ? 'true' : 'false' ?>">
                <?= htmlspecialchars($round['title'] ?? '') ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php foreach ($municipalesRounds as $round): ?>
          <?php
            $roundKey = $round['key'] ?? '';
            $roundDisplayMode = $round['display_mode'] ?? 'results';
            $roundResults = $round['results'] ?? array();
            $roundInfos = $round['infos'] ?? array();
            $roundListes = $round['listes'] ?? array();
          ?>
          <div
            class="municipales-round-content"
            data-round="<?= htmlspecialchars($roundKey) ?>"
            <?= $roundKey !== $municipalesDefaultRound ? 'style="display:none"' : '' ?>>
            <div class="card border mt-3">
              <div class="card-body py-3">
                <div class="h4 font-weight-bold mt-3 mb-3 title"><?= $roundDisplayMode === 'listes' ? 'Listes candidates au ' : 'Résultats du ' ?><?= mb_strtolower($round['title'] ?? '') ?> <?= $ville_infos['nom_a'] ?></div>
                <?php if ($roundDisplayMode === 'results' && !empty($roundInfos)): ?>
                  <div class="election-stats d-flex mb-3 pb-3">
                    <div class="election-stat">
                      <span class="election-stat-label">Inscrits</span>
                      <span class="election-stat-value"><?= formatNumber($roundInfos['inscrits'] ?? 0) ?></span>
                    </div>
                    <div class="election-stat-divider mx-3"></div>
                    <div class="election-stat">
                      <span class="election-stat-label">Votants</span>
                      <span class="election-stat-value"><?= formatNumber($roundInfos['votants'] ?? 0) ?></span>
                    </div>
                    <div class="election-stat-divider mx-3"></div>
                    <div class="election-stat">
                      <span class="election-stat-label">Abstention</span>
                      <span class="election-stat-value"><?= number_format($roundInfos['abstention_pct'] ?? 0, 2, ',', ' ') ?>%</span>
                    </div>
                    <div class="election-stat-divider mx-3"></div>
                    <div class="election-stat">
                      <span class="election-stat-label">Blancs et nuls</span>
                      <span class="election-stat-value"><?= formatNumber($roundInfos['blancs_nuls'] ?? 0) ?></span>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($roundDisplayMode === 'results' && !empty($roundResults)): ?>
                  <?php foreach ($roundResults as $candidate): ?>
                    <?php $score_pct = max(0, min(100, (float)($candidate['voix_pct'] ?? 0))); ?>

                    <div class="candidate-row d-flex flex-wrap align-items-start py-2">
                      <div class="col-8 order-1 col-lg-6 order-lg-1 px-0 mb-1 mb-lg-0" style="min-width: 0;">
                        <div class="d-flex align-items-center">
                          <div class="partie-dot mr-3 flex-shrink-0" style="background-color: <?= $candidate['nuance_color'] ?>;"></div>
                          <div>
                            <div class="font-weight-bold">
                              <?= trim(($candidate['prenom'] ?? '') . ' ' . ($candidate['nom'] ?? '')) ?>
                              <?php if ($roundKey === 't1' && ($roundInfos['pourvu'] ?? null) === 'T1' && !empty($candidate['seats'])): ?>
                                <span class="badge badge-primary ml-1"><?= (int) $candidate['seats'] ?> sièges</span>
                              <?php elseif ($roundKey === 't1' && ($roundInfos['pourvu'] ?? null) !== 'T1' && (int) ($candidate['qualified'] ?? 0) === 1): ?>
                                <span class="badge badge-primary ml-1">Qualifiée</span>
                              <?php endif; ?>
                            </div>
                            <?php if (!empty($candidate['nuance'])): ?>
                              <div style="font-weight: 600"><?= $candidate['nuance_edited'] ?></div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>

                      <div class="col-4 order-2 col-lg-2 order-lg-3 px-0 text-right mb-1 mb-md-0">
                        <div class="font-weight-bold">
                          <?= number_format($candidate['voix_pct'] ?? 0, 2, ',', ' ') ?>%
                        </div>
                        <small class="text-muted">
                          <?= formatNumber($candidate['voix'] ?? 0) ?> vote<?= ($candidate['voix'] ?? 0) > 1 ? 's' : '' ?>
                        </small>
                      </div>

                      <div class="col-12 order-3 col-lg-4 px-0 order-lg-2 ml-lg-0 px-lg-5 align-self-lg-center">
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
                <?php elseif ($roundDisplayMode === 'listes' && !empty($roundListes)): ?>
                  <div class="mt-4" id="municipalesRoundListesAccordion-<?= htmlspecialchars($roundKey) ?>">
                    <?php foreach ($roundListes as $liste): ?>
                      <?php
                        $headOfList = $liste['tete_de_liste'] ?? '';
                        if ($headOfList === '' && !empty($liste['candidats'][0])) {
                          $headCandidate = $liste['candidats'][0];
                          $headOfList = trim(($headCandidate['prenom'] ?? '') . ' ' . ($headCandidate['nom'] ?? ''));
                        }
                        $collapseId = 'municipales-round-' . ($roundKey !== '' ? $roundKey : 'round') . '-liste-' . (int) ($liste['numero_panneau'] ?? 0);
                      ?>
                      <div class="liste-card mb-3">
                        <div class="liste-header d-flex align-items-center px-4 py-3" data-toggle="collapse" data-target="#<?= htmlspecialchars($collapseId) ?>" aria-controls="<?= htmlspecialchars($collapseId) ?>" aria-expanded="false">
                          <div class="partie-dot mr-3" style="background-color: <?= $liste['nuance_color'] ?>;"></div>
                          <div class="flex-grow-1">
                            <div class="liste-tete"><?= htmlspecialchars($headOfList) ?></div>
                            <div class="liste-meta">
                              <?php if (!empty($liste['nuance_edited'])): ?>
                                <span class="nuance"><?= htmlspecialchars($liste['nuance_edited']) ?></span>
                                <span class="liste-separator">·</span>
                              <?php endif; ?>
                              <span><?= htmlspecialchars($liste['libelle_liste']) ?></span>
                            </div>
                          </div>
                          <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                          </svg>
                        </div>
                        <div id="<?= htmlspecialchars($collapseId) ?>" class="collapse">
                          <div class="liste-candidates p-4">
                            <?php foreach (($liste['candidats'] ?? array()) as $candidat): ?>
                              <div class="candidate-row py-2">
                                <?= (int) ($candidat['ordre'] ?? 0) ?>. <?= htmlspecialchars($candidat['prenom'] ?? '') ?> <?= htmlspecialchars($candidat['nom'] ?? '') ?>
                                <?php if (($candidat['code_personnalite'] ?? '') === 'DEP'): ?>
                                  <span class="badge badge-primary ml-2">Député<?= ($candidat['sexe'] ?? '') === 'F' ? 'e' : '' ?></span>
                                <?php endif; ?>
                                <?php if (($candidat['code_personnalite'] ?? '') === 'SEN'): ?>
                                  <span class="badge badge-primary ml-2"><?= ($candidat['sexe'] ?? '') === 'F' ? 'Sénatrice' : 'Sénateur' ?></span>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p class="text-muted mb-0"><?= $roundDisplayMode === 'listes' ? 'Aucune liste disponible.' : 'Aucun résultat disponible.' ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-info mt-4">
          Les bureaux de votes ferment entre 18h et 20h selon les communes. Les premiers résultats seront communiqués une fois qu'ils seront mis en ligne par le Ministère de l'Intérieur.
        </div>
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
                                <span class="election-stat-label">Votants</span>
                                <span class="election-stat-value"><?= formatNumber($round['infos']['votants'] ?? 0) ?></span>
                              </div>
                              <div class="election-stat-divider mx-3"></div>
                              <div class="election-stat">
                                <span class="election-stat-label">Abstention</span>
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

    // Handle Municipales 2026 round switch (Premier tour / Second tour)
    var municipalesRoundSwitchButtons = document.querySelectorAll('.municipales-round-btn');
    municipalesRoundSwitchButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        var round = this.getAttribute('data-round');
        if (!round) {
          return;
        }

        document.querySelectorAll('.municipales-round-btn').forEach(function(btn) {
          var isActive = btn.getAttribute('data-round') === round;
          btn.classList.toggle('active', isActive);
          btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        document.querySelectorAll('.municipales-round-content').forEach(function(content) {
          content.style.display = content.getAttribute('data-round') === round ? '' : 'none';
        });

      });
    });
  });
</script>