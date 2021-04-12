    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
      <div class="container d-flex flex-column justify-content-center py-2">
        <h1><?= $title ?></h1>
      </div>
    </div>
    <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
    <div class="container pg-groupe-membres my-4">
      <!-- ROW PRESIDENT -->
      <?php if ($groupe['libelleAbrev'] != 'NI'): ?>
        <div class="row my-5">
          <div class="col-12 d-flex flex-column align-items-center">
            <h2>Président<?php if ($president['civ'] == 'Mme'): ?>e<?php endif; ?></h2>
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $president, 'tag' => 'h3')) ?>
          </div>
        </div>
      <?php endif; ?>
      <!-- ROW MEMBRES -->
      <div class="row my-5">
        <?php if ($groupe['libelleAbrev'] != 'NI'): ?>
          <div class="col-12 d-flex justify-content-center">
            <h2>Membres</h2>
          </div>
        <?php endif; ?>
        <?php foreach ($membres as $mp): ?>
          <div class="col-lg-4 col-md-6 d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mp, 'tag' => 'h3')) ?>
          </div>
        <?php endforeach; ?>
      </div>
      <!-- ROW APPARENTES -->
      <?php if ($groupe['libelleAbrev'] != 'NI' && !empty($apparentes)): ?>
        <div class="row my-5">
          <div class="col-12 d-flex justify-content-center">
            <h2>Membres apparentés</h2>
          </div>
          <?php foreach ($apparentes as $mp): ?>
            <div class="col-lg-4 col-md-6 d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mp, 'tag' => 'h3')) ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
