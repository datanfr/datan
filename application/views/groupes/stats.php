<div class="container-fluid bloc-img-deputes async_background d-flex" id="container-always-fluid" style="height: 13em">
  <div class="container banner-groupe-mobile d-flex d-lg-none flex-column justify-content-center py-2">
    <div class="row">
      <div class="col-md-10 offset-md-1">
        <a class="btn btn-primary text-border mb-2" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
          <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
          Retour profil
        </a>
        <span class="title d-block"><?= name_group($title) ?> (<?= $groupe['libelleAbrev'] ?>)</span>
      </div>
    </div>
  </div>
</div>
<?php if (!empty($groupe['couleurAssociee'])): ?>
  <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
<?php endif; ?>
<div class="container pg-groupe-stats test-border">
  <div class="row test-border">
    <div class="col-lg-4 d-none d-lg-block test-border"> <!-- CARD ONLY > lg -->
      <?php $this->load->view('groupes/partials/card_individual.php', array('tag' => 'h1')) ?>
    </div>
    <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5 test-border">
      <a class="btn btn-outline-primary mx-2 mt-4" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
        <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
        Retour profil
      </a>
      <h1 class="mb-4 mt-4">Les statistiques du groupe <?= name_group($title) ?></h1>
      <p>Sur cette page, vous trouverez le détail des <b>statistiques de vote</b> du groupe <i><?= name_group($title) ?></i>.</p>
      <p>Quelle est la cohésion interne du groupe ? Les députés du groupe participent-ils souvent aux scrutins ? Quelle est la proximité idéologique entre le groupe <i><?= name_group($title) ?></i> et les autres groupes de l'Assemblée nationale ?</p>
      <p>Vous trouverez sur cette page un <b>historique</b> des statistiques du groupe <?= name_group($title) ?>. Pour avoir plus d'information sur l'historique du groupe, <a href="#link-stats">cliquez ici</a>.</p>
      <p>Ces statistiques sont développées par l'équipe de Datan. Pour plus d'information sur nos statistiques, <a href="<?= base_url() ?>statistiques/aide">cliquez ici</a>.</p>
      <div class="mt-5 test-border">
        <h2>Participation aux votes</h2>
        <p>Depuis le début de la législature, le taux de participation moyen du groupe <i><?= name_group($title) ?></i> est de <span class="font-weight-bold text-primary"><?= $stats['participation']['value'] ?>%</span>. Autrement dit, en moyenne, <?= $stats['participation']['value'] ?>% des députés du groupe prennent part aux scrutins solennels. Le groupe participe <?= $edito_participation ?> que la moyenne des autres groupes, qui est de <?= $statsAverage['participation'] ?>%.</p>
        <p>Retrouvez ci-dessous l'historique du taux de participation aux scrutins du groupe <i><?= name_group($title) ?></i>.</p>
        <div class="card">
          <div class="card-body pb-0">
            <h3>Historique par législature</h3>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $stats_history['participation'], 'type' => 'pct', 'terms' => TRUE, 'divided_by' => 1, 'grid' => TRUE)) ?>
          </div>
        </div>
        <div class="card mt-5">
          <div class="card-body pb-0">
            <h3>Historique par mois</h3>
            <p>Législature XX</p>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_chartJS.php', array('stats_history_chart' => $stats_monthly['participation'], 'type' => 'participation', 'max' => 100)) ?>
          </div>
        </div>
      </div>
      <div class="mt-5 test-border">
        <h2>Cohésion au sein du groupe</h2>
        <p>Pour la législature actuelle, le taux de cohésion du groupe <i><?= name_group($title) ?></i> est de <span class="font-weight-bold text-primary"><?= round($stats['cohesion']['value'], 2) ?></span>. Plus le score de cohésion est proche de 1, plus le groupe est uni quand il s'agit de voter dans l'hémicycle.</p>
        <p>Le groupe <i><?= name_group($title) ?></i> peut être considéré comme <?= $edito_cohesion['absolute'] ?> soudé quand il s'agit de voter. En effet, le groupe est <?= $edito_cohesion['relative'] ?> soudé que la moyenne des autres groupes, qui est de <?= round($statsAverage['cohesion'], 2) ?>.</p>
        <p>Retrouvez ci-dessous l'historique du taux de cohésion du groupe <i><?= name_group($title) ?></i>.</p>
        <div class="card mt-5">
          <div class="card-body pb-0">
            <h3>Historique par législature</h3>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $stats_history['cohesion'], 'type' => 'score', 'terms' => TRUE, 'divided_by' => 1, 'grid' => TRUE)) ?>
          </div>
        </div>
        <div class="card mt-5">
          <div class="card-body pb-0">
            <h3>Historique par mois</h3>
            <p>Législature XX</p>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_chartJS.php', array('stats_history_chart' => $stats_monthly['cohesion'], 'type' => 'cohesion', 'max' => 1)) ?>
          </div>
        </div>
      </div>
      <?php if ($stats_history['majority']): ?>
        <div class="mt-5 test-border">
          <h2>Proximité avec la majorité présidentielle</h2>
          <p>Pour la législature actuelle, le groupe <i><?= name_group($title) ?></i> a voté sur la même ligne que la majorité présidentielle dans <span class="font-weight-bold text-primary"><?= $stats['majority']['value'] ?>%</span> des cas.</p>
          <p>Le groupe est <?= $edito_majorite ?> proche de la majorité présidentielle que la moyenne des autres groupes politiques, qui est de <?= $statsAverage['majority'] ?>%.</p>
          <p>Retrouvez ci-dessous l'historique de la proximité avec la majorité présidentielle du groupe <i><?= name_group($title) ?></i>.</p>
          <div class="card mt-5">
            <div class="card-body pb-0">
              <h3>Historique par législature</h3>
              <p>CCCC</p>
              <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $stats_history['majority'], 'type' => 'pct', 'terms' => TRUE, 'divided_by' => 1, 'grid' => TRUE)) ?>
            </div>
          </div>
          <div class="card mt-5">
            <div class="card-body pb-0">
              <h3>Historique par mois</h3>
              <p>Législature XX</p>
              <p>CCCC</p>
              <?php $this->load->view('groupes/partials/stats_chartJS.php', array('stats_history_chart' => $stats_monthly['majority'], 'type' => 'majority', 'max' => 100)) ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="mt-5 test-border">
        <h2>Proximité avec chaque groupe politique</h2>
        <p>Le groupe <i><?= name_group($title) ?></i> vote-t-il souvent avec les autres groupes politiques qui composent l'Assemblée nationale ?</p>
        <p>Nous mesurons la proximité entre deux groupes au moment du vote. Deux groupes sont considérés comme proches s'ils votent souvent ensemble dans l'hémicycle.</p>
        <p>
          Pour la législature actuelle, le groupe avec lequel le groupe <i><?= name_group($title) ?></i> est le plus proche est <i><?= name_group($accord_groupes_first['libelle']) ?></i>.
          En effet, les deux groupes ont eu la même position dans <span class="font-weight-bold text-primary"><?= $accord_groupes_first['score'] ?>%</span> des cas.</p>
        <p>Retrouvez ci-dessous l'historique de proximité du groupe <i><?= name_group($title) ?></i>.</p>
        <div class="card mt-5">
          <div class="card-body pb-0">
            <h3>Historique par mois</h3>
            <p>Législature XX</p>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_chartJS_multiline.php') ?>
          </div>
        </div>
      </div>
      <div class="mt-5 test-border">
        <h2>Le nombre de députés membre du groupe <?= $groupe['libelleAbrev'] ?></h2>
        <p>Le groupe <i><?= name_group($title) ?></i> compte actuellement <span class="font-weight-bold text-primary"><?= $groupe['effectif'] ?> députés</span>. Cela représente <?= $groupe['effectifShare'] ?>% des députés de l'Assemblée nationale. Pour rappel, l'Assemblée compte 577 députés.</p>
        <p>Retrouvez ci-dessous un aperçu du nombre de députés membres du groupe politique <?= $groupe['libelleAbrev'] ?>.</p>
        <div class="card">
          <div class="card-body pb-0">
            <h3>Classement des groupes de la <?= $groupe['legislature'] ?><sup>ème</sup> législature</h3>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $members, 'type' => 'score', 'max' => 100, 'terms' => FALSE, 'divided_by' => $members_max, 'grid' => FALSE)) ?>
          </div>
        </div>
        <div class="card mt-5">
          <div class="card-body pb-0">
            <h3>Historique par mois</h3>
            <p>Législature XX</p>
            <p>CCCC</p>
            <?php $members_history[0]['effectif'] ?>
            <?php $this->load->view('groupes/partials/stats_chartJS.php', array('stats_history_chart' => $members_history, 'type' => 'members', 'max' => $members_max_history + 10)) ?>
          </div>
        </div>
      </div>
      <div class="mt-5 test-border">
        <h2>L'âge moyen des députés du groupe <?= $groupe['libelleAbrev'] ?></h2>
        <p>L'âge moyen des députés membres du groupe <i><?= name_group($title) ?></i> est de <span class="font-weight-bold text-primary"><?= $groupe['age'] ?> ans</span>.</p>
        <p>L'âge moyen des députés de l'Assemblée nationale est de <?= $ageMean ?> ans. Les députés du groupe <i><?= name_group($title) ?></i> sont donc <?= $ageEdited ?> âgés que la moyenne de l'Assemblée.</p>
        <p>Retrouvez ci-dessous un aperçu de l'âge moyen des députés membres du groupe politique <?= $groupe['libelleAbrev'] ?>.</p>
        <div class="card">
          <div class="card-body pb-0">
            <h3>Classement des groupes de la <?= $groupe['legislature'] ?><sup>ème</sup> législature</h3>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $age, 'type' => 'score', 'max' => 100, 'terms' => FALSE, 'divided_by' => $age_max, 'grid' => FALSE)) ?>
          </div>
        </div>
        <div class="card mt-5">
          <div class="card-body pb-0">
            <h3>Classement des groupes de la <?= $groupe['legislature'] ?><sup>ème</sup> législature</h3>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $orga_history['age'], 'type' => 'score', 'max' => 100, 'terms' => FALSE, 'divided_by' => $age_max, 'grid' => FALSE)) ?>
          </div>
        </div>
      </div>
      <div class="mt-5 test-border">
        <h2>Le taux de féminisation au sein du groupe <?= $groupe['libelleAbrev'] ?></h2>
        <p>Il y a <span class="font-weight-bold text-primary"><?= $groupe['womenN'] ?> députées femmes</span> au sein du groupe <i><?= name_group($title) ?></i>. Cela représente <?= $groupe['womenPct'] ?>% des de l'ensemble des membres du groupe. C'est <?= $womenEdited ?> que la moyenne de l'Assemblée nationale, qui est de <?= $womenPctTotal ?>%.</p>
        <p>Retrouvez ci-dessous un aperçu de la féminisation du groupe groupe politique <?= $groupe['libelleAbrev'] ?>.</p>
        <div class="card">
          <div class="card-body pb-0">
            <h3>Classement des groupes de la <?= $groupe['legislature'] ?><sup>ème</sup> législature</h3>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $women, 'type' => 'pct', 'max' => 100, 'terms' => FALSE, 'divided_by' => $women_max, 'grid' => FALSE)) ?>
          </div>
        </div>
        <div class="card mt-5">
          <div class="card-body pb-0">
            <h3>Historique par législature</h3>
            <p>CCCC</p>
            <?php $this->load->view('groupes/partials/stats_vertical.php', array('stats_history_chart' => $orga_history['womenPct'], 'type' => 'pct', 'max' => 100, 'terms' => FALSE, 'divided_by' => 1, 'grid' => FALSE)) ?>
          </div>
        </div>
      </div>
      <div class="mt-5 test-border">
        <h2 class="anchor" id="link-stats">Historique du groupe <?= name_group($title) ?></h2>
        <p>Sur cette page, vous trouvez un historique des statistiques du groupe. Pour chaque groupe politique, nous avons répertorié les anciens ou nouveaux groupes qui leur sont liés.</p>
        <p>Sur Datan, nous récupérons les statistiques de l'Assemblée nationale depuis la 14<sup>ème</sup> législature (2012).</p>
        <p>Vous trouverez ci-dessous la liste des groupes analysés sur le site Datan et liés au groupe <i><?= name_group($title) ?></i>.</p>
        <div class="row">
          <?php foreach ($history_list as $key => $value): ?>
            <div class="col-6">
              <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $value, 'tag' => 'span', 'cat' => $value['legislature'].'<sup>ème</sup> législature', 'stats' => NULL)) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('groupes/partials/mps_footer.php') ?>
