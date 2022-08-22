    <div class="container pg-stats-index mb-5">
      <div class="row bloc-titre">
        <div class="col-md-12">
          <h1><?= $title ?></h1>
        </div>
        <div class="col-md-8 col-lg-7">
          <div class="mt-4">
            <p>
              Quel député vote le plus ? Qui est le plus loyal à son groupe ? Quel groupe parlementaire est le plus divisé ? Ou encore quel député est le plus âgé ?
            </p>
            <p>
              L'équipe de <b>Datan</b> vous propose une série de classements qui répondent à ces questions. Et si vous avez d'autres idées, n'hésitez pas à nous contacter : info[at]datan.fr !
            </p>
            <p>
              Pour en savoir plus sur nos statistiques, <a href="<?= base_url() ?>statistiques/aide">cliquez ici</a>.
            </p>
          </div>
        </div>
        <div class="col-md-4 offset-lg-1 d-none d-md-flex align-items-center">
          <div class="px-4">
            <?= file_get_contents(asset_url()."imgs/svg/undraw_visual_data_b1wx_2.svg") ?>
          </div>
        </div>
      </div>
      <!-- AGE -->
      <div class="row">
        <div class="col-12">
          <div class="title_svg">
            <h2>L'âge des députés</h2>
            <?= file_get_contents(asset_url()."imgs/svg/blob_1.svg") ?>
          </div>
        </div>
      </div>
      <div class="row bloc-ranking mt-5">
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0">
          <div class="card">
            <div class="card-body">
              <h3>Les plus âgés ? Les plus jeunes ?</h3>
              <p>L'âge moyen des députés est de <b><?= $age_mean ?> ans</b>. Découvrez dans ce tableau les trois députés les plus âgés et les trois députés les plus jeunes.</p>
              <div class="table_ranking mt-4">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Député</th>
                      <th>Âge</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mps_oldest as $mp): ?>
                      <tr>
                        <th scope="row"><?= $mp['rank'] ?></th>
                        <td>
                          <a href="<?= base_url() ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>" class="no-decoration underline"><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['libelleAbrev'] ?>)</a>
                        </td>
                        <td><?= $mp['age'] ?> ans</td>
                      </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td colspan="3" class="text-center">...</td>
                    </tr>
                    <?php foreach ($mps_youngest as $mp): ?>
                      <tr>
                        <th scope="row"><?= $mp['rank'] ?></th>
                        <td>
                          <a href="<?= base_url() ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>" class="no-decoration underline"><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['libelleAbrev'] ?>)</a>
                        </td>
                        <td><?= $mp['age'] ?> ans</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <a href="<?= base_url() ?>statistiques/deputes-age" class="no-decoration">
              <div class="card-footer text-center">
                <span>Découvrez tout le classement</span>
              </div>
            </a>
          </div>
        </div>
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <?php if (isset($groups_age_edited)): ?>
              <div class="card-body pb-0">
                <h3>La moyenne d'âge par groupe</h3>
                <p>Découvrez le groupe politique ayant la moyenne d'âge la plus élevée et celui avec la moyenne d'âge la plus faible.</p>
                <?php $this->load->view('classements/partials/groups_index.php', $groups_age_edited) ?>
              </div>
              <a href="<?= base_url() ?>statistiques/groupes-age" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
              <?php else: ?>
              <div class="card-body">
                <h3>La moyenne d'âge par groupe</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div> <!-- // END BLOC AGE -->
      <!-- WOMEN -->
      <div class="row mt-5 ">
        <div class="col-12">
          <div class="title_svg">
            <h2>Les femmes à l'Assemblée nationale</h2>
            <?= file_get_contents(asset_url()."imgs/svg/blob_2.svg") ?>
          </div>
        </div>
      </div>
      <div class="row bloc-ranking mt-5">
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0">
          <div class="card">
            <div class="card-body pb-0">
              <h3>L'évolution du taux de députées femmes</h3>
              <div class="mt-lg-3">
                <p>
                  Actuellement, il y a <b><?= $women_mean[1]['n'] ?></b> femmes députées à l'Assemblée nationale.
                  Cela représente <b><?= $women_mean[1]['percentage'] ?> %</b> des effectifs des parlementaires.
                </p>
                <p>
                  Entre la législature précédente et la législature actuelle, le nombre de femmes députées a baissé.
                  En effet, entre 2017 et 2022, il y avait 270 femmes à l'Assemblée nationale.
                 </p>
              </div>
              <div class="row mt-lg-3 bar-container stats pr-2">
                <div class="col-10 offset-2">
                  <div class="chart">
                    <div class="chart-grid">
                      <div id="ticks">
                      <div class="tick" style="height: 50%;"><p>100 %</p></div>
                      <div class="tick" style="height: 50%;"><p>50 %</p></div>
                      <div class="tick" style="height: 0;"><p>0 %</p></div>
                      </div>
                    </div>
                    <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
                      <?php foreach ($women_history as $key => $term): ?>
                        <div class="bars mx-1 <?= $key < 1 ? "d-none d-sm-flex" : "d-flex" ?>" style="height: <?= $term['pct'] ?>%" data-toggle="tooltip" data-placement="top" title="<?= $term['term'] ?>e législature (<?= $term['year_start'] ?> - <?= $term['year_end'] ?>)">
                          <span class="score"><?= $term['pct'] ?>%</span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <div class="col-10 offset-2 d-flex justify-content-between mt-2">
                  <?php foreach ($women_history as $key => $term): ?>
                    <div class="legend-element <?= $key < 1 ? "d-none d-sm-flex" : "d-flex" ?> align-items-center justify-content-center text-center" data-toggle="tooltip" data-placement="bottom" title="<?= $term['term'] ?>e législature (<?= $term['year_start'] ?> - <?= $term['year_end'] ?>)">
                      <span><?= $term['year_start'] ?>-<?= substr($term['year_end'], 2, 2) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <p class="mt-3">
                Le pourcentage de femmes députées a considérablement augmenté depuis le début de la Cinquième République. En 1958, l'Assemblée ne comptait parmi ses membres que 8 femmes, soit 1,4% des effectifs.
              </p>
            </div>
          </div>
        </div>
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <?php if ($groups_women_more): ?>
              <div class="card-body">
                <h3>La féminisation des groupes politiques</h3>
                <p>Certains groupes comptent dans leur rang plus de femmes que d'autres. Découvrez ici les groupes les féminisés et ceux qui le sont moins.</p>
                <div class="table_ranking">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col"></th>
                        <th scope="col">Groupe</th>
                        <th scope="col">% de femmes</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($groups_women_more as $group): ?>
                        <tr>
                          <th scope="row"><?= $group['rank'] ?></th>
                          <td><?= name_group($group['libelle']) . ' ('.$group['libelleAbrev'].')' ?></td>
                          <td><?= round($group['pct']) ?> %</td>
                        </tr>
                      <?php endforeach; ?>
                      <tr>
                        <td colspan="3" class="text-center">...</td>
                      </tr>
                      <?php foreach ($groups_women_less as $group): ?>
                        <tr>
                          <th scope="row"><?= $group['rank'] ?></th>
                          <td><?= $group['libelle'].' ('.$group['libelleAbrev'].')' ?></td>
                          <td><?= round($group['pct']) ?> %</td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <a href="<?= base_url() ?>statistiques/groupes-feminisation" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
            <?php else: ?>
              <div class="card-body">
                <h3>La féminisation des groupes politiques</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div> <!-- // END WOMEN -->
      <!-- LOYAUTE -->
      <div class="row mt-5 ">
        <div class="col-12">
          <div class="title_svg">
            <h2>La loyauté politique</h2>
            <?= file_get_contents(asset_url()."imgs/svg/blob_3.svg") ?>
          </div>
        </div>
      </div>
      <div class="row bloc-ranking mt-5">
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0">
          <div class="card">
            <?php if ($mps_loyalty): ?>
              <div class="card-body">
                <h3>La loyauté des députés envers leur groupe</h3>
                <p>
                  Quand ils votent, les députés peuvent décider de suivre la ligne de leur groupe politique. Ils peuvent également décider de voter contre leur groupe, par exemple si leur position personnelle est différente ou si les intérêts de leur circonscription sont contraires à la ligne du groupe.
                </p>
                <p>
                  En moyenne, les députés votent avec leur groupe dans <b><?= $loyalty_mean['mean'] ?> %</b> des cas.
                </p>
                <div class="table_ranking">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col"></th>
                        <th scope="col">Député</th>
                        <th scope="col">Taux de loyauté</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($mps_loyalty_more as $mp): ?>
                        <tr>
                          <th scope="row"><?= $mp['rank'] ?></th>
                          <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['libelleAbrev'] ?>)</td>
                          <td><?= $mp['score'] ?> %</td>
                        </tr>
                      <?php endforeach; ?>
                      <tr>
                        <td colspan="3" class="text-center">...</td>
                      </tr>
                      <?php foreach ($mps_loyalty_less as $mp): ?>
                        <tr>
                          <th scope="row"><?= $mp['rank'] ?></th>
                          <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['libelleAbrev'] ?>)</td>
                          <td><?= $mp['score'] ?> %</td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <a href="<?= base_url() ?>statistiques/deputes-loyaute" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
            <?php else: ?>
              <div class="card-body">
                <h3>La loyauté des députés envers leur groupe</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <?php if ($groups_cohesion): ?>
              <div class="card-body pb-0">
                <h3>La cohésion au sein des groupes</h3>
                <p>
                  Quand ils votent, les députés peuvent décider de suivre la ligne de leur groupe politique. Ils peuvent également décider de voter contre leur groupe, par exemple si leur position personnelle est différente ou si les intérêts de leur circonscription sont contraires à la ligne du groupe.
                </p>
                <?php $this->load->view('classements/partials/groups_index.php', $groups_cohesion_edited) ?>
              </div>
              <a href="<?= base_url() ?>statistiques/groupes-cohesion" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
            <?php else: ?>
              <div class="card-body">
                <h3>La cohésion au sein des groupes</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div> <!-- // END LOYAUTE -->
      <!-- PARTICIPATION -->
      <div class="row mt-5 ">
        <div class="col-12">
          <div class="title_svg">
            <h2>La participation politique</h2>
            <?= file_get_contents(asset_url()."imgs/svg/blob_4.svg") ?>
          </div>
        </div>
      </div>
      <div class="row bloc-ranking mt-5">
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0">
          <div class="card">
            <?php if ($mps_participation): ?>
              <div class="card-body">
                <h3>La participation des députés aux votes</h3>
                <p>En moyenne, les députés participent à <b><?= $mps_participation_mean ?> %</b> des scrutins.</p>
                <p><b>Attention</b>, à cause de la crise de la Covid-19, les députés ayant rejoint le parlement récemment ont des scores de participation plus faibles que la moyenne.</p>
                <div class="table_ranking">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col"></th>
                        <th scope="col">Député</th>
                        <th scope="col">Taux de participation</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($mps_participation_first as $mp): ?>
                        <tr>
                          <th scope="row"><?= $mp['rank'] ?></th>
                          <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['libelleAbrev'] ?>)</td>
                          <td><?= $mp['score'] * 100 ?> %</td>
                        </tr>
                      <?php endforeach; ?>
                      <tr>
                        <td colspan="3" class="text-center">...</td>
                      </tr>
                      <?php foreach ($mps_participation_last as $mp): ?>
                        <tr>
                          <th scope="row"><?= $mp['rank'] ?></th>
                          <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['libelleAbrev'] ?>)</td>
                          <td><?= $mp['score'] * 100 ?> %</td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <a href="<?= base_url() ?>statistiques/deputes-participation" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
            <?php else: ?>
              <div class="card-body">
                <h3>La participation des députés aux votes</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <?php if ($groups_participation): ?>
              <div class="card-body pb-0">
                <h3>La participation au sein des groupes</h3>
                <p>Quels sont les groups politiques ayant les députés participant le plus aux votes à l'Assemblée nationale ?</p>
                <p><b>Attention</b>, à cause de la crise de la Covid-19, les députés ayant rejoint le parlement récemment ont des scores de participation plus faibles que la moyenne.</p>
                <?php $this->load->view('classements/partials/groups_index.php', $groups_participation_edited) ?>
              </div>
              <a href="<?= base_url() ?>statistiques/groupes-participation" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
            <?php else: ?>
              <div class="card-body">
                <h3>La participation au sein des groupes</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div> <!-- // END PARTICIPATION -->
      <!-- ORIGINE SOCIALE -->
      <div class="row mt-5">
        <div class="col-12">
          <div class="title_svg">
            <h2>L'origine sociale des députés</h2>
            <?= file_get_contents(asset_url()."imgs/svg/blob_1.svg") ?>
          </div>
        </div>
      </div>
      <div class="row bloc-ranking mt-5">
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <?php if ($groups_cadres): ?>
              <div class="card-body pb-0">
                <h3>La part de députés cadres dans chaque groupe politique</h3>
                <p>La grande majorité de parlementaires étaient cadres ou exerçaient une profession intellectuelle supérieure (par exemple avocat, médecin ou ingénieur).</p>
                <p>Découvrez le groupe politique qui compte le plus de députés cadres et celui en ayant le moins.</p>
                <?php $this->load->view('classements/partials/groups_index.php', $groups_cadres_edited) ?>
              </div>
              <a href="<?= base_url() ?>statistiques/groupes-origine-sociale" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
            <?php else: ?>
              <div class="card-body">
                <h3>La part des députés cadres dans chaque groupe politique</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <?php if ($groups_rose): ?>
              <div class="card-body pb-0">
                <h3>La représentativité sociale des groupes politiques</h3>
                <p>Un groupe politique est-il représentatif de la population française ? Quel groupe politique ressemble le plus socialement à la population ?</p>
                <p>Nous utilisons un <span class="url_obf" url_obf="<?= url_obfuscation("https://rdrr.io/rforge/polrep/man/Rose.html") ?>">indicateur</span> permettant de mesurer la représentation des groupes de l'Assemblée nationale. Il va de 0 (le moins représentatif) à 1 (le plus représentatif).</p>
                <?php $this->load->view('classements/partials/groups_index.php', $groups_rose_edited) ?>
              </div>
              <a href="<?= base_url() ?>statistiques/groupes-origine-sociale" class="no-decoration">
                <div class="card-footer text-center">
                  <span>Découvrez tout le classement</span>
                </div>
              </a>
            <?php else: ?>
              <div class="card-body">
                <h3>La représentativité sociale des groupes politiques</h3>
                <p>Ces données seront prochainement disponibles.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-12 mt-5">
          <div class="card">
            <div class="card-body">
              <h3>Les députés selon leur catégorie professionnelle</h3>
              <div class="row row-grid mt-5 mt-md-4">
                <div class="col-lg-4 d-flex flex-column justify-content-center">
                  <p>
                    Les députés sont-ils représentatifs de la population française ?
                  </p>
                  <p>
                    La grande majorité des parlementaires étaient cadres ou exerçaient une profession intellectuelle supérieure, par exemple avocat, médecin ou ingénieur. Ils représentent <?= round($famSocPro_cadres['mps']) ?> % des députés, alors que seulement <?= round($famSocPro_cadres['population']) ?> % de la population française appartient à cette catégorie.
                  </p>
                  <p>
                    Au contraire, <b>peu de députés étaient employés ou ouvriers</b>, alors qu'ils représentent un tiers de la population.
                  </p>
                </div>
                <div class="col-lg-8">
                  <h4 class="text-center mb-4">Pourcentage de députés et de citoyens dans chaque catégorie</h4>
                  <div style="min-height: 425px">
                    <canvas id="chartOrigineSociale"></canvas>
                  </div>
                </div>
              </div>
            </div>
            <a href="<?= base_url() ?>statistiques/deputes-origine-sociale" class="no-decoration">
              <div class="card-footer text-center">
                <span>Découvrez tout le classement</span>
              </div>
            </a>
          </div>
        </div>
      </div> <!-- // END BLOC ORIGINE SOCIALE -->
    </div>
    <script type="text/javascript">
      var colorMp = "rgba(0, 183, 148, 1)";
      var colorPop = "rgba(255, 102, 26, 1)";
      const labels = [
        <?php foreach ($famSocPro as $x) {
          echo "[";
          foreach ($x['familleCut'] as $y) {
            if ($y) {
              echo '"'.$y.'",';
            }
          }
          echo "],";
        } ?>
      ];
      const data = {
        labels: labels,
        datasets: [
          {
            label: 'Députés',
            data: [
              <?php foreach ($famSocPro as $fam) {
                echo '"'.$fam['mps'].'",';
              } ?>
            ],
            borderColor: colorMp,
            backgroundColor: colorMp
          },
          {
            label: 'Population',
            data: [
              <?php foreach ($famSocPro as $fam) {
                echo '"'.$fam['population'].'",';
              } ?>
            ],
            borderColor: colorPop,
            backgroundColor: colorPop
          }
        ]
      };
      const options = {
        responsive: true,
        maintainAspectRatio: false,
        tooltips: {
          callbacks: {
            label: function(tooltipItem, data) {
              return data.datasets[tooltipItem.datasetIndex]['data'][tooltipItem.index] + ' %';
            }
          }
        }
      };
      const cut = 10;
      var ctx = document.getElementById('chartOrigineSociale');
      var myChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: data,
        options: options
      });
    </script>
