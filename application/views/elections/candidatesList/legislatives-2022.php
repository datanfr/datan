<div class="row mt-5 mb-3">
  <div class="col-12">
    <h2>Découvrez la <span class="font-weight-bold text-primary">candidature des députés</span> aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?></h2>
  </div>
  <div class="col-lg-9 mt-3">
    <p>Notre équipe a répertorié <?= $candidatsN ?> député<?= $candidatsN > 1 ? 's' : '' ?> candidat<?= $candidatsN > 1 ? 's' : '' ?> aux élections législatives de 2022. Si un député n'est pas dans la liste, n'hésitez pas à nous envoyer un email : info@datan.fr</p>
  </div>
  <div class="col-12 d-flex flex-column flex-lg-row">
    <div class="d-flex flex-even px-2">
      <div class="d-flex align-items-center">
        <span class="candidatsN"><?= $candidatsN ?></span>
      </div>
      <div class="d-flex align-items-center ml-1">
        <span>député<?= $candidatsN > 1 ? "s" : "" ?> <b>candidat<?= $candidatsN > 1 ? "s" : "" ?> à leur réélection</b></span>
      </div>
    </div>
    <?php if ($state > 0): ?>
      <div class="d-flex flex-even px-2">
        <div class="d-flex align-items-center">
          <span class="candidatsN"><?= $candidatsN_second ?></span>
        </div>
        <div class="d-flex align-items-center ml-1">
          <span>maintenu<?= $candidatsN_second > 1 ? "s" : "" ?> pour le <b>second tour</b></span>
        </div>
      </div>
      <div class="d-flex flex-even px-2">
        <div class="d-flex align-items-center">
          <span class="candidatsN"><?= $candidatsN_eliminated ?></span>
        </div>
        <div class="d-flex align-items-center ml-1">
          <span><b>éliminé<?= $candidatsN_eliminated > 1 ? "s" : "" ?></b></span>
        </div>
      </div>
      <div class="d-flex flex-even px-2">
        <div class="d-flex align-items-center">
          <span class="candidatsN"><?= $candidatsN_elected ?></span>
        </div>
        <div class="d-flex align-items-center ml-1">
          <span><b>élu<?= $candidatsN_elected > 1 ? "s" : "" ?></b></span>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<div class="row">
  <div class="pb-4 col-lg-3 search-element sticky-top sticky-top-lg">
    <div class="sticky-top sticky-offset">
      <!-- Search -->
      <div class="mt-3 mt-lg-0">
        <input type="text" id="quicksearch" placeholder="Rechercher un député..." />
      </div>
      <!-- Filters state -->
      <div class="filters stateChange mt-md-5 d-none d-lg-block">
        <input class="radio-btn" name="state" id="radio-200" type="radio" checked value="*">
        <label for="radio-200" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center"><b>Tous les députés</b></span>
        </label>
        <input class="radio-btn" name="state" id="radio-201" type="radio" value=".candidate">
        <label for="radio-201" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center"><b>Députés candidats</b></span>
        </label>
        <input class="radio-btn" name="state" id="radio-202" type="radio" value=".not-candidate">
        <label for="radio-202" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center"><b>Députés non candidats</b></span>
        </label>
        <input class="radio-btn" name="state" id="radio-204" type="radio" value=".elected">
        <label for="radio-204" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center"><b>Élu</b></span>
        </label>
        <input class="radio-btn" name="state" id="radio-205" type="radio" value=".lost">
        <label for="radio-205" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center"><b>Éliminé</b></span>
        </label>
      </div>
      <div class="filters stateChange mt-md-3 d-none d-lg-block">
        <p class="surtitre mt-3">Filtrer par département</p>
        <select class="custom-select filters" id="districtChange" onchange="districtChange()">
          <option selected value="*">Tous les députés</option>
          <?php foreach ($districts as $district): ?>
            <option value=".<?= $district['id'] ?>"><?= $district['libelle'] ?></option>
          <?php endforeach; ?>
        </select>
        <p class="surtitre mt-3">Filtrer par groupe</p>
        <select class="custom-select filters" id="groupChange" onchange="groupChange()">
          <option selected value="*">Tous les députés</option>
          <?php foreach ($groupes as $group): ?>
            <option value=".gp-<?= mb_strtolower($group['libelleAbrev']) ?>"><?= $group['libelle'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
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
