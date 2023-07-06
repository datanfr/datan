  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
              <li class="breadcrumb-item active">Starter Page</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Mes votes non publiés</h5>
              </div>
              <div class="card-body">
                <?php if (empty($votesUnpublished)): ?>
                  <p class="card-text">Aucun vote en brouillon.</p>
                  <?php else: ?>
                  <table class="table">
                    <thead>
                      <tr>
                        <th>num.</th>
                        <th>titre</th>
                        <th></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votesUnpublished as $vote): ?>
                        <tr>
                          <td><?= $vote['voteNumero'] ?></td>
                          <td><?= $vote['title'] ?></td>
                          <td><a href="<?= base_url() ?>admin/votes/modify/<?= $vote['id'] ?>" class="btn btn-primary">Modifier</a></td>
                          <td><a href="<?= base_url() ?>admin/votes/delete/<?= $vote['id'] ?>" class="btn btn-danger">Supprimer</a></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
              </div>
              <div class="card-footer d-flex justify-content-around">
                <a href="<?= base_url() ?>admin/votes/create" class="btn btn-primary">Créer un vote</a>
                <a href="<?= base_url() ?>admin/votes" class="btn btn-primary">Tous les votes</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Mes derniers votes publiés</h5>
              </div>
              <div class="card-body">
                <?php if (empty($votesLast)): ?>
                  <p>Aucun vote.</p>
                  <?php else: ?>
                    <table class="table">
                      <thead>
                        <tr>
                          <th>id</th>
                          <th>numero</th>
                          <th>titre</th>
                          <th>catégorie</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($votesLast as $vote): ?>
                          <tr>
                            <td><?= $vote['id'] ?></td>
                            <td><?= $vote['voteNumero'] ?></td>
                            <td><?= $vote['title'] ?></td>
                            <td><?= $vote['categoryName'] ?></td>
                            <td><a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="btn btn-primary">Voir</a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Nouveaux députés</h5>
              </div>
              <div class="card-body">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Nom</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($deputes_entrants as $depute_entrant): ?>
                        <tr>
                          <td><a target="_blank" href="<?= base_url().'deputes/'.$depute_entrant['dptSlug'].'/depute_'.$depute_entrant['nameUrl'] ?>"><?= "{$depute_entrant['nameFirst']} {$depute_entrant['nameLast']}" ?></a></td>
                          <td><?= $depute_entrant['datePriseFonction'] ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
              </div>
            </div><!-- /.card -->
          </div>
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Nouveaux entrants dans un groupe</h5>
              </div>
              <div class="card-body">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Nom</th>
                        <th>Groupe</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($groupes_entrants as $groupe_entrant): ?>
                        <tr>
                          <td><a target="_blank" href="<?= base_url().'deputes/'.$groupe_entrant['dptSlug'].'/depute_'.$groupe_entrant['nameUrl'] ?>"><?= "{$groupe_entrant['nameFirst']} {$groupe_entrant['nameLast']}" ?></a></td>
                          <td><?= $groupe_entrant['libelle'] ?></td>
                          <td><?= $groupe_entrant['dateDebut'] ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Abonnements à la newletter</h5>
              </div>
              <div class="card-body">
                  <p>Il y a <b><?= $newsletter_total ?> personnes</b> abonnées à la newsletter générale de Datan.</p>
                  <div class="chart">
                    <canvas id="barChart" style="height:230px"></canvas>
                  </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Ils souhaitent voir ces votes décryptés !</h5>
              </div>
              <div class="card-body">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Legislature</th>
                        <th>Vote n°</th>
                        <th>Nombre</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votes_requested as $vote): ?>
                        <tr>
                          <td><?= $vote['legislature'] ?></td>
                          <td><?= $vote['vote'] ?></td>
                          <td><?= $vote['n'] ?></td>
                          <td><a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['vote'] ?>" class="btn btn-primary">Voir</a></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
              </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Dernières explications de vote</h5>
              </div>
              <div class="card-body">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Député</th>
                        <th>Vote</th>
                        <th>Texte</th>
                        <th>Modification</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($explications as $e): ?>
                        <tr>
                          <td><?= $e['nameFirst'] . ' ' . $e['nameLast'] . ' (' . $e['libelleAbrev'] . ')' ?></td>
                          <td><a href="<?= base_url() ?>votes/legislature-<?= $e['legislature'] ?>/vote_<?= $e['voteNumero'] ?>" target="_blank"><?= $e['vote'] ?></a></td>
                          <td><?= $e['text'] ?></td>
                          <td><?= $e['modified_at'] ?></td>
                          <td><a href="<?= base_url() ?>votes/legislature-<?= $e['legislature'] ?>/vote_<?= $e['voteNumero'] ?>/explication_<?= $e['mpId'] ?>" target="_blank" class="btn btn-primary">Voir</a></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <script>
  var ctx = document.getElementById('barChart').getContext('2d');
  const data = [
    <?php foreach ($newsletter_month as $month) {
      echo $month['n'].",";
    } ?>
  ];
  const labels = [
    <?php foreach ($newsletter_month as $month) {
      echo '"'.$month['y'].'",';
    } ?>
  ];
  var myChart = new Chart(ctx, {
      type: 'bar',
      data: {
          labels: labels,
          datasets: [{
              label: "Nombre d'abonnés supplémentaires",
              data: data,
              borderWidth: 1
          }]
      }
  });
  </script>
