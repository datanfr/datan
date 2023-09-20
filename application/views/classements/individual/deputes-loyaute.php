      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel député de l'Assemblée nationale vote le plus souvent comme son groupe politique ? Qui est le plus rebelle ?</p>
          <p>La proximité avec son groupe est un élément important à l'Assemblée. Dans la plupart des cas, les députés suivent la ligne du groupe quand ils votent.</p>
          <p>Un député peut voter contre la position officielle de son groupe pour plusieurs raisons, par exemple s'il a opinion différente de celle de son groupe ou si la position de son groupe est contraire aux intérêts des électeurs de sa circonscription.</p>
          <?php if ($deputes): ?>
            <p>
              La plupart des parlementaires sont loyaux à leur groupe et votent rarement contre la ligne du groupe.
              <?= ucfirst($mpLoyalGender['le']) ?> député<?= $mpLoyalGender['e'] ?> <?= $mpLoyalGender['le'] ?> plus proche de son groupe est <a href="<?= base_url() ?>deputes/<?= $mpLoyal['dptSlug'] ?>/depute_<?= $mpLoyal['nameUrl'] ?>" target="_blank"><?= $mpLoyal['name'] ?></a> :
              <?= $mpLoyalGender['pronom'] ?> a voté sur la même ligne que son groupe dans <?= $mpLoyal['score'] ?> % des cas.
            </p>
            <p>
              Au contraire, <?= $mpRebelGender['le'] ?> député<?= $mpRebelGender['e'] ?> votant le moins fréquemment avec son groupe est <a href="<?= base_url() ?>deputes/<?= $mpRebel['dptSlug'] ?>/depute_<?= $mpRebel['nameUrl'] ?>" target="_blank"><?= $mpRebel['name'] ?></a> :
              <?= $mpRebelGender['pronom'] ?> a voté sur la même ligne que son groupe dans <?= $mpRebel['score'] ?> % des cas.
            </p>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!$deputes): ?>
        <div class="alert alert-danger mt-4" role="alert">
          Ces données seront prochainement disponibles.
        </div>
      <?php endif; ?>
      <?php if ($deputes): ?>
        <div class="row row-grid mt-5">
          <div class="col-md-6 py-3">
            <h2 class="text-center"><?= ucfirst($mpLoyalGender['le']) ?> plus proche de son groupe</h2>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpLoyal, 'tag' => 'span', 'stats' => $mpLoyal['score'] . " %", 'cat' => true, 'logo' => true)) ?>
            </div>
          </div>
          <div class="col-md-6 py-3">
            <h2 class="text-center"><?= ucfirst($mpRebelGender['le']) ?> moins proche de son groupe</h2>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpRebel, 'tag' => 'span', 'stats' => $mpRebel['score'] . " %", 'cat' => true, 'logo' => true)) ?>
            </div>
          </div>
        </div>
        <div class="mt-5">
          <h2 class="mb-5">Classement des députés en fonction de leur taux de proximité avec leur groupe</h2>
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
      <?php endif; ?>
    </div>
