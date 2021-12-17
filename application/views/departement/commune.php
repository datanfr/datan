    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="height: 13em">
      <div class="container d-flex flex-column justify-content-center py-2 pg-commune">
        <div class="row title">
          <div class="col-12">
            <p class="city mb-0"><?= $ville['commune_nom'] ?></p>
            <?php if ($n_circos > 1): ?>
              <span class="department mb-0"><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>) -
                Circonscriptions :
                <?php foreach ($circos as $key => $circo): ?>
                  <?= $circo["number"] ?>
                  <sup><?= $circo["abbrev"] ?></sup>
                  <?php if ($key < count($circos) - 1): ?>
                    /
                  <?php endif; ?>
                <?php endforeach; ?>
              <?php else: ?>
              <span class="department mb-0"><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>) - <?= $ville['circo'] ?><sup><?= $ville['circo_abbrev'] ?></sup> circonscription</span>
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
        <?php if ($noMP): ?>
          <div class="col-12">
            <div class="text-center alert alert-danger">
              Il n'y a actuellement aucun député représentant à l'Assemblée nationale les citoyens habitant dans cette ville.
            </div>
          </div>
          <?php else: ?>
            <?php if ($n_circos == 1): ?>
              <div class="col-12 d-flex justify-content-center">
                <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_commune, 'tag' => 'h2', 'cat' => true)) ?>
              </div>
            <?php else: ?>
              <div class="col-12 d-flex flex-wrap justify-content-around">
                <?php foreach ($deputes_commune as $depute_commune): ?>
                  <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_commune, 'tag' => 'h2', 'cat' => true)) ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
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
              <p><b><?= $ville["commune_nom"] ?></b> est une commune située <?= $ville["libelle_1"] ?><?= $ville["dpt_nom"] ?> (<?= $ville["dpt"] ?>). Elle se trouve dans la région <?= $ville['region_name'] ?>.</p>
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
                  <a href="https://www.interieur.gouv.fr/Elections/Les-resultats/Presidentielles/elecresult__presidentielle-2017/(path)/presidentielle-2017/<?= $ville_insee["region"] ?>/<?= $ville_insee["dpt"] ?>/<?= $ville_insee["old_insee"] ?>.html" target="_blank" rel="noopener" class="no-decoration">
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
      <?php if (!empty($deputes_dpt)): ?>
        <div class="row mt-4">
          <div class="col-12">
            <h2 class="text-center">Autres députés élus <?= $ville['libelle_1'] ?><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>)</h2>
          </div>
          <div class="col-12 py-4 d-flex flex-wrap justify-content-around">
            <?php foreach ($deputes_dpt as $depute): ?>
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute, 'tag' => 'h3', 'cat' => true)) ?>
            <?php endforeach; ?>
          </div>
        </div> <!-- END OTHER MPS FROM THE SAME DEPARTMENT -->
      <?php endif; ?>
    </div>
    <!-- BLOC FOLLOW-US -->
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
