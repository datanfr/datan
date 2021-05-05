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
              L'équipe de <b>Datan</b> vous propose une série de classements qui répondent à ces questions. Et si vous avez d'autres idées, n'hésitez pas à nous envoyer <a href="mailto:info@datan.fr">un mail</a> !
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
              <p>L'âge moyen des députés est de <b><?= $age_mean['mean'] ?> ans</b>. Découvrez dans ce tableau les trois députés les plus âgés et les trois députés les plus jeunes.</p>
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
                          <a href="<?= base_url() ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>" class="no-decoration underline"><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['groupLibelleAbrev'] ?>)</a>
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
                          <a href="<?= base_url() ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>" class="no-decoration underline"><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['groupLibelleAbrev'] ?>)</a>
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
            <div class="card-body pb-0">
              <h3>La moyenne d'âge par groupe</h3>
              <p>Découvrez le groupe politique ayant la moyenne d'âge la plus élevée et celui avec la moyenne d'âge la plus faible.</p>
              <div class="ranking-graph-group mt-4 py-4 row row-grid">
                <div class="col-sm-6 d-flex flex-column align-items-center">
                  <div class="title text-center mb-4">
                    Le plus âgé
                  </div>
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_age_oldest["libelleAbrev"]) ?>">
                    <div class="score mb-4">
                      <div class="avatar">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groups_age_oldest['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groups_age_oldest['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groups_age_oldest['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groups_age_oldest['libelle'] ?>">
                        </picture>
                      </div>
                      <div class="age">
                        <span class="badge badge-primary shadow"><?= round($groups_age_oldest["age"]) ?> ans</span>
                      </div>
                    </div>
                  </a>
                  <div class="description">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_age_oldest["libelleAbrev"]) ?>" class="no-decoration underline"><?= $groups_age_oldest["libelle"] ?></a>
                  </div>
                </div>
                <div class="col-sm-6 d-flex flex-column align-items-center">
                  <div class="title text-center mb-4">
                    Le plus jeune
                  </div>
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_age_youngest["libelleAbrev"]) ?>">
                    <div class="score mb-4">
                      <div class="avatar">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groups_age_youngest['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groups_age_youngest['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groups_age_youngest['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groups_age_youngest['libelle'] ?>">
                        </picture>
                      </div>
                      <div class="age">
                        <span class="badge badge-primary shadow"><?= round($groups_age_youngest["age"]) ?> ans</span>
                      </div>
                    </div>
                  </a>
                  <div class="description">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_age_youngest["libelleAbrev"]) ?>" class="no-decoration underline"><?= $groups_age_youngest["libelle"] ?></a>
                  </div>
                </div>
              </div>
            </div>
            <a href="<?= base_url() ?>statistiques/groupes-age" class="no-decoration">
              <div class="card-footer text-center">
                <span>Découvrez tout le classement</span>
              </div>
            </a>
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
                  Entre la législature précédente et la législature actuelle, le nombre de femmes députées a augmenté de 45%.
                  En effet, entre 2012 et 2017, il n'y avait que 155 femmes députées.
                 </p>
              </div>
              <div class="row mt-lg-3 bar-container pr-2">
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
                        <div class="bars <?= $key < 1 ? "d-none d-sm-flex" : "d-flex" ?> align-items-center justify-content-center" style="height: <?= $term['pct'] ?>%" data-toggle="tooltip" data-placement="top" title="<?= $term['term'] ?>e législature (<?= $term['year_start'] ?> - <?= $term['year_end'] ?>)">
                          <span class="score"><?= $term['pct'] ?> %</span>
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
                        <td><?= $group['libelle'].' ('.$group['libelleAbrev'].')' ?></td>
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
                        <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['groupLibelleAbrev'] ?>)</td>
                        <td><?= $mp['score'] ?> %</td>
                      </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td colspan="3" class="text-center">...</td>
                    </tr>
                    <?php foreach ($mps_loyalty_less as $mp): ?>
                      <tr>
                        <th scope="row"><?= $mp['rank'] ?></th>
                        <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['groupLibelleAbrev'] ?>)</td>
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
          </div>
        </div>
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <div class="card-body pb-0">
              <h3>La cohésion au sein des groupes</h3>
              <p>
                Quand ils votent, les députés peuvent décider de suivre la ligne de leur groupe politique. Ils peuvent également décider de voter contre leur groupe, par exemple si leur position personnelle est différente ou si les intérêts de leur circonscription sont contraires à la ligne du groupe.
              </p>
              <div class="ranking-graph-group mt-4 py-4 row row-grid">
                <div class="col-sm-6 d-flex flex-column align-items-center">
                  <div class="title text-center mb-4">
                    Le plus uni
                  </div>
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_cohesion_first["libelleAbrev"]) ?>">
                    <div class="score mb-4">
                      <div class="avatar">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groups_cohesion_first['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groups_cohesion_first['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groups_cohesion_first['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groups_cohesion_first['libelle'] ?>">
                        </picture>
                      </div>
                    </div>
                  </a>
                  <div class="description">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_cohesion_first["libelleAbrev"]) ?>" class="no-decoration underline"><?= $groups_cohesion_first["libelle"] ?></a>
                  </div>
                </div>
                <div class="col-sm-6 d-flex flex-column align-items-center">
                  <div class="title text-center mb-4">
                    Le plus divisé
                  </div>
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_cohesion_last["libelleAbrev"]) ?>">
                    <div class="score mb-4">
                      <div class="avatar">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groups_cohesion_last['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groups_cohesion_last['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groups_cohesion_last['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groups_cohesion_last['libelle'] ?>">
                        </picture>
                      </div>
                    </div>
                  </a>
                  <div class="description">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_cohesion_last["libelleAbrev"]) ?>" class="no-decoration underline"><?= $groups_cohesion_last["libelle"] ?></a>
                  </div>
                </div>
              </div>
            </div>
            <a href="<?= base_url() ?>statistiques/groupes-cohesion" class="no-decoration">
              <div class="card-footer text-center">
                <span>Découvrez tout le classement</span>
              </div>
            </a>
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
            <div class="card-body">
              <h3>La participation des députés aux votes</h3>
              <p>En moyenne, les députés participent à <b><?= $mps_participation_mean ?> %</b> des scrutins.</p>
              <p><b>Attention</b>, le taux de participation à l'Assemblée tend à être faible du fait de l'organisation du travail. De plus, à cause de la crise de la Covid-19, les députés ayant rejoint le parlement récemment ont des scores de participation plus faibles que la moyenne.</p>
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
                        <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['groupLibelleAbrev'] ?>)</td>
                        <td><?= $mp['score'] * 100 ?> %</td>
                      </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td colspan="3" class="text-center">...</td>
                    </tr>
                    <?php foreach ($mps_participation_last as $mp): ?>
                      <tr>
                        <th scope="row"><?= $mp['rank'] ?></th>
                        <td><?= $mp['nameFirst'].' '.$mp['nameLast'] ?> (<?= $mp['groupLibelleAbrev'] ?>)</td>
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
          </div>
        </div>
        <div class="col-lg-6 col-md-10 offset-md-1 offset-lg-0 mt-5 mt-lg-0">
          <div class="card">
            <div class="card-body pb-0">
              <h3>La participation au sein des groupes</h3>
              <p>Quels sont les groups politiques ayant les députés participant le plus aux votes à l'Assemblée nationale ?</p>
              <p><b>Attention</b>, le taux de participation est faible à cause de l'organisation du travail. Avec plusieurs réunions en même temps, seuls les députés spécialistes d'un sujet participent aux discussions en séance plénière.</p>
              <div class="ranking-graph-group mt-4 py-4 row row-grid">
                <div class="col-sm-6 d-flex flex-column align-items-center">
                  <div class="title text-center mb-4">
                    Vote le plus souvent
                  </div>
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_participation_first["libelleAbrev"]) ?>">
                    <div class="score mb-4">
                      <div class="avatar">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groups_participation_first['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groups_participation_first['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groups_participation_first['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groups_participation_first['libelle'] ?>">
                        </picture>
                      </div>
                      <div class="age">
                        <span class="badge badge-primary shadow"><?= round($groups_participation_first["participation"]) ?> %</span>
                      </div>
                    </div>
                  </a>
                  <div class="description">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_participation_first["libelleAbrev"]) ?>" class="no-decoration underline"><?= $groups_participation_first["libelle"] ?></a>
                  </div>
                </div>
                <div class="col-sm-6 d-flex flex-column align-items-center">
                  <div class="title text-center mb-4">
                    Vote le moins souvent
                  </div>
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_participation_last["libelleAbrev"]) ?>">
                    <div class="score mb-4">
                      <div class="avatar">
                        <picture>
                          <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groups_participation_last['libelleAbrev'] ?>.webp" type="image/webp">
                          <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groups_participation_last['libelleAbrev'] ?>.png" type="image/png">
                          <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $groups_participation_last['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groups_participation_last['libelle'] ?>">
                        </picture>
                      </div>
                      <div class="age">
                        <span class="badge badge-primary shadow"><?= round($groups_participation_last["participation"]) ?> %</span>
                      </div>
                    </div>
                  </a>
                  <div class="description">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groups_participation_last["libelleAbrev"]) ?>" class="no-decoration underline"><?= $groups_participation_last["libelle"] ?></a>
                  </div>
                </div>
              </div>
            </div>
            <a href="<?= base_url() ?>statistiques/groupes-participation" class="no-decoration">
              <div class="card-footer text-center">
                <span>Découvrez tout le classement</span>
              </div>
            </a>
          </div>
        </div>
      </div> <!-- // END PARTICIPATION -->
      <!-- AGE -->
      <div class="row mt-5">
        <div class="col-12">
          <div class="title_svg">
            <h2>L'origine sociale des députés</h2>
            <?= file_get_contents(asset_url()."imgs/svg/blob_1.svg") ?>
          </div>
        </div>
      </div>
      <div class="row bloc-ranking mt-5">
        <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1 offset-lg-0">
          <div class="card">
            <div class="card-body">
              <h3>Les députés selon leur catégorie socioprofessionnelle</h3>
              <p>Pour comprendre l'origine sociale des députés, nous analysons le nombre d'élus dans chaque catégorie socioprofessionnelle. Ces catégories ont été élaborées par <span class="url_obf" url_obf="<?= url_obfuscation("https://www.insee.fr/fr/information/2400059") ?>">l'insee</span> et permettent grouper les députés selon leur ancienne activité professionnelle.</p>
              <p>Cette analyse permet de mesurer la représentativité sociale de l'Assemblée nationale. Par exemples, y a-t-il autant d'agriculteurs au sein du parlement et dans la population française ?</p>
              <canvas id="chartOrigineSociale" height="200px"></canvas>
            </div>
            <a href="<?= base_url() ?>statistiques/" class="no-decoration">
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
        <?php foreach ($famSocPro as $fam) {
          echo '"'.$fam['famille'].'",';
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

      };
      var ctx = document.getElementById('chartOrigineSociale');
      var myChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: data,
        options: options
      });
    </script>
