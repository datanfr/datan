      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel est le groupe politique avec le plus de députées femmes ? Celui qui compte le moins de femmes dans ses rangs ? Découvrez sur cette page le classement des groupes parlementaires en fonction de leur taux de féminisation.</p>
          <p>Le genre, tout comme l'âge ou le parcours social d'un parlementaire, est un élément clé de la représentation politique. Pour certains, la composition du parlement doit être similaire à la composition de la société. Les chercheurs ont montré que, plus les femmes sont présentes dans les parlements, plus leurs intérêts sont pris en compte dans les politiques adoptées.</p>
          <p>Il y a <b><?= $womenMean["n"] ?> députées femmes</b>. Cela représente <?= $womenMean["pct"] ?> % des effectifs de l'Assemblée nationale. C'est <?= $womenMean["diff"] ?> points de moins que le pourcentage de femmes dans la société française (<?= $womenMean['nSociety'] ?> %).</p>
          <p>Le groupe parlementaire avec le taux de féminisation le plus élevé est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupsWomenFirst["libelleAbrev"]) ?>"><?= $groupsWomenFirst["libelle"] ?></a> (<?= $groupsWomenFirst["libelleAbrev"] ?>), qui compte <?= $groupsWomenFirst["female"] ?> députées femmes dans ses rangs (<?= $groupsWomenFirst["pct"] ?> % de ses effectifs).</p>
          <p><a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupsWomenLast["libelleAbrev"]) ?>"><?= $groupsWomenLast["libelle"] ?></a> (<?= $groupsWomenLast["libelleAbrev"] ?>) est le groupe avec le taux de féminisation le plus faible. Il ne compte que <?= $groupsWomenLast["female"] ?> députées femmes dans ses rangs (<?= $groupsWomenLast["pct"] ?> % de ses effectifs).</p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus féminisé</h2>
          <div class="card card-groupe">
            <div class="liseret" style="background-color: <?= $groupsWomenFirst["couleurAssociee"] ?>"></div>
            <div class="card-avatar group">
              <img src="<?= asset_url() ?>imgs/groupes/<?= $groupsWomenFirst['libelleAbrev'] ?>.png" alt="<?= $groupsWomenFirst['libelle'] ?>">
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <span class="d-block card-title my-2">
                <a href="<?= base_url(); ?>groupes/<?php echo mb_strtolower($groupsWomenFirst['libelleAbrev']) ?>" class="stretched-link no-decoration"><?php echo $groupsWomenFirst['libelle'] ?></a>
              </span>
              <span><?= $groupsWomenFirst["libelleAbrev"] ?></span>
              <span class="badge badge-primary badge-stats mt-3"><?= $groupsWomenFirst["pct"] ?> % de femmes</span>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $groupsWomenFirst["effectif"] ?> membres</span>
            </div>
          </div>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le moins féminisé</h2>
          <div class="card card-groupe">
            <div class="liseret" style="background-color: <?= $groupsWomenLast["couleurAssociee"] ?>"></div>
            <div class="card-avatar group">
              <img src="<?= asset_url() ?>imgs/groupes/<?= $groupsWomenLast['libelleAbrev'] ?>.png" alt="<?= $groupsWomenLast['libelle'] ?>">
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <span class="d-block card-title my-2">
                <a href="<?= base_url(); ?>groupes/<?php echo mb_strtolower($groupsWomenLast['libelleAbrev']) ?>" class="stretched-link no-decoration"><?php echo $groupsWomenLast['libelle'] ?></a>
              </span>
              <span><?= $groupsWomenLast["libelleAbrev"] ?></span>
              <span class="badge badge-primary badge-stats mt-3"><?= $groupsWomenLast["pct"] ?> % de femmes</span>
            </div>
            <div class="card-footer d-flex justify-content-center align-items-center">
              <span><?= $groupsWomenLast["effectif"] ?> membres</span>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-5">Classement des groupes selon leur taux de féminisation</h2>
        <table class="table table-stats">
          <thead>
            <tr>
              <th class="text-center">N°</th>
              <th class="text-center">Groupe</th>
              <th class="text-center">Taux de féminisation</th>
              <th class="text-center">Nombre de femmes</th>
              <th class="text-center">Effectif du groupe</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($groupsWomen as $group): ?>
              <tr>
                <td class="text-center"><?= $group["rank"] ?></td>
                <td class="text-center">
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?></a>
                </td>
                <td class="text-center"><?= $group["pct"] ?> %</td>
                <td class="text-center"><?= $group["female"] ?></td>
                <td class="text-center"><?= $group["n"] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
