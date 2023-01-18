    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="height: 13em">
      <div class="container d-flex flex-column justify-content-center py-2 pg-commune">
        <div class="row title">
          <div class="col-12">
            <p class="city mb-0 text-center"><?= $ville['commune_nom'] ?></p>
            <?php if ($n_circos > 1): ?>
              <p class="department text-center mb-0"><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>) -
                Circonscriptions :
                <?php foreach ($circos as $key => $circo): ?>
                  <?= $circo["number"] ?><sup><?= $circo["abbrev"] ?></sup>
                  <?php if ($key < count($circos) - 1): ?>
                    /
                  <?php endif; ?>
                <?php endforeach; ?>
                </p>
              <?php else: ?>
              <p class="department text-center mb-0"><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>) - <?= $ville['circo'] ?><sup><?= $ville['circo_abbrev'] ?></sup> circonscription</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php if ($adjacentes): ?>
      <div class="container-fluid pg-commune bloc-adjacentes py-2 d-flex flex-column flex-md-row justify-content-center align-items-center">
        <span class="my-0 mx-3 text-white">Communes voisines :</span>
        <div class="d-flex justify-content-around flex-wrap mt-1 mt-md-0">
          <?php $i = 1 ?>
          <?php foreach ($adjacentes as $adjacente): ?>
            <div class="<?= $i > 2 ? 'd-none d-md-flex' : 'd-flex' ?> justify-content-center">
              <a role="button" class="url_obf btn btn-outline-light btn-sm mx-1 my-1" url_obf="<?= url_obfuscation(base_url()."deputes/".$adjacente['slug']."/ville_".$adjacente['commune_slug']) ?>"><?= $adjacente['commune_nom'] ?></a>
            </div>
            <?php $i++ ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <div class="container pg-commune">
      <div class="row">
        <div class="col-lg-3 col-md-4 pt-md-5 pt-3 bloc-infos" style="background-color: #e9e9e9; color: black!important">
          <p><b><?= $ville["commune_nom"] ?></b> est une commune située <?= $ville["libelle_1"] ?><?= $ville["dpt_nom"] ?> (<?= $ville["dpt"] ?>), dans la région <?= $ville['region_name'] ?>.</p>
          <?php if (!empty($ville["pop2007"])): ?>
            <p><?= $ville["commune_nom"] ?> compte <b><?= $ville["pop2017_format"] ?> habitants</b>. La population de la commune a <b><?= $ville['evol10_text'] ?></b> de <?= round($ville['evol10_edited'], 1) ?> % depuis 10 ans.</p>
          <?php endif; ?>
          <?php if (!empty($mayor["nameFirst"])): ?>
            <p><?= ucfirst($mayor["gender_le"]) ?> maire de <?= $ville["commune_nom"] ?> est <b><?= $mayor["nameFirst"]." ".ucfirst(mb_strtolower($mayor["nameLast"])) ?></b>.</p>
          <?php endif; ?>
          <h2 class="mt-4"><?= $n_circos == 1 ? 'La' : 'Les' ?> circonscription<?= $n_circos == 1 ? '' : 's' ?> de <?= $ville['commune_nom'] ?></h2>
          <?php if ($n_circos == 1): ?>
            <p><?= $ville['commune_nom'] ?> fait partie de la <?= $ville['circo'] ?><sup><?= $ville['circo_abbrev'] ?></sup> circonscription électorale <?= $ville['libelle_2'] ?><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>).</p>
            <?php else: ?>
            <p><?= $ville['commune_nom'] ?> est une grande ville qui comprend plusieurs circonscriptions électorales.</p>
            <ul>
              <?php foreach ($circos as $key => $circo): ?>
                <li><?= $circo['number'] ?><sup><?= $circo['abbrev'] ?></sup> circonscription <?= $ville['libelle_2'] ?><?= $ville['dpt_nom'] ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <?php if ($n_circos == 1 && $noMP == FALSE): ?>
            <p>
              <?= ucfirst($gender['le']) ?> <?= $gender['depute'] ?> élu<?= $gender['e'] ?> dans cette circonscription est <?= $depute_commune['nameFirst'] ?> <?= $depute_commune['nameLast'] ?>.
            </p>
          <?php endif; ?>
        </div>
        <div class="col-lg-9 col-md-8 pt-5 pb-5">
          <h1 class="text-center"><?= $title ?></h1>
          <?php if ($noMP): ?>
            <div class="text-center alert alert-danger">
              Il n'y a actuellement aucun député représentant à l'Assemblée nationale les citoyens habitant dans cette ville.
            </div>
          <?php else: ?>
            <?php if ($n_circos == 1): ?>
              <div class="d-flex justify-content-center">
                <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_commune, 'tag' => 'h2', 'cat' => true, 'logo' => true)) ?>
              </div>
            <?php else: ?>
              <div class="d-flex flex-wrap justify-content-around">
                <?php foreach ($deputes_commune as $depute_commune): ?>
                  <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute_commune, 'tag' => 'h2', 'cat' => true, 'logo' => true)) ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- CONTACT YOUR MP -->
    <?php if (!$noMP): ?>
      <div class="container-fluid py-5 pg-commune bloc-contact">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <?php if ($n_circos == 1): ?>
              <h2 class="text-center text-white mb-4">Je contacte mon député</h2>
              <p class="text-center text-white mb-0">
                Pour contacter votre député <b><?= $depute_commune['nameFirst'] . ' ' .$depute_commune['nameLast'] ?></b>, nous vous conseillons de lui envoyer un email à son adresse :
                <a class="text-white" href="mailto:<?= mb_strtolower($depute_commune['mailAn']) ?>"><?= mb_strtolower($depute_commune['mailAn']) ?></a>
              </p>
            <?php else: ?>
              <h2 class="text-center text-white font-weight-bold mb-4">Je contacte un député</h2>
              <p class="text-center text-white">
                <?= $ville['commune_nom'] ?> est une grande ville et plusieurs députés y sont élus. Nous vous conseillons de contacter un des députés de <?= $ville['commune_nom'] ?> par email :
              </p>
              <?php foreach ($deputes_commune as $key => $value): ?>
                <p class="text-center text-white mb-1">
                  <?= $value['nameFirst'] . ' ' . $value['nameLast'] ?> :
                  <a class="text-white text-center" href="mailto:<?= mb_strtolower($value['mailAn']) ?>"><?= mb_strtolower($value['mailAn']) ?></a>
                </p>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <!-- ELECTIONS LEGISLATIVES IN THE CITY -->
    <?php if ($results_legislatives_last): ?>
      <div class="container-fluid pg-commune py-5">
        <div class="container">
          <div class="row mt-2">
            <div class="col-12">
              <h2 class="text-center">L' élection législative de 2022 à <?= $ville['commune_nom'] ?></h2>
            </div>
          </div>
          <div class="row mt-3">
            <div class="<?= $n_circos == 1 ? 'col-lg-7' : 'col-lg-8 offset-lg-2' ?> d-flex flex-column justify-content-center">
              <p class="text-primary font-weight-bold">Le contexte</p>
              <p>Les <a href="<?= base_url() ?>elections/legislatives-2022">dernières élections législatives</a> se sont tenues en juin 2022. Elles ont permis d'élire <a href="<?= base_url() ?>deputes">les députés</a> qui composent l'Assemblée nationale. Ces élections ont fait suite à l'élection présidentielle, qui a vu la victoire d'Emmanuel Macron.</p>
              <p>Les prochaines élections législatives se tiendront en 2027.</p>
              <p class="text-primary font-weight-bold">L'élection à <?= $ville['commune_nom'] ?></p>
              <?php if ($n_circos == 1): ?>
                <p>Pour le second tour des législatives, la ville de <?= $ville['commune_nom'] ?> comptait <?= number_format($results_legislatives_last_first['inscrits'], 0, ',', ' ') ?> personnes inscrites sur les listes électorales. Seuls <?= number_format($results_legislatives_last_first['votants'], 0, ',', ' ') ?> habitants se sont déplacés aux urnes. Le <b>taux d'abstention était de <?= round($results_legislatives_last_first['abs'] / $results_legislatives_last_first['inscrits'] * 100)  ?> %</b>, contre 54 % au niveau national.</p>
                <p>En tête, <?= $results_legislatives_last_first['prenom'] ?> <?= ucfirst(mb_strtolower($results_legislatives_last_first['nom'])) ?> a récolté <?= number_format($results_legislatives_last_first['voix'], 0, ',', ' ') ?> voix, soit <?= $results_legislatives_last_first['pct'] ?> % des votants.</p>
              <?php endif; ?>
              <?php if ($n_circos > 1): ?>
                <p><?= $ville['commune_nom'] ?> étant une ville de taille importante, elle compte plusieurs députés. Découvrez ci-dessous les parlementaires élus pour la ville de <?= $ville['commune_nom'] ?>.</p>
              <?php endif; ?>
            </div>
            <div class="<?= $n_circos == 1 ? 'col-lg-5 justify-content-center' : 'col-lg-12 justify-content-around' ?> mt-4 d-flex flex-wrap bloc-elections">
              <?php foreach ($results_legislatives_last as $key => $value): ?>
                <div class="card mx-1 my-4">
                  <div class="card-body pb-0">
                    <h3>Élection législative 2022</h3>
                    <span class="round"><?= reset($value)['circo'] ?><sup><?= abbrev_n(reset($value)['circo'], TRUE) ?></sup> circonscription - 2<sup>nd</sup> tour</span>
                    <div class="chart mt-3">
                      <div class="results d-flex flex-row justify-content-center align-items-center">
                        <?php foreach ($value as $candidate): ?>
                          <div class="bar d-flex flex-row justify-content-center align-items-end">
                            <div class="element d-flex align-items-center justify-content-center" style="height: <?= $candidate['pct'] ?>%">
                              <span class="score"><?= round($candidate['pct']) ?>%</span>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <div class="names d-flex flex-row justify-content-center align-items-center">
                        <?php foreach ($value as $candidate): ?>
                          <div class="name">
                            <p class="text-center"><?= $candidate['prenom'] . ' ' . ucfirst(mb_strtolower($candidate['nom'])) ?></p>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <span url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Legislatives/elecresult__legislatives-2022/(path)/legislatives-2017/".$ville['interieurGouv']['dpt']."/".$ville['interieurGouv']['dpt']."0".reset($value)['circo']."".$ville['interieurGouv']['commune'].".html") ?>" class="url_obf no-decoration">
                    <div class="card-footer">
                      <p class="text-center mb-0">Plus d'infos</p>
                    </div>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div> <!-- // END ELECTION LEGISLATIVE IN THE CITY -->
    <?php endif; ?>
    <!-- ALL ELECTIONS -->
    <div class="container-fluid pg-commune py-5" id="pattern_background">
      <div class="container">
        <div class="row mt-2">
          <div class="col-12">
            <h2 class="text-center">Les dernières élections à <?= $ville["commune_nom"] ?></h2>
          </div>
          <?php if (isset($results_pres_edited)): ?>
            <div class="col-lg-7 mt-4">
              <p class="text-primary font-weight-bold">Élection présidentielle 2022</p>
              <p><?= $results_pres_edited ?></p>
            </div>
          <?php endif; ?>
        </div>
        <div class="d-flex justify-content-around flex-wrap mt-4 bloc-elections">
          <?php if ($results_pres_2022[0]['votants'] > 0): ?>
            <div class="card mx-1 my-4">
              <div class="card-body pb-0">
                <h3>Élection présidentielle 2022</h3>
                <span class="round">2<sup>nd</sup> tour</span>
                <div class="chart mt-3">
                  <div class="results d-flex flex-row justify-content-center align-items-center">
                    <?php foreach ($results_pres_2022 as $key => $value): ?>
                      <div class="bar d-flex flex-row justify-content-center align-items-end">
                        <div class="element d-flex align-items-center justify-content-center" style="height: <?= round($value['share']) ?>%">
                          <span class="score"><?= round($value['share']) ?>%</span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="names d-flex flex-row justify-content-center align-items-center">
                    <?php foreach ($results_pres_2022 as $key => $value): ?>
                      <div class="name">
                        <p class="text-center"><?= $value['candidate'] ?></p>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
              <span url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Presidentielles/elecresult__presidentielle-2017/(path)/presidentielle-2017/".$ville['interieurGouv']['region']."/".$ville['interieurGouv']['dpt']."/".$ville['interieurGouv']['dpt']."".$ville['interieurGouv']['commune'].".html") ?>" class="url_obf no-decoration">
                <div class="card-footer">
                  <p class="text-center mb-0">Plus d'infos</p>
                </div>
              </span>
            </div>
          <?php endif; ?>
          <div class="card mx-1 my-4">
            <div class="card-body pb-0">
              <h3>Élections européennes 2019</h3>
              <span class="round">Les 3 premiers partis politiques</span>
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
            <span url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Europeennes/elecresult__europeennes-2019/(path)/europeennes-2019/".$ville['interieurGouv']['region']."/".$ville['interieurGouv']['dpt']."/".$ville['interieurGouv']['dpt']."".$ville['interieurGouv']['commune_europeennes'].".html") ?>" class="url_obf no-decoration">
              <div class="card-footer">
                <p class="text-center mb-0">Plus d'infos</p>
              </div>
            </span>
          </div>
          <?php if ($results_pres_2017[0]['votants'] > 0): ?>
            <div class="card mx-1 my-4">
              <div class="card-body pb-0">
                <h3>Élection présidentielle 2017</h3>
                <span class="round">2<sup>nd</sup> tour</span>
                <div class="chart mt-3">
                  <div class="results d-flex flex-row justify-content-center align-items-center">
                    <?php foreach ($results_pres_2017 as $key => $value): ?>
                      <div class="bar d-flex flex-row justify-content-center align-items-end">
                        <div class="element d-flex align-items-center justify-content-center" style="height: <?= round($value['share']) ?>%">
                          <span class="score"><?= round($value['share']) ?>%</span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="names d-flex flex-row justify-content-center align-items-center">
                    <?php foreach ($results_pres_2017 as $key => $value): ?>
                      <div class="name">
                        <p class="text-center"><?= $value['candidate'] ?></p>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
              <span url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Presidentielles/elecresult__presidentielle-2017/(path)/presidentielle-2017/".$ville['interieurGouv']['region']."/".$ville['interieurGouv']['dpt']."/".$ville['interieurGouv']['dpt']."".$ville['interieurGouv']['commune'].".html") ?>" class="url_obf no-decoration">
                <div class="card-footer">
                  <p class="text-center mb-0">Plus d'infos</p>
                </div>
              </span>
            </div>
          <?php endif; ?>
          <?php if ($results_leg_2017 && $n_circos == 1): ?>
            <div class="card mx-1 my-4">
              <div class="card-body pb-0">
                <h3>Élections législatives 2017</h3>
                <span class="round"><?= $results_leg_2017[0]['circo'] ?><sup>e</sup> circonscription - 2<sup>nd</sup> tour</span>
                <div class="chart mt-3">
                  <div class="results d-flex flex-row justify-content-center align-items-center">
                    <?php foreach ($results_leg_2017 as $candidate): ?>
                      <div class="bar d-flex flex-row justify-content-center align-items-end">
                        <div class="element d-flex align-items-center justify-content-center" style="height: <?= $candidate['pct'] ?>%">
                          <span class="score"><?= round($candidate['pct']) ?>%</span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="names d-flex flex-row justify-content-center align-items-center">
                    <?php foreach ($results_leg_2017 as $candidate): ?>
                      <div class="name">
                        <p class="text-center"><?= $candidate['prenom'] . ' ' . ucfirst(mb_strtolower($candidate['nom'])) ?></p>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
              <span url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Legislatives/elecresult__legislatives-2017/(path)/legislatives-2017/".$ville['interieurGouv']['dpt']."/".$ville['interieurGouv']['dpt']."0".$results_leg_2017[0]['circo']."".$ville['interieurGouv']['commune'].".html") ?>" class="url_obf no-decoration">
                <div class="card-footer">
                  <p class="text-center mb-0">Plus d'infos</p>
                </div>
              </span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div> <!-- // END ALL ELECTIONS -->
    <div class="container pg-commune mt-5">
      <!-- OTHER MPS FROM THE SAME DEPARTMENT -->
      <?php if (!empty($deputes_dpt)): ?>
        <div class="row mt-4">
          <div class="col-12">
            <h2 class="text-center">Autres députés élus <?= $ville['libelle_1'] ?><?= $ville['dpt_nom'] ?> (<?= $ville['dpt'] ?>)</h2>
          </div>
          <div class="col-12 py-4 d-flex flex-wrap justify-content-around">
            <?php foreach ($deputes_dpt as $depute): ?>
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute, 'tag' => 'h3', 'cat' => true, 'logo' => true)) ?>
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
