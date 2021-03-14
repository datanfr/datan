      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel est le député de l'Assemblée nationale le plus loyal à son groupe parlementaire ? Qui est le plus rebelle ?</p>
          <p>La loyauté politique est un élément important à l'Assemblée nationale. Dans la plupart des cas, les députés suivent la ligne de leur groupe quand ils votent.</p>
          <p>Un député peut voter contre la position officielle de son groupe pour plusieurs raisons. Un député peut par exemple être rebelle quand il vote s'il n'est pas d'accord avec la position du groupe. De plus, il peut décider de voter contre la ligne du groupe si celle-ci est contraire aux intérêts de ses électeurs.</p>
          <p>
            Beaucoup de parlementaires sont très loyaux à leur groupe. Autrement dit, ils ne votent jamais contre la ligne de leur groupe parlementaire.
            En prenant en compte le nombre de votes, <?= $mpLoyalGender['le'] ?> député<?= $mpLoyalGender['e'] ?> <?= $mpLoyalGender['le'] ?> plus loyal<?= $mpLoyalGender['e'] ?> est <a href="<?= base_url() ?>deputes/<?= $mpLoyal['dptSlug'] ?>/depute_<?= $mpLoyal['nameUrl'] ?>" target="_blank"><?= $mpLoyal['name'] ?></a>.
            <?= ucfirst($mpLoyalGender['pronom']) ?> a voté sur la même ligne que son groupe dans <?= $mpLoyal['score'] ?> % des cas.
          </p>
          <p>
            Au contraire, peu de députés ne votent quasiment jamais comme la ligne de leur groupe parlementaire. <?= ucfirst($mpRebelGender['le']) ?> député<?= $mpRebelGender['e'] ?> le plus « rebelle » est <a href="<?= base_url() ?>deputes/<?= $mpRebel['dptSlug'] ?>/depute_<?= $mpRebel['nameUrl'] ?>" target="_blank"><?= $mpRebel['name'] ?></a>.
            <?= ucfirst($mpRebelGender['pronom']) ?> a voté sur la même ligne que son groupe dans <?= $mpRebel['score'] ?> % des cas.
          </p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center"><?= ucfirst($mpLoyalGender['le']) ?> <?= $mpLoyalGender['depute'] ?> <?= $mpLoyalGender["le"] ?> plus loyal<?= $mpLoyalGender["e"] ?></h2>
          <div class="card card-depute">
            <div class="liseret" style="background-color: <?= $mpLoyal["couleurAssociee"] ?>"></div>
            <div class="card-avatar">
              <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" data-src="<?= base_url(); ?>assets/imgs/deputes/depute_<?= substr($mpLoyal["mpId"], 2) ?>.png" alt="<?= $mpLoyal['nameFirst'].' '.$mpLoyal['nameLast'] ?>">
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
              <div>
                <span class="d-block card-title">
                  <a href="<?= base_url(); ?>deputes/<?= $mpLoyal['dptSlug'].'/depute_'.$mpLoyal['nameUrl'] ?>" class="stretched-link no-decoration"><?= $mpLoyal['nameFirst'] .' ' . $mpLoyal['nameLast'] ?></a>
                </span>
                <span class="badge badge-primary badge-stats mb-3"><?= $mpLoyal["score"] ?> %</span>
                <span class="d-block"><?= $mpLoyal["departementNom"] ?> (<?= $mpLoyal["departementCode"] ?>)</span>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $mpLoyal["libelle"] ?> (<?= $mpLoyal["libelleAbrev"] ?>)</span>
            </div>
          </div>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center"><?= ucfirst($mpRebelGender['le']) ?> <?= $mpRebelGender['depute'] ?> <?= $mpRebelGender["le"] ?> plus rebelle</h2>
          <div class="card card-depute">
            <div class="liseret" style="background-color: <?= $mpRebel["couleurAssociee"] ?>"></div>
            <div class="card-avatar">
              <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" data-src="<?= base_url(); ?>assets/imgs/deputes/depute_<?= substr($mpRebel["mpId"], 2) ?>.png" alt="<?= $mpRebel['nameFirst'].' '.$mpRebel['nameLast'] ?>">
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
              <div>
                <span class="d-block card-title">
                  <a href="<?= base_url(); ?>deputes/<?= $mpRebel['dptSlug'].'/depute_'.$mpRebel['nameUrl'] ?>" class="stretched-link no-decoration"><?= $mpRebel['nameFirst'] .' ' . $mpRebel['nameLast'] ?></a>
                </span>
                <span class="badge badge-primary badge-stats mb-3"><?= $mpRebel["score"] ?> %</span>
                <span class="d-block"><?= $mpRebel["departementNom"] ?> (<?= $mpRebel["departementCode"] ?>)</span>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $mpRebel["libelle"] ?> (<?= $mpRebel["libelleAbrev"] ?>)</span>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-5">Classement des députés en fonction de leur taux de loyauté</h2>
        <table class="table table-stats" id="table-stats">
          <thead>
            <tr>
              <th class="text-center all">N°</th>
              <th class="text-center min-tablet">Député</th>
              <th class="text-center all">Groupe</th>
              <th class="text-center all">Taux de loyauté</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; ?>
            <?php foreach ($deputes as $depute): ?>
              <tr>
                <td class="text-center"><?= $depute["rank"] ?></td>
                <td class="text-center"><?= $depute["nameFirst"]." ".$depute["nameLast"] ?></td>
                <td class="text-center"><?= $depute["libelleAbrev"] ?></td>
                <td class="text-center"><?= $depute["score"] ?> %</td>
              </tr>
              <?php $i++; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
