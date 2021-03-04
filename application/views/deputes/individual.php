  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg" style="height: 13em">
  </div>
  <?php if (!empty($group['couleurAssociee'])): ?>
    <div class="liseret-groupe" style="background-color: <?= $group['couleurAssociee'] ?>"></div>
  <?php endif; ?>
  <div class="container pg-depute-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4">
        <div class="sticky-top" style="margin-top: -110px; top: 110px;">
          <div class="card card-profile">
            <div class="card-body">
              <!-- IMAGE MP -->
              <div class="img">
                <div class="d-flex justify-content-center">
                  <div class="depute-img-circle">
                    <?php if ($depute['img'] == 1): ?>
                      <picture>
                        <source srcset="<?= asset_url(); ?>imgs/deputes_webp/depute_<?= $depute['idImage'] ?>_webp.webp" alt="<?= $title ?>" type="image/webp">
                        <source srcset="<?= asset_url(); ?>imgs/deputes/depute_<?= $depute['idImage'] ?>.png" type="image/png">
                        <img src="<?= asset_url(); ?>imgs/deputes/depute_<?= $depute['idImage'] ?>.png" alt="<?= $title ?>">
                      </picture>
                      <?php else: ?>
                        <picture>
                          <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" type="image/png">
                          <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" alt="<?= $title ?>">
                        </picture>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <!-- INFOS GENERALES -->
              <div class="bloc-infos">
                <h1 class="text-center"><?= $title ?></h1>
                <?php if (!empty($depute['libelle'])): ?>
                  <div class="link-group text-center mt-1">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($depute['libelleAbrev']) ?>" style="color: <?= $depute['couleurAssociee'] ?>; --color-group: <?= $depute['couleurAssociee'] ?>">
                      <?= $depute['libelle'] ?>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
              <!-- BIOGRAPHIE -->
              <div class="bloc-bref mt-3 d-flex justify-content-center justify-content-lg-start">
                <ul>
                  <li class="first">
                    <div class="label"><?php echo file_get_contents(base_url().'/assets/imgs/icons/geo-alt-fill.svg') ?></div>
                    <div class="value"><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></div>
                  </li>
                  <?php if ($active == TRUE): ?>
                    <li>
                      <div class="label"><?php echo file_get_contents(base_url().'/assets/imgs/icons/person-fill.svg') ?></div>
                      <div class="value"><?= $depute['age'] ?> ans</div>
                    </li>
                    <li class="mb-0">
                      <div class="label"><?php echo file_get_contents(base_url().'/assets/imgs/icons/briefcase-fill.svg') ?></div>
                      <div class="value">Commission <?= $commission_parlementaire['commissionAbrege'] ?></div>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
            <?php if ($active == TRUE): ?>
              <div class="mandats d-flex justify-content-center align-items-center active">
                <span class="active"><?= mb_strtoupper($mandat_edito) ?> MANDAT</span>
              </div>
              <?php else: ?>
                <div class="mandats d-flex justify-content-center align-items-center inactive">
                  <span class="inactive">PLUS EN ACTIVITÉ</span>
                </div>
            <?php endif; ?>
          </div> <!-- END CARD PROFILE -->
        </div> <!-- END STICKY TOP -->
      </div> <!-- END COL -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5">
        <!-- BIO & ELECTION -->
        <div class="bloc-bio mt-5">
          <h2 class="mb-4">Qui est-<?= ($gender['pronom']) ?> ?</h2>
          <!-- Paragraphe introductif -->
          <?php if ($active == TRUE): ?>
            <p>
              <b><?= $title ?></b>, né<?= $gender['e'] ?> le <?= $depute['dateNaissanceFr'] ?> à <?= $depute['birthCity'] ?>, est <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></a>.
            </p>
            <?php else: ?>
            <p>
              <b><?= $title ?></b>, né<?= $gender['e'] ?> le <?= $depute['dateNaissanceFr'] ?> à <?= $depute['birthCity'] ?>, était un<?= $gender['e'] ?> député<?= $gender['e'] ?> de l'Assemblée nationale.
              Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $gender['pronom'] ?> a été élu<?= $gender['e'] ?> dans le département <?= $depute['dptLibelle2'] ?> <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></a>.
            </p>
          <?php endif; ?>
          <!-- Paragraphe historique -->
          <?php if ($active == TRUE): ?>
            <p>
              <?= ucfirst($gender['pronom']) ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?> et est en est à son <?= $mandat_edito ?> mandat.
              Au total, <?= $title ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale, soit <?= $history_edito ?> des députés, qui est de <?= $history_average['length'] ?> ans.
            </p>
          <?php elseif($depute['legislature'] == legislature_current()): ?>
            <p>
              Pour son dernier mandat, pendant la <?= legislature_current() ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
              <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
              Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
            </p>
          <?php else: ?>
            <p>
              Pour son dernier mendat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
              <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
              Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
            </p>
          <?php endif; ?>
          <!-- Paragraphe end -->
          <?php if ($active == TRUE): ?>
          <?php elseif ($depute['legislature'] == legislature_current()): ?>
            <p>
              <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?>
              <?php if (strpos($depute['causeFin'], 'Nomination comme membre du Gouvernement') !== false): ?>
                pour cause de nomination au Gouvernement.
              <?php elseif (strpos($depute['causeFin'], 'Décès') !== false): ?>
                pour cause de décès.
              <?php elseif (strpos($depute['causeFin'], "Démission d'office sur décision du Conseil constitutionnel") !== false): ?>
                pour cause de démission sur décision du Conseil constitutionnel.
              <?php elseif (strpos($depute['causeFin'], 'Démission') !== false): ?>
                pour cause de démission.
              <?php elseif (strpos($depute['causeFin'], "Annulation de l'élection sur décision du Conseil constitutionnel") !== false): ?>
                pour cause d'annulation de l'élection sur décision du Conseil constitutionnel.
              <?php elseif (strpos($depute['causeFin'], "Reprise de l'exercice du mandat d'un ancien membre du Gouvernement") !== false): ?>
                . Remplaçant un député nommé au Gouvernement, <?= $title ?> a quitté l'Assemblée lorsque celui-ci est redevenu député.
              <?php else: ?>
                .
              <?php endif; ?>
            </p>
          <?php else: ?>
            <p>
              <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?>.
            </p>
          <?php endif; ?>
          <!-- Paragraphe groupe parlementaire -->
          <?php if ($active == TRUE): ?>
            <?php if ( $depute['libelleAbrev'] == "NI"): ?>
              <p>
                À l'Assemblée nationale, <?= $title ?> n'est pas membre d'un groupe parlementaire, et siège donc en non-inscrit<?= $gender['e'] ?>.
              </p>
            <?php else: ?>
              <p>
                À l'Assemblée nationale, <?= $gender['pronom'] ?> siège avec le groupe <a href="<?= base_url() ?>groupes/<?= mb_strtolower($depute['libelleAbrev']) ?>"><?= $depute['libelle'] ?></a> (<?= $depute["libelleAbrev"] ?>), un groupe <b>classé <?= $infos_groupes[$depute['libelleAbrev']] ?></b> de l'échiquier politique.
                <?php if ($isGroupPresident == TRUE): ?><?= $title ?> en est le président.<?php endif; ?>
              </p>
            <?php endif; ?>
          <?php else: ?>
            <?php if (!empty($depute['libelle'])): ?>
              <p>
                Au cours de son dernier mandat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a siégé avec le groupe <?= $depute['libelle'] ?> (<?= $depute['libelleAbrev'] ?>).
              </p>
            <?php endif; ?>
          <?php endif; ?>
          <!-- Paragraphe commission parlementaire -->
          <?php if ($active == TRUE && !empty($commission_parlementaire)): ?>
            <p><?= $title ?> est <?= mb_strtolower($commission_parlementaire['commissionCodeQualiteGender']) ?> de la <?= $commission_parlementaire['commissionLibelle'] ?>.</p>
          <?php endif; ?>
          <!-- Paragraphe parti politique -->
          <?php if ($politicalParty['libelle'] != ""): ?>
            <?php if ($active == TRUE): ?>
              <p>
                <?= $title ?> est rattaché<?= $gender['e'] ?> financièrement au parti politique <a href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($politicalParty['libelleAbrev']) ?>"><?= $politicalParty['libelle'] ?> (<?= $politicalParty['libelleAbrev'] ?>)</a>.
                Le rattachement permet aux partis politiques de recevoir, pour chaque député, une subvention publique.
              </p>
            <?php else: ?>
              <p>
                Quand <?= $gender['pronom'] ?> était <?= $gender['depute'] ?>, <?= $title ?> était rattaché<?= $gender['e'] ?> financièrement au parti politique <a href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($politicalParty['libelleAbrev']) ?>"><?= $politicalParty['libelle'] ?> (<?= $politicalParty['libelleAbrev'] ?>)</a>.
                Le rattachement permet aux partis politiques de recevoir, pour chaque député, une subvention publique.
              </p>
            <?php endif; ?>
          <?php endif; ?>
          </div>
          <!-- BLOC POSITIONS CLEFS -->
          <?php if ($key_votes !== NULL): ?>
            <div class="bloc-key-votes mt-5">
              <div class="row">
                <div class="col-12">
                  <h2 class="mb-4">Ses positions importantes</h2>
                  <div class="card">
                    <div class="card-body key-votes">
                      <?php if (isset($key_votes[3254])): ?>
                        <div class="row">
                          <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                            <span class="sort-<?= $key_votes[3254]["vote_libelle"] ?>"><?= mb_strtoupper($key_votes[3254]["vote_libelle"]) ?></span>
                          </div>
                          <div class="col-md-9 value">
                            <?= $title ?><b>
                              <?php if ($key_votes[3254]["vote"] === "1"): ?>
                                a voté en faveur de
                              <?php elseif ($key_votes[3254]["vote"] === "-1"): ?>
                                a voté contre
                              <?php else: ?>
                                s'est abstenu<?= $gender["e"] ?> lors du vote sur
                              <?php endif; ?>
                              la proposition de loi Sécurité globale</b>.
                            <?= ucfirst($gender["le"]) ?> député<?= $gender["e"] ?> <?= $key_votes[3254]["loyaute"] === "1" ? "a été loyal" : "n'a pas été loyal" ?><?= $gender['e'] ?> a son groupe.
                            <a href="<?= base_url() ?>votes/legislature-15/vote_3254" class="font-italic">Voir le vote</a>
                          </div>
                        </div>
                      <?php endif; ?>
                      <?php if (isset($key_votes[2940])): ?>
                        <div class="row">
                          <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                            <span class="sort-<?= $key_votes[2940]["vote_libelle"] ?>"><?= mb_strtoupper($key_votes[2940]["vote_libelle"]) ?></span>
                          </div>
                          <div class="col-md-9 value">
                            <?= $title ?> a voté <b>
                              <?php if ($key_votes[2940]["vote"] === "1"): ?>
                                a voté en faveur de
                              <?php elseif ($key_votes[2940]["vote"] === "-1"): ?>
                                a voté contre
                              <?php else: ?>
                                s'est abstenu<?= $gender["e"] ?> sur le vote concernant
                              <?php endif; ?>
                              la réintroduction des pesticides néonicotinoïdes</b> jusqu'en 2023</b>.
                            <?= ucfirst($gender['pronom']) ?> <?= $key_votes[2940]["loyaute"] === "1" ? "a été loyal" : "n'a pas été loyal" ?><?= $gender['e'] ?> a son groupe.
                            <a href="<?= base_url() ?>votes/legislature-15/vote_2940" class="font-italic">Voir le vote</a>
                          </div>
                        </div>
                      <?php endif; ?>
                      <?php if (isset($key_votes[2814])): ?>
                        <div class="row">
                          <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                            <span class="sort-<?= $key_votes[2814]["vote_libelle"] ?>"><?= mb_strtoupper($key_votes[2814]["vote_libelle"]) ?></span>
                          </div>
                          <div class="col-md-9 value">
                            <?= $title ?> <b>
                              <?php if ($key_votes[2814]["vote"] === "1"): ?>
                                a accordé sa confiance
                              <?php elseif ($key_votes[2814]["vote"] === "-1"): ?>
                                n'a pas accordé sa confiance
                              <?php else: ?>
                                s'est abstenu<?= $gender["e"] ?> lors du vote de confiance
                              <?php endif; ?>
                              au Premier ministre Jean Castex</b>.
                            <?= ucfirst($gender['pronom']) ?> <?= $key_votes[2814]["loyaute"] === "1" ? "a été loyal" : "n'a pas été loyal" ?><?= $gender['e'] ?> a son groupe politique.
                            <a href="<?= base_url() ?>votes/legislature-15/vote_2814" class="font-italic">Voir le vote</a>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <!-- BLOC VOTES -->
          <?php if (!empty($votes_datan)): ?>
            <div class="bloc-votes mt-5">
              <div class="row">
                <div class="col-12">
                  <div class="d-flex justify-content-between mb-4">
                    <h2>Ses derniers votes</h2>
                    <div class="bloc-carousel-votes">
                      <div class="carousel-buttons">
                        <a class="btn all mx-2" href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>/votes">
                          <span>VOIR TOUS</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row bloc-carousel-votes-flickity">
                <div class="col-12 carousel-cards">
                  <?php foreach ($votes_datan as $vote): ?>
                    <div class="card card-vote">
                      <?php if ($vote['vote_depute'] == 'absent'): ?>
                        <div class="thumb absent d-flex align-items-center">
                          <div class="d-flex align-items-center">
                            <span>ABSENT<?= mb_strtoupper($gender['e']) ?></span>
                          </div>
                        </div>
                        <?php else: ?>
                          <div class="thumb d-flex align-items-center <?= $vote['vote_depute'] ?>">
                            <div class="d-flex align-items-center">
                              <span><?= mb_strtoupper($vote['vote_depute']) ?></span>
                            </div>
                          </div>
                      <?php endif; ?>
                      <div class="card-header d-flex flex-row justify-content-between">
                        <span class="date"><?= $vote['dateScrutinFRAbbrev'] ?></span>
                      </div>
                      <div class="card-body d-flex align-items-center">
                        <span class="title">
                          <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link no-decoration"><?= $vote['vote_titre'] ?></a>
                        </span>
                      </div>
                      <div class="card-footer">
                        <span class="field badge badge-primary py-1 px-2"><?= $vote['category_libelle'] ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                  <div class="card card-vote see-all">
                    <div class="card-body d-flex align-items-center justify-content-center">
                      <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>/votes" class="stretched-link no-decoration">VOIR TOUS</a>
                    </div>
                  </div>
                </div>
              </div> <!-- // END BLOC VOTES -->
              <div class="row mt-2"> <!-- BUTTONS BELOW -->
                <div class="col-12 d-flex justify-content-center">
                  <div class="bloc-carousel-votes">
                    <div class="carousel-buttons">
                      <button type="button" class="btn prev mr-2 button--previous">
                        <?php echo file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
                      </button>
                      <button type="button" class="btn next ml-2 button--next">
                        <?php echo file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div> <!-- // END BLOC VOTES -->
          <?php endif; ?>
          <!-- BLOC ELECTION -->
          <div class="bloc-election mt-5">
            <h2 class="mb-4">Son élection</h2>
            <div class="card">
              <div class="card-body">
                <?php if ($active == TRUE): ?>
                  <p>
                    <?= $title ?> est <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></a>.
                  </p>
                <?php else: ?>
                  <p>
                    <?= $title ?> était <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></a>.
                  </p>
                <?php endif; ?>
                <?php if (!empty($election_result)): ?>
                  <p>
                    <?= ucfirst($gender['pronom']) ?> a été élu<?= $gender['e'] ?> au <b><?= $election_result['tour_election'] ?> tour</b> avec <?= $election_result['score_pct'] ?>% des voix.
                  </p>
                <?php endif; ?>
                <?php if (!empty($election_result)): ?>
                  <div class="chart">
                    <div class="majority d-flex align-items-center" style="flex-basis: <?= $election_result['score_pct'] ?>%">
                      <span><?= $election_result['score_pct'] ?>%</span>
                    </div>
                    <div class="line">
                    </div>
                    <div class="minority" style="flex-basis: <?= 100 - $election_result['score_pct'] ?>%">
                    </div>
                  </div>
                  <div class="legend d-flex justify-content-center mt-1">
                    <span>50%</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div><!-- // END BLOC ELECTION -->
          <!-- BLOC STATISTIQUES -->
          <?php if ($depute['legislature'] == legislature_current()): ?>
            <div class="bloc-statistiques mt-5">
              <h2 class="mb-3">Son comportement politique</h2>
              <div class="card card-statistiques my-4">
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 d-flex flex-row align-items-center">
                      <div class="icon">
                        <?php echo file_get_contents(base_url().'/assets/imgs/icons/voting.svg') ?>
                      </div>
                      <h3 class="ml-3">PARTICIPATION AUX VOTES
                        <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="no-decoration" title="Taux de participation" data-content="Le taux de participation est le <b>pourcentage de votes auxquels le ou la député a participé</b>.<br><br>Attention, le taux de participation ne mesure pas toute l'activité d'un député ou d'un groupe. Contrairement au <a href='https://www.europarl.europa.eu/about-parliament/fr/organisation-and-rules/how-plenary-works' title='lien'>Parlement européen</a>, les votes à l'Assemblée nationale se déroulent à n'importe quel moment de la semaine. D'autres réunions ont souvent lieu en même temps, expliquant le faible taux de participation des députés et des groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#participation' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                      </h3>
                    </div>
                  </div>
                  <div class="row">
                    <?php if ($no_participation == TRUE): ?>
                      <div class="col-12 mt-2">
                        <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
                      </div>
                      <?php else: ?>
                      <div class="col-lg-3 offset-lg-1 mt-2">
                        <div class="d-flex justify-content-center align-items-center">
                          <div class="c100 p<?= $participation_commission['score'] ?> m-0">
                              <span><?= $participation_commission['score'] ?> %</span>
                              <div class="slice">
                                  <div class="bar"></div>
                                  <div class="fill"></div>
                              </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-8 infos mt-4 mt-lg-2">
                        <div class="texte ml-md-3 pl-md-3 mt-md-0 mt-3">
                          <p>
                            <?php if ($active == TRUE): ?>
                              Depuis sa prise de fonctions,
                              <?php else: ?>
                                Quand <?= $gender['pronom'] ?> était en activité à l'Assemblée,
                            <?php endif; ?>
                            <?= $title ?> a participé à <?= $participation_commission['score'] ?> % des votes ayant un lien avec son domaine de spécialisation.
                          </p>
                          <p><?= ucfirst($gender['pronom']) ?> <?= $active == TRUE ? "vote" : "votait" ?> donc <b><?= $edito_participation_commission['phrase'] ?></b> que la moyenne des députés, qui est de <?= $participation_commission['mean'] ?> %.</p>
                          <p>Ce score prend en compte les votes éléctroniques en séance publique sur les textes qui ont été examinés dans la commission du député. Ce sont sur ces textes que les élus sont susceptibles d'avoir un intérêt ou une expertise pariculière.</p>
                          <p><i>Nous avons modifié le score de participation le 31 janvier 2021. Désormais, le score ne prend en compte que les textes en lien avec la commission du parlementaire du député.</i></p>
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
                        <?php echo file_get_contents(base_url().'assets/imgs/icons/loyalty.svg') ?>
                      </div>
                      <h3 class="ml-3">LOYAUTÉ ENVERS SON GROUPE
                        <a tabindex="0" role="button" data-toggle="popover" class="no-decoration" data-trigger="focus" title="Loyauté envers le groupe politique" data-content="Le taux de loyauté est le <b>pourcentage de votes où le ou la député a voté sur la même ligne que son groupe</b>.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les taux de cohésion des autres parlementaires.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#loyalty' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                      </h3>
                    </div>
                  </div>
                  <div class="row">
                    <?php if ($no_participation == TRUE): ?>
                      <div class="col-12 mt-2">
                        <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
                      </div>
                      <?php else: ?>
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
                          <p><?php if ($active == FALSE): ?>Quand <?= $gender['pronom'] ?> était en activité, <?php endif; ?><?= $title ?> a voté sur la même ligne que son groupe dans <?= $loyaute['score'] ?>% des cas.</p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active == TRUE ? "est" : "était" ?> donc <b><?= $edito_loyaute['phrase'] ?><?= $gender['e'] ?></b> que la moyenne des députés, qui est de <?= $loyaute['mean'] ?>%.
                          </p>
                          <?php if (isset($loyaute_history)): ?>
                            <p>
                              <?= $title ?> a appartenu à plusieurs groupes parlementaires. Pour voir son taux de loyauté avec ces différents groupes, <a data-toggle="collapse" href="#collapseLoyaute" role="button" aria-expanded="false" aria-controls="collapseLoyaute">cliquez ici</a>.
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
                                  <?php foreach ($loyaute_history as $y): ?>
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
                          <p><i>Nous avons modifé le calcul de ce score le 26 janvier 2021. Désormais, il s'agit de taux de loyauté du député à son groupe actuel (ou le dernier groupe pour les députés plus en activité), et non à tous les groupes auxquels il ou elle a pu appartenir.</i></p>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div> <!-- END CARD LOYAUTE -->
              <!-- CARD MAJORITE -->
              <?php if ($depute['libelleAbrev'] != "LAREM"): ?>
                <div class="card card-statistiques my-4">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12 d-flex flex-row align-items-center">
                        <div class="icon">
                          <?php echo file_get_contents(base_url().'/assets/imgs/icons/elysee.svg') ?>
                        </div>
                        <h3 class="ml-3">PROXIMITÉ AVEC LA MAJORITÉ PRÉSIDENTIELLE
                          <a tabindex="0" role="button" data-toggle="popover" class="no-decoration" data-trigger="focus" title="Loyauté envers le groupe politique" data-content="Le taux de loyauté est le <b>pourcentage de votes où le ou la député a voté sur la même ligne que son groupe</b>.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les taux de cohésion des autres parlementaires.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#loyalty' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                        </h3>
                      </div>
                    </div>
                    <div class="row">
                      <?php if ($no_participation == TRUE): ?>
                        <div class="col-12 mt-2">
                          <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
                        </div>
                        <?php else: ?>
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
                            <p><?= $title ?> a voté comme la majoité présientielle (<a href="<?= base_url() ?>groupes/larem">La République en Marche</a>) dans <?= $majorite['score'] ?> % des cas.</p>
                            <p><?= ucfirst($gender['pronom']) ?> <?= $active == TRUE ? "est" : "était" ?> <b><?= $edito_majorite ?></b> de la majorité présidentielle que la moyenne des députés n'y appartenant pas, qui est de (<?= $majorite['mean'] ?> %).</p>
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
                        <?php echo file_get_contents(base_url().'/assets/imgs/icons/group.svg') ?>
                      </div>
                    </div>
                    <div class="col-10">
                      <h3>PROXIMITÉ AVEC LES GROUPES POLITIQUES
                        <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="no-decoration" title="Proximité avec les groupes politiques" data-content="Le <b>taux de proximité avec les groupes</b> représente le pourcentage de fois où un député vote la même chose qu'un groupe parlementaire. Chaque groupe se voit attribuer pour chaque vote une <i>position majoritaire</i>, en fonction du vote de ses membres. Cette position peut soit être 'pour', 'contre', ou 'absention'. Pour chaque vote, nous déterminons si le ou la députée a voté la même chose que la position majoritaire d'un groupe. Le taux de proximité est le pourcentage de fois où le ou la députée a voté de la même façon qu'un groupe.<br><br>Par exemple, si le taux est de 75%, cela signifie que <?= $title ?> a voté avec ce groupe dans 75% des cas.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                      </h3>
                    </div>
                  </div>
                  <?php if ($no_votes != TRUE): ?>
                    <div class="row">
                      <div class="offset-2 col-10">
                        <h4><?= $title ?> vote <b>souvent</b> avec :</h4>
                      </div>
                    </div>
                    <div class="row mt-1 bar-container pr-2">
                      <div class="offset-2 col-10">
                        <div class="chart">
                          <div class="chart-grid">
                            <div id="ticks">
                            <div class="tick" style="height: 50%;"><p>100 %</p></div>
                            <div class="tick" style="height: 50%;"><p>50 %</p></div>
                            <div class="tick" style="height: 0;"><p>0 %</p></div>
                            </div>
                          </div>
                          <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
                            <?php foreach ($accord_groupes_first as $group): ?>
                              <div class="bars d-flex align-items-center justify-content-center" style="height: <?= $group['accord'] ?>%">
                                <span class="score"><?= $group['accord'] ?>%</span>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      </div>
                      <div class="offset-2 col-10 d-flex justify-content-between mt-2">
                        <?php foreach ($accord_groupes_first as $group): ?>
                          <div class="legend-element d-flex align-items-center justify-content-center">
                            <span><?= $group['libelleAbrev'] ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                    <div class="row mt-3">
                      <div class="offset-2 col-10">
                        <p>
                        <?php if ($group['libelleAbrev'] != "NI"): ?>
                          En plus de son propre groupe,
                          <?php else: ?>
                          Le
                        <?php endif; ?>
                        <b><?= $title ?></b> <?= $active == TRUE ? "vote" : "votait" ?> souvent (dans <?= $proximite["first1"]["accord"] ?>% des cas) avec le groupe <a href="<?= base_url() ?>groupes/<?= mb_strtolower($proximite["first1"]["libelleAbrev"]) ?>"><?= $proximite["first1"]["libelleAbrev"] ?></a>, <?= $proximite["first1"]["maj_pres"] ?>
                        <?php if ($proximite['first1']["libelleAbrev"] != "NI"): ?>
                          classé <?= $proximite["first1"]["ideologiePolitique"] ?> de l'échiquier politique.</p>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="row mt-5">
                      <div class="col-10 offset-2">
                        <h4><?= ucfirst($gender['pronom']) ?> vote <b>rarement</b> avec :</h4>
                      </div>
                    </div>
                    <div class="row mt-1 bar-container pr-2">
                      <div class="offset-2 col-10">
                        <div class="chart">
                          <div class="chart-grid">
                            <div id="ticks">
                            <div class="tick" style="height: 50%;"><p>100 %</p></div>
                            <div class="tick" style="height: 50%;"><p>50 %</p></div>
                            <div class="tick" style="height: 0;"><p>0 %</p></div>
                            </div>
                          </div>
                          <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
                            <?php foreach ($accord_groupes_last_sorted as $group): ?>
                              <div class="bars d-flex align-items-center justify-content-center" style="height: <?= $group['accord'] ?>%">
                                <span class="score"><?= $group['accord'] ?>%</span>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      </div>
                      <div class="offset-2 col-10 d-flex justify-content-between mt-2">
                        <?php foreach ($accord_groupes_last_sorted as $group): ?>
                          <div class="legend-element d-flex align-items-center justify-content-center">
                            <span><?= $group['libelleAbrev'] ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                    <div class="row mt-3">
                      <div class="col-10 offset-2 ">
                        <p>À l'opposé, le groupe avec lequel <?= $title; ?> <?= $active == TRUE ? "est" : "était" ?> le moins proche est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($proximite["last1"]["libelleAbrev"]) ?>"><?= $proximite["last1"]["libelle"] ?></a>, <?= $proximite["last1"]["maj_pres"] ?>
                        <?php if ($proximite['last1']["libelleAbrev"] != "NI"): ?>
                          classé <?= $proximite["last1"]["ideologiePolitique"] ?> de l'échiquier politique.
                        <?php endif; ?>
                        <?= ucfirst($gender["pronom"]) ?> <?= $active == TRUE ? "ne vote" : "n'a voté" ?> avec ce groupe que dans <b><?= $proximite["last1"]["accord"] ?>%</b> des cas.</p>
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
                              <?php foreach ($accord_groupes_all as $group): ?>
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
                  <?php else: ?>
                  <p>Du fait d'un nombre insuffisant de votes de la part de <?= $title ?>, aucune statistique n'a pu être produite.</p>
                  <?php endif; ?>
                </div>
              </div> <!-- // END BLOC PROXIMITY -->
            </div> <!-- // END BLOC STATISTIQUES -->
            <?php endif; ?>
          <!-- BLOC SOCIAL-MEDIA -->
          <div class="bloc-links p-lg-0 p-md-2 mt-5">
            <h2>En savoir plus sur <?= $title ?></h2>
            <div class="row mt-4">
              <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                <span class="url_obf" url_obf="<?= url_obfuscation("http://www2.assemblee-nationale.fr/deputes/fiche/OMC_" . $depute['mpId']) ?>">
                  <a class="btn btn-an d-flex align-items-center justify-content-center">
                    Profil Assemblée Nationale
                  </a>
                </span>
              </div>
              <?php if ($depute['website'] !== NULL): ?>
                <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                  <span class="url_obf" url_obf="<?= url_obfuscation("https://" . $depute['website']) ?>">
                    <a class="btn btn-website d-flex align-items-center justify-content-center">
                      Site internet
                    </a>
                  </span>
                </div>
              <?php endif; ?>
              <?php if ($depute['facebook'] !== NULL): ?>
                <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                  <span class="url_obf" url_obf="<?= url_obfuscation("https://www.facebook.com/" . $depute['facebook']) ?>">
                    <a class="btn btn-fcb d-flex align-items-center justify-content-center">
                      <?php echo file_get_contents(base_url().'/assets/imgs/logos/facebook_svg.svg') ?>
                      <span class="ml-3">Profil Facebook</span>
                    </a>
                  </span>
                </div>
              <?php endif; ?>
              <?php if ($depute['twitter'] !== NULL): ?>
                <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                  <span class="url_obf" url_obf="<?= url_obfuscation("https://twitter.com/" . $depute['twitter']) ?>">
                    <a class="btn btn-twitter d-flex align-items-center justify-content-center">
                      <?php echo file_get_contents(base_url().'/assets/imgs/logos/twitter_svg.svg') ?>
                      <span class="ml-3">Profil Twitter</span>
                    </a>
                  </span>
                </div>
              <?php endif; ?>
            </div>
          </div> <!-- END BLOC SOCIAL MEDIA -->
          <!-- BLOC CONTACT -->
          <?php if ($depute['mailAn'] !== NULL): ?>
            <div class="bloc-links p-lg-0 p-md-2 mt-5">
              <h2>Contacter <?= $title ?></h2>
              <div class="row mt-4">
                <div class="col-12">
                  <span class="mr-4">
                    <?php echo file_get_contents(base_url().'/assets/imgs/icons/envelope-fill.svg') ?>
                  </span>
                  <a href="mailto:<?= $depute['mailAn'] ?>" class="no-decoration underline text-dark"><?= $depute['mailAn'] ?></a>
                </div>
              </div>
            </div>
          <?php endif; ?><!-- END BLOC SOCIAL MEDIA -->
      </div>
    </div>
  </div> <!-- END CONTAINER -->
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid pg-depute-individual bloc-others-container">
    <div class="container bloc-others">
      <?php if (isset($other_deputes)): ?>
        <div class="row mb-5">
          <div class="col-12">
            <?php if ($depute['legislature'] != legislature_current()): ?>
              <h2>Les autres députés de la <?= $depute['legislature'] ?><sup>e</sup> législature</h2>
            <?php elseif($active == TRUE): ?>
              <h2>Les autres députés <?= $depute['libelle'] ?> (<?= $depute['libelleAbrev'] ?>)</h2>
            <?php else: ?>
              <h2>Les autres députés plus en activité</h2>
            <?php endif; ?>
            <div class="row mt-3">
              <?php foreach ($other_deputes as $mp): ?>
                <div class="col-6 col-md-3 py-2">
                  <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'].' '.$mp['nameLast'] ?></a>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-3">
              <?php if ($depute['legislature'] != legislature_current()): ?>
                <a href="<?= base_url(); ?>deputes/legislature-<?= $depute['legislature'] ?>">Tous les députés de la législature <?= $depute['legislature'] ?></a>
              <?php elseif($active == TRUE): ?>
                <a href="<?= base_url() ?>groupes/<?= mb_strtolower($depute['libelleAbrev']) ?>">Voir tous les députés membres du groupe <?= $depute['libelle'] ?> (<?= $depute['libelleAbrev'] ?>)</a>
              <?php else: ?>
                <a href="<?= base_url(); ?>deputes/inactifs">Tous les députés plus en activité</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="row">
        <div class="col-12">
          <h2>Les députés en activité du département <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></h2>
          <div class="row mt-3">
            <?php foreach ($other_deputes_dpt as $mp): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'].' '.$mp['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>">Voir tous les députés élus dans le département <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
