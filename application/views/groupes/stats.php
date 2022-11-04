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
      <h1 class="mb-0 mt-4">Les statistiques du groupe <?= name_group($title) ?></h1>
    </div>
  </div>
</div>
<?php $this->load->view('groupes/partials/mps_footer.php') ?>
