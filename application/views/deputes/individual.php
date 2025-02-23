  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  <?php if (!empty($depute['couleurAssociee'])) : ?>
    <div class="liseret-groupe" style="background-color: <?= $depute['couleurAssociee'] ?>"></div>
  <?php endif; ?>
  <div class="container pg-depute-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0">
        <div class="sticky-top" style="margin-top: -150px; top: 80px;">
          <?php $this->load->view('deputes/partials/card_individual.php', array('historique' => FALSE, 'last_legislature' => $depute['legislature'], 'legislature' => $depute['legislature'], 'tag' => 'h1')) ?>
        </div>
      </div> <!-- END COL -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5">
        <?php $this->view('deputes/partials/voteFeature.php') ?>
        <div class="bloc-bio mt-5">
          <!-- For critical css -->
          <div class="card card-election-feature not-candidate d-none"></div>
          <div class="card card-election-feature candidate d-none"></div>
          <h2 class="mb-4 title-center">Qui est-<?= ($gender['pronom']) ?> ?</h2>
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
              <?= ucfirst($gender['pronom']) ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?> et en est à son <?= $mandat_edito ?> mandat.
              Au total, <?= $title ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale, soit <?= $history_edito ?> des députés, qui est de <?= $history_average ?> ans.
            </p>
          <?php elseif ($depute['legislature'] == legislature_current()) : ?>
            <p>
              Pour son dernier mandat, pendant la <?= legislature_current() ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
              <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
              Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
            </p>
          <?php else : ?>
            <p>
              Pour son dernier mandat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
              <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
              Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
            </p>
          <?php endif; ?>
          <!-- Paragraphe end -->
          <?php if(!$active): ?>
            <?php if ($depute['legislature'] == legislature_current()) : ?>
              <p>
                <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?><?= $this->depute_edito->get_end_mandate($depute) ?>.
              </p>
            <?php else : ?>
              <p>
                <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?>.
              </p>
            <?php endif; ?>
          <?php endif; ?>
          <!-- Paragraphe groupe parlementaire -->
          <?php if ($active) : ?>
            <?php if ($depute['libelleAbrev'] == "NI") : ?>
              <p>
                À l'Assemblée nationale, <?= $title ?> n'est pas membre d'un groupe parlementaire, et siège donc en non-inscrit<?= $gender['e'] ?>.
              </p>
            <?php else : ?>
              <p>
                À l'Assemblée, <?= $title ?> siège avec le groupe <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($depute['libelleAbrev']) ?>"><?= name_group($depute['libelle']) ?></a> (<?= $depute["libelleAbrev"] ?>), un groupe <b>classé <?= $infos_groupes[$depute['libelleAbrev']]['edited'] ?></b> de l'échiquier politique.
                <?php if ($depute['preseanceGroupe'] == 24): ?><?= $title  ?> n'est pas membre mais <b>apparenté<?= $gender['e'] ?></b> au groupe <?= $depute['libelleAbrev'] ?> : <?= $gender['pronom'] ?> est donc associé<?= $gender['e'] ?> au groupe tout en gardant une marge de liberté.<?php endif; ?>
                <?php if ($isGroupPresident) : ?><?= $title ?> en est <?= $gender['le'] ?> <b>président<?= $gender['e'] ?></b>.<?php endif; ?>
              </p>
            <?php endif; ?>
          <?php else : ?>
            <?php if (!empty($depute['libelle'])) : ?>
              <p>
                Au cours de son dernier mandat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a siégé avec le groupe <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>).
              </p>
            <?php endif; ?>
          <?php endif; ?>
          <!-- Paragraphe commission parlementaire -->
          <?php if ($active && !empty($commission_parlementaire)) : ?>
            <p><?= $title ?> est <?= mb_strtolower($commission_parlementaire['commissionCodeQualiteGender']) ?> de la <?= $commission_parlementaire['commissionLibelle'] ?>.</p>
          <?php endif; ?>
          <!-- Paragraphe parti politique -->
          <?php if ($politicalParty && $politicalParty['libelle'] != "") : ?>
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
              <?php if($famSocPro !== null): ?>
                Comme <?= round($famSocPro['pct']) ?>% des députés, <?= $gender['pronom'] ?> fait partie de la famille professionnelle <?= mb_strtolower($depute['famSocPro']) ?>.
              <?php endif; ?>
              Pour en savoir plus sur l'origine sociale des parlementaires, <a href="<?= base_url() ?>statistiques">cliquez ici</a>.
            </p>
            <?php if ($hatvpJobs): ?>
              <p>
                Certains députés ne déclarent pas leur dernière activité professionnelle mais un métier exercé il y a plusieurs années. La <span class="url_obf" url_obf="<?= url_obfuscation("https://www.hatvp.fr/") ?>">Haute Autorité pour la transparence de la vie publique</span> publie au contraire les dernier métier des élus.
                Pour découvrir les dernières activités de <?= $title ?>, <a href="#modalHatvp" data-toggle="modal" data-target="#modalHatvp">cliquez ici</a>.
              </p>
              <!-- modalHatvp -->
              <div class="modal fade modalDatan" id="modalHatvp" tabindex="-1" role="dialog" aria-labelledby="modalHatvpLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <span class="modal-title" id="modalHatvpLabel">Les dernières activités professionnelles de <?= $title ?></span>
                      <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </span>
                    </div>
                    <div class="modal-body">
                      <table class="table table-sm mt-3">
                        <thead>
                          <tr>
                            <th scope="col">Métier</th>
                            <th scope="col" class="text-center">Organisation</th>
                            <th scope="col" class="text-center">Début</th>
                            <th scope="col" class="text-center">Fin</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($hatvpJobs as $job): ?>
                            <tr>
                              <td><?= ucfirst(strtolower($job['value'])) ?></td>
                              <td class="text-center"><?= ucfirst(strtolower($job['employeur'])) ?></td>
                              <td class="text-center"><?= $job['dateDebut'] ?></td>
                              <td class="text-center"><?= $job['dateFin'] ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                      <p class="mt-4 source">Ces données viennent de la déclaration de <?= $title ?> à la <span class="url_obf" url_obf="<?= url_obfuscation("https://www.hatvp.fr/") ?>">Haute Autorité pour la transparence de la vie publique</span> (HATVP).</p>
                      <p class="source">Pour découvrir la déclaration de <?= $title ?>, <span class="url_obf" url_obf="<?= url_obfuscation($depute['hatvp']) ?>">cliquez ici</span>.</p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($depute['mailAn'] !== NULL && $active): ?>
            <div class="d-flex justify-content-center mt-5">
              <a href="mailto:<?= $depute['mailAn'] ?>" class="btn btn-primary">
                <?= file_get_contents(asset_url() . "imgs/icons/envelope.svg") ?>
                <span class="ml-2">Contacter <?= $title ?></span>
              </a>
            </div>
          <?php endif; ?>
        </div>
        <!-- BLOC POSITIONS CLEFS -->
        <?php if ($key_votes) : ?>
          <div class="bloc-key-votes mt-5">
            <div class="row">
              <div class="col-12">
                <h2 class="mb-4 title-center">Ses positions importantes</h2>
                <div class="card">
                  <div class="card-body key-votes">
                    <?php foreach ($key_votes as $key => $value): ?>
                      <div class="row">
                        <div class="col-md-3 libelle d-flex align-items-center justify-content-md-center">
                          <span class="sort-<?= $value["vote_libelle"] ?>"><?= mb_strtoupper($value["vote_libelle"]) ?></span>
                        </div>
                        <div class="col-md-9 value">
                          <?= $title ?><b>
                            <?php if ($value['vote'] === "1") : ?>
                              a voté en faveur de
                            <?php elseif ($value['vote'] === "-1") : ?>
                              a voté contre
                            <?php else : ?>
                              s'est abstenu<?= $gender['e'] ?> sur le vote concernant
                            <?php endif; ?>
                            <?= $value['text'] ?></b>.
                          <?= ucfirst($gender["pronom"]) ?> <?= $value["scoreLoyaute"] === "1" ? "a voté " : "n'a pas voté " ?>comme son groupe.
                          <a href="<?= base_url() ?>votes/legislature-16/vote_<?= $value['voteNumero'] ?>" class="font-italic">Voir le vote</a>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <!-- BLOC VOTES -->
        <?php if (!empty($votes_datan)) : ?>
          <div class="bloc-votes carousel-container mt-5">
            <div class="row">
              <div class="col-12">
                <div class="d-flex justify-content-between mb-4">
                  <h2 class="title-center">Ses derniers votes</h2>
                  <div class="bloc-carousel-votes">
                    <a class="btn see-all-votes mx-2" href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>/votes">
                      <span>VOIR TOUS</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <div class="row bloc-carousel-votes-flickity">
              <div class="col-12 carousel-cards">
                <?php foreach ($votes_datan as $vote) : ?>
                  <?php $this->load->view('deputes/partials/card_vote.php', array('vote' => $vote)) ?>
                <?php endforeach; ?>
                <div class="card card-vote see-all">
                  <div class="card-body d-flex align-items-center justify-content-center">
                    <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>/votes" class="stretched-link no-decoration">VOIR TOUS</a>
                  </div>
                </div>
              </div>
            </div> <!-- // END BLOC VOTES -->
            <div class="row">
              <!-- BUTTONS BELOW -->
              <div class="carousel-buttons col-12 d-flex justify-content-center">
                <button type="button" class="btn prev mr-2 carousel--prev" aria-label="précédent">
                  <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
                </button>
                <button type="button" class="btn next ml-2 carousel--next" aria-label="suivant">
                  <?= file_get_contents(asset_url()."imgs/icons/arrow_right.svg") ?>
                </button>
              </div>
            </div>
          </div> <!-- // END BLOC VOTES -->
        <?php endif; ?>
        <!-- BLOC EXPLICATION -->
        <?php if ($explication): ?>
          <div class="bloc-explication mt-5">
            <h2 class="mb-4 title-center">Sa dernière explication de vote</h2>
            <div class="card border-primary">
              <div class="card-body">
                <p class="title mb-1">
                  <a class="no-decoration underline" href="<?= base_url() ?>votes/legislature-<?= $explication['legislature'] ?>/vote_<?= is_congress_numero($explication['voteNumero']) ?>"><?= $explication['title'] ?></a>
                </p>
                <p class="date mb-4">Scrutin du <?= $explication['dateScrutinFR'] ?></p>
                <p class="mb-2">
                  <span class="badge badge-<?= mb_strtolower($explication['vote_depute']) ?>"><?= mb_strtoupper($explication['vote_depute']) ?></span>
                </p>
                <p>
                  <?= ucfirst($gender['le']) ?> <?= $gender['depute'] ?> <span class="font-weight-bold"><?= $title ?></span> <?= $explication['vote_depute_edito'] ?> ce vote.
                  Découvrez son explication.
                </p>
                <p class="quoted"><?= $explication['text'] ?></p>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <!-- BLOC ELECTION -->
        <div class="bloc-election mt-5">
          <h2 class="mb-4 title-center">Son élection</h2>
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
              <?php if ($election_canceled && $election_canceled['cause']): ?>
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
                <span class="subtitle"><?= $election_result['tour_election'] ?> tour des élections législatives de 2024 - <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] ?></span>
                <div class="d-flex flex-column mt-4">
                  <div class="d-flex flex-column justify-content-center">
                    <p>Il y avait dans  la circonscription <b><?= formatNumber($election_infos['inscrits']) ?> personnes inscrites</b> sur les listes électorales.</p>
                    <p>Pendant le <?= $election_result['tour_election'] ?> tour, le taux de participation était de <?= $election_infos['participation'] ?>%. Au niveau national, il était de <?= $election_result['tour'] == 1 ? 67 : 67 ?>%.</p>
                    <p><?= $title ?> a été élu<?= $gender['e'] ?> avec <?= formatNumber($election_result['voix']) ?> voix, soit <?= round($election_result['voix'] * 100 / $election_infos['inscrits']) ?>% des inscrits.</p>
                    <p>Plus d'information ? <span class="url_obf" url_obf="<?= url_obfuscation("https://www.resultats-elections.interieur.gouv.fr/legislatives2024/ensemble_geographique/index.html") ?>">Cliquez ici.</span></p>
                  </div>
                  <div class="bar-container stats election mt-3 p-3" id="pattern_background">
                    <p class="text-center title mb-0">Le choix électoral des <u><?= formatNumber($election_infos['inscrits']) ?> inscrits</u></p>
                    <div class="chart">
                      <div class="bar-chart d-flex justify-content-between align-items-end">
                        <div class="bars mx-1 mx-md-3" style="height: <?= round($election_result['voix'] / $election_infos['inscrits'] * 100) ?>%">
                          <span class="score text-center"><?= formatNumber($election_result['voix']) ?></span>
                        </div>
                        <?php if (isset($election_opponents)): ?>
                          <?php foreach ($election_opponents as $opponent): ?>
                            <div class="bars mx-1 mx-md-3" style="height: <?= round($opponent['voix'] / $election_infos['inscrits'] * 100) ?>%">
                              <span class="score text-center"><?= formatNumber($opponent['voix']) ?></span>
                            </div>
                          <?php endforeach; ?>
                        <?php endif; ?>
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
                      <?php if (isset($election_opponents)): ?>
                        <?php foreach ($election_opponents as $opponent): ?>
                          <div class="legend-element text-center mx-1"><?= $opponent['candidat'] ?></div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                      <div class="legend-element text-center mx-1">Blancs et nuls</div>
                      <div class="legend-element text-center mx-1">Abstentions</div>
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
            <h2 class="mb-3 title-center">
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
                    <h3 class="ml-3 text-uppercase">Participation aux votes
                      <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" aria-label="Tooltip participation" class="no-decoration popover_focus" title="Taux de participation" data-content="Le taux de participation est le <b>pourcentage de votes auxquels le ou la député a participé</b>.<br><br>Attention, le taux de participation ne mesure pas toute l'activité d'un député ou d'un groupe. Contrairement au <a href='https://www.europarl.europa.eu/about-parliament/fr/organisation-and-rules/how-plenary-works' title='lien'>Parlement européen</a>, les votes à l'Assemblée nationale se déroulent à n'importe quel moment de la semaine. D'autres réunions ont souvent lieu en même temps, expliquant le faible taux de participation des députés et des groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#participation' target='_blank'>cliquez ici</a>."><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
                    </h3>
                  </div>
                </div>
                <div class="row">
                  <?php if ($no_participation) : ?>
                    <div class="col-12 mt-2">
                      <p>Il n'y a pas encore eu suffisamment de votes solennels à l'Assemblée nationale pour afficher cette statistique. Pour consulter la participation des députés à l'ensemble des scrutins, <a href="<?= base_url() ?>statistiques/deputes-participation">cliquez ici</a>.</p>
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
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['all'] ?></b> que la moyenne des députés, qui est <?= $edito_participation['all'] == "autant" ? "également" : "" ?> de <?= $participation['all'] ?>%.
                          </p>
                          <?php if ($participation['group']): ?>
                            <p>
                              De plus, <?= $title ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['group'] ?></b> que la moyenne des députés de son groupe politique, qui est <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <?= $participation['group'] ?>%.
                            </p>
                          <?php endif; ?>
                        <?php else: ?>
                          <!-- Paragraph for MP from older legislatures -->
                          <p>
                            Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a participé à <?= $participation['score'] ?>% des votes solennels à l'Assemblée nationale.
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['all'] ?></b> que la moyenne des députés, qui était <?= $edito_participation['all'] == "autant" ? "également" : "" ?> de <?= $participation['all'] ?>%.
                          </p>
                          <?php if ($participation['group']): ?>
                            <p>
                              De plus, <?= $title ?> <?= $active ? "vote" : "votait" ?> <b><?= $edito_participation['group'] ?></b> que la moyenne des députés de son groupe politique, qui était <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <?= $participation['group'] ?>%.
                            </p>
                          <?php endif; ?>
                        <?php endif; ?>
                        <p>
                           Les votes solennels sont les votes considérés comme importants pour lesquels les députés connaissent à l'avance le jour et l'heure du vote.
                        </p>
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
                    <h3 class="ml-3 text-uppercase">Proximité avec son groupe
                      <a tabindex="0" role="button" data-toggle="popover" class="no-decoration popover_focus" data-trigger="focus" aria-label="Tooltip loyauté" title="Proximité ou loyauté envers le groupe politique" data-content="Le taux de proximité est le <b>pourcentage de votes où le ou la député a voté sur la même ligne que son groupe</b>.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les taux de loyauté des autres parlementaires.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#loyalty' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
                    </h3>
                  </div>
                </div>
                <div class="row">
                  <?php if ($no_loyaute) : ?>
                    <div class="col-12 mt-2">
                      <p>Cette statistique sera disponible dès qu'un nombre suffisant de votes sera atteint pour <?= $title ?>.</p>
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
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "a" : "avait" ?> un taux de proximité avec son groupe <b><?= $edito_loyaute['all'] ?></b> que la moyenne des députés, qui est <?= $edito_loyaute['all'] == "aussi élevé" ? "également" : "" ?> de <?= $loyaute['all'] ?>%.
                          </p>
                          <?php if ($loyaute['group']): ?>
                            <p>
                              De plus, son taux de proximité <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['group'] ?></b> que la moyenne des députés de son groupe, qui est de <?= $loyaute['group'] ?>%.
                            </p>
                          <?php endif; ?>
                        <?php else: ?>
                            <p>
                              Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a voté sur la même ligne que son groupe politique dans <?= $loyaute['score'] ?>% des cas.
                            </p>
                            <p>
                              <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['all'] ?><?= $gender['e'] ?></b> que la moyenne des députés, qui était <?= $edito_loyaute['all'] == "aussi loyal" ? "également" : "" ?> de <?= $loyaute['all'] ?>.
                            </p>
                            <?php if ($loyaute['group']): ?>
                              <p>
                                De plus, <?= $title ?> <?= $active ? "est" : "était" ?> <b><?= $edito_loyaute['group'] ?></b> que la moyenne des députés de son groupe politique, qui était <?= $edito_participation['group'] == "autant" ? "également" : "" ?> de <?= $loyaute['group'] ?>%.
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
                                  <th scope="col" class="text-center">Loyauté</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php foreach ($loyaute_history as $y) : ?>
                                  <tr>
                                    <td>
                                      <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($y['libelleAbrev']) ?>" class="no-decoration underline"><?= name_group($y['libelle']) ?></a>
                                    </td>
                                    <td class="text-center"><?= $y['score'] ?>%</td>
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
            <?php if (!in_array($depute['groupeId'], $this->groupes_model->get_all_groupes_majority()) && $depute['legislature'] != 17): ?>
              <div class="card card-statistiques my-4">
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 d-flex flex-row align-items-center">
                      <div class="icon">
                        <?= file_get_contents(base_url() . '/assets/imgs/icons/elysee.svg') ?>
                      </div>
                      <h3 class="ml-3 text-uppercase">Proximité avec la majorité gouvernementale
                        <a tabindex="0" role="button" data-toggle="popover" class="no-decoration popover_focus" data-trigger="focus" aria-label="Tooltip majorité" title="Proximité avec la majorité gouvernementale" data-content="Le <b>taux de proximité avec la majorité gouvernementale</b> représente le pourcentage de fois où un député vote la même chose que le groupe présidentiel (La République en Marche).<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
                      </h3>
                    </div>
                  </div>
                  <div class="row">
                    <?php if ($no_majorite) : ?>
                      <div class="col-12 mt-2">
                        <p>Cette statistique sera disponible dès qu'un nombre suffisant de votes sera atteint pour <?= $title ?>.</p>
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
                          <p>
                            <?= $title ?> a voté comme la majorité gouvernementale dans <?= $majorite['score'] ?>% des cas. Les votes de <?= $title ?> sont comparés à ceux du groupe politique le plus gros de la majorité (<a href="<?= base_url() ?>groupes/legislature-<?= $groupMajority['legislature'] ?>/<?= mb_strtolower($groupMajority['libelleAbrev']) ?>"><?= name_group($groupMajority['libelle']) ?></a>).
                          </p>
                          <p>
                            <?= ucfirst($gender['pronom']) ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite['all'] ?></b> de la majorité gouvernementale que la moyenne des députés non membres de la majorité (<?= $majorite['all'] ?>%).
                          </p>
                          <?php if ($majorite['group']): ?>
                            <p>
                              De plus, <?= $title ?> <?= $active ? "est" : "était" ?> <b><?= $edito_majorite['group'] ?></b> de la majorité gouvernementale que la moyenne des députés de son groupe politique (<?= $majorite['group'] ?>%).
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
                      <?= file_get_contents(base_url() . '/assets/imgs/icons/group.svg') ?>
                    </div>
                  </div>
                  <div class="col-10">
                    <h3 class="text-uppercase">Proximité avec les groupes politiques 
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
                  <?php if ($depute['legislature'] == legislature_current() && dissolution() === false): ?>
                    <div class="row mt-3">
                      <div class="offset-2 col-10">
                        
                          <p>
                            <?php if ($group['libelleAbrev'] != "NI") : ?>
                              En plus de son propre groupe,
                            <?php else : ?>
                              Le
                            <?php endif; ?>
                            <b><?= $title ?></b> <?= $active ? "vote" : "votait" ?> souvent (dans <?= $proximite["first1"]["accord"] ?>% des cas) avec le groupe <a href="<?= base_url() ?>groupes/legislature-<?= $depute["legislature"] ?>/<?= mb_strtolower($proximite["first1"]["libelleAbrev"]) ?>"><?= $proximite["first1"]["libelleAbrev"] ?></a>, <?= $proximite["first1"]["maj_pres"] ?>
                            <?php if ($proximite["first1"]["libelleAbrev"] != "NI") : ?>
                              classé <?= $proximite["first1"]["ideologiePolitique"]["edited"] ?> de l'échiquier politique.
                            <?php endif; ?>
                          </p>
                      </div>
                    </div>
                  <?php endif; ?>
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
                  <?php if ($depute['legislature'] == legislature_current() && dissolution() === false): ?>
                    <div class="row mt-3">
                      <div class="col-10 offset-2 ">
                        
                          <p>
                            À l'opposé, le groupe avec lequel <?= $title; ?> <?= $active ? "est" : "était" ?> le moins proche est <a href="<?= base_url() ?>groupes/legislature-<?= $depute["legislature"] ?>/<?= mb_strtolower($proximite["last1"]["libelleAbrev"]) ?>"><?= $proximite["last1"]["libelle"] ?></a>, <?= $proximite["last1"]["maj_pres"] ?>
                            <?php if ($proximite["last1"]["libelleAbrev"] != "NI") : ?>
                              classé <?= $proximite["last1"]["ideologiePolitique"]["edited"] ?> de l'échiquier politique.
                            <?php endif; ?>
                            <?= ucfirst($gender["pronom"]) ?> <?= $active ? "ne vote" : "n'a voté" ?> avec ce groupe que dans <b><?= $proximite["last1"]["accord"] ?>%</b> des cas.
                          </p>
                      </div>
                    </div>
                  <?php endif; ?>
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
                                <td><?= name_group($group['libelle']) ?> (<?= $group['libelleAbrev'] ?>)</td>
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
                  <p>Cette statistique sera disponible dès qu'un nombre suffisant de votes sera atteint pour <?= $title ?>.</p>
                <?php endif; ?>
              </div>
            </div> <!-- // END BLOC PROXIMITY -->
          </div> <!-- // END BLOC STATISTIQUES -->
        <?php endif; ?>
        <!-- BLOC ELECTIONS -->
        <?php if ($elections): ?>
          <div class="bloc-elections-history mt-5">
            <h2 class="mb-4 title-center">Ses participations électorales</h2>
            <p>
              <?= $title ?> a été candidat<?= $gender['e'] ?> <?= count($elections) > 1 ? 'à plusieurs élections' : 'à une élection' ?> alors qu'<?= $gender['pronom'] ?> était député<?= $gender['e'] ?>.
            </p>
            <table class="table">
              <tbody>
                <?php foreach ($elections as $election): ?>
                  <tr>
                    <td class="font-weight-bold"><?= $election['dateYear'] ?></td>
                    <td><?= $election['libelle'] ?></td>
                    <td><?= $election['district']['libelle'] ?></td>
                    <td class="font-weight-bold sort-<?= $election['electedColor'] ?>"><?= $election['electedLibelle'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
        <!-- BLOG PROFESSIONS DE FOI -->
        <?php if ($professions_foi): ?>
          <div class="mt-5">
            <h2 class="mb-4 title-center">Ses professions de foi</h2>
            <p>Une <span class="font-weight-bold text-primary">profession de foi</span> est un document rédigé par un candidat à une élection. Dans ce document, le candidat se présente et expose ses idées et son programme qu'il ou elle souhaite défendre et mettre en place en cas d'élection. Avec une profession de foi, les candidats tentent de se démarquer des autres candidats, aussi bien sur le fond que la forme.</p>
            <p>En France, les professions de foi sont envoyées par courrier au domicile des personnes inscrites sur les listes électorales.</p>
            <table class="table table-bordered mt-4">
              <thead class="thead-dark">
                <th>Élection</th>
                <th></th>
                <th></th>
              </thead>
              <tbody>
                <?php foreach ($professions_foi as $key => $value): ?>
                  <tr class="bg-white">
                    <td class="font-weight-bold align-middle"><?= $value['election'] ?></td>
                    <td class="text-center align-middle">
                      <?php if ($value['round1']): ?>
                        <a class="btn btn-outline-primary" href="<?= $value['round1'] ?>" target="_blank">
                          <?= file_get_contents(base_url().'/assets/imgs/icons/arrow_external_right.svg') ?>
                          Profession 1er tour
                        </a>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <?php if ($value['round2']): ?>
                        <a class="btn btn-outline-primary" href="<?= $value['round2'] ?>" target="_blank">
                          <?= file_get_contents(base_url().'/assets/imgs/icons/arrow_external_right.svg') ?>
                          Profession 2nd tour
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
        <!-- BLOC PARRAINAGES -->
        <?php if ($parrainage): ?>
          <div class="mt-5 bloc-elections-history">
            <h2 class="mb-4 title-center">Ses parrainages présidentiels</h2>
            <p>
              <?= $title ?> a déjà parrainé un candidat à l'élection présidentiel pendant son mandat de député<?= $gender['e'] ?>.
              <table class="table">
                <tbody>
                  <tr>
                    <td class="font-weight-bold">Élection présidentielle 2024</td>
                    <td>Parrainagé accordé à <b><?= $parrainage['candidat'] ?></b></td>
                  </tr>
                </tbody>
              </table>
            </p>
          </div>
        <?php endif; ?>
        <!-- BLOC PARTAGEZ -->
        <div class="bloc-social mt-5">
          <h2 class="title-center mb-4">Partagez cette page</h2>
          <?php $this->load->view('partials/share.php') ?>
        </div>
        <!-- BLOC HISTORIQUE MANDATS -->
        <div class="bloc-mandats mt-5">
          <h2 class="mb-4 title-center">Historique des mandats</h2>
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
          <h2 class="title-center">Suivez l'action de <?= $title ?></h2>
          <div class="row mt-4">
            <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
              <span class="url_obf btn btn-an" url_obf="<?= url_obfuscation("http://www2.assemblee-nationale.fr/deputes/fiche/OMC_" . $depute['mpId']) ?>">Profil officiel</span>
            </div>
            <?php if ($depute['website'] !== NULL) : ?>
              <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
                <span class="url_obf btn btn-website" url_obf="<?= url_obfuscation("https://" . $depute['website']) ?>">Site internet</span>
              </div>
            <?php endif; ?>
            <?php if ($depute['facebook'] !== NULL) : ?>
              <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
                <span class="url_obf btn btn-fcb" url_obf="<?= url_obfuscation("https://www.facebook.com/" . $depute['facebook']) ?>">
                  <?= file_get_contents(base_url() . '/assets/imgs/logos/facebook_svg.svg') ?>
                  <span class="ml-3">Facebook</span>
                </span>
              </div>
            <?php endif; ?>
            <?php if ($depute['twitter'] !== NULL) : ?>
              <div class="col-12 col-sm-4 mt-2 d-flex justify-content-center align-items-center">
                <span class="url_obf btn btn-twitter" url_obf="<?= url_obfuscation("https://twitter.com/" . ltrim($depute['twitter'], '@')) ?>">
                  <?= file_get_contents(base_url() . '/assets/imgs/logos/twitter_svg.svg') ?>
                  <span class="ml-3">Twitter</span>
                </span>
              </div>
            <?php endif; ?>
          </div>
        </div> <!-- END BLOC SOCIAL MEDIA -->
        <!-- BLOC CONTACT -->
        <?php if ($depute['mailAn'] !== NULL && $active) : ?>
          <div class="bloc-links p-lg-0 p-md-2 mt-5">
            <h2 class="title-center">Contactez <?= $title ?></h2>
            <div class="row mt-4">
              <div class="col-12">
                <span class="mr-4">
                  <?= file_get_contents(base_url() . '/assets/imgs/icons/envelope-fill.svg') ?>
                </span>
                <a href="mailto:<?= $depute['mailAn'] ?>" class="no-decoration underline text-dark"><?= $depute['mailAn'] ?></a>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <!-- END BLOC SOCIAL MEDIA -->
      </div>
    </div>
  </div> <!-- END ROW -->
</div> <!-- END CONTAINER -->
  <!-- CONTAINER FOLLOW US -->
  <?php $this->load->view('partials/follow-us.php') ?>
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid pg-depute-individual bloc-others-container">
    <div class="container bloc-others">
      <?php if (isset($other_deputes)) : ?>
        <div class="row mb-5">
          <div class="col-12">
            <?php if ($depute['legislature'] != legislature_current()) : ?>
              <h2>Les autres députés de la <?= $depute['legislature'] ?><sup>e</sup> législature</h2>
            <?php elseif ($active) : ?>
                <h2>Les autres députés <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>)</h2>
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
                <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($depute['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>)</a>
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
  <!-- EXPLICATIONS DE VOTE -->
  <?php if (is_iterable($votes_datan)): ?>
    <?php foreach ($votes_datan as $key => $value): ?>
      <?php if ($value['explication']): ?>
        <!-- Modal explain -->
        <?php $this->load->view('votes/modals/explain.php', array('id' => 'explication-l' . $value['legislature'] . '-v' . $value['voteNumero'], 'title' => "L'avis de " . $title, 'value' => $value, 'vote_titre' => $value['vote_titre'], 'explication' => $value['explication'], 'img' => $depute['idImage'])) ?>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>
