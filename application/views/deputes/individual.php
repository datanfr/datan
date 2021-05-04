  <?php if (($regionales2021 && $regionales2021['visible']) || isset($_GET['regionales2021'])) : ?>
    <a href="<?= base_url() ?>elections/regionales-2021">
      <div class="container-fluid electionBanner d-flex justify-content-center align-items-center py-4">
        <span class="text-center">
          <?= $title ?> est candidat<?= $gender['e'] ?> aux prochaines élections régionales de juin 2021, dans la région <?= $regionales2021['regionLibelle'] ?>.
      </div>
    </a>
  <?php endif; ?>
  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  <?php if (!empty($depute['couleurAssociee'])) : ?>
    <div class="liseret-groupe" style="background-color: <?= $depute['couleurAssociee'] ?>"></div>
  <?php endif; ?>
  <div class="container pg-depute-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4">
        <?php $this->load->view('deputes/partials/card_individual.php', array('historique' => FALSE, 'last_legislature' => $depute['legislature'])) ?>
      </div> <!-- END COL -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5">
        <!-- BIO & ELECTION -->
        <div class="bloc-bio mt-5">
          <h2 class="mb-4">Qui est-<?= ($gender['pronom']) ?> ?</h2>
          <!-- Paragraphe introductif -->
          <?php if ($active) : ?>
            <p>
              <b><?= $title ?></b>, né<?= $gender['e'] ?> le <?= $depute['dateNaissanceFr'] ?> à <?= $depute['birthCity'] ?>, est <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
            </p>
          <?php else : ?>
            <p>
              <b><?= $title ?></b>, né<?= $gender['e'] ?> le <?= $depute['dateNaissanceFr'] ?> à <?= $depute['birthCity'] ?>, était un<?= $gender['e'] ?> député<?= $gender['e'] ?> de l'Assemblée nationale.
              Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $gender['pronom'] ?> a été élu<?= $gender['e'] ?> dans le département <?= $depute['dptLibelle2'] ?> <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
            </p>
          <?php endif; ?>
          <!-- Paragraphe historique -->
          <?php if ($active) : ?>
            <p>
              <?= ucfirst($gender['pronom']) ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?> et est en est à son <?= $mandat_edito ?> mandat.
              Au total, <?= $title ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale, soit <?= $history_edito ?> des députés, qui est de <?= $history_average['length'] ?> ans.
            </p>
          <?php elseif ($depute['legislature'] == legislature_current()) : ?>
            <p>
              Pour son dernier mandat, pendant la <?= legislature_current() ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
              <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
              Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
            </p>
          <?php else : ?>
            <p>
              Pour son dernier mendat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
              <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
              Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
            </p>
          <?php endif; ?>
          <!-- Paragraphe end -->
          <?php if ($active) : ?>
          <?php elseif ($depute['legislature'] == legislature_current()) : ?>
            <p>
              <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?>
              <?php if (strpos($depute['causeFin'], 'Nomination comme membre du Gouvernement') !== false) : ?>
                pour cause de nomination au Gouvernement.
              <?php elseif (strpos($depute['causeFin'], 'Décès') !== false) : ?>
                pour cause de décès.
              <?php elseif (strpos($depute['causeFin'], "Démission d'office sur décision du Conseil constitutionnel") !== false) : ?>
                pour cause de démission sur décision du Conseil constitutionnel.
              <?php elseif (strpos($depute['causeFin'], 'Démission') !== false) : ?>
                pour cause de démission.
              <?php elseif (strpos($depute['causeFin'], "Annulation de l'élection sur décision du Conseil constitutionnel") !== false) : ?>
                pour cause d'annulation de l'élection sur décision du Conseil constitutionnel.
              <?php elseif (strpos($depute['causeFin'], "Reprise de l'exercice du mandat d'un ancien membre du Gouvernement") !== false) : ?>
                . Remplaçant un député nommé au Gouvernement, <?= $title ?> a quitté l'Assemblée lorsque celui-ci est redevenu député.
              <?php else : ?>
                .
              <?php endif; ?>
            </p>
          <?php else : ?>
            <p>
              <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?>.
            </p>
          <?php endif; ?>
          <!-- Paragraphe groupe parlementaire -->
          <?php if ($active) : ?>
            <?php if ($depute['libelleAbrev'] == "NI") : ?>
              <p>
                À l'Assemblée nationale, <?= $title ?> n'est pas membre d'un groupe parlementaire, et siège donc en non-inscrit<?= $gender['e'] ?>.
              </p>
            <?php else : ?>
              <p>
                À l'Assemblée nationale, <?= $gender['pronom'] ?> siège avec le groupe <a href="<?= base_url() ?>groupes/<?= mb_strtolower($depute['libelleAbrev']) ?>"><?= $depute['libelle'] ?></a> (<?= $depute["libelleAbrev"] ?>), un groupe <b>classé <?= $infos_groupes[$depute['libelleAbrev']] ?></b> de l'échiquier politique.
                <?php if ($isGroupPresident) : ?><?= $title ?> en est le président.<?php endif; ?>
              </p>
            <?php endif; ?>
          <?php else : ?>
            <?php if (!empty($depute['libelle'])) : ?>
              <p>
                Au cours de son dernier mandat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a siégé avec le groupe <?= $depute['libelle'] ?> (<?= $depute['libelleAbrev'] ?>).
              </p>
            <?php endif; ?>
          <?php endif; ?>
          <!-- Paragraphe commission parlementaire -->
          <?php if ($active && !empty($commission_parlementaire)) : ?>
            <p><?= $title ?> est <?= mb_strtolower($commission_parlementaire['commissionCodeQualiteGender']) ?> de la <?= $commission_parlementaire['commissionLibelle'] ?>.</p>
          <?php endif; ?>
          <!-- Paragraphe parti politique -->
          <?php if ($politicalParty['libelle'] != "") : ?>
            <?php if ($active) : ?>
              <p>
                <?= $title ?> est rattaché<?= $gender['e'] ?> financièrement au parti politique <a href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($politicalParty['libelleAbrev']) ?>"><?= $politicalParty['libelle'] ?> (<?= $politicalParty['libelleAbrev'] ?>)</a>.
                Le rattachement permet aux partis politiques de recevoir, pour chaque député, une subvention publique.
              </p>
            <?php else : ?>
              <p>
                Quand <?= $gender['pronom'] ?> était <?= $gender['depute'] ?>, <?= $title ?> était rattaché<?= $gender['e'] ?> financièrement au parti politique <a href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($politicalParty['libelleAbrev']) ?>"><?= $politicalParty['libelle'] ?> (<?= $politicalParty['libelleAbrev'] ?>)</a>.
                Le rattachement permet aux partis politiques de recevoir, pour chaque député, une subvention publique.
              </p>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($depute['job'] && !in_array(mb_strtolower($depute['job']), $no_job)): ?>
            <p>
              Avant de devenir <?= $gender['depute'] ?>, <?= $title ?> exerçait le metier <b><?= mb_strtolower($depute['job']) ?></b>.
              Comme <?= round($famSocPro['pct']) ?>% des députés, <?= $gender['pronom'] ?> fait partie de la famille professionnelle <?= mb_strtolower($depute['famSocPro']) ?>.
              Pour en savoir plus sur l'origine social des parlementaires, <a href="<?= base_url() ?>statistiques">cliquez ici</a>.
            </p>
          <?php endif; ?>
        </div>
        <!-- BLOC POSITIONS CLEFS -->
        <?php if ($key_votes !== NULL) : ?>
          <div class="bloc-key-votes mt-5">
            <div class="row">
              <div class="col-12">
                <h2 class="mb-4">Ses positions importantes</h2>
                <div class="card">
                  <div class="card-body key-votes">
                    <?php if (isset($key_votes[3254])) : ?>
                      <div class="row">
                        <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                          <span class="sort-<?= $key_votes[3254]["vote_libelle"] ?>"><?= mb_strtoupper($key_votes[3254]["vote_libelle"]) ?></span>
                        </div>
                        <div class="col-md-9 value">
                          <?= $title ?><b>
                            <?php if ($key_votes[3254]["vote"] === "1") : ?>
                              a voté en faveur de
                            <?php elseif ($key_votes[3254]["vote"] === "-1") : ?>
                              a voté contre
                            <?php else : ?>
                              s'est abstenu<?= $gender["e"] ?> lors du vote sur
                            <?php endif; ?>
                            la proposition de loi Sécurité globale</b>.
                          <?= ucfirst($gender["le"]) ?> député<?= $gender["e"] ?> <?= $key_votes[3254]["loyaute"] === "1" ? "a été loyal" : "n'a pas été loyal" ?><?= $gender['e'] ?> a son groupe.
                          <a href="<?= base_url() ?>votes/legislature-15/vote_3254" class="font-italic">Voir le vote</a>
                        </div>
                      </div>
                    <?php endif; ?>
                    <?php if (isset($key_votes[2940])) : ?>
                      <div class="row">
                        <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                          <span class="sort-<?= $key_votes[2940]["vote_libelle"] ?>"><?= mb_strtoupper($key_votes[2940]["vote_libelle"]) ?></span>
                        </div>
                        <div class="col-md-9 value">
                          <?= $title ?> a voté <b>
                            <?php if ($key_votes[2940]["vote"] === "1") : ?>
                              a voté en faveur de
                            <?php elseif ($key_votes[2940]["vote"] === "-1") : ?>
                              a voté contre
                            <?php else : ?>
                              s'est abstenu<?= $gender["e"] ?> sur le vote concernant
                            <?php endif; ?>
                            la réintroduction des pesticides néonicotinoïdes</b> jusqu'en 2023</b>.
                          <?= ucfirst($gender['pronom']) ?> <?= $key_votes[2940]["loyaute"] === "1" ? "a été loyal" : "n'a pas été loyal" ?><?= $gender['e'] ?> a son groupe.
                          <a href="<?= base_url() ?>votes/legislature-15/vote_2940" class="font-italic">Voir le vote</a>
                        </div>
                      </div>
                    <?php endif; ?>
                    <?php if (isset($key_votes[2814])) : ?>
                      <div class="row">
                        <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                          <span class="sort-<?= $key_votes[2814]["vote_libelle"] ?>"><?= mb_strtoupper($key_votes[2814]["vote_libelle"]) ?></span>
                        </div>
                        <div class="col-md-9 value">
                          <?= $title ?> <b>
                            <?php if ($key_votes[2814]["vote"] === "1") : ?>
                              a accordé sa confiance
                            <?php elseif ($key_votes[2814]["vote"] === "-1") : ?>
                              n'a pas accordé sa confiance
                            <?php else : ?>
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
        <?php if (!empty($votes_datan)) : ?>
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
                <?php foreach ($votes_datan as $vote) : ?>
                  <div class="card card-vote">
                    <?php if ($vote['vote_depute'] == 'absent') : ?>
                      <div class="thumb absent d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <span>ABSENT<?= mb_strtoupper($gender['e']) ?></span>
                        </div>
                      </div>
                    <?php else : ?>
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
            <div class="row mt-2">
              <!-- BUTTONS BELOW -->
              <div class="col-12 d-flex justify-content-center">
                <div class="bloc-carousel-votes">
                  <div class="carousel-buttons">
                    <button type="button" class="btn prev mr-2 button--previous" aria-label="précédent">
                      <?php echo file_get_contents(asset_url() . "imgs/icons/arrow_left.svg") ?>
                    </button>
                    <button type="button" class="btn next ml-2 button--next" aria-label="suivant">
                      <?php echo file_get_contents(asset_url() . "imgs/icons/arrow_right.svg") ?>
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
            <div class="card-body pb-0">
              <?php if ($active) : ?>
                <p>
                  <?= $title ?> est <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
                </p>
              <?php else : ?>
                <p>
                  <?= $title ?> était <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
                </p>
              <?php endif; ?>
              <?php if ($election_canceled['cause']): ?>
                <p><?= $election_canceled['cause'] ?></p>
                <p>Pour découvrir les résultats des élection législatives partielles, organisées après l'invalidation par le Conseil constitutionnel, <span class="url_obf" url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Partielles/Legislatives") ?>">cliquez ici</span>.</p>
              <?php endif; ?>
              <?php if (isset($election_result)) : ?>
                <p>
                  <?= ucfirst($gender['pronom']) ?> a été élu<?= $gender['e'] ?> au <b><?= $election_result['tour_election'] ?> tour</b> avec <?= formatNumber($election_result['voix']) ?> voix, soit <?= round($election_result['pct_exprimes']) ?>% des suffrages exprimés.
                </p>
                <div class="chart-election mt-4">
                  <div class="majority d-flex align-items-center" style="flex-basis: <?= round($election_result['pct_exprimes']) ?>%">
                    <span><?= round($election_result['pct_exprimes']) ?>%</span>
                  </div>
                  <div class="line">
                  </div>
                  <div class="minority" style="flex-basis: <?= 100 - round($election_result['pct_exprimes']) ?>%">
                  </div>
                </div>
                <div class="legend d-flex justify-content-center mt-1">
                  <span>50%</span>
                </div>
                <h3 class="mt-4">L'élection de <?= $title ?> en détail</h3>
                <span class="subtitle"><?= $election_result['tour_election'] ?> tour des élections législatives de 2017 - <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] ?></span>
                <div class="row row-chart-election mt-4">
                  <div class="col-md-5 d-flex flex-column justify-content-center">
                    <p>Il y avait dans dans la circonscription <b><?= formatNumber($election_infos['inscrits']) ?> personnes inscrites</b> sur les listes électorales.</p>
                    <p>Pendant le <?= $election_result['tour_election'] ?> tour, le taux d'abstention était de <?= $election_infos['abstention_rate'] ?> %. Au niveau national, il était de 57 %.</p>
                    <p><?= $title ?> a été élu avec <?= formatNumber($election_result['voix']) ?> voix, soit <?= round($election_result['voix'] * 100 / $election_infos['inscrits']) ?>% des inscrits.</p>
                    <p>Plus d'information ? <span class="url_obf" url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Legislatives/elecresult__legislatives-2017/(path)/legislatives-2017/" . $election_infos['dpt_url'] . "/" . $election_infos['dpt_url'] . "" . $election_infos['circo_url'] .".html") ?>">Cliquez ici.</span></p>
                  </div>
                  <div class="col-md-7 p-0 d-flex">
                    <div class="bar-container election py-4 px-2 pr-md-1 pl-md-0" id="pattern_background">
                      <p class="text-center title">Le choix électoral des <u><?= formatNumber($election_infos['inscrits']) ?> inscrits</u></p>
                      <div class="chart">
                        <div class="bar-chart d-flex justify-content-between align-items-end">
                          <div class="bars mx-1 mx-md-3" style="height: <?= round($election_result['voix'] / $election_infos['inscrits'] * 100) ?>%">
                            <span class="score text-center"><?= formatNumber($election_result['voix']) ?></span>
                          </div>
                          <div class="bars mx-1 mx-md-3" style="height: <?= round($election_opponent['voix'] / $election_infos['inscrits'] * 100) ?>%">
                            <span class="score text-center"><?= formatNumber($election_opponent['voix']) ?></span>
                          </div>
                          <div class="bars mx-1 mx-md-3" style="height: <?= round(($election_infos['blancs'] + $election_infos['nuls']) / $election_infos['inscrits'] * 100) ?>%">
                            <span class="score text-center"><?= formatNumber($election_infos['blancs'] + $election_infos['nuls']) ?></span>
                          </div>
                          <div class="bars mx-1 mx-md-3" style="height: <?= round($election_infos['abstentions'] / $election_infos['inscrits'] * 100) ?>%">
                            <span class="score text-center"><?= formatNumber($election_infos['abstentions']) ?></span>
                          </div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between mt-2">
                        <div class="legend-element text-center mx-1"><?= $title ?></div>
                        <div class="legend-element text-center mx-1"><?= $election_opponent['candidat'] ?></div>
                        <div class="legend-element text-center mx-1">Blancs et nuls</div>
                        <div class="legend-element text-center mx-1">Abstentions</div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div><!-- // END BLOC ELECTION -->
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
                      <?php echo file_get_contents(base_url() . '/assets/imgs/icons/voting.svg') ?>
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
                          <p>
                            <?php if ($active) : ?>
                              Depuis sa prise de fonctions,
                            <?php else : ?>
                              Quand <?= $gender['pronom'] ?> était en activité à l'Assemblée,
                            <?php endif; ?>
                            <?= $title ?> a participé à <?= $participation['score'] ?> % des votes ayant un lien avec son domaine de spécialisation.
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "vote" : "votait" ?> donc <b><?= $edito_participation['phrase'] ?></b> que la moyenne des députés, qui est de <?= $participation['mean'] ?> %.
                          </p>
                          <p>
                            Ce score prend en compte les votes éléctroniques en séance publique sur les textes qui ont été examinés dans la commission du député. Ce sont sur ces textes que les élus sont susceptibles d'avoir un intérêt ou une expertise pariculière.
                          </p>
                        <?php else: ?>
                          <p>
                            Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a participé à <?= $participation['score'] ?> % des votes électroniques à l'Assemblée nationale.
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "vote" : "votait" ?> donc <b><?= $edito_participation['phrase'] ?></b> que la moyenne des députés, qui était de <?= $participation['mean'] ?> %.
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
                      <?php echo file_get_contents(base_url() . 'assets/imgs/icons/loyalty.svg') ?>
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
                            <?php if (!$active) : ?>Quand <?= $gender['pronom'] ?> était en activité, <?php endif; ?><?= $title ?> a voté sur la même ligne que son groupe politique dans <?= $loyaute['score'] ?>% des cas.
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> donc <b><?= $edito_loyaute['phrase'] ?><?= $gender['e'] ?></b> que la moyenne des députés, qui est de <?= $loyaute['mean'] ?>%.
                          </p>
                        <?php else: ?>
                            <p>
                              Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a voté sur la même ligne que son groupe politique dans <?= $loyaute['score'] ?>% des cas.
                            </p>
                            <p>
                              <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> donc <b><?= $edito_loyaute['phrase'] ?><?= $gender['e'] ?></b> que la moyenne des députés, qui est de <?= $loyaute['mean'] ?>%.
                            </p>
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
                        <?php echo file_get_contents(base_url() . '/assets/imgs/icons/elysee.svg') ?>
                      </div>
                      <h3 class="ml-3">PROXIMITÉ AVEC LA MAJORITÉ PRÉSIDENTIELLE
                        <a tabindex="0" role="button" data-toggle="popover" class="no-decoration popover_focus" data-trigger="focus" aria-label="Tooltip majorité" title="Proximité avec la majorité présidentielle" data-content="Le <b>taux de proximité avec la majorité présidentielle</b> représente le pourcentage de fois où un député vote la même chose que le groupe présidentiel (La République en Marche).<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
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
                              <?= $title ?> a voté comme la majoité présientielle (<a href="<?= base_url() ?>groupes/larem">La République en Marche</a>) dans <?= $majorite['score'] ?> % des cas.
                            </p>
                            <p>
                              <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite ?></b> de la majorité présidentielle que la moyenne des députés n'y appartenant pas, qui est de <?= $majorite['mean'] ?> %.
                            </p>
                          <?php else: ?>
                            <p>
                              Pendant la <?= $depute['legislature'] ?><sup></sup> législature, <?= $title ?> a voté comme le groupe de la majorité présidentielle dans <?= $majorite['score'] ?> % des cas.
                            </p>
                            <p>
                              <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite ?></b> de la majorité présidentielle que la moyenne des députés, qui est de <?= $majorite['mean'] ?> %.
                            </p>
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
                      <?php echo file_get_contents(base_url() . '/assets/imgs/icons/group.svg') ?>
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
                            classé <?= $proximite["first1"]["ideologiePolitique"] ?> de l'échiquier politique.
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
                            classé <?= $proximite["last1"]["ideologiePolitique"] ?> de l'échiquier politique.
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
              Cette page publie les statistiques de <?= $title ?> pour sa législature la plus récente (<?= $depute['legislature'] ?><sup>ème</sup> législature). Nous publions sur Datan l'historique de tous les mandats depuis la 14<sup>ème</sup> législature.
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
                  <?php echo file_get_contents(base_url() . '/assets/imgs/logos/facebook_svg.svg') ?>
                  <span class="ml-3">Profil Facebook</span>
                </span>
              </div>
            <?php endif; ?>
            <?php if ($depute['twitter'] !== NULL) : ?>
              <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                <span class="url_obf btn btn-twitter d-flex align-items-center justify-content-center" url_obf="<?= url_obfuscation("https://twitter.com/" . $depute['twitter']) ?>">
                  <?php echo file_get_contents(base_url() . '/assets/imgs/logos/twitter_svg.svg') ?>
                  <span class="ml-3">Profil Twitter</span>
                </span>
              </div>
            <?php endif; ?>
          </div>
        </div> <!-- END BLOC SOCIAL MEDIA -->
        <!-- BLOC CONTACT -->
        <?php if ($depute['mailAn'] !== NULL && $active) : ?>
          <div class="bloc-links p-lg-0 p-md-2 mt-5">
            <h2>Contacter <?= $title ?></h2>
            <div class="row mt-4">
              <div class="col-12">
                <span class="mr-4">
                  <?php echo file_get_contents(base_url() . '/assets/imgs/icons/envelope-fill.svg') ?>
                </span>
                <a href="mailto:<?= $depute['mailAn'] ?>" class="no-decoration underline text-dark"><?= $depute['mailAn'] ?></a>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <!-- END BLOC SOCIAL MEDIA -->
      </div>
    </div>
  </div> <!-- END CONTAINER -->
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid pg-depute-individual bloc-others-container">
    <div class="container bloc-others">
      <?php if (isset($other_deputes)) : ?>
        <div class="row mb-5">
          <div class="col-12">
            <?php if ($depute['legislature'] != legislature_current()) : ?>
              <h2>Les autres députés de la <?= $depute['legislature'] ?><sup>e</sup> législature</h2>
            <?php elseif ($active) : ?>
              <h2>Les autres députés <?= $depute['libelle'] ?> (<?= $depute['libelleAbrev'] ?>)</h2>
            <?php else : ?>
              <h2>Les autres députés plus en activité</h2>
            <?php endif; ?>
            <div class="row mt-3">
              <?php foreach ($other_deputes as $mp) : ?>
                <div class="col-6 col-md-3 py-2">
                  <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'] . ' ' . $mp['nameLast'] ?></a>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-3">
              <?php if ($depute['legislature'] != legislature_current()) : ?>
                <a href="<?= base_url(); ?>deputes/legislature-<?= $depute['legislature'] ?>">Tous les députés de la législature <?= $depute['legislature'] ?></a>
              <?php elseif ($active) : ?>
                <a href="<?= base_url() ?>groupes/<?= mb_strtolower($depute['libelleAbrev']) ?>">Voir tous les députés membres du groupe <?= $depute['libelle'] ?> (<?= $depute['libelleAbrev'] ?>)</a>
              <?php else : ?>
                <a href="<?= base_url(); ?>deputes/inactifs">Tous les députés plus en activité</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="row">
        <div class="col-12">
          <h2>Les députés en activité du département <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></h2>
          <div class="row mt-3">
            <?php foreach ($other_deputes_dpt as $mp) : ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'] . ' ' . $mp['nameLast'] ?></a>
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
