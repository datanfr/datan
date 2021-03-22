      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel est le groupe politique avec la moyenne d'âge la plus élevée ? Le groupe le plus jeune ? Découvrez sur cette page le classement des groupes en fonction de l'âge des députés qui en sont membres.</p>
          <p>L'âge, tout comme le genre ou le parcours social d'un parlementaire, est un élément clé de la représentation politique. Pour certains, la composition du parlement doit être similaire à la composition de la société. Par exemple, les jeunes ne peuvent être représentés que par des députés issus de cette classe d'âge.</p>
          <p>Le groupe parlementaire avec la moyenne d'âge la plus élevée est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupOldest["libelleAbrev"]) ?>"><?= $groupOldest["libelle"] ?></a>. Ses membres ont en moyenne <?= $groupOldest["age"] ?> ans.</p>
          <p>Le groupe <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupYoungest["libelleAbrev"]) ?>"><?= $groupYoungest["libelle"] ?></a> est celui avec la moyenne d'âge la plus faible. En effet, les députés membres du groupe <?= $groupYoungest["libelleAbrev"] ?> ont en moyenne <?= $groupYoungest["age"] ?> ans.</p>
          <p>La moyenne d'âge dans la population française éligible (plus de 18 ans) est de <?= $ageMeanPop ?> ans.</p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus âgé</h2>
          <div class="card card-groupe">
            <div class="liseret" style="background-color: <?= $groupOldest["couleurAssociee"] ?>"></div>
            <div class="card-avatar group">
              <img src="<?= asset_url() ?>imgs/groupes/<?= $groupOldest['libelleAbrev'] ?>.png" alt="<?= $groupOldest['libelle'] ?>">
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <span class="d-block card-title my-2">
                <a href="<?= base_url(); ?>groupes/<?php echo mb_strtolower($groupOldest['libelleAbrev']) ?>" class="stretched-link no-decoration"><?php echo $groupOldest['libelle'] ?></a>
              </span>
              <span><?= $groupOldest["libelleAbrev"] ?></span>
              <span class="badge badge-primary badge-stats mt-3">Moyenne : <?= $groupOldest["age"] ?> ans</span>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $groupOldest["effectif"] ?> membres</span>
            </div>
          </div>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus jeune</h2>
          <div class="card card-groupe">
            <div class="liseret" style="background-color: <?= $groupYoungest["couleurAssociee"] ?>"></div>
            <div class="card-avatar group">
              <img src="<?= asset_url() ?>imgs/groupes/<?= $groupYoungest['libelleAbrev'] ?>.png" alt="<?= $groupYoungest['libelle'] ?>">
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <span class="d-block card-title my-2">
                <a href="<?= base_url(); ?>groupes/<?php echo mb_strtolower($groupYoungest['libelleAbrev']) ?>" class="stretched-link no-decoration"><?php echo $groupYoungest['libelle'] ?></a>
              </span>
              <span><?= $groupYoungest["libelleAbrev"] ?></span>
              <span class="badge badge-primary badge-stats mt-3">Moyenne : <?= $groupYoungest["age"] ?> ans</span>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $groupYoungest["effectif"] ?> membres</span>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-5">Classement des groupes en fonction de l'âge de leurs membres</h2>
        <table class="table table-stats">
          <thead>
            <tr>
              <th class="text-center">N°</th>
              <th class="text-center">Groupe</th>
              <th class="text-center">Âge moyen des députés</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($groupsAge as $group): ?>
              <tr>
                <td class="text-center"><?= $group["rank"] ?></td>
                <td class="text-center">
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?> (<?= $group["libelleAbrev"] ?>)</a>
                </td>
                <td class="text-center"><?= $group["age"] ?> ans</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
