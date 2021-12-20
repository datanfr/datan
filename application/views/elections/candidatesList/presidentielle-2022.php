<div class="row mt-5 mb-3">
  <div class="col-12">
    <h2>Découvrez les députés candidats à l'élection présidentielle de 2022</h2>
  </div>
  <div class="col-12 mt-3">
    <p>Au total, nous avons répertorié <?= $candidatsN ?> député<?= $candidatsN > 1 ? 's' : '' ?> candidat<?= $candidatsN > 1 ? 's' : '' ?> à l'élection présidentielle de 2022.</p>
  </div>
</div>
<div class="row">
  <div class="col-12 d-flex flex-wrap justify-content-around">
    <?php foreach ($deputes as $depute): ?>
      <div class="d-flex justify-content-center">
        <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute, 'tag' => 'h3', 'cat' => false, 'logo' => false)) ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
