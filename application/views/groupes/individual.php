<div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg" style="height: 13em">
</div>
<?php if (!empty($groupe['couleurAssociee'])): ?>
  <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
<?php endif; ?>
<div class="container pg-groupe-individual">
  <div class="row">
    <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4">
      <div class="sticky-top" style="margin-top: -110px; top: 110px;">
        <div class="card card-profile">
          <div class="card-body">
            <!-- IMAGE MP -->
            <div class="img">
              <div class="d-flex justify-content-center">
                <div class="">
                  <img src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" alt="<?= $groupe['libelle'] ?>">
                </div>
              </div>
            </div>
            <!-- INFOS GENERALES -->
            <div class="bloc-infos">
              <h1 class="text-center text-lg-left"><?= $title ?></h1>
            </div>
            <!-- BIOGRAPHIE -->
            <div class="bloc-bref mt-3 d-flex justify-content-center justify-content-lg-start">
              <ul>
                <li class="first">
                  <div class="label">Création</div>
                  <div class="value"><?= $dateDebut ?></div>
                </li>
                <li>
                  <div class="label">Effectif</div>
                  <div class="value"><?= $groupe['effectif'] ?> membres</div>
                </li>
                <?php if ($groupe['libelleAbrev'] != "NI"): ?>
                  <li>
                    <div class="label">Président</div>
                    <div class="value"><?= $president['nameFirst']." ".$president['nameLast'] ?></div>
                  </li>
                  <li>
                    <div class="label">Positionnement</div>
                    <div class="value"><?= ucfirst($edito['ideology']) ?></div>
                  </li>
                <?php endif; ?>
              </ul>
            </div>
            <div class="text-center mt-4">
              <a class="btn btn-outline-primary" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">
                Voir tous les membres
              </a>
            </div>
          </div>
          <?php if ($active == TRUE): ?>
            <div class="mandats d-flex justify-content-center align-items-center active">
              <span class="active">EN ACTIVITÉ</span>
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
        <h2 class="mb-4">En quelques mots</h2>
        <?php if ($groupe['libelleAbrev'] == "NI"): ?>
          <p>Les <?= mb_strtolower($title) ?> (NI) ne sont pas membres d'un groupe politique.</p>
          <p>Ces députés peuvent cependant être membres d'un parti politique. Le parti politique est un groupement constitué hors de l'Assemblée nationale, alors que le <a href="http://www2.assemblee-nationale.fr/15/les-groupes-politiques/" target="_blank">groupe politique</a> est un organe officiel de l'Assemblée qui comporte au minimum 15 députés. Certains groupes, comme <a href="<?= base_url() ?>groupes/udi-a-i/" target="_blank">UDI-A-I</a>, regroupent ainsi des députés venant de partis différents.</p>
          <p>Les <?= mb_strtolower($title) ?> ne sont pas représentés à la <a href="http://www2.assemblee-nationale.fr/15/la-conference-des-presidents" target="_blank">conférence des présidents</a> ni au <a href="http://www2.assemblee-nationale.fr/15/le-bureau-de-l-assemblee-nationale" target="_blank">bureau de l'Assemblée nationale</a>.</p>
          <p>Cependant, ils disposent de certains droits, comme l'attribution de temps de parole pour la discussion des textes (<i>art. 49</i> du <a href="http://www.assemblee-nationale.fr/connaissance/reglement.pdf" target="_blank">règlement de l'Assemblée nationale</a>).</p>
          <p>Actuellement, le parti politique le plus représenté parmi les <?= mb_strtolower($title) ?> est le Rassemblement national (avec par exemple <a href="<?= base_url() ?>deputes/pas-de-calais-62/depute_marine-lepen" target="_blank">Marine Le Pen</a>). Avec seulement 7 députés, les élus du Rassemblement nationale n'atteignent pas les 15 députés nécessaires pour former leur propre groupe politique.</p>
          <p>Parmi les <?= mb_strtolower($title) ?>, on retrouve également <a href="<?= base_url() ?>deputes/essonne-91/depute_nicolas-dupontaignan" target="_blank">Nicolas Dupont-Aignan</a>, du parti politique Debout La France, <a href="<?= base_url() ?>deputes/deux-sevres-79/depute_delphine-batho" target="_blank">Delphine Batho</a>, du parti écologiste Génération écologie, ainsi que d'anciens membres du groupe La République en Marche, comme <a href="<?= base_url() ?>deputes/indre-et-loire-37/depute_sabine-thillaye" target="_blank">Sabine Thillaye</a> ou <a href="<?= base_url() ?>deputes/nord-59/depute_jennifer-detemmerman" target="_blank">Jennifer de Temmerman</a>.</p>
        <?php else: ?>
          <p>
            Le groupe <b><?= $groupe['libelle'] ?></b> (<?= $groupe['libelleAbrev'] ?>) <?= $active == TRUE ? 'est' : 'était' ?> un groupe classé <?= $edito['ideology_edited'] ?> de l'échiquier politique.
            Il a été créé en <?= $dateDebutMois ?><?= $edito['creation'] ?>.
            <?php if ($active == FALSE): ?>
              Le groupe a été dissout le <?= $groupe['dateFinFr'] ?>.
            <?php endif; ?>
          </p>
          <p>
            <?= ucfirst($president['son']) ?> président<?= $president['e'] ?> <?= $active == TRUE ? 'est' : 'était' ?> <a href="<?php echo base_url(); ?>deputes/<?= $president['dpt_slug'] ?>/depute_<?= $president['nameUrl'] ?>"><?= $president['nameFirst']." ".$president['nameLast'] ?></a>, à ce poste depuis le <?= $president['dateDebutFR'] ?>.
          </p>
          <p>
            Le groupe s'est déclaré comme faisant partie de <b><?= $edito['opposition'] ?></b>
            <?php if ($groupe['positionPolitique'] != 'Majoritaire'): ?>
              S'il n'est pas majoritaire, un groupe peut soit appartenir à l'opposition, soit être allié à la majorité présidentielle. Dans les deux cas, l'Assemblée nationale leur octroie <a href="http://www.assemblee-nationale.fr/connaissance/reglement/reforme-reglement-2009-4-11.pdf" target="_blank">des droits particuliers</a>, notamment au niveau de la prise de parole en séance publique.
            <?php endif; ?>
          </p>
        <?php endif; ?>
        </div>
        <!-- BLOC VOTES -->
        <?php if (!(empty($votes_datan))): ?>
          <div class="bloc-votes mt-5">
            <div class="row">
              <div class="col-12">
                <div class="d-flex justify-content-between mb-4">
                  <h2>Derniers votes</h2>
                  <div class="bloc-carousel-votes">
                    <div class="carousel-buttons">
                      <a class="btn all mx-2" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/votes">
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
                    <?php if ($vote['vote'] == 'nv'): ?>
                      <div class="thumb absent d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <span>ABSENT</span>
                        </div>
                      </div>
                      <?php else: ?>
                        <div class="thumb d-flex align-items-center <?= $vote['vote'] ?>">
                          <div class="d-flex align-items-center">
                            <span><?= mb_strtoupper($vote['vote']) ?></span>
                          </div>
                        </div>
                    <?php endif; ?>
                    <div class="card-header d-flex flex-row justify-content-between">
                      <span class="date"><?= $vote['dateScrutinFR'] ?></span>
                    </div>
                    <div class="card-body d-flex align-items-center">
                      <span class="title">
                        <a href="<?= base_url() ?>votes/vote_<?= $vote['voteNumero'] ?>" class="stretched-link no-decoration"></a>
                        <?= $vote['vote_titre'] ?>
                      </span>
                    </div>
                    <div class="card-footer">
                      <span class="field badge badge-primary py-1 px-2"><?= $vote['category_libelle'] ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
                <div class="card card-vote see-all">
                  <div class="card-body d-flex align-items-center justify-content-center">
                    <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/votes" class="stretched-link no-decoration">VOIR TOUS</a>
                  </div>
                </div>
              </div>
            </div>
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
        <!-- BLOC CHIFFRES -->
        <div class="bloc-chiffres mt-5">
          <h2 class="mb-3">En chiffres</h2>
          <div class="row">
            <div class="col-sm-6">
              <div class="card">
                <div class="card-body">
                  <h3>Nombre de députés</h3>
                  <div class="stat">
                    <span><?= $groupe['effectif'] ?></span>
                  </div>
                  <div class="explanation">
                    <?php if ($active == FALSE): ?>
                      <p>Avec <?= $groupe['effectif'] ?> députés, le groupe <?= $title ?> représentait <?= $groupe['effectifShare'] ?>% du nombre total de députés à l'Assemblée nationale, qui est de 577.</p>
                      <?php else: ?>
                      <p>Avec <?= $groupe['effectif'] ?> députés, le groupe <?= $groupe['libelleAbrev'] ?> est le <?= $groupe['classement'] != "1" ? $groupe['classement'].'<sup>e</sup> ' : '' ?>groupe le plus important (sur <?= $groupesN ?> groupes). Il représente <?= $groupe['effectifShare'] ?>% du nombre total de députés à l'Assemblée nationale.</p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mt-4 mt-sm-0">
              <div class="card">
                <div class="card-body">
                  <h3>Âge moyen des députés</h3>
                  <div class="stat">
                    <span><?= $groupe['age'] ?> ans</span>
                  </div>
                  <div class="explanation">
                    <p>Les députés membres du groupe <?= $groupe['libelleAbrev'] ?> ont en moyenne <?= $groupe['age'] ?> ans. C'est plus <?= $ageEdited ?> que la moyenne de l'Assemblée nationale, qui est de <?= $ageMean ?> ans.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-sm-6">
              <div class="card">
                <div class="card-body">
                  <h3>Taux de féminisation</h3>
                  <div class="stat">
                    <span><?= $groupe['womenPct'] ?> %</span>
                  </div>
                  <div class="explanation">
                    <p>Le groupe <?= $groupe['libelleAbrev'] ?> compte <?= $groupe['womenN'] ?> députées femmes parmi ses rangs, soit <?= $groupe['womenPct'] ?> % de ses effectifs. C'est <?= $womenEdited ?> que la moyenne de l'Assemblée nationale, qui est de <?= $womenPctTotal ?>%.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6">
            </div>
          </div>
        </div> <!-- // END BLOC CHIFFRES -->
        <!-- BLOC STATISTIQUES -->
        <div class="bloc-statistiques mt-5">
          <h2 class="mb-3">Comportement politique</h2>
          <!-- CARD PARTICIPATION -->
          <div class="card card-statistiques my-4">
            <div class="card-body">
              <div class="row">
                <div class="col-12 d-flex flex-row align-items-center">
                  <div class="icon">
                    <?php echo file_get_contents(base_url().'/assets/imgs/icons/voting.svg') ?>
                  </div>
                  <h3 class="ml-3">PARTICIPATION AUX VOTES
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="no-decoration" title="Taux de participation" data-content="Ce taux de participation représente, en moyenne, <b>le pourcentage de députés du groupe participant aux votes</b>.<br><br>Attention, le taux de participation ne mesure pas toute l'activité d'un député ou d'un groupe. Contrairement au <a href='https://www.europarl.europa.eu/about-parliament/fr/organisation-and-rules/how-plenary-works' title='lien'>Parlement européen</a>, les votes à l'Assemblée nationale se déroulent à n'importe quel moment de la semaine. D'autres réunions ont souvent lieu en même temps, expliquant le faible taux de participation des députés et des groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#participation' target='_blank'>cliquez ici</a>." id="popover_focus"><?php echo file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                  </h3>
                </div>
              </div>
              <div class="row">
                <?php if ($no_participation == TRUE): ?>
                  <div class="col-12 mt-2">
                    <p>Du fait d'un nombre insuffisant de votes de la part du groupe <?= $title ?>, aucune statistique n'a pu être produite.</p>
                  </div>
                  <?php else: ?>
                    <div class="col-lg-3 offset-lg-1 mt-2">
                      <div class="d-flex justify-content-center align-items-center">
                        <div class="c100 p<?= $stats_participation['score'] ?> m-0">
                            <span><?= $stats_participation['score'] ?> %</span>
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
                          En moyenne, <?= $stats_participation['score'] ?>% <?php if ($groupe['libelleAbrev'] != "NI"): ?>des députés du groupe <?= $groupe['libelleAbrev'] ?><?php else: ?>des <?= mb_strtolower($title) ?><?php endif; ?> <?= $active == TRUE ? 'prennent' : 'prenaient' ?> part aux votes.
                        </p>
                        <p>
                          <?php if ($groupe['libelleAbrev'] != "NI"): ?>Le groupe <b><?= $active == TRUE ? 'participe' : 'participait' ?> <?php else: ?>Les <?= mb_strtolower($title) ?> <b>participent <?php endif; ?> <?= $edito_participation ?></b> que la moyenne <?= $active == TRUE ? "des autres groupes" : "de tous les groupes de l'Assemblée" ?>, qui est <?php if ($edito_participation == "autant"): ?>
                            aussi
                          <?php endif; ?> de <?= $stats_participation_moyenne['moyenne']*100 ?>%.
                        </p>
                        <!-- <span><a href="#" target="_blank">> Voir le classement général</a></span> -->
                      </div>
                    </div>
                <?php endif; ?>
              </div>
            </div>
          </div> <!-- END CARD PARTICIPATION -->
          <!-- CARD COHESION -->
          <div class="card card-statistiques my-4">
            <div class="card-body">
              <div class="row">
                <div class="col-12 d-flex flex-row align-items-center">
                  <div class="icon">
                    <?php echo file_get_contents(base_url().'assets/imgs/icons/loyalty.svg') ?>
                  </div>
                  <h3 class="ml-3">COHESION AU SEIN DU GROUPE
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="no-decoration" title="Taux de cohésion" data-content="Le taux de cohésion représente <b>l'unité d'un groupe politique</b> lorsqu'il vote. Il peut prendre des mesures allant de 0 à 1. Un taux proche de 1 signifie que le groupe est très uni.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les mesures de cohésion des autres groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#cohesion' target='_blank'>cliquez ici</a>." id="popover_focus"><?php echo file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                  </h3>
                </div>
              </div>
              <div class="row">
                <?php if ($no_cohesion == TRUE): ?>
                  <div class="col-12 mt-2">
                    <p>Du fait d'un nombre insuffisant de votes de la part du groupe <?= $title ?>, aucune statistique n'a pu être produite.</p>
                  </div>
                  <?php else: ?>
                  <div class="col-lg-3 offset-lg-1 mt-2">
                    <div class="d-flex justify-content-center align-items-center">
                      <div class="c100 p<?= $stats_cohesion['score'] ?> m-0">
                          <span><?= $stats_cohesion['cohesion'] ?></span>
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
                        Avec un taux de cohésion de <?= $stats_cohesion['cohesion'] ?>,<?php if ($groupe['libelleAbrev'] != "NI"): ?> le groupe <?= $groupe['libelleAbrev'] ?> peut être considéré<?php else: ?> les <?= mb_strtolower($title) ?> peuvent être considérés<?php endif; ?> comme <b><?= $edito_cohesion["absolute"] ?> soudé<?php if ($groupe['libelleAbrev'] == "NI"): ?>s<?php endif; ?></b> quand il <?= $active == TRUE ? "s'agit" : "s'agissait" ?> de voter.
                      </p>
                      <p>
                        Le groupe <?= $active == TRUE ? "est" : "était" ?> en effet <b><?= $edito_cohesion["relative"] ?> uni</b> que la moyenne de tous les groupes, qui est de <?= $stats_cohesion_moyenne['moyenne'] ?>.
                      </p>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div> <!-- END CARD COHESION -->
          <!-- CARD MAJORITE -->
          <?php if ($groupe['libelleAbrev'] != "LAREM"): ?>
            <div class="card card-statistiques my-4">
              <div class="card-body">
                <div class="row">
                  <div class="col-12 d-flex flex-row align-items-center">
                    <div class="icon">
                      <?php echo file_get_contents(base_url().'/assets/imgs/icons/group.svg') ?>
                    </div>
                    <h3 class="ml-3">PROXIMITÉ AVEC LA MAJORITE PRESIDENTIELLE
                      <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="no-decoration" title="Taux de proximité avec la majorité" data-content="Le taux de proximité avec la majorité présidentielle représente le pourcentage de votes où le groupe <b>a voté en accord avec le groupe de la majorité</b> (La République en Marche).<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?php echo file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                    </h3>
                  </div>
                </div>
                <div class="row">
                  <?php if ($no_participation == TRUE): ?>
                    <div class="col-12 mt-2">
                      <p>Du fait d'un nombre insuffisant de votes de la part du groupe <?= $title ?>, aucune statistique n'a pu être produite.</p>
                    </div>
                    <?php else: ?>
                    <div class="col-lg-3 offset-lg-1 mt-2">
                      <div class="d-flex justify-content-center align-items-center">
                        <div class="c100 p<?= $stats_majorite['score'] ?> m-0">
                            <span><?= $stats_majorite['score'] ?> %</span>
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
                          <?php if ($groupe['libelleAbrev'] != "NI"): ?>
                            Le groupe <?= $groupe['libelleAbrev'] ?> a
                            <?php else: ?>
                            Les <?= mb_strtolower($title) ?> ont
                          <?php endif; ?>
                          voté en accord avec le groupe de la majorité présidentielle (<a href="<?= base_url(); ?>groupes/larem" target="_blank"?>LREM</a>) dans <?= $stats_majorite['score'] ?>% des cas.
                        </p>
                        <p>
                          <?php if ($groupe['libelleAbrev'] != "NI"): ?>
                            Le groupe <?= $groupe['libelleAbrev'] ?> <?= $active == TRUE ? "vote" : "votait" ?>
                            <?php else: ?>
                            Les députés <?= $groupe['libelleAbrev'] ?> votent
                          <?php endif; ?>
                          <b><?= $edito_majorite ?> souvent</b> avec la majorité présidentielle que la moyenne des autres groupes, qui est de <?= $stats_majorite_moyenne['moyenne']*100 ?> %.
                        </p>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div> <!-- END CARD MAJORITE -->
          <?php endif; ?>
          <!-- BLOC PROXIMITY -->
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
                    <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" class="no-decoration" title="Taux de proximité avec les groupes" data-content="Le <b>taux de proximité entre deux groupes</b> représente le pourcentage de fois où les deux groupes ont voté la même chose. Chaque groupe se voit attribuer une <i>position majoritaire</i>, en fonction du vote de ses membres. Cette position peut soit être 'pour', 'contre', ou 'absention'. Pour chaque vote, nous déterminons si les deux groupes ont la même position majoritaire. Le taux de proximité est le pourcentage de fois où les deux groupes ont cette même position majoritaire.<br><br>Par exemple, si le taux est de 75%, cela signifie que les deux groupes ont voté la même chose dans 75% des cas. <br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?php echo file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                  </h3>
                </div>
              </div>
              <?php if ($no_proximite == TRUE): ?>
                <div class="row">
                  <div class="col-10 offset-2">
                    <p>Du fait d'un nombre insuffisant de votes de la part du groupe <?= $title ?>, aucune statistique n'a pu être produite.</p>
                  </div>
                </div>
                <?php else: ?>
                <div class="row">
                  <div class="col-10 offset-2">
                    <h4><?= $title ?> vote <b>souvent</b> avec :</h4>
                  </div>
                </div>
                <div class="row mt-1 bar-container pr-2">
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
                        <?php foreach ($accord_groupes_first as $group): ?>
                          <div class="bars d-flex align-items-center justify-content-center" style="height: <?= $group['score'] ?>%">
                            <span class="score"><?= $group['score'] ?>%</span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-10 offset-2 d-flex justify-content-between mt-2">
                    <?php foreach ($accord_groupes_first as $group): ?>
                      <div class="legend-element d-flex align-items-center justify-content-center">
                        <span class="font-weight-bold"><?= $group['libelleAbrev'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-10 offset-2 ">
                    <p>
                      Le groupe avec lequel le groupe <?= $groupe['libelleAbrev'] ?> <?= $active == TRUE ? "est" : "était" ?> le plus proche est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($edito_proximite['first1']['libelleAbrev']) ?>" target="_blank"><?= $edito_proximite['first1']['libelle'] ?></a>,
                      <?php if ($edito_proximite['first1']['libelleAbrev'] != "NI"): ?>
                        <?= $edito_proximite['first1']['maj_pres'] ?> classé <?= $edito_proximite['first1']['ideology'] ?> de l'échiquier politique.
                        <?php else: ?>
                          <?= $edito_proximite['first1']['maj_pres'] ?>.
                      <?php endif; ?>
                      Il <?= $active == TRUE ? "est" : "était" ?> également proche du groupe <a href="<?= base_url() ?>groupes/<?= mb_strtolower($edito_proximite['first2']['libelleAbrev']) ?>" target="_blank"><?= $edito_proximite['first2']['libelle'] ?></a>,
                      <?php if ($edito_proximite['first2']['libelleAbrev'] != "NI"): ?>
                        <?= $edito_proximite['first2']['maj_pres'] ?> classé <?= $edito_proximite['first2']['ideology'] ?>.
                        <?php else: ?>
                          <?= $edito_proximite['first2']['maj_pres'] ?>.
                      <?php endif; ?>
                    </p>
                  </div>
                </div>
                <div class="row mt-5">
                  <div class="col-10 offset-2">
                    <h4><?= $title ?> vote <b>rarement</b> avec :</h4>
                  </div>
                </div>
                <div class="row mt-1 bar-container pr-2">
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
                        <?php foreach ($accord_groupes_last as $group): ?>
                          <div class="bars d-flex align-items-center justify-content-center" style="height: <?= $group['score'] ?>%">
                            <span class="score"><?= $group['score'] ?>%</span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-10 offset-2 d-flex justify-content-between mt-2">
                    <?php foreach ($accord_groupes_last as $group): ?>
                      <div class="legend-element d-flex align-items-center justify-content-center">
                        <span class="font-weight-bold"><?= $group['libelleAbrev'] ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-10 offset-2">
                    <p>
                      À l'opposé, le groupe avec lequel le groupe <?= $groupe['libelleAbrev'] ?> <?= $active == TRUE ? "est" : "était" ?> le moins proche est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($edito_proximite['last1']['libelleAbrev']) ?>" target="_blank"><?= $edito_proximite['last1']['libelle'] ?></a>,
                      <?php if ($edito_proximite['last1']['libelleAbrev'] != "NI"): ?>
                        <?= $edito_proximite['last1']['maj_pres'] ?> classé <?= $edito_proximite['last1']['ideology'] ?> de l'échiquier politique.
                        <?php else: ?>
                          <?= $edito_proximite['first2']['maj_pres'] ?>.
                      <?php endif; ?>
                      Il <?= $active == TRUE ? "vote" : "votait" ?> également très peu avec <a href="<?= base_url() ?>groupes/<?= mb_strtolower($edito_proximite['last2']['libelleAbrev']) ?>" target="_blank"><?= $edito_proximite['last2']['libelle'] ?> </a>,
                      <?php if ($edito_proximite['last2']['libelleAbrev'] != "NI"): ?>
                        <?= $edito_proximite['last2']['maj_pres'] ?> classé <?= $edito_proximite['last2']['ideology'] ?>.
                        <?php else: ?>
                          <?= $edito_proximite['last2']['maj_pres'] ?>.
                      <?php endif; ?>
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
            </div>
          </div> <!-- END BLOC PROXIMITY -->
        </div> <!-- // END BLOC STATISTIQUES -->
        <!-- BLOC SOCIAL-MEDIA -->
        <div class="bloc-links p-lg-0 p-md-2 mt-5">
          <h2>En savoir plus</h2>
          <div class="row mt-4">
            <?php if (isset($groupe['website'])): ?>
              <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                <a href="<?= $groupe['website'] ?>" target="_blank" role="button" class="btn btn-website d-flex align-items-center justify-content-center">
                  Site internet
                </a>
              </div>
            <?php endif; ?>
            <?php if (isset($groupe['facebook'])): ?>
              <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                <a href="https://www.facebook.com/<?= $groupe['facebook'] ?>" target="_blank" role="button" class="btn btn-fcb d-flex align-items-center justify-content-center">
                  <?php echo file_get_contents(base_url().'/assets/imgs/logos/facebook_svg.svg') ?>
                  <span class="ml-3">Profil Facebook</span>
                </a>
              </div>
            <?php endif; ?>
            <?php if (isset($groupe['twitter'])): ?>
              <div class="col-12 col-sm-6 mt-2 d-flex justify-content-center">
                <a href="https://twitter.com/<?= $groupe['twitter'] ?>" target="_blank" role="button" class="btn btn-twitter d-flex align-items-center justify-content-center">
                  <?php echo file_get_contents(base_url().'/assets/imgs/logos/twitter_svg.svg') ?>
                  <span class="ml-3">Profil Twitter</span>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div> <!-- END BLOC SOCIAL MEDIA -->
    </div>
  </div>
</div> <!-- END CONTAINER -->
<!-- AUTRES DEPUTES -->
<div class="container-fluid pg-groupe-individual bloc-others-container">
  <div class="container bloc-others">
    <?php if ($groupe['libelleAbrev'] != "NI"): ?>
      <div class="row">
        <div class="col-12">
          <h2>Président du groupe <?= $title ?></h2>
          <div class="row mt-3">
            <div class="col-6 col-md-3 py-2">
              <a class="membre no-decoration underline" href="<?php echo base_url(); ?>deputes/<?= $president['dpt_slug'] ?>/depute_<?= $president['nameUrl'] ?>"><?= $president['nameFirst']." ".$president['nameLast'] ?></a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-12">
        <h2>Tous les députés membres du groupe <?= $title ?></h2>
        <div class="row mt-3">
          <?php foreach ($membres as $key => $membre): ?>
            <div class="col-6 col-md-3 py-2">
              <a class="membre no-decoration underline" href="<?php echo base_url(); ?>deputes/<?= $membre['dpt_slug'] ?>/depute_<?= $membre['nameUrl'] ?>"><?= $membre['nameFirst']." ".$membre['nameLast'] ?></a>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3">
          <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= $groupe['libelleAbrev'] ?></a>
        </div>
      </div>
    </div>
    <?php if (!empty($apparentes)): ?>
      <div class="row">
        <div class="col-12">
          <h2>Tous les députés apparentés du groupe <?= $title ?></h2>
          <div class="row mt-3">
            <?php foreach ($apparentes as $key => $mp): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?php echo base_url(); ?>deputes/<?= $mp['dpt_slug'] ?>/depute_<?= $mp['nameUrl'] ?>"><?= $mp['nameFirst']." ".$mp['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés apparentés au groupe <?= $groupe['libelleAbrev'] ?></a>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-12">
        <h2>Tous les groupes parlementaires actifs de la 15e législature</h2>
        <div class="row mt-3">
          <?php foreach ($groupesActifs as $group): ?>
            <div class="col-6 col-md-4 py-2">
              <a class="membre no-decoration underline" href="<?php echo base_url(); ?>groupes/<?= mb_strtolower($group['libelleAbrev']) ?>"><?= $group['libelle']." (".$group['libelleAbrev'].")" ?></a>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3">
          <a href="<?= base_url() ?>groupes">Voir tous les groupes parlementaires de la 15e législature</a>
        </div>
      </div>
    </div>
  </div>
</div>
