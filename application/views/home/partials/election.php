<!-- BLOC ELECTION -->
<div class="row bloc-election" id="pattern_background">
  <div class="container p-md-0">
    <div class="row py-4">
      <div class="col-12">
        <h2 class="text-center my-4"><span class="text-primary">Élections législatives 2022</span><span style="display: block" class="mt-3">Découvrez les députés réélus</span></h2>
      </div>
    </div>
    <div class="row pt-2 pb-5">
      <div class="col-md-7 d-flex flex-column justify-content-center">
        <p>Les élections législatives prochaines se sont déroulées les dimanches <b>12 et 19 juin 2022</b>.</p>
        <p>Aux élections législatives, <?= $candidatsN ?> députés étaient candidats à leur réélection.</p>
        <p>Au total, <span class="font-weight-bold text-primary"><?= $electedN ?> députés</span> ont été réélus pendant les législatives de 2022.</p>
      </div>
      <div class="col-md-5 mt-5 mt-md-0">
        <div class="d-flex justify-content-center">
          <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $candidatRandom, 'tag' => 'span', 'cat' => true, 'logo' => false)) ?>
        </div>
      </div>
    </div>
    <div class="row pb-5">
      <div class="col-12 d-flex justify-content-center">
        <a href="<?= base_url();?>elections/legislatives-2022" class="no-decoration">
          <button type="button" class="btn btn-primary">Découvrez les députés réélus</button>
        </a>
      </div>
    </div>
  </div>
</div> <!-- END BLOC ELECTIONS -->
