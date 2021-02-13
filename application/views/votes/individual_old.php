    <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
    </div>
    <div class="container pg-vote-individual">
      <div class="row row-grid mt-4">
        <div class="col-lg-8 col-md-8 col-12 bloc-infos">
          <div class="row">
            <div class="col-12">
              <?php if ($vote['title'] != "" & $vote['state'] == "published"): ?>
                <a href="<?= base_url() ?>votes/decryptes/<?= $vote['category_slug'] ?>" class="category d-block mb-2 no-decoration"><?= mb_strtoupper($vote['category']) ?></a>
                <h1><?= ucfirst($vote['title']) ?></h1>
                <span class="font-italic d-block"><?= ucfirst($vote['titre']) ?></span>
                <?php else: ?>
                <h1><?= ucfirst($vote['titre']) ?></h1>
              <?php endif; ?>
              <div class="mt-3">
                <span class="badge sort badge-<?= $vote['sortCode'] ?>"><?= mb_strtoupper($vote['sortCode']) ?></span>
              </div>
              <div class="mt-3">
                <p class="font-italic">Vote n° <?= $vote['voteNumero'] ?> - <?= $vote['date_edited'] ?></p>
              </div>
              <div class="mt-3">
                <p>
                  Le groupe de la <span>majorité présidentielle</span> (<a href="<?= base_url() ?>groupes/<?= mb_strtolower($vote_majoritaire['libelleAbrev']) ?>" target="_blank"><?= $vote_majoritaire['libelle'] ?></a>) a voté <span><?= $vote_majoritaire['positionMajoritaire'] ?></span> <?= $vote['type_edited_libelle'] ?>.
                </p>
              </div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12">
              <table class="table table-vote-info">
                <tbody>
                  <tr>
                    <td colspan="2" class="sub-row-line">
                      <h2 class="surtitre">INFOS</h2>
                    </td>
                  </tr>
                  <tr>
                    <td>Type de vote</td>
                    <td>
                      <?= ucfirst($vote['type_edited']) ?>
                      <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="<?= ucfirst($vote['type_edited']) ?>" data-content="<?= $vote['type_edited_explication'] ?>" id="popover_focus">
                        <i class="fas fa-question-circle">
                        </i>
                      </a>
                    </td>
                  </tr>
                  <?php if ($vote['dossier_titre'] != ""): ?>
                    <tr>
                      <td>Dossier</td>
                      <td><?= $vote['dossier_titre'] ?></td>
                    </tr>
                  <?php endif; ?>
                  <?php if ($vote['description'] != ""): ?>
                    <tr>
                      <td>Explication</td>
                      <td><?= $vote['description'] ?></td>
                    </tr>
                  <?php endif; ?>
                  <tr>
                    <td colspan="2" class="sub-row-padding sub-row-line">
                      <h2 class="surtitre">QU'ONT-ILS VOTÉ ?</h2>
                    </td>
                  </tr>
                  <tr>
                    <td><?= ucfirst($vote['sortCode']) ?> par :</td>
                    <td>
                      <div class="row">
                        <?php foreach ($groupes as $groupe): ?>
                          <?php if ($groupe['positionMajoritaire'] == 'pour' && $vote['sortCode'] == 'adopté'): ?>
                            <div class="col-lg-3 col-md-4 col-6 my-1">
                              <div class="card-vote-groupe">
                                <img class="img" src="<?= asset_url() ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" alt="Logo <?= $groupe['libelleAbrev'] ?>">
                              </div>
                            </div>
                          <?php elseif ($groupe['positionMajoritaire'] == 'contre' && $vote['sortCode'] == 'rejeté'): ?>
                            <div class="col-lg-3 col-md-4 col-6 my-1">
                              <div class="card-vote-groupe">
                                <img class="img" src="<?= asset_url() ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" alt="Logo <?= $groupe['libelleAbrev'] ?>">
                              </div>
                            </div>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Opposition</td>
                    <td>
                      <div class="row">
                        <?php foreach ($groupes as $groupe): ?>
                          <?php if ($groupe['positionMajoritaire'] == 'contre' && $vote['sortCode'] == 'adopté'): ?>
                            <div class="col-lg-3 col-md-4 col-6 my-1">
                              <div class="card-vote-groupe">
                                <img class="img" src="<?= asset_url() ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" alt="Logo <?= $groupe['libelleAbrev'] ?>">
                              </div>
                            </div>
                          <?php elseif ($groupe['positionMajoritaire'] == 'pour' && $vote['sortCode'] == 'rejeté'): ?>
                            <div class="col-lg-3 col-md-4 col-6 my-1">
                              <div class="card-vote-groupe">
                                <img class="img" src="<?= asset_url() ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" alt="Logo <?= $groupe['libelleAbrev'] ?>">
                              </div>
                            </div>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-10 offset-lg-1 offset-md-0 offset-1 bloc-results">
          <div class="card card-pie">
            <div class="card-header">
              <h2 class="title text-center">RÉSULTATS</h2>
            </div>
            <div class="card-body">
              <canvas id="pie-chart" width="100%" height="auto"></canvas>
              <table class="table table-vote-info">
                <tbody>
                  <tr>
                    <td>Nombre de votants</td>
                    <td><?= $vote['nombreVotants'] ?></td>
                  </tr>
                  <tr>
                    <td>Majorité requise</td>
                    <td><?= $vote['nbrSuffragesRequis'] ?></td>
                  </tr>
                  <tr>
                    <td>Pour</td>
                    <td class="pour"><?= $vote['pour'] ?></td>
                  </tr>
                  <tr>
                    <td>Abstention</td>
                    <td class="abstention"><?= $vote['abstention'] ?></td>
                  </tr>
                  <tr>
                    <td>Contre</td>
                    <td class="contre"><?= $vote['contre'] ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4 bloc-table">
        <div class="col-12">
          <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
              <a class="nav-item nav-link active no-decoration" id="nav-groupes-tab" data-toggle="tab" href="#nav-groupes" role="tab" aria-controls="nav-groupes" aria-selected="true">
                <h3>Groupes</h3>
              </a>
              <a class="nav-item nav-link no-decoration" id="nav-deputes-tab" data-toggle="tab" href="#nav-deputes" role="tab" aria-controls="nav-deputes" aria-selected="false">
                <h3>Députés</h3>
              </a>
            </div>
          </nav>
          <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-groupes" role="tabpanel" aria-labelledby="nav-groupes-tab">
              <table class="table table-striped table-vote-individual" id="table-vote-individual-groupes" style="width: 100%">
                <thead>
                  <tr>
                    <th class="all">Groupe</th>
                    <th class="text-center all">Position</th>
                    <th class="text-center min-tablet">Pour</th>
                    <th class="text-center min-tablet">Abstention</th>
                    <th class="text-center min-tablet">Contre</th>
                    <th class="text-center min-tablet">Participation</th>
                    <th class="text-center min-tablet">
                      Cohésion
                      <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Taux de cohésion" data-content="Le taux de cohésion représente <b>l'unité d'un groupe politique</b> lorsqu'il vote. Il peut prendre des mesures allant de 0 à 1. Un taux proche de 1 signifie que le groupe est très uni.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les mesures de cohésion des autres groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques#cohesion' target='_blank'>cliquez ici</a>." id="popover_focus"><?php echo file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                    </th>
                    <th class="pl-4"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($groupes as $v_groupe): ?>
                    <tr>
                      <td><a href="<?= base_url() ?>groupes/<?= mb_strtolower($v_groupe['libelleAbrev']) ?>" target="_blank" class="no-decoration"><?= $v_groupe['libelle'] ?> (<?= $v_groupe['libelleAbrev'] ?>)</a></td>
                      <td class="d-flex justify-content-center align-items-center">
                        <?php if ($v_groupe['positionMajoritaire'] != "nv"): ?>
                          <img src="<?= asset_url() ?>imgs/thumbs/<?= $v_groupe['positionMajoritaire'] ?>.png" alt="Vote <?= $v_groupe['positionMajoritaire'] ?>">
                          <?php else: ?>
                          Non votant
                        <?php endif; ?>
                      </td>
                      <td class="text-center"><?= $v_groupe['nombrePours'] ?></td>
                      <td class="text-center"><?= $v_groupe['nombreAbstentions'] ?></td>
                      <td class="text-center"><?= $v_groupe['nombreContres'] ?></td>
                      <td class="text-center"><?= $v_groupe['percentageVotants'] ?> %</td>
                      <td class="text-center"><?= $v_groupe['cohesion'] ?></td>
                      <td></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="tab-pane fade pt-3" id="nav-deputes" role="tabpanel" aria-labelledby="nav-deputes-tab">
              <table class="table table-striped table-vote-individual" id="table-vote-individual-deputes" style="width: 100%">
                <thead>
                  <th class="all">Député</th>
                  <th class="text-center min-tablet">Groupe</th>
                  <th class="text-center all">Vote</th>
                  <th class="text-center all">Loyauté</th>
                </thead>
                <tbody>
                  <?php foreach ($deputes as $v_depute): ?>
                    <tr>
                      <td><a href="<?= base_url() ?>deputes/<?= $v_depute['dpt_slug'] ?>/depute_<?= $v_depute['nameUrl'] ?>" target="_blank" class="no-decoration"><?= $v_depute['nameFirst'].' '.$v_depute['nameLast'] ?></a></td>
                      <td class="text-center"><?= $v_depute['libelle'] ?> (<?= $v_depute['libelleAbrev'] ?>)</td>
                      <td class="d-flex justify-content-center align-items-center">
                        <img src="<?= asset_url() ?>imgs/thumbs/<?= $v_depute['vote_libelle'] ?>.png" alt="">
                      </td>
                      <td class="text-center sort-<?= $v_depute['loyaute_libelle'] ?>"><?= mb_strtoupper($v_depute['loyaute_libelle']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid pg-vote-individual mt-5" id="container-always-fluid">
      <div class="row votes-next d-flex flex-md-row flex-column align-items-center justify-content-center py-4">
        <?php if ($vote_previous != FALSE): ?>
          <a class="btn" href="<?= base_url() ?>votes/vote_<?= $vote_previous ?>" role="button">
            <?php echo file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?> Vote précédent</a>
        <?php endif; ?>
        <a class="btn" href="<?= base_url() ?>votes" role="button">Tous les votes</a>
        <?php if ($vote_next != FALSE): ?>
          <a class="btn" href="<?= base_url() ?>votes/vote_<?= $vote_next ?>" role="button">Vote suivant <?php echo file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="container pg-vote-individual">
      <div class="row votes-decryptes">
        <div class="col-12 py-5">
          <h2>Les derniers votes décryptés par Datan</h2>
        </div>
      </div>
      <div class="row bloc-carousel-votes-flickity">
        <div class="col-12 carousel-cards">
          <?php foreach ($votes_datan as $vote_datan): ?>
            <div class="card card-vote">
              <div class="thumb d-flex align-items-center <?= $vote['sortCode'] ?>">
                <div class="d-flex align-items-center">
                  <span><?= mb_strtoupper($vote['sortCode']) ?></span>
                </div>
              </div>
              <div class="card-header d-flex flex-row justify-content-between">
                <span class="date"><?= $vote_datan['dateScrutinFR'] ?></span>
              </div>
              <div class="card-body d-flex align-items-center">
                <span class="title">
                  <a href="<?= base_url() ?>votes/vote_<?= $vote_datan['voteNumero'] ?>" class="stretched-link"></a>
                  <?= $vote_datan['voteTitre'] ?></span>
              </div>
              <div class="card-footer">
                <span class="field badge badge-primary py-1 px-2"><?= $vote_datan['category_libelle'] ?></span>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="card card-vote see-all">
            <div class="card-body d-flex align-items-center justify-content-center">
              <a href="<?= base_url() ?>votes/decryptes" class="stretched-link no-decoration">VOIR TOUS</a>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4 mb-5"> <!-- BUTTONS BELOW -->
        <div class="col-12 d-flex justify-content-center">
          <div class="bloc-carousel-votes">
            <div class="carousel-buttons">
              <button type="button" class="btn prev mr-2 button--previous">
                <?php echo file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
              </button>
              <a class="btn all mx-2" href="<?= base_url() ?>votes/decryptes">
                <span>VOIR TOUS</span>
              </a>
              <button type="button" class="btn next ml-2 button--next">
                <?php echo file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript">
      new Chart(document.getElementById("pie-chart"), {
          type: 'pie',
          data: {
            labels: ["Pour", "Abstention", "Contre"],
            datasets: [{
              label: "Population (millions)",
              backgroundColor: ["#00B794", "#FFBA49","#C5283D"],
              data: [<?= $vote['pour'] ?>, <?= $vote['abstention'] ?>, <?= $vote['contre'] ?>]
            }]
          },
          options: {
            title: {
              display: false,
              text: ''
            }
          }
      });

    </script>
