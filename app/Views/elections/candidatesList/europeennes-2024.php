<div class="row mt-5 mb-3">
  <div class="col-12">
    <h2>Découvrez la <span class="font-weight-bold text-primary">candidature des députés ou anciens députés</span> aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?></h2>
  </div>
  <div class="col-lg-9 mt-3">
    <p>Notre équipe a répertorié <?= $candidatsN ?> député<?= $candidatsN > 1 ? 's' : '' ?> ou anciens députés candidat<?= $candidatsN > 1 ? 's' : '' ?> aux élections européennes de 2024. Si un député n'est pas dans la liste, n'hésitez pas à nous envoyer un email : info@datan.fr</p>
  </div>
  <div class="col-12 d-flex flex-column flex-lg-row">
    <div class="d-flex flex-even px-2">
      <div class="d-flex align-items-center">
        <span class="candidatsN"><?= $candidatsN ?></span>
      </div>
      <div class="d-flex align-items-center ml-1">
        <span>député<?= $candidatsN > 1 ? "s" : "" ?> ou anciens députés <b>candidat<?= $candidatsN > 1 ? "s" : "" ?> aux élections européennes 2024</b></span>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="pb-4 col-lg-3 search-element sticky-top sticky-top-lg">
    <div class="sticky-top sticky-offset">
      <!-- Search -->
      <div class="mt-3 mt-lg-0">
        <input type="text" id="quicksearch" placeholder="Rechercher un député..." />
      </div>
      <!-- Filters state (removed so far) -->
    </div>
  </div>
  <div class="col-lg-9 col-md-12">
    <div class="row mt-2 sorting">
      <?php foreach ($deputes as $depute): ?>
        <div class="col-md-6 col-xl-4 sorting-item <?= $depute['candidature'] == 1 ? 'candidate' : 'not-candidate' ?> <?= strtolower($depute['districtId']) ?> <?= strtolower($depute['electionState']) ?> gp-<?= mb_strtolower($depute['libelleAbrev']) ?>">
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute, 'tag' => 'h3', 'footer' => 'active', 'logo' => false)) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
