      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel est le député le plus âgé ? Qui est le moins jeune ?</p>
          <p>Si vous attachez de l'importance à la représentativité « sociale » du parlement, l'âge est un facteur à prendre en compte. Par exemple, certains estiment que les jeunes seront moins bien représentés si aucun député n'est issu de cette classe d'âge.</p>
          <p>Les chercheurs montrent que les députés, partout dans le monde, sont en moyenne plus âgés que la population. En France, <b>la moyenne d'âge des députés est de <?= $ageMean ?> ans</b>. C'est <?= $ageDiffStr ?> que la <a href="<?= base_url() ?>statistiques/aide#ageMoyen">moyenne d'âge des Français éligibles</a> (plus de 18 ans), qui est de <?= $ageMeanPop ?> ans.</p>
          <p><?= ucfirst($mpOldestGender['le']) ?> <?= $mpOldestGender['depute'] ?> en activité le plus âgé<?= $mpOldestGender['e'] ?> est <a href="<?= base_url() ?>deputes/<?= $mpOldest['dptSlug'] ?>/depute_<?= $mpOldest['nameUrl'] ?>" target="_blank"><?= $mpOldest['name'] ?></a> (<?= $mpOldest['age'] ?> ans), membre du groupe <?= name_group($mpOldest["libelle"]) ?>.</p>
          <p>
            <a href="<?= base_url() ?>deputes/<?= $mpYoungest['dptSlug'] ?>/depute_<?= $mpYoungest['nameUrl'] ?>" target="_blank"><?= $mpYoungest['name'] ?></a> (<?= $mpYoungest['age'] ?> ans) est <?= $mpYoungestGender['le'] ?> <?= $mpYoungestGender['depute'] ?> <?= $mpYoungestGender['le'] ?> plus jeune.
            <?= ucfirst($mpYoungestGender['pronom']) ?> est membre du groupe <?= name_group($mpYoungest['libelle']) ?>.
          </p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center"><?= ucfirst($mpOldestGender['le']) ?> <?= $mpOldestGender['depute'] ?> <?= $mpOldestGender["le"] ?> plus âgé<?= $mpOldestGender["e"] ?></h2>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpOldest, 'tag' => 'span', 'stat' => $mpOldest['age'] . " ans", 'cat' => true, 'logo' => true)) ?>
          </div>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center"><?= ucfirst($mpYoungestGender['le']) ?> <?= $mpYoungestGender['depute'] ?> <?= $mpYoungestGender["le"] ?> plus jeune</h2>
          <div class="d-flex justify-content-center">
            <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpYoungest, 'tag' => 'span', 'stat' => $mpYoungest['age'] . " ans", 'cat' => true, 'logo' => true)) ?>
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
