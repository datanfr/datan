      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel est le groupe politique avec la moyenne d'âge la plus élevée ? Le groupe le plus jeune ? Découvrez sur cette page le classement des groupes en fonction de l'âge des députés qui en sont membres.</p>
          <p>L'âge, tout comme le genre ou le parcours social d'un parlementaire, est un élément clé de la représentation politique. Pour certains, la composition du parlement doit être similaire à la composition de la société. Par exemple, les jeunes ne peuvent être représentés que par des députés issus de cette classe d'âge.</p>
          <p>Le groupe parlementaire avec la moyenne d'âge la plus élevée est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupOldest["libelleAbrev"]) ?>"><?= $groupOldest["libelle"] ?></a>. Ses membres ont en moyenne <?= $groupOldest["age"] ?> ans.</p>
          <p>Le groupe <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupYoungest["libelleAbrev"]) ?>"><?= $groupYoungest["libelle"] ?></a> est celui avec la moyenne d'âge la plus faible. En effet, les députés membres du groupe <?= $groupYoungest["libelleAbrev"] ?> ont en moyenne <?= $groupYoungest["age"] ?> ans.</p>
          <p>La <a href="<?= base_url() ?>statistiques/aide#ageMoyen">moyenne d'âge dans la population française éligible</a> (plus de 18 ans) est de <?= $ageMeanPop ?> ans.</p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus âgé</h2>
          <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupOldest, 'tag' => 'span', 'active' => TRUE, 'stats' => "Moyenne : " . $groupOldest['age'] . " ans", 'cat' => true)) ?>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus jeune</h2>
          <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupYoungest, 'tag' => 'span', 'active' => TRUE, 'stats' => "Moyenne : " . $groupYoungest['age'] . " ans", 'cat' => true)) ?>
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
