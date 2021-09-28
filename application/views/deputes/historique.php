<div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
<div class="container pg-depute-individual mb-5">
  <div class="row">
    <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4">
      <?php $this->load->view('deputes/partials/card_individual.php', array('historique' => TRUE, 'last_legislature' => $depute_last['legislature'])) ?>
    </div> <!-- END COL -->
    <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5">
      <!-- BIO & ELECTION -->
      <div class="bloc-bio mt-5">
        <h2 class="mb-4">Historique de la <?= $legislature ?><sup>ème</sup> législature</h2>
        <p>
          <b><?= $title ?></b> est né<?= $gender['e'] ?> le <?= $depute['dateNaissanceFr'] ?> à <?= $depute['birthCity'] ?>.
          Pendant la <?= $legislature ?><sup>ème</sup> législature, <?= $gender['pronom'] ?> a été <?= $gender['le'] ?> <?= $gender['depute'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
        </p>
        <p>
          Pendant ce mandat, <?= $title ?> siégeait avec le groupe <?= $depute['libelle'] ?> (<?= $depute['libelleAbrev'] ?>).
        </p>
        <p>
          La dernière législature au cours de laquelle <?= $gender['le'] ?> <?= $gender['depute'] ?> a siégé est la <?= $depute_last['legislature'] ?><sum>ème</sup>.
          Pour plus d'information sur l'activité de <?= $title ?> au cours de cette législature, <a href="<?= base_url() ?>deputes/<?= $depute_last['dptSlug'] ?>/depute_<?= $depute_last['nameUrl'] ?>">cliquez ici</a>.
        </p>
      </div>
      <!-- BLOC STATISTIQUES -->
      <?php if (in_array($depute['legislature'], legislature_all())) : ?>
        <div class="bloc-statistiques mt-5">
          <h2 class="mb-3">
            Son comportement politique
            <?php if ($depute['legislature'] != legislature_current()): ?>
              (<?= $depute['legislature'] ?><sup>e</sup> législature)
            <?php endif; ?>
          </h2>
          <div class="card card-statistiques my-4">
            <div class="card-body">
              <div class="row">
                <div class="col-12 d-flex flex-row align-items-center">
                  <div class="icon">
                    <?= file_get_contents(base_url() . '/assets/imgs/icons/voting.svg') ?>
                  </div>
                  <h3 class="ml-3">PARTICIPATION AUX VOTES
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" aria-label="Tooltip participation" class="no-decoration popover_focus" title="Taux de participation" data-content="Le taux de participation est le <b>pourcentage de votes auxquels le ou la député a participé</b>.<br><br>Attention, le taux de participation ne mesure pas toute l'activité d'un député ou d'un groupe. Contrairement au <a href='https://www.europarl.europa.eu/about-parliament/fr/organisation-and-rules/how-plenary-works' title='lien'>Parlement européen</a>, les votes à l'Assemblée nationale se déroulent à n'importe quel moment de la semaine. D'autres réunions ont souvent lieu en même temps, expliquant le faible taux de participation des députés et des groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#participation' target='_blank'>cliquez ici</a>."><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
                  </h3>
                </div>
              </div>
              <div class="row">
                <?php if ($no_participation) : ?>
                  <div class="col-12 mt-2">
                    <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
                  </div>
                <?php else : ?>
                  <div class="col-lg-3 offset-lg-1 mt-2">
                    <div class="d-flex justify-content-center align-items-center">
                      <div class="c100 p<?= $participation['score'] ?> m-0">
                        <span><?= $participation['score'] ?> %</span>
                        <div class="slice">
                          <div class="bar"></div>
                          <div class="fill"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-8 infos mt-4 mt-lg-2">
                    <div class="texte ml-md-3 pl-md-3 mt-md-0 mt-3">
                      <?php if ($depute['legislature'] == legislature_current()): ?>
                        <!-- Paragraph for MP from the current legislature -->
                        <p>
                          <?php if ($active) : ?>
                            Depuis sa prise de fonctions,
                          <?php else : ?>
                            Quand <?= $gender['pronom'] ?> était en activité à l'Assemblée,
                          <?php endif; ?>
                          <?= $title ?> a participé à <?= $participation['score'] ?>% des votes solennels à l'Assemblée nationale.
                        </p>
                        <p>
                          <?= ucfirst($gender['pronom']) ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['all'] ?></b> que la moyenne des députés, qui est de <u><?= $participation['all'] ?>%</u>.
                        </p>
                      <?php else: ?>
                        <!-- Paragraph for MP from older legislatures -->
                        <p>
                          Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a participé à <?= $participation['score'] ?>% des votes solennels à l'Assemblée nationale.
                        </p>
                        <p>
                          <?= ucfirst($gender['pronom']) ?> votait <b><?= $edito_participation['all'] ?></b> que la moyenne des députés, qui était de <u><?= $participation['all'] ?>%</u>.
                        </p>
                        <?php if ($participation['group']): ?>
                          <p>
                            De plus, <?= $title ?> votait <b><?= $edito_participation['group'] ?></b> que la moyenne des députés de son groupe, qui était <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <u><?= $participation['group'] ?>%</u>.
                          </p>
                        <?php endif; ?>
                        <p>
                           Les votes solennels sont les votes considérés comme importants pour lesquels les députés connaissent à l'avance le jour et l'heure du vote.
                        </p>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="card card-statistiques my-4">
            <div class="card-body">
              <div class="row">
                <div class="col-12 d-flex flex-row align-items-center">
                  <div class="icon">
                    <?= file_get_contents(base_url() . 'assets/imgs/icons/loyalty.svg') ?>
                  </div>
                  <h3 class="ml-3">LOYAUTÉ ENVERS SON GROUPE
                    <a tabindex="0" role="button" data-toggle="popover" class="no-decoration popover_focus" data-trigger="focus" aria-label="Tooltip loyauté" title="Loyauté envers le groupe politique" data-content="Le taux de loyauté est le <b>pourcentage de votes où le ou la député a voté sur la même ligne que son groupe</b>.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les taux de cohésion des autres parlementaires.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#loyalty' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
                  </h3>
                </div>
              </div>
              <div class="row">
                <?php if ($no_participation) : ?>
                  <div class="col-12 mt-2">
                    <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
                  </div>
                <?php else : ?>
                  <div class="col-lg-3 offset-lg-1 mt-2">
                    <div class="d-flex justify-content-center align-items-center">
                      <div class="c100 p<?= $loyaute['score'] ?> m-0">
                        <span><?= $loyaute['score'] ?> %</span>
                        <div class="slice">
                          <div class="bar"></div>
                          <div class="fill"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-8 infos mt-4 mt-lg-2">
                    <div class="texte ml-md-3 pl-md-3 mt-md-0 mt-3">
                      <?php if ($depute['legislature'] == legislature_current()): ?>
                        <p>
                          <?php if (!$active) : ?>Quand <?= $gender['pronom'] ?> était en activité, <?php endif; ?><?= $title ?> a voté sur la même ligne que son groupe politique dans <u><?= $loyaute['score'] ?>%</u> des cas.
                        </p>
                        <p>
                          <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> donc <b><?= $edito_loyaute['all'] ?><?= $gender['e'] ?></b> que la moyenne des députés, qui est <?= $edito_loyaute['all'] == "aussi loyal" ? "également" : "" ?> de <u><?= $loyaute['all'] ?>%</u>.
                        </p>
                        <?php if ($loyaute['group']): ?>
                          <p>
                            De plus, <?= $title ?> <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['group'] ?></b> que la moyenne des députés de son groupe politique, qui était <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <u><?= $loyaute['group'] ?>%</u>.
                          </p>
                        <?php endif; ?>
                      <?php else: ?>
                          <p>
                            Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a voté sur la même ligne que son groupe politique dans <u><?= $loyaute['score'] ?>%</u> des cas.
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['all'] ?><?= $gender['e'] ?></b> que la moyenne des députés, qui est <?= $edito_loyaute['all'] == "aussi loyal" ? "également" : "" ?> de <u><?= $loyaute['all'] ?>%</u>.
                          </p>
                          <?php if ($loyaute['group']): ?>
                            <p>
                              De plus, <?= $title ?> était <b><?= $edito_loyaute['group'] ?></b> que la moyenne des députés de son groupe politique, qui était <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <u><?= $loyaute['group'] ?>%</u>.
                            </p>
                          <?php endif; ?>
                      <?php endif; ?>
                      <?php if (isset($loyaute_history)) : ?>
                        <p>
                          Ce score ne prend en compte que le dernier groupe politique auquel <?= $title ?> a appartenu. Pour voir les taux de loyauté <?= $gender['du'] ?> <?= $gender['depute'] ?> avec ses précédents groupes, <a data-toggle="collapse" href="#collapseLoyaute" role="button" aria-expanded="false" aria-controls="collapseLoyaute">cliquez ici</a>.
                        </p>
                        <div class="collapse my-4" id="collapseLoyaute">
                          <table class="table">
                            <thead>
                              <tr>
                                <th scope="col">Groupe</th>
                                <th scope="col">Loyauté</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($loyaute_history as $y) : ?>
                                <tr>
                                  <td>
                                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($y['libelleAbrev']) ?>" class="no-decoration underline"><?= $y['libelle'] ?></a>
                                  </td>
                                  <td><?= $y['score'] ?> %</td>
                                </tr>
                              <?php endforeach; ?>
                              <?php $i = 1; ?>
                            </tbody>
                          </table>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div> <!-- END CARD LOYAUTE -->
          <!-- CARD MAJORITE -->
          <?php if ($depute['libelleAbrev'] != "LAREM") : ?>
            <div class="card card-statistiques my-4">
              <div class="card-body">
                <div class="row">
                  <div class="col-12 d-flex flex-row align-items-center">
                    <div class="icon">
                      <?= file_get_contents(base_url() . '/assets/imgs/icons/elysee.svg') ?>
                    </div>
                    <h3 class="ml-3">PROXIMITÉ AVEC LA MAJORITÉ PRÉSIDENTIELLE
                      <a tabindex="0" role="button" data-toggle="popover" class="no-decoration popover_focus" data-trigger="focus" aria-label="Tooltip majorité" title="Proximité avec la majorité présidentielle" data-content="Le <b>taux de proximité avec la majorité présidentielle</b> représente le pourcentage de fois où un député vote la même chose que le groupe présidentiel (La République en Marche).<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
                    </h3>
                  </div>
                </div>
                <div class="row">
                  <?php if ($no_majorite) : ?>
                    <div class="col-12 mt-2">
                      <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
                    </div>
                  <?php else : ?>
                    <div class="col-lg-3 offset-lg-1 mt-2">
                      <div class="d-flex justify-content-center align-items-center">
                        <div class="c100 p<?= $majorite['score'] ?> m-0">
                          <span><?= $majorite['score'] ?> %</span>
                          <div class="slice">
                            <div class="bar"></div>
                            <div class="fill"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-8 infos mt-4 mt-lg-2">
                      <div class="texte ml-md-3 pl-md-3 mt-md-0 mt-3">
                        <?php if ($depute['legislature'] == legislature_current()): ?>
                          <p>
                            <?= $title ?> a voté comme la majoité présientielle (<a href="<?= base_url() ?>groupes/larem">La République en Marche</a>) dans <?= $majorite['score'] ?>% des cas.
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite['all'] ?></b> de la majorité présidentielle que la moyenne des députés n'y appartenant pas, qui est de <u><?= $majorite['all'] ?>%</u>.
                          </p>
                          <?php if ($majorite['group']): ?>
                            <p>
                              De plus, <?= $title ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite['group'] ?></b> de la majorité présidentielle que la moyenne des députés de son groupe politique (<u><?= $majorite['group'] ?>%</u>).
                            </p>
                          <?php endif; ?>
                        <?php else: ?>
                          <p>
                            Pendant la <?= $depute['legislature'] ?><sup></sup> législature, <?= $title ?> a voté comme le groupe de la majorité présidentielle dans <?= $majorite['score'] ?>% des cas.
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite['all'] ?></b> de la majorité présidentielle que la moyenne des députés, qui est de <u><?= $majorite['all'] ?>%</u>.
                          </p>
                          <?php if ($majorite['group']): ?>
                            <p>
                              De plus, <?= $title ?> était <b><?= $edito_majorite['group'] ?></b> de la majorité présidentielle que la moyenne des députés de son groupe politique (<u><?= $majorite['group'] ?>%</u>).
                            </p>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <div class="card card-statistiques bloc-proximity my-4">
            <div class="card-body">
              <div class="row">
                <div class="col-2">
                  <div class="icon">
                    <?= file_get_contents(base_url() . '/assets/imgs/icons/group.svg') ?>
                  </div>
                </div>
                <div class="col-10">
                  <h3>PROXIMITÉ AVEC LES GROUPES POLITIQUES
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" aria-label="Tooltip proximité" class="no-decoration popover_focus" title="Proximité avec les groupes politiques" data-content="Le <b>taux de proximité avec les groupes</b> représente le pourcentage de fois où un député vote la même chose qu'un groupe parlementaire. Chaque groupe se voit attribuer pour chaque vote une <i>position majoritaire</i>, en fonction du vote de ses membres. Cette position peut soit être 'pour', 'contre', ou 'absention'. Pour chaque vote, nous déterminons si le ou la députée a voté la même chose que la position majoritaire d'un groupe. Le taux de proximité est le pourcentage de fois où le ou la députée a voté de la même façon qu'un groupe.<br><br>Par exemple, si le taux est de 75%, cela signifie que <?= $title ?> a voté avec ce groupe dans 75% des cas.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
                  </h3>
                </div>
              </div>
              <?php if ($no_votes != TRUE) : ?>
                <div class="row">
                  <div class="offset-2 col-10">
                    <h4><?= $title ?> <?= $depute['legislature'] == legislature_current() ? "vote" : "votait" ?> <b>souvent</b> avec :</h4>
                  </div>
                </div>
                <div class="row mt-1 bar-container stats pr-2">
                  <div class="offset-2 col-10">
                    <div class="chart">
                      <div class="chart-grid">
                        <div id="ticks">
                          <div class="tick" style="height: 50%;">
                            <p>100 %</p>
                          </div>
                          <div class="tick" style="height: 50%;">
                            <p>50 %</p>
                          </div>
                          <div class="tick" style="height: 0;">
                            <p>0 %</p>
                          </div>
                        </div>
                      </div>
                      <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
                        <?php foreach ($accord_groupes_first as $group) : ?>
                          <div class="bars mx-1 mx-md-3" style="height: <?= $group['accord'] ?>%">
                            <span class="score"><?= $group['accord'] ?>%</span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <div class="offset-2 col-10 d-flex justify-content-between mt-2">
                    <?php foreach ($accord_groupes_first as $group) : ?>
                      <div class="legend-element d-flex align-items-center justify-content-center">
                        <span><?= $group['libelleAbrev'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="offset-2 col-10">
                    <?php if ($depute['legislature'] == legislature_current()): ?>
                      <p>
                        <?php if ($group['libelleAbrev'] != "NI") : ?>
                          En plus de son propre groupe,
                        <?php else : ?>
                          Le
                        <?php endif; ?>
                        <b><?= $title ?></b> <?= $active ? "vote" : "votait" ?> souvent (dans <?= $proximite["first1"]["accord"] ?>% des cas) avec le groupe <a href="<?= base_url() ?>groupes/<?= mb_strtolower($proximite["first1"]["libelleAbrev"]) ?>"><?= $proximite["first1"]["libelleAbrev"] ?></a>, <?= $proximite["first1"]["maj_pres"] ?>
                        <?php if ($proximite['first1']["libelleAbrev"] != "NI") : ?>
                          classé <?= $proximite["first1"]["ideologiePolitique"]["edited"] ?> de l'échiquier politique.
                        <?php endif; ?>
                      </p>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="row mt-5">
                  <div class="col-10 offset-2">
                    <h4><?= ucfirst($gender['pronom']) ?> <?= $depute['legislature'] == legislature_current() ? "vote" : "votait" ?> <b>rarement</b> avec :</h4>
                  </div>
                </div>
                <div class="row mt-1 bar-container stats pr-2">
                  <div class="offset-2 col-10">
                    <div class="chart">
                      <div class="chart-grid">
                        <div id="ticks">
                          <div class="tick" style="height: 50%;">
                            <p>100 %</p>
                          </div>
                          <div class="tick" style="height: 50%;">
                            <p>50 %</p>
                          </div>
                          <div class="tick" style="height: 0;">
                            <p>0 %</p>
                          </div>
                        </div>
                      </div>
                      <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
                        <?php foreach ($accord_groupes_last_sorted as $group) : ?>
                          <div class="bars mx-1 mx-md-3" style="height: <?= $group['accord'] ?>%">
                            <span class="score"><?= $group['accord'] ?>%</span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <div class="offset-2 col-10 d-flex justify-content-between mt-2">
                    <?php foreach ($accord_groupes_last_sorted as $group) : ?>
                      <div class="legend-element d-flex align-items-center justify-content-center">
                        <span><?= $group['libelleAbrev'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-10 offset-2 ">
                    <?php if ($depute['legislature'] == legislature_current()): ?>
                      <p>
                        À l'opposé, le groupe avec lequel <?= $title; ?> <?= $active ? "est" : "était" ?> le moins proche est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($proximite["last1"]["libelleAbrev"]) ?>"><?= $proximite["last1"]["libelle"] ?></a>, <?= $proximite["last1"]["maj_pres"] ?>
                        <?php if ($proximite['last1']["libelleAbrev"] != "NI") : ?>
                          classé <?= $proximite["last1"]["ideologiePolitique"]["edited"] ?> de l'échiquier politique.
                        <?php endif; ?>
                        <?= ucfirst($gender["pronom"]) ?> <?= $active ? "ne vote" : "n'a voté" ?> avec ce groupe que dans <b><?= $proximite["last1"]["accord"] ?>%</b> des cas.
                      </p>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="row mt-4">
                  <div class="col-12">
                    <div class="text-center">
                      <a class="btn btn-primary" id="btn-ranking" data-toggle="collapse" href="#collapseProximity" role="button" aria-expanded="false" aria-controls="collapseProximity">
                        Voir tout le classement
                      </a>
                    </div>
                    <div class="collapse mt-3" id="collapseProximity">
                      <table class="table">
                        <thead>
                          <tr>
                            <th scope="col"></th>
                            <th scope="col">Groupe</th>
                            <th scope="col">Taux de proximité</th>
                            <th scope="col">Groupe dissout ?</th>
                            <th scope="col">Nbr de votes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $i = 1; ?>
                          <?php foreach ($accord_groupes_all as $group) : ?>
                            <tr>
                              <th scope="row"><?= $i ?></th>
                              <td><?= $group['libelle'] ?> (<?= $group['libelleAbrev'] ?>)</td>
                              <td><?= $group['accord'] ?> %</td>
                              <td><?= $group['ended'] == 1 ? "Oui" : "" ?></td>
                              <td><?= $group['votesN'] ?></td>
                            </tr>
                            <?php $i++ ?>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              <?php else : ?>
                <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
              <?php endif; ?>
            </div>
          </div> <!-- // END BLOC PROXIMITY -->
        </div> <!-- // END BLOC STATISTIQUES -->
      <?php endif; ?>
      <!-- BLOC HISTORIQUE MANDATS -->
      <div class="bloc-mandats mt-5">
        <h2 class="mb-4">Historique des mandats</h2>
        <?php if (count($mandats) > 1): ?>
          <p>
            <?= $title ?> a été élu<?= $gender['e'] ?> à plusieurs reprises. Au total, <?= $gender['pronom'] ?> a été <?= $gender['depute'] ?> à l'Assemblée nationale pendant <?= $depute['lengthEdited'] ?>.
          </p>
          <p>
            <?= $title ?> a été député pendant les législatures suivantes :
            <?php $i = 1; ?>
            <?php foreach ($mandatsReversed as $mandat): ?>
              <?php if ($mandat['legislature']): ?>
                <i><?= $mandat['legislature'] ?>ème législature</i><?php if ($i < count($mandats)): ?>,<?php else: ?>.<?php endif; ?>
              <?php endif; ?>
              <?php $i++; ?>
            <?php endforeach; ?>
          </p>
          <p>
            Cette page publie les statistiques de <?= $title ?> pour la <?= $depute['legislature'] ?><sup>ème</sup> législature. Nous publions sur Datan l'historique de tous les mandats depuis la 14<sup>ème</sup> législature.
          </p>
          <div class="row">
            <?php foreach ($mandats as $mandat): ?>
              <?php if ($mandat['legislature'] >= 14 && $mandat['legislature'] != $depute['legislature']): ?>
                <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                  <?php if ($mandat['legislature'] == legislature_current()): ?>
                    <a class="btn d-flex align-items-center justify-content-center" href="<?= base_url() ?>deputes/<?= $mandat['dptSlug'] ?>/depute_<?= $mandat['nameUrl'] ?>">
                      <?= $mandat['legislature'] ?>ème législature
                    </a>
                  <?php else: ?>
                    <a class="btn d-flex align-items-center justify-content-center" href="<?= base_url() ?>deputes/<?= $mandat['dptSlug'] ?>/depute_<?= $mandat['nameUrl'] ?>/legislature-<?= $mandat['legislature'] ?>">
                      <?= $mandat['legislature'] ?>ème législature
                    </a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p>
            <?= $title ?> n'a été élu<?= $gender['e'] ?> <?= $gender['depute'] ?> qu'une fois. Au total, <?= $gender['pronom'] ?> a été <?= $gender['depute'] ?> à l'Assemblée nationale pendant <?= $depute['lengthEdited'] ?>.
          </p>
          <p>
            <?= $title ?> a été député pendant les législatures suivantes :
            <?php $i = 1; ?>
            <?php foreach ($mandatsReversed as $mandat): ?>
              <?php if ($mandat['legislature']): ?>
                <i><?= $mandat['legislature'] ?>ème législature</i><?php if ($i < count($mandats)): ?>,<?php else: ?>.<?php endif; ?>
              <?php endif; ?>
              <?php $i++; ?>
            <?php endforeach; ?>
          </p>
        <?php endif; ?>
      </div> <!-- // END BLOC HISTORIQUE MANDAT -->
      <!-- BLOC SOCIAL-MEDIA -->
      <div class="bloc-links p-lg-0 p-md-2 mt-5">
        <h2>En savoir plus sur <?= $title ?></h2>
        <div class="row mt-4">
          <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
            <span class="url_obf btn btn-an d-flex align-items-center justify-content-center" url_obf="<?= url_obfuscation("http://www2.assemblee-nationale.fr/deputes/fiche/OMC_" . $depute['mpId']) ?>">
              Profil Assemblée Nationale
            </span>
          </div>
          <?php if ($depute['website'] !== NULL) : ?>
            <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
              <span class="url_obf btn btn-website d-flex align-items-center justify-content-center" url_obf="<?= url_obfuscation("https://" . $depute['website']) ?>">
                Site internet
              </span>
            </div>
          <?php endif; ?>
          <?php if ($depute['facebook'] !== NULL) : ?>
            <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
              <span class="url_obf btn btn-fcb d-flex align-items-center justify-content-center" url_obf="<?= url_obfuscation("https://www.facebook.com/" . $depute['facebook']) ?>">
                <?= file_get_contents(base_url() . '/assets/imgs/logos/facebook_svg.svg') ?>
                <span class="ml-3">Profil Facebook</span>
              </span>
            </div>
          <?php endif; ?>
          <?php if ($depute['twitter'] !== NULL) : ?>
            <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
              <span class="url_obf btn btn-twitter d-flex align-items-center justify-content-center" url_obf="<?= url_obfuscation("https://twitter.com/" . $depute['twitter']) ?>">
                <?= file_get_contents(base_url() . '/assets/imgs/logos/twitter_svg.svg') ?>
                <span class="ml-3">Profil Twitter</span>
              </span>
            </div>
          <?php endif; ?>
        </div>
      </div> <!-- END BLOC SOCIAL MEDIA -->
    </div>
  </div>
</div> <!-- END CONTAINER -->
