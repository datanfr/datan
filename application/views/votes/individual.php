    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="position: relative; height: 155px">
      <?php if ($vote['edited']): ?>
        <div class="container pg-vote-individual d-flex align-items-end" style="height: 100%">
          <?php if ($vote['logo']): ?>
            <div class="logo-category mr-3">
              <?= file_get_contents(asset_url()."imgs/fields_white/".$vote['category_slug'].".svg") ?>
            </div>
          <?php endif; ?>
          <span class="category"><?= mb_strtoupper($vote['category']) ?></span>
        </div>
      <?php endif; ?>
    </div>
    <div class="container pg-vote-individual">
      <div class="row row-grid mt-4">
        <div class="col-lg-8 col-md-8 col-12 bloc-infos">
          <div class="row">
            <div class="col-12">
              <?php if ($vote['title'] != "" & $vote['state'] == "published"): ?>
                <h1><?= ucfirst($vote['title']) ?></h1>
                <span class="font-italic d-block"><?= ucfirst($vote['titre']) ?></span>
                <?php else: ?>
                <h1><?= ucfirst($vote['titre']) ?></h1>
              <?php endif; ?>
              <div class="mt-3">
                <span class="badge sort badge-<?= $vote['sortCode'] ?>"><?= mb_strtoupper($vote['sortCode']) ?></span>
              </div>
            </div>
          </div>
          <div class="row mt-5 bloc-groupes d-flex justify-content-around">
            <!-- POUR -->
            <?php if (in_array_r("pour", $groupes)): ?>
              <div class="card my-3" style="border: 0.5px solid rgba(0, 183, 148, 1)">
                <div class="thumb2 d-flex justify-content-end">
                  <span style="background-color: rgba(0, 183, 148, 1);">
                    POUR
                  </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-around flex-wrap">
                  <?php foreach ($groupes as $groupe): ?>
                    <?php if ($groupe['positionMajoritaire'] == 'pour'): ?>
                      <div class="card-vote-groupe my-1 mx-1" data-toggle="tooltip" data-placement="top" title="<?= $groupe['libelle'] ?> (<?= $groupe['libelleAbrev'] ?>)">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groupe['libelle'] ?>">
                        </picture>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
            <!-- CONTRE -->
            <?php if (in_array_r("contre", $groupes)): ?>
              <div class="card my-3" style="border: 0.5px solid rgba(197, 40, 61, 1)">
                <div class="thumb2 d-flex justify-content-end">
                  <span style="background-color: rgba(197, 40, 61, 1)">
                    CONTRE
                  </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-around flex-wrap">
                  <?php foreach ($groupes as $groupe): ?>
                    <?php if ($groupe['positionMajoritaire'] == 'contre'): ?>
                      <div class="card-vote-groupe my-1 mx-1" data-toggle="tooltip" data-placement="top" title="<?= $groupe['libelle'] ?> (<?= $groupe['libelleAbrev'] ?>)">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groupe['libelle'] ?>">
                        </picture>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
            <!-- ABSTENTION -->
           <?php if (in_array_r("abstention", $groupes)): ?>
             <div class="card my-3" style="border: 0.5px solid rgba(255, 173, 41, 1)">
               <div class="thumb2 d-flex justify-content-end">
                 <span style="background-color: rgba(255, 173, 41, 1)">
                   ABSTENTION
                 </span>
               </div>
               <div class="card-body d-flex align-items-center justify-content-around flex-wrap">
                 <?php foreach ($groupes as $groupe): ?>
                   <?php if ($groupe['positionMajoritaire'] == 'abstention'): ?>
                     <div class="card-vote-groupe my-1 mx-1" data-toggle="tooltip" data-placement="top" title="<?= $groupe['libelle'] ?> (<?= $groupe['libelleAbrev'] ?>)">
                       <picture>
                         <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
                         <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
                         <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groupe['libelle'] ?>">
                       </picture>
                     </div>
                   <?php endif; ?>
                 <?php endforeach; ?>
               </div>
             </div>
           <?php endif; ?>
          </div>
          <?php if ($description): ?>
            <div class="row bloc-description mt-5">
              <div class="col-12">
                <h2>Pour mieux comprendre</h2>
                <div class="card mt-4">
                  <div class="card-body">
                    <div class="read-more-container">
                      <?= $vote['description'] ?>
                      <p class="read-more-button"><a href="#" class="btn btn-primary">En savoir plus</a></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($author)): ?>
            <div class="row bloc-auteur mt-5">
              <div class="col-12 test-border">
                <h2 class="text-center"><?= $authorTitle ?></h2>
                <?php if ($authorType == "mp"): ?>
                  <p>Député</p>
                  <div class="d-flex justify-content-center">
                    <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $author, 'tag' => 'h3', 'cat' => false)) ?>
                  </div>
                <?php endif; ?>
                <?php if ($authorType == "gvt"): ?>
                  <p>Gouvernement</p>
                  <div class="d-flex justify-content-center">
                    <?php print_r($author) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="col-lg-3 col-md-4 col-10 offset-lg-1 offset-md-0 offset-1 bloc-results">
          <div class="sticky-top">
            <div class="card card-pie">
              <div class="card-header">
                <h2 class="title text-center">VOTE n° <?= $vote['voteNumero'] ?></h2>
              </div>
              <div class="card-body">
                <ul class="infos px-3">
                  <li class="first">
                    <div class="label"><?= file_get_contents(base_url().'/assets/imgs/icons/calendar.svg') ?></div>
                    <div class="value"><?= $vote['date_edited'] ?></div>
                  </li>
                  <li>
                    <div class="label"><?= file_get_contents(base_url().'/assets/imgs/icons/journal.svg') ?></div>
                    <div class="value">
                      <?= ucfirst($vote['type_edited']) ?>
                      <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="question no-decoration" aria-label="Tooltip explication" data-content="<?= $vote['type_edited_explication'] ?>"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                    </div>
                  </li>
                  <?php if ($vote['dossier_titre'] != ""): ?>
                    <li>
                      <div class="label"><?= file_get_contents(base_url().'/assets/imgs/icons/folder.svg') ?></div>
                      <div class="value"><?= $vote['dossier_titre'] ?></div>
                    </li>
                  <?php endif; ?>
                </ul>
                <div class="d-flex justify-content-center mt-3">
                  <div style="width: 120px">
                    <canvas id="pie-chart" width="100px" height="100px"></canvas>
                  </div>
                </div>
                <div class="results d-flex mt-3">
                  <div class="flex-fill" style="background-color: #00B794">
                    <span class="legend">POUR</span>
                    <span class="number"><?= $vote['pour'] ?></span>
                  </div>
                  <div class="flex-fill" style="background-color: #FFAD29">
                    <span class="legend">ABSTENTION</span>
                    <span class="number"><?= $vote['abstention'] ?></span>
                  </div>
                  <div class="flex-fill" style="background-color: #C5283D">
                    <span class="legend">CONTRE</span>
                    <span class="number"><?= $vote['contre'] ?></span>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-none d-md-block card nos-lois mt-5 url_obf" url_obf="<?= url_obfuscation("https://www.noslois.fr/") ?>" >
              <div class="card-body d-flex flex-column align-items-center py-3">
                <span class="text-center mb-2">Prenez position sur</span>
                <img src="<?= asset_url() ?>imgs/logos/nos-lois.png" alt="Logo Nos Lois">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4 bloc-table mt-5">
        <div class="col-12">
          <h2>Les votes des députés et des groupes</h2>
          <nav class="mt-4">
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
              <table class="table table-striped" id="table-vote-individual-groupes" style="width: 100%">
                <thead>
                  <tr>
                    <th class="all">Groupe</th>
                    <th class="text-center all">Vote</th>
                    <th class="text-center min-tablet">Pour</th>
                    <th class="text-center min-tablet">Abstention</th>
                    <th class="text-center min-tablet">Contre</th>
                    <th class="text-center">Participation</th>
                    <th class="text-center min-tablet">
                      Cohésion
                      <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Taux de cohésion" aria-label="Tooltip cohésion" data-content="Le taux de cohésion représente <b>l'unité d'un groupe politique</b> lorsqu'il vote. Il peut prendre des mesures allant de 0 à 1. Un taux proche de 1 signifie que le groupe est très uni.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les mesures de cohésion des autres groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques#cohesion' target='_blank'>cliquez ici</a>."><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                    </th>
                    <th class="pl-4"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($groupes as $v_groupe): ?>
                    <tr>
                      <td class="name">
                        <a href="<?= base_url() ?>groupes/<?= mb_strtolower($v_groupe['libelleAbrev']) ?>" target="_blank" class="no-decoration underline">
                          <?= $v_groupe['libelle'] ?> (<?= $v_groupe['libelleAbrev'] ?>)
                        </a>
                      </td>
                      <td class="text-center vote sort-<?= $v_groupe['positionMajoritaire'] ?>">
                        <?php if ($v_groupe['positionMajoritaire'] != "nv"): ?>
                          <?= mb_strtoupper($v_groupe['positionMajoritaire']) ?>
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
                  <tr>
                    <th class="all" style="vertical-align: top">Député</th>
                    <th class="text-center min-tablet">Groupe</th>
                    <th class="text-center all">Vote</th>
                    <th class="text-center all">Loyauté</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($deputes as $v_depute): ?>
                    <tr>
                      <td class="name">
                        <a href="<?= base_url() ?>deputes/<?= $v_depute['dptSlug'] ?>/depute_<?= $v_depute['nameUrl'] ?>" target="_blank" class="no-decoration underline"><?= $v_depute['nameFirst'].' '.$v_depute['nameLast'] ?></a>
                      </td>
                      <td class="text-center"><?= $v_depute['libelle'] ?></td>
                      <td class="text-center vote sort-<?= $v_depute['vote_libelle'] ?>"><?= mb_strtoupper($v_depute['vote_libelle']) ?></td>
                      <td class="text-center sort-<?= $v_depute['loyaute_libelle'] ?>"><?= mb_strtoupper($v_depute['loyaute_libelle']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Député</th>
                        <th>Groupes</th>
                        <th>Vote</th>
                        <th>Loyauté</th>
                    </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4 d-block d-md-none">
        <div class="col-12">
          <div class="card nos-lois url_obf" url_obf="<?= url_obfuscation("https://www.noslois.fr/") ?>" >
            <div class="card-body d-flex flex-column align-items-center py-3">
              <span class="text-center mb-2">Prenez position sur</span>
              <img src="<?= asset_url() ?>imgs/logos/nos-lois.png" alt="Logo Nos Lois">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid pg-vote-individual mt-5" id="container-always-fluid">
      <div class="row votes-next d-flex flex-md-row flex-column align-items-center justify-content-center py-4">
        <?php if ($vote_previous != FALSE): ?>
          <a class="btn" href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/vote_<?= $vote_previous ?>" role="button">
            <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?> Vote précédent</a>
        <?php endif; ?>
        <a class="btn" href="<?= base_url() ?>votes" role="button">Tous les votes</a>
        <?php if ($vote_next != FALSE): ?>
          <a class="btn" href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/vote_<?= $vote_next ?>" role="button">Vote suivant <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="container pg-vote-individual">
      <div class="row votes-decryptes">
        <div class="col-12 py-5">
          <h2>Les derniers votes décryptés par Datan</h2>
        </div>
      </div>
        <?php $this->load->view('votes/partials/votes_carousel.php', array('votes' => $votes_datan)) ?>
      <div class="row mt-4 mb-5"> <!-- BUTTONS BELOW -->
        <div class="col-12 d-flex justify-content-center">
          <div class="bloc-carousel-votes">
            <div class="carousel-buttons">
              <button type="button" class="btn prev mr-2 button--previous" aria-label="précédent">
                <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
              </button>
              <a class="btn all mx-2" href="<?= base_url() ?>votes/decryptes">
                <span>VOIR TOUS</span>
              </a>
              <button type="button" class="btn next ml-2 button--next" aria-label="suivant">
                <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript">
    // Pie chart
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
            display: false
          },
          legend: {
            display: false
          },
          layout: {
              padding: {
                left: 0,
                right: 0,
                top: 0,
                bottom: 0
              }
          }
        }
    });

    </script>
