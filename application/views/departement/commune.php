    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
      <div class="container d-flex flex-column justify-content-center py-2 pg-commune">
        <div class="row title">
          <div class="col-12">
            <p class="city mb-0"><?= $ville['commune_nom'] ?></p>
            <?php if ($n_circos > 1): ?>
              <span class="department mb-0"><?= $ville['dpt_nom'] ?> (<?= $ville['dpt_edited'] ?>) -
                Circonscriptions :
                <?php foreach ($circos as $key => $circo): ?>
                  <?= $circo["number"] ?>
                  <sup><?= $circo["abbrev"] ?></sup>
                  <?php if ($key < count($circos) - 1): ?>
                    /
                  <?php endif; ?>
                <?php endforeach; ?>
              <?php else: ?>
              <span class="department mb-0"><?= $ville['dpt_nom'] ?> (<?= $ville['dpt_edited'] ?>) - <?= $ville['circo'] ?><sup><?= $ville['circo_abbrev'] ?></sup> circonscription</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="container pb-4 pg-commune">
      <!-- TITLE OF THE PAGE -->
      <div class="row my-5">
        <div class="col-12">
          <h1 class="text-center"><?= $title ?></h1>
        </div>
      </div> <!-- // END TITLE OF THE PAGE -->
      <!-- MPS FROM THE CITY -->
      <div class="row">
        <?php if ($n_circos == 1): ?>
          <div class="col-12">
            <div class="card card-depute">
              <div class="liseret" style="background-color: <?= $depute_commune["couleurAssociee"] ?>"></div>
              <div class="card-avatar">
                <picture>
                  <source srcset="<?= asset_url(); ?>imgs/deputes_webp/depute_<?= substr($depute_commune["mpId"], 2) ?>_webp.webp" alt="<?= $depute_commune['nameFirst'].' '.$depute_commune['nameLast'] ?>" type="image/webp">
                  <source srcset="<?= asset_url(); ?>imgs/deputes/depute_<?= substr($depute_commune["mpId"], 2) ?>.png" type="image/png">
                  <img src="<?= asset_url(); ?>imgs/deputes/depute_<?= substr($depute_commune["mpId"], 2) ?>.png" alt="<?= $depute_commune['nameFirst'].' '.$depute_commune['nameLast'] ?>">
                </picture>
              </div>
              <div class="card-body">
                <h2 class="card-title">
                  <a href="<?php echo base_url(); ?>deputes/<?php echo $depute_commune['dptSlug'].'/depute_'.$depute_commune['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute_commune['nameFirst'] .' ' . $depute_commune['nameLast'] ?></a>
                </h2>
                <span><?= $depute_commune["electionCirco"] ?><sup><?= $depute_commune['electionCircoAbbrev'] ?></sup> circonscription</span>
              </div>
              <div class="card-footer d-flex justify-content-center align-items-center">
                <span><?= $depute_commune["libelle"] ?></span>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="col-12 d-flex flex-wrap justify-content-around">
            <?php foreach ($deputes_commune as $depute_commune): ?>
              <div class="card card-depute mx-2">
                <div class="liseret" style="background-color: <?= $depute_commune["couleurAssociee"] ?>"></div>
                <div class="card-avatar">
                  <picture>
                    <source srcset="<?= asset_url(); ?>imgs/deputes_webp/depute_<?= substr($depute_commune["mpId"], 2) ?>_webp.webp" alt="<?= $depute_commune['nameFirst'].' '.$depute_commune['nameLast'] ?>" type="image/webp">
                    <source srcset="<?= asset_url(); ?>imgs/deputes/depute_<?= substr($depute_commune["mpId"], 2) ?>.png" type="image/png">
                    <img src="<?= asset_url(); ?>imgs/deputes/depute_<?= substr($depute_commune["mpId"], 2) ?>.png" alt="<?= $depute_commune['nameFirst'].' '.$depute_commune['nameLast'] ?>">
                  </picture>
                </div>
                <div class="card-body">
                  <h2 class="card-title">
                    <a href="<?php echo base_url(); ?>deputes/<?php echo $depute_commune['dptSlug'].'/depute_'.$depute_commune['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute_commune['nameFirst'] .' ' . $depute_commune['nameLast'] ?></a>
                  </h2>
                  <span><?= $depute_commune["electionCirco"] ?><sup><?= $depute_commune['electionCircoAbbrev'] ?></sup> circonscription</span>
                </div>
                <div class="card-footer d-flex justify-content-center align-items-center">
                  <span><?= $depute_commune["libelle"] ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div><!-- // END MPS FROM THE CITY -->
    </div>
    <!-- INFOS ON THE CITY -->
    <div class="container-fluid pg-commune py-5 my-5" id="pattern_background">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <h2 class="text-center">Quelques informations sur <?= $ville["commune_nom"] ?></h2>
          </div>
        </div>
        <div class="row mt-5">
          <div class="col-lg-4 d-flex align-items-center">
            <div>
              <p><b><?= $ville["commune_nom"] ?></b> est une commune située <?= $ville["libelle_1"] ?><?= $ville["dpt_nom"] ?> (<?= $ville["dpt_edited"] ?>). Elle se trouve dans la région <?= $ville['region_name'] ?>.</p>
              <?php if (!empty($ville["pop2007"])): ?>
                <p><?= $ville["commune_nom"] ?> compte environ <b><?= $ville["pop2017_format"] ?> habitants</b>. La population de la commune a <b><?= $ville['evol10_text'] ?></b> de <?= round($ville['evol10_edited'], 1) ?> % depuis 10 ans.</p>
              <?php endif; ?>
              <?php if (!empty($mayor["nameFirst"])): ?>
                <p><?= ucfirst($mayor["gender_le"]) ?> maire de <?= $ville["commune_nom"] ?> est <?= $mayor["nameFirst"]." ".ucfirst(mb_strtolower($mayor["nameLast"])) ?>.</p>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-lg-8 mt-5 mt-lg-0">
            <div class="row bloc-elections">
              <div class="col-md-6">
                <div class="card">
                  <div class="card-body pb-0">
                    <h3>Élection présidentielle 2017</h3>
                    <span class="round">2<sup>nd</sup> tour</span>
                    <div class="chart mt-3">
                      <div class="results d-flex flex-row justify-content-center align-items-center">
                        <div class="bar d-flex flex-row justify-content-center align-items-end">
                          <div class="element d-flex align-items-center justify-content-center" style="height: <?= round($results_2017_pres_2['macron_pct']) ?>%">
                            <span class="score"><?= round($results_2017_pres_2['macron_pct']) ?>%</span>
                          </div>
                        </div>
                        <div class="bar d-flex flex-row justify-content-center align-items-end">
                          <div class="element d-flex align-items-center justify-content-center" style="height: <?= round($results_2017_pres_2['lePen_pct']) ?>%">
                            <span class="score"><?= round($results_2017_pres_2['lePen_pct']) ?>%</span>
                          </div>
                        </div>
                      </div>
                      <div class="names d-flex flex-row justify-content-center align-items-center">
                        <div class="name">
                          <p class="text-center">Emmanuel Macron</p>
                        </div>
                        <div class="name">
                          <p class="text-center">Marine Le Pen</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <a href="https://www.interieur.gouv.fr/Elections/Les-resultats/Presidentielles/elecresult__presidentielle-2017/(path)/presidentielle-2017/<?= $ville_insee["region"] ?>/<?= $ville_insee["dpt"] ?>/<?= $ville_insee["insee"] ?>.html" target="_blank" rel="noopener" class="no-decoration">
                    <div class="card-footer">
                      <p class="text-center mb-0">Plus d'infos</p>
                    </div>
                  </a>
                </div>
              </div>
              <div class="col-md-6 mt-5 mt-md-0">
                <div class="card">
                  <div class="card-body pb-0">
                    <h3>Élections européennes 2019</h3>
                    <span class="round">3 premiers partis politiques</span>
                    <div class="chart mt-3">
                      <div class="results d-flex flex-row justify-content-center align-items-center">
                        <?php foreach ($results_2019_europe as $list): ?>
                          <div class="bar d-flex flex-row justify-content-center align-items-end">
                            <div class="element d-flex align-items-center justify-content-center" style="height: <?= round($list['value']) ?>%">
                              <span class="score" style="color: #fff"><?= round($list['value']) ?>%</span>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <div class="names d-flex flex-row justify-content-center align-items-center">
                        <?php foreach ($results_2019_europe as $list): ?>
                          <div class="name">
                            <p class="text-center"><?= $list["partiName"] ?></p>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <a href="https://www.interieur.gouv.fr/Elections/Les-resultats/Europeennes/elecresult__europeennes-2019/(path)/europeennes-2019/<?= $ville_insee["region"] ?>/<?= $ville_insee["dpt"] ?>/<?= $ville_insee["insee"] ?>.html" target="_blank" rel="noopener" class="no-decoration">
                    <div class="card-footer">
                      <p class="text-center mb-0">Plus d'infos</p>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- // END INFOS ON THE CITY -->
    <div class="container pg-commune">
      <!-- OTHER MPS FROM THE SAME DEPARTMENT -->
      <div class="row mt-4">
        <div class="col-12">
          <h2 class="text-center">Autres députés élus <?= $ville['libelle_1'] ?><?= $ville['dpt_nom'] ?> (<?= $ville['dpt_edited'] ?>)</h2>
        </div>
        <div class="col-12 py-4 d-flex flex-wrap justify-content-around">
          <?php foreach ($deputes_dpt as $depute): ?>
            <div class="card card-depute mx-2">
              <div class="liseret" style="background-color: <?= $depute["couleurAssociee"] ?>"></div>
              <div class="card-avatar">
                <picture>
                  <source srcset="<?= asset_url(); ?>imgs/deputes_webp/depute_<?= substr($depute["mpId"], 2) ?>_webp.webp" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>" type="image/webp">
                  <source srcset="<?= asset_url(); ?>imgs/deputes/depute_<?= substr($depute["mpId"], 2) ?>.png" type="image/png">
                  <img src="<?= asset_url(); ?>imgs/deputes/depute_<?= substr($depute["mpId"], 2) ?>.png" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>">
                </picture>
              </div>
              <div class="card-body">
                <h3 class="card-title">
                  <a href="<?php echo base_url(); ?>deputes/<?php echo $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
                </h3>
                <span><?= $depute["electionCirco"] ?><sup>e</sup> circonscription</span>
              </div>
              <div class="card-footer d-flex justify-content-center align-items-center">
                <span><?= $depute["libelle"] ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div> <!-- END OTHER MPS FROM THE SAME DEPARTMENT -->
    </div>
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
                <h2>Communes les plus peuplées <?= $ville['libelle_2'] ?><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>)</h2>
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
