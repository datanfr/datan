      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel groupe politique a le plus fort taux de participation à l'Assemblée nationale ?</p>
          <p>Pour chaque groupe, <b>nous avons calculé le taux de participation moyen de leurs députés</b>. Autrement dit, si le groupe parlementaire affiche un taux de 80 %, cela signifie que, en moyenne, les députés membres du groupes participent en moyenne à 80 % des votes de l'Assemblée nationale.</p>
          <p>Les parlementaires ont de nombreuses activités, que ce soit à l'Assemblée nationale ou dans leur circonscription. <b>Le vote est une des activités essentielles</b> : ce sont les projets de loi et les amendements adoptés par une majorité des députés qui impacteront la vie des citoyens.</p>
          <p><span class="font-weight-bold text-primary">Nous proposons donc trois scores de participation :</span></p>
          <p>Le <u>premier</u> ne concerne que les votes solennels. Les votes solennels sont les votes les plus importants et concernent des projets de loi significatifs et fortement discutés dans les médias. Pour ces votes, le jour et l'heure du vote sont connus à l'avance, favorisant ainsi la présence des parlementaires dans l'hémicycle. En moyenne, le taux de participation des groupes politiques pour les scrutins solennels est de <?= $average['participationSPS'] ?> %. C'est ce score qui est souvent mis en avant sur Datan.</p>
          <p>Le <u>deuxième</u> ne prend en compte que les votes en lien avec le domaine de spécialisation d'un député. Par exemple, un député avec un score de 100% aura participé, en séance publique, à tous les scrutins sur des textes qui ont été précédemment examinés dans sa commission parlementaire. Ce sont sur ces votes que les élus ont un intérêt et une expertise particulière, et sont donc plus susceptibles de participer aux travaux parlementaires. Le taux de participation moyen pour ce score est de <?= $average['participationCommission'] ?> %.</p>
          <p>Le <u>troisième</u> prend en compte tous les votes en séance publique. Le taux de participation moyen pour ce score est de <?= $average['participation'] ?> %.</p>
          <?php if ($n_sps < 10): ?>
            <div class="alert alert-danger my-4">
              Nous mettons en avant sur Datan le score de participation aux scrutins solennels. Cependant, parce que la législature vient de commencer, et qu'il n'y a eu que <?= $n_sps ?> votes solennels, nous mettons en avant sur cette page le score de participation à tous les scrutins.
            </div>
          <?php endif; ?>
          <?php if ($groups): ?>
            <p>
              Le groupe parlementaire le plus actif au moment de voter est <a href="<?= base_url() ?>groupes/legislature-<?= $groupsFirst["legislature"] ?>/<?= mb_strtolower($groupsFirst["libelleAbrev"]) ?>"><?= $groupsFirst["libelle"] ?></a> (<?= $groupsFirst["libelleAbrev"] ?>). Son taux moyen de participation est de <?= $groupsFirst["participation"] ?> %. Autrement dit, en moyenne, les députés membres de ce groupe parlementaire participent à <?= $groupsFirst["participation"] ?> % des scrutins.
            </p>
            <p>
              Le groupe avec le taux de participation le plus faible est <a href="<?= base_url() ?>groupes/legislature-<?= $groupsLast["legislature"] ?>/<?= mb_strtolower($groupsLast["libelleAbrev"]) ?>"><?= $groupsLast["libelle"] ?></a> (<?= $groupsLast["libelleAbrev"] ?>). En moyenne, les députés membres de ce groupe parlementaire participent à <?= $groupsLast["participation"] ?> % des scrutins.
            </p>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!$votes_sps): ?>
        <div class="alert alert-danger mt-4" role="alert">
          Ces données seront prochainement disponibles.
        </div>
      <?php endif; ?>
      <?php if ($votes_sps): ?>
        <div class="row row-grid mt-5">
          <div class="col-md-6 py-3">
            <h2 class="text-center">Participe le plus</h2>
            <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsFirst, 'tag' => 'span', 'active' => TRUE, 'stats' => "Participation : " . round($groupsFirst["participation"], 2) . " %", 'cat' => true)) ?>
          </div>
          <div class="col-md-6 py-3">
            <h2 class="text-center">Participe le moins</h2>
            <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $groupsLast, 'tag' => 'span', 'active' => TRUE, 'stats' => "Participation : " . round($groupsLast["participation"], 2) . " %", 'cat' => true)) ?>
          </div>
        </div>
        <div class="mt-5">
          <h2 class="mb-5">Classement des groupes en fonction de leur taux de participation moyen</h2>
          <nav class="mt-4">
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
              <a class="nav-item nav-link no-decoration <?= $n_sps < 10 ? "" : "active" ?>" id="nav-votes-solennels" data-toggle="tab" href="#nav-solennels" role="tab" aria-controls="nav-commission" aria-selected="false">
                <h3>Votes solennels</h3>
              </a>
              <a class="nav-item nav-link no-decoration" id="nav-votes-com" data-toggle="tab" href="#nav-commission" role="tab" aria-controls="nav-commission" aria-selected="false">
                <h3>Votes par spécialisation</h3>
              </a>
              <a class="nav-item nav-link no-decoration <?= $n_sps < 10 ? "active" : "" ?>" id="nav-votes-all" data-toggle="tab" href="#nav-all" role="tab" aria-controls="nav-all" aria-selected="true">
                <h3>Tous les votes</h3>
              </a>
            </div>
          </nav>
          <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade <?= $n_sps < 10 ? "" : "show active" ?>" id="nav-solennels" role="tabpanel" aria-labelledby="nav-votes-solennels">
              <p class="my-4">
                <i>Ce tableau comprend tous les votes solennels en séance publique. Les votes solennels sont des votes sur des dossiers considérés comme importants. Le jour et l'heure du vote sont connus à l'avance, afin de favoriser la présence des députés.</i>
              </p>
              <p>
                <i>Le taux de participation moyen pour ce score est de <?= $average['participationSPS'] ?> %.</i>
              </p>
              <table class="table table-stats">
                <thead>
                  <tr>
                    <th class="text-center">N°</th>
                    <th class="text-center">Groupe</th>
                    <th class="text-center">Taux de participation moyen</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($votes_sps as $group): ?>
                    <tr>
                      <td class="text-center"><?= $group["rank"] ?></td>
                      <td class="text-center">
                        <a href="<?= base_url() ?>groupes/legislature-<?= $group["legislature"] ?>/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?> (<?= $group["libelleAbrev"] ?>)</a>
                      </td>
                      <td class="text-center"><?= $group["participation"] ?> %</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="tab-pane fade" id="nav-commission" role="tabpanel" aria-labelledby="nav-votes-commission">
              <p class="my-4">
                <i>Ce tableau comprend tous les votes en séance publique et qui ont un lien avec le domaine de spécialisation (par exemple économie, environnement) du député. Plus précisément, ce score ne prend en compte que les textes précédemment examinés dans la commission du député.</i>
              </p>
              <p>
                <i>Le taux de participation moyen pour ce score est de <?= $average['participationCommission'] ?> %.</i>
              </p>
              <table class="table table-stats" style="width: 100%;">
                <thead>
                  <tr>
                    <th class="text-center">N°</th>
                    <th class="text-center">Groupe</th>
                    <th class="text-center">Taux de participation moyen</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; ?>
                  <?php foreach ($votes_commission as $group): ?>
                    <tr>
                      <td class="text-center"><?= $group["rank"] ?></td>
                      <td class="text-center">
                        <a href="<?= base_url() ?>groupes/legislature-<?= $group["legislature"] ?>/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?> (<?= $group["libelleAbrev"] ?>)</a>
                      </td>
                      <td class="text-center"><?= $group["participation"] ?> %</td>
                    </tr>
                    <?php $i++; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="tab-pane fade <?= $n_sps < 10 ? "show active" : "" ?>" id="nav-all" role="tabpanel" aria-labelledby="nav-votes-all">
              <p class="my-4">
                <i>Ce tableau comprend tous les votes en séance publique auxquels un député a pu participer. Ce score est souvent plus faible, du fait de l'organisation du travail à l'Assemblée (un vote en séance publique peut se tenir en même temps qu'une réunion de commission). Depuis le début de la législature, il y a eu <?= $votesN ?> votes.</i>
              </p>
              <p>
                <i>Le taux de participation moyen pour ce score est de <?= $average['participation'] ?> %.</i>
              </p>
              <table class="table table-stats">
                <thead>
                  <tr>
                    <th class="text-center">N°</th>
                    <th class="text-center">Groupe</th>
                    <th class="text-center">Taux de participation moyen</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($votes_all as $group): ?>
                    <tr>
                      <td class="text-center"><?= $group["rank"] ?></td>
                      <td class="text-center">
                        <a href="<?= base_url() ?>groupes/legislature-<?= $group["legislature"] ?>/<?= mb_strtolower($group["libelleAbrev"]) ?>" class="no-decoration underline"><?= $group["libelle"] ?> (<?= $group["libelleAbrev"] ?>)</a>
                      </td>
                      <td class="text-center"><?= $group["participation"] ?> %</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
