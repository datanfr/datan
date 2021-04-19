      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel groupe politique a le plus fort taux de participation à l'Assemblée nationale ?</p>
          <p>Pour chaque groupe, <b>nous avons calculé le taux de participation moyen de leurs députés</b>. Autrement dit, si le groupe parlementaire affiche un taux de 25 %, cela signifie que, en moyenne, les députés membres du groupes participent à 25 % des votes.</p>
          <p>Les parlementaires ont de nombreuses activités, que ce soit à l'Assemblée nationale ou dans leur circonscription. <b>Le vote est une des activités essentielles</b> : ce sont les projets de loi et les amendements adoptés par une majorité des députés qui impacteront la vie des citoyens.</p>
          <p>Attention, les faibles taux de participation à l'Assemblée nationale s'expliquent par l'organisation du travail : avec plusieurs réunions en même temps, seuls les députés spécialistes d'un sujet participent aux discussions et votent en séance.</p>
          <p>L'équipe de Datan conseille donc de <b>comparer les taux de participation des différents groupes</b>. En effet, une participation moyenne de 20 % pour un groupe n'est pertinente que si elle est comparée avec les moyennes des autres groupes parlementaires.</p>
          <p>
            Le groupe parlementaire le plus actif au moment de voter est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupsFirst["libelleAbrev"]) ?>"><?= $groupsFirst["libelle"] ?></a> (<?= $groupsFirst["libelleAbrev"] ?>). Son taux moyen de participation est de <?= $groupsFirst['participation'] ?> %. Autrement dit, en moyenne, les députés membres de ce groupe parlementaire participent à <?= $groupsFirst['participation'] ?> % des scrutins.
          </p>
          <p>
            Le groupe avec le taux de participation le plus faible est <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupsLast["libelleAbrev"]) ?>"><?= $groupsLast["libelle"] ?></a> (<?= $groupsLast["libelleAbrev"] ?>). En moyenne, les députés membres de ce groupe parlementaire participent à <?= $groupsLast['participation'] ?> % des scrutins.
          </p>
        </div>
      </div>
      <div class="row row-grid mt-5">
        <div class="col-md-6 py-3">
          <h2 class="text-center">Participe le plus</h2>
          <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsFirst, 'tag' => 'span', 'active' => TRUE, 'stats' => "Participation : " . round($groupsFirst["participation"], 2) . " %")) ?>
        </div>
        <div class="col-md-6 py-3">
          <h2 class="text-center">Le plus divisé</h2>
          <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsLast, 'tag' => 'span', 'active' => TRUE, 'stats' => "Participation : " . round($groupsLast["participation"], 2) . " %")) ?>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-5">Classement des groupes en fonction de leur taux de participation moyen</h2>
        <table class="table table-stats">
          <thead>
            <tr>
              <th class="text-center">N°</th>
              <th class="text-center">Groupe</th>
              <th class="text-center">Taux de participation moyen</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($groups as $group): ?>
              <tr>
                <td class="text-center"><?= $group["rank"] ?></td>
                <td class="text-center">
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?> (<?= $group["libelleAbrev"] ?>)</a>
                </td>
                <td class="text-center"><?= $group["participation"] ?> %</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
