<div class="container pg-elections-candidats">
  <div class="row bloc-titre">
    <div class="col-12">
      <h1><?= $title ?></h1>
    </div>
  </div>
</div>
<div class="container-fluid pg-elections-candidats infosIndividual py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-lg-7 my-4">
        <h2>Informations</h2>
        <p>Les <?= mb_strtolower($election['libelle']) ?> <?= $election['dateYear'] ?> se dérouleront en deux tours.</p>
        <p>Le premier tour se tiendra le <?= $election['dateFirstRoundFr'] ?>, tandis que le second tour se déroulera le <?= $election['dateSecondRoundFr'] ?>.</p>
        <p><b>Attention</b>, au vu de la crise sanitaire de la Covid-19, les dates des <?= mb_strtolower($election['libelle']) ?> sont susceptibles de changer.</p>
        <p>
          Découvrez sur cette page les députés candidats aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>.
          <?php if ($election['candidatsN']): ?>
            Nous avons jusqu'à présent répertorié <span class="font-weight-bold"><?= $election['candidatsN'] ?> député<?= $election['candidatsN'] > 1 ? "s" : NULL ?> candidat<?= $election['candidatsN'] > 1 ? "s" : NULL ?></span>.
            <?php else: ?>
            Nous avons jusqu'à présent répertorié <span class="font-weight-bold">aucun député candidat</span>.
          <?php endif; ?>
        </p>
        <p>Un député candidat ne se trouve pas dans la liste ? N'hésitez pas à nous le faire savoir: <a href="mailto:contact@datan.fr">contact@datan.fr</a> !</p>
      </div>
    </div>
  </div>
</div>
<div class="container pg-elections-candidats py-5">
  <div class="row">
    <?php if ($election['id'] == 1): ?>
      <div class="col-md-8 col-lg-7">
        <h2 class="my-4">La couleur politique actuelle des conseils régionaux</h2>
        <p>Les dernières élections régionales ont eu lieu en 2015. Pour retrouver les résultats, <a href="https://www.interieur.gouv.fr/Elections/Les-resultats/Regionales/elecresult__regionales-2015/(path)/regionales-2015/index.html" target="_blank" rel="nofollow noreferrer noopener">cliquez ici</a>.</p>
        <p>Dans les conseils régionaux, les partis politiques qui arrivent en têtes aux élections reçoivent la majorité des sièges et en prennent la présidence. C'est donc la liste arrivée en tête qui se retrouve à la tête de la région.</p>
        <p>Découvrez sur cette carte la couleur politique des différentes régions de 2015 à 2021.</p>
        <p>CARTE</p>
      </div>
    <?php endif; ?>
    <?php if (!empty($electionInfos)): ?>
      <div class="col-md-8 col-lg-4 offset-lg-1 mt-5 mt-lg-0">
        <div class="mt-4 infosGeneral">
          <h2 class="title ml-md-5 ml-3">Mieux comprendre</h2>
          <div class="px-4">
            <?= $electionInfos ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <div class="row my-5">
    <div class="col-12">
      <h2>Retrouvez les <?= $election['candidatsN'] ?> députés candidats aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?></h2>
    </div>
  </div>
  <div class="row">
    <div class="pb-4 col-lg-3 search-element sticky-top sticky-top-lg">
      <div class="sticky-top sticky-offset">
        <!-- Groupes -->
        <div class="d-flex flex-column d-lg-none">
          <h3>
            Les <span class="text-primary">577</span> députés candidats aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>
          </h3>
          <div class="mt-3 badges-groupes">
            <?php foreach ($districts as $district): ?>
              <a class="badge badge-primary no-decoration" href="<?= base_url() ?>elections/<?= mb_strtolower($district['libelle']) ?>/membres">
                <span>XX</span> <?= $district['libelle'] ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <!-- Search -->
        <div class="mt-3 mt-lg-0">
          <input type="text" id="quicksearch" placeholder="Recherchez un député..." />
        </div>
        <!-- Filters -->
        <?php if (count($districts) <= 25): ?>
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
                <h3 class="d-block card-title">
                  <a href="<?php echo base_url(); ?>deputes/<?php echo $depute['dptSlug'].'/depute_'.$depute['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $depute['nameFirst'] .' ' . $depute['nameLast'] ?></a>
                </h3>
                <span class="d-block"><?= $depute["departementNom"] ?> (<?= $depute["departementCode"] ?>)</span>
              </div>
              <div class="card-footer d-flex justify-content-center align-items-center">
                <span><?= $depute["districtLibelle"] ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
