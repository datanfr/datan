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
      <p>Sur cette page, vous trouverez le détail des <b>statistiques de vote</b> du groupe <?= name_group($title) ?>.</p>
      <p>Quelle est la cohésion interne du groupe ? Les députés du groupe participent-ils souvent aux scrutins ? Quelle est la proximité idéologique entre le groupe <?= name_group($title) ?> et les autres groupes de l'Assemblée nationale ?</p>
      <p>Vous trouverez sur cette page un <b>historique</b> des statistiques du groupe <?= name_group($title) ?>. Pour avoir plus d'information sur l'historique du groupe, <a href="#link-stats">cliquez ici</a>.</p>
      <p>Ces statistiques sont développées par l'équipe de Datan. Pour plus d'information sur nos statistiques, <a href="<?= base_url() ?>statistiques/aide">cliquez ici</a>.</p>
      <h2 class="anchor" id="link-stats">Historique du groupe <?= name_group($title) ?></h2>
      <p>Sur cette page, vous trouvez un historique des statistiques du groupe. Pour chaque groupe politique, nous avons répertorié les anciens ou nouveaux groupes qui leur sont liés.</p>
      <p>Sur Datan, nous récupérons les statistiques de l'Assemblée nationale depuis la 14<sup>ème</sup> législature (2012).</p>
      <p>Vous trouverez ci-dessous la liste des groupes analysés sur le site Datan et liés au groupe <?= name_group($title) ?>.</p>
      <div class="row">
        <?php foreach ($history_list as $key => $value): ?>
          <div class="col-6">
            <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $value, 'tag' => 'span', 'cat' => $value['legislature'].'<sup>ème</sup> législature')) ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('groupes/partials/mps_footer.php') ?>
