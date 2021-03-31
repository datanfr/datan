<div class="container pg-elections-candidats">
  <div class="row bloc-titre">
    <div class="col-12">
      <h1><?= $title ?></h1>
    </div>
    <?php if (!empty($electionInfos)): ?>
      <div class="col-md-8 col-lg-7">
        <div class="mt-4">
          <?= $electionInfos ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <div class="row mt-2">
    <div class="pb-4 col-lg-3 search-element sticky-top sticky-top-lg">
      <div class="sticky-top sticky-offset">
        <!-- Groupes -->
        <div class="d-flex flex-column d-lg-none">
          <?php if ($active): ?>
            <h3>
              Les <span class="text-primary">577</span> députés de l'Assemblée nationale
            </h3>
          <?php else: ?>
            <h3>
              <span class="text-primary"><?= $number_inactive ?></span> députés de la 15<sup>e</sup> législature ne sont plus en activité
            </h3>
          <?php endif; ?>
          <?php if ($active): ?>
            <div class="mt-3 badges-groupes">
              <?php foreach ($groupes as $groupe): ?>
                <a class="badge badge-primary no-decoration" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">
                  <span><?= $groupe['effectif'] ?></span> <?= $groupe['libelle'] ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- Search -->
        <div class="mt-3 mt-lg-0">
          <input type="text" id="quicksearch" placeholder="Recherchez un député..." />
        </div>
        <!-- Filters -->
        <div class="filters mt-md-4 d-none d-lg-block">
          <input class="radio-btn" name="radio-collection" id="radio-1" type="radio" checked="" value="*">
          <label for="radio-1" class="radio-label d-flex align-items-center">
            <span class="d-flex align-items-center"><b>Tous les députés</b></span>
          </label>
          <?php $i=2 ?>
          <?php foreach ($districts as $district): ?>
            <input class="radio-btn" name="radio-collection" id="radio-<?= $i ?>" type="radio" value=".<?= strtolower($district['id']) ?>">
            <label class="radio-label d-flex align-items-center" for="radio-<?= $i ?>">
              <span class="d-flex align-items-center"><?= $district['libelle'] ?></span>
            </label>
            <?php $i++ ?>
          <?php endforeach; ?>
        </div>
        <!-- Députés inactifs bouton -->
        <div class="d-none d-lg-flex justify-content-center mt-md-5">
          <a class="btn btn-outline-primary d-none d-md-block" href="<?= base_url() ?>deputes/inactifs">Liste des députés <b>plus en activité</b></a>
        </div>
      </div>
    </div>
    <div class="col-lg-9 col-md-12">
      <div class="row mt-2 sorting">
        <?php foreach ($deputes as $depute): ?>
          <div class="col-lg-4 col-md-6 sorting-item <?= strtolower($depute["districtId"]) ?>">
            <div class="card card-depute">
              <div class="liseret" style="background-color: <?= $depute["couleurAssociee"] ?>"></div>
              <div class="card-avatar">
                <?php if ($depute['img']): ?>
                  <img class="img-lazy placeholder" src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" data-src="<?= base_url(); ?>assets/imgs/deputes_nobg/depute_<?= substr($depute["mpId"], 2) ?>.png" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>">
                  <?php else: ?>
                  <img class="img-lazy placeholder" src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" alt="<?= $depute['nameFirst'].' '.$depute['nameLast'] ?>">
                <?php endif; ?>
              </div>
              <div class="card-body">
                <h2 class="d-block card-title">
                  <a href="<?php echo base_url(); ?>deputes/<?php echo $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
                </h2>
                <span class="d-block"><?= $depute["departementNom"] ?> (<?= $depute["departementCode"] ?>)</span>
              </div>
              <div class="card-footer d-flex justify-content-center align-items-center">
                <span><?= $depute["libelle"] ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
