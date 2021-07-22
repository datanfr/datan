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
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpLoyal, 'tag' => 'span', 'stats' => $mpLoyal['score'] . " %", 'cat' => false)) ?>
          </div>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center"><?= ucfirst($mpRebelGender['le']) ?> <?= $mpRebelGender['depute'] ?> <?= $mpRebelGender["le"] ?> plus rebelle</h2>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpRebel, 'tag' => 'span', 'stats' => $mpRebel['score'] . " %", 'cat' => false)) ?>
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
