  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  </div>
  <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
  <div class="container pg-groupe-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4 ">
        <?php $this->load->view('groupes/partials/card_individual.php') ?>
      </div> <!-- END COL -->
      <!-- BIO VOTES -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5 bloc-votes-datan">
        <div class="row mt-4">
          <div class="col-12 btn-back text-center text-md-left">
            <a class="btn btn-outline-primary mx-2" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
              <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
              Retour profil groupe
            </a>
          </div>
        </div>
        <div class="row mt-5">
          <div class="col-12">
            <h2 class="text-center text-md-left">Les votes du groupe <?= $groupe['libelleAbrev'] ?> concernant <?= $field['libelle'] ?></h2>
          </div>
        </div>
        <div class="row mt-4">
          <?php foreach ($votes as $vote): ?>
            <div class="col-md-6 d-flex justify-content-center my-3">
              <?php $this->load->view('groupes/partials/card_vote.php', array('vote' => $vote)) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div> <!-- END CONTAINER -->
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid pg-groupe-individual bloc-others-container mt-5">
    <div class="container bloc-others">
      <?php if ($groupe['libelleAbrev'] != "NI"): ?>
        <div class="row">
          <div class="col-12">
            <h2>Président du groupe <?= $title ?></h2>
            <div class="row mt-3">
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $president['dptSlug'] ?>/depute_<?= $president['nameUrl'] ?>"><?= $president['nameFirst']." ".$president['nameLast'] ?></a>
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
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $membre['dptSlug'] ?>/depute_<?= $membre['nameUrl'] ?>"><?= $membre['nameFirst']." ".$membre['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= $groupe['libelleAbrev'] ?></a>
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
                  <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"><?= $mp['nameFirst']." ".$mp['nameLast'] ?></a>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-3">
              <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés apparentés au groupe <?= $groupe['libelleAbrev'] ?></a>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="row">
        <div class="col-12">
          <h2>Tous les groupes parlementaires en activité de la 15e législature</h2>
          <div class="row mt-3">
            <?php foreach ($groupesActifs as $group): ?>
              <div class="col-6 col-md-4 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($group['libelleAbrev']) ?>"><?= $group['libelle']." (".$group['libelleAbrev'].")" ?></a>
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
