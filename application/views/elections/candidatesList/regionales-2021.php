<div class="row mt-5 mb-3">
  <div class="col-12">
    <h2>Retrouvez les députés candidats aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?></h2>
  </div>
  <div class="col-12 d-flex flex-column flex-lg-row">
    <div class="d-flex flex-even px-2">
      <div class="d-flex align-items-center">
        <span class="candidatsN"><?= $candidatsN ?></span>
      </div>
      <div class="d-flex align-items-center ml-4">
        <span>Au total, <?= $candidatsN ?> députés étaient candidats au <b>premier tour</b> des <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>.</span>
      </div>
    </div>
    <?php if ($state > 0): ?>
      <div class="d-flex flex-even px-2">
        <div class="d-flex align-items-center">
          <span class="candidatsN"><?= $candidatsN_second ?></span>
        </div>
        <div class="d-flex align-items-center ml-4">
          <span>Après le premier tour, <?= $candidatsN_second ?> députés se sont maintenus au <b>second tour</b> des <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>.</span>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($state > 1): ?>
      <div class="d-flex flex-even px-2">
        <div class="d-flex align-items-center">
          <span class="candidatsN"><?= $candidatsN_elected ?></span>
        </div>
        <div class="d-flex align-items-center ml-4">
          <span>Après le second tour, <?= $candidatsN_elected ?> députés ont été <b>élus</b> lors des <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>.</span>
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
        <input type="text" id="quicksearch" placeholder="Recherchez un député..." />
      </div>
      <!-- Filters state -->
      <div class="filters filtersState mt-md-4 d-none d-lg-block">
        <p class="surtitre">Résultat de l'élection</p>
        <input class="radio-btn" name="state" id="radio-100" type="radio" checked="" value="*">
        <label for="radio-100" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center"><b>Tous les députés</b></span>
        </label>
        <input class="radio-btn" name="state" id="radio-101" type="radio" value=".elected">
        <label for="radio-101" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center">Élu</span>
        </label>
        <input class="radio-btn" name="state" id="radio-102" type="radio" value=".lost">
        <label for="radio-102" class="radio-label d-flex align-items-center">
          <span class="d-flex align-items-center">Éliminé</span>
        </label>
      </div>
      <!-- Filters -->
      <?php if (count($districts) <= 25): ?>
        <div class="filters filtersDistrict mt-md-5 d-none d-lg-block">
          <p class="surtitre">Région</p>
          <input class="radio-btn" name="district" id="radio-1" type="radio" checked="" value="*">
          <label for="radio-1" class="radio-label d-flex align-items-center">
            <span class="d-flex align-items-center"><b>Tous les députés</b></span>
          </label>
          <?php $i=2 ?>
          <?php foreach ($districts as $district): ?>
            <input class="radio-btn" name="district" id="radio-<?= $i ?>" type="radio" value=".<?= strtolower($district['id']) ?>">
            <label for="radio-<?= $i ?>" class="radio-label d-flex align-items-center">
              <span class="d-flex align-items-center"><?= $district['libelle'] ?></span>
            </label>
            <?php $i++ ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <br>
        <select class="custom-select filters" id="selectFilter" onchange="changeFilterFunc()">
          <option selected value="*">Tous les députés</option>
          <?php foreach ($districts as $district): ?>
            <option value=".<?= $district['id'] ?>"><?= $district['libelle'] ?> (<?= $district['id'] ?>)</option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-9 col-md-12">
    <div class="row mt-2 sorting">
      <?php foreach ($deputes as $depute): ?>
        <div class="col-md-6 col-xl-4 sorting-item <?= strtolower($depute['districtId']) ?> <?= strtolower($depute['electionState']) ?>">
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $depute, 'tag' => 'h3', 'cat' => false)) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
