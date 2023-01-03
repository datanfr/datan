<div class="container-fluid bloc-img-deputes async_background d-none d-lg-flex" id="container-always-fluid" style="height: 13em"></div>
<?php if (!empty($groupe['couleurAssociee'])): ?>
  <div class="liseret-groupe d-none d-lg-block" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
<?php endif; ?>
<div class="container pg-groupe-stats">
  <div class="row">
    <div class="col-lg-4 d-none d-lg-block"> <!-- CARD ONLY > lg -->
      <?php $this->load->view('groupes/partials/card_individual.php', array('tag' => 'h1')) ?>
    </div>
    <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5">
      <a class="btn btn-outline-primary mx-2 mt-4" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
        <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
        Retour profil
      </a>
      <div class="title my-4">
        <h1>Les statistiques du groupe <?= name_group($title) ?></h1>
        <p><?= $legislature['edito'] ?></p>
      </div>
      <p>Cette page présente en détail les <b>statistiques</b> du groupe <i><?= name_group($title) ?></i>.</p>
      <p>Quelle est la cohésion au sein du groupe ? Ses députés participent-ils souvent aux scrutins ? Quelle est sa proximité avec les autres groupes de l'Assemblée nationale ?</p>
      <p>Cette page présente un <b>historique</b> des statistiques du groupe <i><?= name_group($title) ?></i>. Pour en savoir plus sur l'historique du groupe, <a href="#link-stats">cliquez ici</a>.</p>
      <p>Ces <a href="<?= base_url() ?>statistiques/aide">statistiques</a> sont développées par l'équipe de Datan.</p>
      <div class="mt-5">
        <p class="h4 font-weight-bold text-primary">Accès rapide aux statistiques</p>
        <a class="btn btn-primary my-1" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/statistiques#participation">Participation aux votes</a>
        <a class="btn btn-primary my-1" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/statistiques#cohesion">Cohesion interne</a>
        <a class="btn btn-primary my-1" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/statistiques#majorite">Proximité avec la majorité</a>
        <a class="btn btn-primary my-1" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/statistiques#proximite">Proximité avec chaque groupe</a>
        <a class="btn btn-primary my-1" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/statistiques#effectifs">Effectifs du groupe</a>
        <a class="btn btn-primary my-1" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/statistiques#age">Âge moyen des députés</a>
        <a class="btn btn-primary my-1" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/statistiques#feminisation">Taux de féminisation</a>
      </div>
      <div class="mt-5">
        <h2 class="anchor mb-3" id="participation">Participation aux votes</h2>
        <p>
          <?= $active ? 'Depuis le début de' : 'Pendant la' ?> <?= $groupe['legislature'] ?>ème législature, le taux de participation moyen du groupe <i><?= name_group($title) ?></i> <?= $active ? 'est' : 'était' ?> de <span class="font-weight-bold text-primary"><?= $stats['participation']['value'] ?>%</span>.
          Autrement dit, en moyenne, pour chaque scrutin en séance publique, <?= $stats['participation']['value'] ?>% des députés du groupe ont pris part au vote.
        </p>
        <p>Le groupe <b><?= $active ? 'participe' : 'participait' ?> <?= $edito_participation ?></b> que les autres groupes politiques. La moyenne de l'Assemblée nationale <?= $active ? 'est' : 'était' ?> de <?= $statsAverage['participation'] ?>%.</p>
        <?php if (count($stats_history['participation']) > 1): ?>
          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <?php if ($active): ?>
                <h3>La participation du groupe <?= $this->groupes_edito->get_evolution_edited(round($stats_history['participation'][count($stats_history['participation'])-1]['value'] * 100), round($stats_history['participation'][count($stats_history['participation'])-2]['value'] * 100)) ?></h3>
              <?php else: ?>
                <h3>Historique de la participation du groupe <?= $groupe['libelleAbrev'] ?></h3>
              <?php endif; ?>
              <p>Évolution du taux de participation du groupe <?= $groupe['libelleAbrev'] ?> sur les dernières législatures</p>
              <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $stats_history['participation'], 'type' => 'pct', 'terms' => TRUE, 'divided_by' => 1, 'grid' => TRUE, 'organeRef' => $groupe['uid'], 'tooltip' => TRUE)) ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="card mt-4">
          <div class="card-body card-stats pb-2">
            <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
            <h3>Évolution de la participation sur la dernière législature</h3>
            <p><?= $legislature['edito'] ?></p>
            <?php $this->load->view('groupes/partials/stats_chartJS.php', array('stats_history_chart' => $stats_monthly['participation'], 'type' => 'graphParticipation', 'max' => 100)) ?>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="anchor mb-3" id="cohesion">Cohésion interne au groupe</h2>
        <p>
          <?= $active ? 'Depuis le début de' : 'Pendant la' ?> <?= $groupe['legislature'] ?>ème législature, le taux de cohésion du groupe <i><?= name_group($title) ?></i> <?= $active ? 'est' : 'était' ?> de <span class="font-weight-bold text-primary"><?= round($stats['cohesion']['value'], 2) ?></span>. Plus ce score de cohésion est proche de 1, plus le groupe est uni quand il s'agit de voter en séance publique.
        </p>
        <p>Le groupe <i><?= name_group($title) ?></i> <?= $active ? 'peut' : 'pouvait' ?> être considéré comme <b><?= $edito_cohesion['absolute'] ?> soudé</b> quand il s'<?= $active ? 'agit' : 'agissait' ?> de voter. En effet, le groupe <?= $active ? 'est' : 'était' ?> <?= $edito_cohesion['relative'] ?> soudé que la moyenne des autres groupes, qui <?= $active ? 'est' : 'était' ?> de <?= round($statsAverage['cohesion'], 2) ?>.</p>
        <?php if (count($stats_history['cohesion']) > 1): ?>
          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <?php if ($active): ?>
                <h3>La cohésion du groupe <?= $this->groupes_edito->get_evolution_edited(round($stats_history['cohesion'][count($stats_history['cohesion'])-1]['value'] * 100), round($stats_history['cohesion'][count($stats_history['cohesion'])-2]['value'] * 100)) ?></h3>
              <?php else: ?>
                  <h3>Historique de la cohésion du groupe <?= $groupe['libelleAbrev'] ?></h3>
              <?php endif; ?>
              <p>Évolution du taux de cohésion du groupe <?= $groupe['libelleAbrev'] ?> sur les dernières législatures</p>
              <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $stats_history['cohesion'], 'type' => 'score', 'terms' => TRUE, 'divided_by' => 1, 'grid' => TRUE, 'organeRef' => $groupe['uid'], 'tooltip' => TRUE)) ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="card card-stats mt-4">
          <div class="card-body pb-2">
            <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
            <h3>Évolution de la cohésion sur la dernière législature</h3>
            <p><?= $legislature['edito'] ?></p>
            <?php $this->load->view('groupes/partials/stats_chartJS.php', array('stats_history_chart' => $stats_monthly['cohesion'], 'type' => 'graphCohesion', 'max' => 1)) ?>
          </div>
        </div>
      </div>
      <?php if ($stats_history['majority']): ?>
        <div class="mt-5">
          <h2 class="anchor mb-3" id="majorite">Proximité avec la majorité présidentielle</h2>
          <p>
            <?= $active ? 'Depuis le début de' : 'Pendant la' ?> <?= $groupe['legislature'] ?>ème législature, le groupe <i><?= name_group($title) ?></i> a voté sur la même ligne que la majorité présidentielle dans <span class="font-weight-bold text-primary"><?= $stats['majority']['value'] ?>%</span> des cas.
          </p>
          <p>Le groupe <?= $active ? 'est' : 'était' ?> <b><?= $edito_majorite ?> proche</b> de la majorité présidentielle que la moyenne des autres groupes politiques, qui <?= $active ? 'est' : 'était' ?> de <?= $statsAverage['majority'] ?>%.</p>
          <?php if (count($stats_history['majority']) > 1): ?>
            <div class="card card-stats mt-4">
              <div class="card-body pb-0">
                <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
                <?php if ($active): ?>
                  <h3>La proximité avec la majorité <?= $this->groupes_edito->get_evolution_edited(round(array_slice($stats_history['majority'], -1, 1)[0]['value'] * 100), round(array_slice($stats_history['majority'], -2, 1)[0]['value'] * 100)) ?></h3>
                <?php else: ?>
                  <h3>Historique de la proximité avec la majorité</h3>
                <?php endif; ?>
                <p>Évolution de la proximité avec la majorité du groupe <?= $groupe['libelleAbrev'] ?> sur les dernières législatures</p>
                <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $stats_history['majority'], 'type' => 'pct', 'terms' => TRUE, 'divided_by' => 1, 'grid' => TRUE, 'organeRef' => $groupe['uid'], 'tooltip' => TRUE)) ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <h3>Évolution de la proximité avec la majorité sur la dernière législature</h3>
              <p><?= $legislature['edito'] ?></p>
              <?php $this->load->view('groupes/partials/stats_chartJS.php', array('stats_history_chart' => $stats_monthly['majority'], 'type' => 'graphMajority', 'max' => 100)) ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="mt-5">
        <h2 class="anchor mb-3" id="proximite">Proximité avec chaque groupe politique</h2>
        <p>Le groupe <i><?= name_group($title) ?></i> <?= $active ? 'vote' : 'votait' ?>-t-il souvent avec les autres groupes politiques qui <?= $active ? 'compose' : 'composait' ?> l'Assemblée nationale ? Deux groupes sont considérés proches s'ils ont les mêmes positions lorsqu'ils votent en séance publique.</p>
        <p>
          <?= $active ? 'Depuis le début de' : 'Pendant la' ?> <?= $groupe['legislature'] ?>ème législature, le groupe <i><?= name_group($title) ?></i> est le plus proche du groupe <i><?= name_group($accord_groupes_first['libelle']) ?></i>.
          Ces deux groupes ont eu la même position dans <span class="font-weight-bold text-primary"><?= $accord_groupes_first['score'] ?>%</span> des cas.</p>
        <div class="card card-stats mt-4">
          <div class="card-body pb-3">
            <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
            <h3>Historique de la proximité avec chaque groupe</h3>
            <p><?= $legislature['edito'] ?></p>
            <p class="small">Ce graphique donne un historique de la proximité entre le groupe <i><?= name_group($groupe['libelle']) ?></i> et les autres groupes de l'Assemblée. Cliquez sur les différents groupes pour faire apparaître les données de proximité.</p>
            <?php $this->load->view('groupes/partials/stats_chartJS_multiline_proximity.php') ?>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="anchor mb-3" id="effectifs">Les effectifs du groupe <?= $groupe['libelleAbrev'] ?></h2>
        <?php if (isset($members)): ?>
          <p>Le groupe <i><?= name_group($title) ?></i> compte actuellement <span class="font-weight-bold text-primary"><?= $groupe['effectif'] ?> députés</span>. Cela représente <b><?= $groupe['effectifShare'] ?>%</b> des députés de l'Assemblée nationale. Pour rappel, l'Assemblée compte 577 députés.</p>
          <?php else: ?>
          <p>Retrouvez ci-dessous l'historique du nombre de députés membres du groupe <i><?= name_group($title) ?></i>.</p>
        <?php endif; ?>
        <?php if (isset($members)): ?>
          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <?php if ($effectifRank['last']): ?>
                <h3>Le plus petit groupe politique de l'Assemblée nationale</h3>
              <?php else: ?>
                <h3>Le <?= ordinaux($effectifRank['number']) ?> plus grand groupe de l'Assemblée</h3>
              <?php endif; ?>
              <p>Classement des groupes de la <?= $groupe['legislature'] ?><sup>ème</sup> législature en fonction de leurs effectifs</p>
              <?php $this->load->view('groupes/partials/stats_horizontal.php', array('stats_history_chart' => $members, 'type' => 'score', 'max' => 100, 'terms' => FALSE, 'divided_by' => $members_max, 'grid' => FALSE, 'organeRef' => $groupe['uid'])) ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="card card-stats mt-4">
          <div class="card-body pb-2">
            <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
            <h3>Historique des effectifs du groupe</h3>
            <p>Évolution des effectifs du groupe <?= $groupe['libelleAbrev'] ?> par mois</p>
            <?php $this->load->view('groupes/partials/stats_chartJS_multiline_members.php') ?>
            <p class="mt-4 small">Le graphique donne le nombre de députés membres des différents groupes liés au groupe <i><?= name_group($groupe['libelle']) ?></i>. Il s'agit des groupes suivants :</p>
            <table class="table table-sm small">
              <thead>
                <tr>
                  <th scope="col">Groupe</th>
                  <th scope="col">Législature</th>
                  <th scope="col">Date d'activité</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($history_list_all as $key => $value): ?>
                  <tr>
                    <th scope="row"><?= name_group($value['libelle']) ?> (<?= $value['libelleAbrev'] ?>)</th>
                    <td><?= $value['legislature'] ?></td>
                    <td><?= date('Y', strtotime($value['dateDebut'])) ?> - <?= $value['dateFin'] ? date('Y', strtotime($value['dateFin'])) : '<i>En cours</i>' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="anchor mb-3" id="age">L'âge moyen des députés du groupe <?= $groupe['libelleAbrev'] ?></h2>
        <p>L'âge moyen des députés membres du groupe <i><?= name_group($title) ?></i> <?= $active ? 'est' : 'était' ?> de <span class="font-weight-bold text-primary"><?= $groupe['age'] ?> ans</span>.</p>
        <p><?= !$active ? 'Pendant la ' . $groupe['legislature'] . 'ème législature' : 'En moyenne' ?>, les députés du groupe <i><?= name_group($title) ?></i> <?= $active ? 'sont' : 'était' ?> <b><?= $ageEdited ?> âgés</b> que la moyenne de l'Assemblée nationale, qui <?= $active ? 'est' : 'était' ?> de <?= $ageMean ?> ans.</p>
        <?php if ($active): ?>
          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <?php if ($ageRanking['last']): ?>
                <h3>Le groupe avec les députés les plus jeunes</h3>
              <?php else: ?>
                <h3>Le <?= ordinaux($ageRanking['number']) ?> groupe avec les députés les plus âgés</h3>
              <?php endif; ?>
              <p>Classement des groupes de la <?= $groupe['legislature'] ?>ème législature en fonction de leurs effectifs</p>
              <?php $this->load->view('groupes/partials/stats_horizontal.php', array('stats_history_chart' => $age, 'type' => 'score', 'max' => 100, 'terms' => FALSE, 'divided_by' => $age_max, 'grid' => FALSE, 'organeRef' => $groupe['uid'])) ?>
            </div>
          </div>
        <?php endif; ?>
        <?php if (count($orga_history['age']) > 1): ?>
          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <h3>Historique de l'âge moyen au sein du groupe</h3>
              <p>Évolution de l'âge moyen des députés du groupe <?= $groupe['libelleAbrev'] ?> par législature</p>
              <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $orga_history['age'], 'type' => 'score', 'max' => 100, 'terms' => TRUE, 'divided_by' => $age_max, 'grid' => FALSE, 'organeRef' => $groupe['uid'], 'tooltip' => TRUE)) ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <div class="mt-5">
        <h2 class="anchor mb-3" id="feminisation">Le taux de féminisation du groupe <?= $groupe['libelleAbrev'] ?></h2>
        <p>
          <?= !$active ? 'Pendant la ' . $groupe['legislature'] . 'ème législature, il y avait' : 'Il y a' ?> <span class="font-weight-bold text-primary"><?= $groupe['womenN'] ?> députées femmes</span> au sein du groupe <i><?= name_group($title) ?></i>.
          Cela <?= $active ? 'représente' : 'représentait' ?> <?= $groupe['womenPct'] ?>% des effectifs du groupe.
          C'est <b><?= $womenEdited ?></b> que la moyenne de l'Assemblée nationale, qui <?= $active ? 'est' : 'était' ?> de <?= $womenPctTotal ?>%.
        </p>
        <?php if ($active): ?>
          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <?php if ($womenRanking['last']): ?>
                <h3>Le groupe avec la part de députées femmes la plus faible</h3>
              <?php else: ?>
                <h3>Le <?= ordinaux($womenRanking['number']) ?> groupe avec la part de députées femmes la plus forte</h3>
              <?php endif; ?>
              <p>Classement des groupes de la <?= $groupe['legislature'] ?>ème législature en fonction du taux de féminisation</p>
              <?php $this->load->view('groupes/partials/stats_horizontal.php', array('stats_history_chart' => $women, 'type' => 'pct', 'max' => 100, 'terms' => FALSE, 'divided_by' => $women_max, 'grid' => FALSE, 'organeRef' => $groupe['uid'])) ?>
            </div>
          </div>
        <?php endif; ?>
        <?php if (count($orga_history['womenPct']) > 1): ?>
          <div class="card card-stats mt-4">
            <div class="card-body pb-0">
              <div class="mb-3 mt-1" style="border-top: 7px solid #00b794; width: 60px"></div>
              <h3>Historique de la part de femmes au sein du groupe</h3>
              <p>Évolution de la part de députées femmes au sein du groupe <?= $groupe['libelleAbrev'] ?> par législature</p>
              <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $orga_history['womenPct'], 'type' => 'pct', 'max' => 100, 'terms' => TRUE, 'divided_by' => 1, 'grid' => TRUE, 'organeRef' => $groupe['uid'], 'tooltip' => TRUE)) ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <div class="mt-5">
        <h2 class="anchor mb-3" id="link-stats">Historique du groupe <?= $groupe['libelleAbrev'] ?></h2>
        <p>Sur cette page, vous trouvez un historique des statistiques du groupe <i><?= name_group($title) ?></i>. Pour chaque groupe politique, nous avons répertorié les anciens ou nouveaux groupes qui leur sont liés.</p>
        <p>Sur Datan, nous récupérons les statistiques de l'Assemblée nationale depuis la 14<sup>ème</sup> législature (depuis 2012).</p>
        <?php if (isset($history_list)): ?>
          <p>Voici les différents groupes liés au groupe <i><?= name_group($title) ?></i> :</p>
          <div class="row">
            <?php foreach ($history_list as $key => $value): ?>
              <div class="col-6">
                <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $value, 'tag' => 'span', 'cat' => $value['legislature'].'<sup>ème</sup> législature', 'stats' => NULL)) ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-warning">
            Nous n'avons pas associé d'autres groupes au groupe <?= $groupe['libelleAbrev'] ?> (celui-ci n'a pas de groupes proches antérieurs ou futurs).
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('groupes/partials/mps_footer.php') ?>
