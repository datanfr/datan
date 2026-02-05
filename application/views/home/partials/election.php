<!-- BLOC ELECTION -->
<div class="row bloc-election" id="pattern_background">
  <div class="container p-md-0">
    <div class="row py-4">
      <div class="col-12">
        <h2 class="text-center my-4">Élections municipales 2026</h2>
        <h3 class="mt-3">Découvrez les députés candidats aux municipales</h3>
      </div>
    </div>
    <div class="row pt-2 pb-5">
      <div class="col-md-7 d-flex flex-column justify-content-center">
        <p>Les élections municipales se dérouleront les dimanches <b>15 et 22 mars 2026</b>.</p>
        <p>Notre équipe a répertorié <?= $candidatsN ?> député<?= $candidatsN > 1 ? "s" : "" ?> candidat<?= $candidatsN > 1 ? "s" : "" ?> aux municipales de 2026.</p>
      </div>
      <div class="col-md-5 mt-5 mt-md-0">
        <div class="d-flex justify-content-center">
          <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $candidatRandom, 'tag' => 'span', 'footer' => 'discover', 'logo' => false)) ?>
        </div>
      </div>
    </div>
    <div class="row pb-5">
      <div class="col-12 d-flex justify-content-center">
        <a href="<?= base_url();?>elections/legislatives-2022" class="no-decoration">
          <button type="button" class="btn btn-primary">Découvrez les députés candidats</button>
        </a>
      </div>
    </div>
  </div>
</div> <!-- END BLOC ELECTIONS -->
