      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel est le groupe politique avec le plus de députées femmes ? Celui qui compte le moins de femmes dans ses rangs ? Découvrez sur cette page le classement des groupes parlementaires en fonction de leur taux de féminisation.</p>
          <p>Le genre, tout comme l'âge ou le parcours social d'un parlementaire, est un élément clé de la représentation politique. Pour certains, la composition du parlement doit être similaire à la composition de la société. Les chercheurs ont montré que, plus les femmes sont présentes dans les parlements, plus leurs intérêts sont pris en compte dans les politiques adoptées.</p>
          <p>Il y a <b><?= $womenMean["n"] ?> députées femmes</b>. Cela représente <?= $womenMean["pct"] ?> % des effectifs de l'Assemblée nationale. C'est <?= $womenMean["diff"] ?> points de moins que le pourcentage de femmes dans la société française (<?= $womenMean['nSociety'] ?> %).</p>
          <?php if ($groupsWomen): ?>
            <p>Le groupe parlementaire avec le taux de féminisation le plus élevé est <a href="<?= base_url() ?>groupes/legislature-<?= $groupsWomenFirst["legislature"] ?>/<?= mb_strtolower($groupsWomenFirst["libelleAbrev"]) ?>"><?= $groupsWomenFirst["libelle"] ?></a> (<?= $groupsWomenFirst["libelleAbrev"] ?>), qui compte <?= $groupsWomenFirst["female"] ?> députées femmes dans ses rangs (<?= $groupsWomenFirst["pct"] ?> % de ses effectifs).</p>
            <p><a href="<?= base_url() ?>groupes/legislature-<?= $groupsWomenLast["legislature"] ?>/<?= mb_strtolower($groupsWomenLast["libelleAbrev"]) ?>"><?= $groupsWomenLast["libelle"] ?></a> (<?= $groupsWomenLast["libelleAbrev"] ?>) est le groupe avec le taux de féminisation le plus faible. Il ne compte que <?= $groupsWomenLast["female"] ?> députées femmes dans ses rangs (<?= $groupsWomenLast["pct"] ?> % de ses effectifs).</p>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!$groupsWomen): ?>
        <div class="alert alert-danger mt-4" role="alert">
          Ces données seront prochainement disponibles.
        </div>
      <?php endif; ?>
      <?php if ($groupsWomen): ?>
        <div class="row row-grid mt-5">
          <div class="col-md-6 py-3">
            <h2 class="text-center">Le plus féminisé</h2>
            <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsWomenFirst, 'tag' => 'span', 'active' => TRUE, 'stats' => $groupsWomenFirst['pct'] . "% de femmes", 'cat' => true)) ?>
          </div>
          <div class="col-md-6 py-3">
            <h2 class="text-center">Le moins féminisé</h2>
            <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsWomenLast, 'tag' => 'span', 'active' => TRUE, 'stats' => $groupsWomenLast['pct'] . "% de femmes", 'cat' => true)) ?>
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
                    <a href="<?= base_url() ?>groupes/legislature-<?= $group["legislature"] ?>/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?></a>
                  </td>
                  <td class="text-center"><?= $group["pct"] ?> %</td>
                  <td class="text-center"><?= $group["female"] ?></td>
                  <td class="text-center"><?= $group["n"] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
