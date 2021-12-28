      <div class="row mt-4">
        <div class="col-md-10">
          <?php print_r($groupsFirst) ?>
          <p>Quel est le groupe politique le plus uni quand il s'agit de voter ? Quel est le groupe le plus divisé ? Découvrez sur cette page le classement des groupes en fonction de leur taux de cohésion.</p>
          <p>Dans beaucoup de parlements, y compris à l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Un groupe parlementaire peut également mettre en place explicitement ou implicitement des sanctions aux députés votant souvent contre la ligne officielle du groupe. </p>
          <p>Les groupes parlementaires ayant un taux de cohésion proche de 1 affichent une forte unité quand il s'agit de voter. Au contraire, les groupes politiques avec un taux de cohésion proche de 0 sont souvent divisés lors des scrutins à l'Assemblée nationale. Pour plus d'information sur la manière dont est calculé le taux de cohésion, <a href="<?= base_url() ?>statistiques/aide#cohesion" target="_blank">cliquez ici</a>.</p>
          <p>
            Le groupe parlement qui affiche la plus grande cohésion est <a href="<?= base_url() ?>groupes/legislature-<?= $groupsFirst["legislature"] ?>/<?= mb_strtolower($groupsFirst["libelleAbrev"]) ?>"><?= $groupsFirst["libelle"] ?></a> (<?= $groupsFirst["libelleAbrev"] ?>), avec un taux de cohésion de <b><?= round($groupsFirst['cohesion'], 2) ?></b>.
            À l'opposé, le groupe parlementaire avec le plus faible taux de cohésion est <a href="<?= base_url() ?>groupes/legislature-<?= $groupsLast["legislature"] ?>/<?= mb_strtolower($groupsLast["libelleAbrev"]) ?>"><?= $groupsLast["libelle"] ?></a> (<?= $groupsLast["libelleAbrev"] ?>).</p>
          <p>Le taux de cohésion moyen de tous les groupes parlementaires de la législature actuelle est de <?= $cohesionMean ?>.</p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus uni</h2>
          <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsFirst, 'tag' => 'span', 'active' => TRUE, 'stats' => "Cohésion : " . round($groupsFirst["cohesion"], 2), 'cat' => true)) ?>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus divisé</h2>
          <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsLast, 'tag' => 'span', 'active' => TRUE, 'stats' => "Cohésion : " . round($groupsLast["cohesion"], 2), 'cat' => true)) ?>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-5">Classement des groupes en fonction de leur taux de cohésion</h2>
        <table class="table table-stats">
          <thead>
            <tr>
              <th class="text-center">N°</th>
              <th class="text-center">Groupe</th>
              <th class="text-center">Taux de cohésion</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($groups as $group): ?>
              <tr>
                <td class="text-center"><?= $group["rank"] ?></td>
                <td class="text-center">
                  <a href="<?= base_url() ?>groupes/legislature-<?= $group["legislature"] ?>/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?> (<?= $group["libelleAbrev"] ?>)</a>
                </td>
                <td class="text-center"><?= $group["cohesion"] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
