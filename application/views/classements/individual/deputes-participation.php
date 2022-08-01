      <div class="row mt-4">
        <div class="col-md-10">
          <p>Quel député vote le plus souvent ? Qui est le moins présent quand il s'agit de voter ?</p>
          <p>Les parlementaires ont de nombreuses activités : ils écrivent des propositions de loi, des amendements, interrogent le gouvernement, exposent leur point de vue lors des débats. Les députés ont également des activités en circonscription : ils rencontrent et peuvent aider leurs électeurs.</p>
          <p><b>Le vote est une activité essentielle</b> des élus : ce sont les projets de loi et les amendements adoptés par une majorité des députés qui impacteront la vie des citoyens. Lors d'un scrutin, un élu peut soit voter « pour », « contre », ou s'abstenir.</p>
          <?php if ($mps): ?>
            <p>Le taux de participation moyen à l'Assemblée nationale est de <?= $participationMean ?> %. Ce faible taux de participation s'explique par l'organisation du travail : avec plusieurs réunions en même temps, seuls les députés spécialistes d'un sujet participent aux discussions et votent en séance.</p>
            <p><span class="font-weight-bold text-primary">Nous proposons donc trois scores de participation :</span></p>
            <p>Le <u>premier</u> ne concerne que les votes solennels. Les votes solennels sont les votes les plus importants et concernent des projets de loi significatifs et fortement discutés dans les médias. Pour ces votes, le jour et l'heure du vote sont connus à l'avance, favorisant ainsi la présence des parlementaires dans l'hémicycle. Le taux de participation moyen pour ce score est de <?= $participationSolennelsMean ?> %. C'est ce score qui est souvent mis en avant sur Datan.</p>
            <p>Le <u>deuxième</u> ne prend en compte que les votes en lien avec le domaine de spécialisation d'un député. Par exemple, un député avec un score de 100% aura participé, en séance publique, à tous les scrutins sur des textes qui ont été précédemment examinés dans sa commission parlementaire. Ce sont sur ces votes que les élus ont un intérêt et une expertise particulière, et sont donc plus susceptibles de participer aux travaux parlementaires. Le taux de participation moyen pour ce score est de <?= $participationCommissionMean ?> %.</p>
            <p>Le <u>troisième</u> prend en compte tous les votes en séance publique. Le taux de participation moyen pour ce score est de <?= $participationMean ?> %.</p>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!$mps): ?>
        <div class="alert alert-danger mt-4" role="alert">
          Ces données seront prochainement disponibles.
        </div>
      <?php endif; ?>
      <?php if ($mps): ?>
        <div class="row row-grid mt-5">
          <div class="col-md-6 py-3">
            <h2 class="text-center">Vote le plus</h2>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpActive, 'tag' => 'span', 'stats' => round($mpActive['score'] * 100) . " %", 'cat' => true, 'logo' => true)) ?>
            </div>
          </div>
          <div class="col-md-6 py-3">
            <h2 class="text-center">Vote le moins</h2>
            <div class="d-flex justify-content-center">
              <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mpInactive, 'tag' => 'span', 'stats' => round($mpInactive['score'] * 100) . " %", 'cat' => true, 'logo' => true)) ?>
            </div>
          </div>
        </div>
        <div class="mt-5">
          <h2 class="mb-5">Classement des députés selon leur taux de participation</h2>
          <nav class="mt-4">
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
              <a class="nav-item nav-link active no-decoration" id="nav-votes-solennels" data-toggle="tab" href="#nav-solennels" role="tab" aria-controls="nav-commission" aria-selected="true">
                <h3>Votes solennels</h3>
              </a>
              <a class="nav-item nav-link no-decoration" id="nav-votes-com" data-toggle="tab" href="#nav-commission" role="tab" aria-controls="nav-commission" aria-selected="true">
                <h3>Votes par spécialisation</h3>
              </a>
              <a class="nav-item nav-link no-decoration" id="nav-votes-all" data-toggle="tab" href="#nav-all" role="tab" aria-controls="nav-all" aria-selected="false">
                <h3>Tous les votes</h3>
              </a>
            </div>
          </nav>
          <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-solennels" role="tabpanel" aria-labelledby="nav-votes-solennels">
              <p class="my-4">
                <i>Ce tableau comprend tous les votes solennels en séance publique. Les votes solennels sont des votes sur des dossiers considérés comme importants. Le jour et l'heure du vote sont connus à l'avance, afin de favoriser la présence des députés.</i>
              </p>
              <p>
                <i>Le taux de participation moyen pour ce score est de <?= $participationSolennelsMean ?> %.</i>
              </p>
              <table class="table table-stats" id="table-stats">
                <thead>
                  <tr>
                    <th class="text-center all">N°</th>
                    <th class="text-center min-tablet">Député</th>
                    <th class="text-center all">Groupe</th>
                    <th class="text-center all">Participation</th>
                    <th class="text-center all">Nombre de votes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($mpsSolennels as $depute): ?>
                    <tr>
                      <td class="text-center"><?= $depute['rank'] ?></td>
                      <td class="text-center"><?= $depute['nameFirst']." ".$depute['nameLast'] ?></td>
                      <td class="text-center"><?= $depute['libelleAbrev'] ?></td>
                      <td class="text-center"><?= $depute['score'] * 100 ?> %</td>
                      <td class="text-center"><?= $depute['votesN'] ?></td>
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
                <i>Le taux de participation moyen pour ce score est de <?= $participationCommissionMean ?> %.</i>
              </p>
              <table class="table table-stats" id="table-stats2" style="width: 100%;">
                <thead>
                  <tr>
                    <th class="text-center all">N°</th>
                    <th class="text-center min-tablet">Député</th>
                    <th class="text-center all">Groupe</th>
                    <th class="text-center all">Participation</th>
                    <th class="text-center all">Nombre de votes</th>
                    <th class="text-center all">Commission actuelle</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; ?>
                  <?php foreach ($mpsCommission as $depute): ?>
                    <tr>
                      <td class="text-center"><?= $i ?></td>
                      <td class="text-center"><?= $depute['nameFirst']." ".$depute['nameLast'] ?></td>
                      <td class="text-center"><?= $depute['libelleAbrev'] ?></td>
                      <td class="text-center"><?= $depute['score'] * 100 ?> %</td>
                      <td class="text-center"><?= $depute['votesN'] ?></td>
                      <td class="text-center"><?= $depute['commission'] ?></td>
                    </tr>
                    <?php $i++; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="tab-pane fade" id="nav-all" role="tabpanel" aria-labelledby="nav-votes-all">
              <p class="my-4">
                <i>Ce tableau comprend tous les votes en séance publique auxquels un député a pu participer. Ce score est souvent plus faible, du fait de l'organisation du travail à l'Assemblée (un vote en séance publique peut se tenir en même temps qu'une réunion de commission). Depuis le début de la législature, il y a eu <?= $votesN ?> votes.</i>
              </p>
              <p>
                <i>Le taux de participation moyen pour ce score est de <?= $participationMean ?> %.</i>
              </p>
              <table class="table table-stats" id="table-stats3" style="width: 100%;">
                <thead>
                  <tr>
                    <th class="text-center all">N°</th>
                    <th class="text-center min-tablet">Député</th>
                    <th class="text-center all">Groupe</th>
                    <th class="text-center all">Participation</th>
                    <th class="text-center all">Nombre de votes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; ?>
                  <?php foreach ($mps as $depute): ?>
                    <tr>
                      <td class="text-center"><?= $i ?></td>
                      <td class="text-center"><?= $depute['nameFirst']." ".$depute['nameLast'] ?></td>
                      <td class="text-center"><?= $depute['libelleAbrev'] ?></td>
                      <td class="text-center"><?= $depute['score'] * 100 ?> %</td>
                      <td class="text-center"><?= $depute['votesN'] ?></td>
                    </tr>
                    <?php $i++; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
