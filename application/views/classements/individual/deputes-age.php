      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel est le député le plus âgé ? Qui est le moins jeune ?</p>
          <p>Si vous attachez de l'importance à la représentativité « sociale » du parlement, l'âge est un facteur à prendre en compte. Par exemple, certains estiment que les jeunes seront moins bien représentés si aucun député n'est issu de cette classe d'âge.</p>
          <p>Les chercheurs montrent que les députés, partout dans le monde, sont en moyenne plus âgés que la population. En France, <b>la moyenne d'âge des députés est de <?= $ageMean ?> ans</b>. C'est <?= $ageDiffStr ?> de plus que la <a href="<?= base_url() ?>statistiques/aide#ageMoyen">moyenne d'âge des Français éligibles</a> (plus de 18 ans), qui est de <?= $ageMeanPop ?> ans.</p>
          <p><?= ucfirst($mpOldestGender['le']) ?> <?= $mpOldestGender['depute'] ?> en activité le plus âgé<?= $mpOldestGender['e'] ?> est <a href="<?= base_url() ?>deputes/<?= $mpOldest['dptSlug'] ?>/depute_<?= $mpOldest['nameUrl'] ?>" target="_blank"><?= $mpOldest['name'] ?></a> (<?= $mpOldest['age'] ?> ans), membre du groupe <?= $mpOldest["libelle"] ?>.</p>
          <p>
            <a href="<?= base_url() ?>deputes/<?= $mpYoungest['dptSlug'] ?>/depute_<?= $mpYoungest['nameUrl'] ?>" target="_blank"><?= $mpYoungest['name'] ?></a> (<?= $mpYoungest['age'] ?> ans) est <?= $mpYoungestGender['le'] ?> <?= $mpYoungestGender['depute'] ?> <?= $mpYoungestGender['le'] ?> plus jeune.
            <?= ucfirst($mpYoungestGender['pronom']) ?> est membre du groupe <?= $mpYoungest['libelle'] ?>.
          </p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center"><?= ucfirst($mpOldestGender['le']) ?> <?= $mpOldestGender['depute'] ?> <?= $mpOldestGender["le"] ?> plus âgé<?= $mpOldestGender["e"] ?></h2>
          <div class="card card-depute">
            <div class="liseret" style="background-color: <?= $mpOldest["couleurAssociee"] ?>"></div>
            <div class="card-avatar">
              <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" data-src="<?= base_url(); ?>assets/imgs/deputes_nobg/depute_<?= substr($mpOldest["mpId"], 2) ?>.png" alt="<?= $mpOldest['nameFirst'].' '.$mpOldest['nameLast'] ?>">
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
              <div>
                <span class="d-block card-title">
                  <a href="<?= base_url(); ?>deputes/<?= $mpOldest['dptSlug'].'/depute_'.$mpOldest['nameUrl'] ?>" class="stretched-link no-decoration"><?= $mpOldest['nameFirst'] .' ' . $mpOldest['nameLast'] ?></a>
                </span>
                <span class="badge badge-primary badge-stats mb-3"><?= $mpOldest["age"] ?> ans</span>
                <span class="d-block"><?= $mpOldest["departementNom"] ?> (<?= $mpOldest["departementCode"] ?>)</span>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $mpOldest["libelle"] ?> (<?= $mpOldest["libelleAbrev"] ?>)</span>
            </div>
          </div>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center"><?= ucfirst($mpYoungestGender['le']) ?> <?= $mpYoungestGender['depute'] ?> <?= $mpYoungestGender["le"] ?> plus jeune</h2>
          <div class="card card-depute">
            <div class="liseret" style="background-color: <?= $mpYoungest["couleurAssociee"] ?>"></div>
            <div class="card-avatar">
              <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" data-src="<?= base_url(); ?>assets/imgs/deputes_nobg/depute_<?= substr($mpYoungest["mpId"], 2) ?>.png" alt="<?= $mpYoungest['nameFirst'].' '.$mpYoungest['nameLast'] ?>">
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
              <div>
                <span class="d-block card-title">
                  <a href="<?= base_url(); ?>deputes/<?= $mpYoungest['dptSlug'].'/depute_'.$mpYoungest['nameUrl'] ?>" class="stretched-link no-decoration"><?= $mpYoungest['nameFirst'] .' ' . $mpYoungest['nameLast'] ?></a>
                </span>
                <span class="badge badge-primary badge-stats mb-3"><?= $mpYoungest["age"] ?> ans</span>
                <span class="d-block"><?= $mpYoungest["departementNom"] ?> (<?= $mpYoungest["departementCode"] ?>)</span>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $mpYoungest["libelle"] ?> (<?= $mpYoungest["libelleAbrev"] ?>)</span>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-5">Classement des députés selon leur âge</h2>
        <table class="table table-stats" id="table-stats">
          <thead>
            <tr>
              <th class="text-center all">N°</th>
              <th class="text-center min-tablet">Député</th>
              <th class="text-center all">Groupe</th>
              <th class="text-center all">Âge</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; ?>
            <?php foreach ($deputes as $depute): ?>
              <tr>
                <td class="text-center"><?= $depute["rank"] ?></td>
                <td class="text-center"><?= $depute["nameFirst"]." ".$depute["nameLast"] ?></td>
                <td class="text-center"><?= $depute["libelleAbrev"] ?></td>
                <td class="text-center"><?= $depute["age"] ?> ans</td>
              </tr>
              <?php $i++; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
