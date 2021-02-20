

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
              <li class="breadcrumb-item active">Starter Page</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
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
                        <th>id</th>
                        <th>numero</th>
                        <th>titre</th>
                        <th>catégorie</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votesUnpublished as $vote): ?>
                        <tr>
                          <td><?= $vote['id'] ?></td>
                          <td><?= $vote['voteNumero'] ?></td>
                          <td><?= $vote['title'] ?></td>
                          <td><?= $vote['categoryName'] ?></td>
                          <td><a href="<?= base_url() ?>admin/votes/modify/<?= $vote['id'] ?>" class="btn btn-primary">Modifier</a></td>
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
            </div><!-- /.card -->
          </div>
          <!-- /.col-md-6 -->
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
                            <td><a href="<?= base_url() ?>votes/vote_<?= $vote['voteNumero'] ?>" class="btn btn-primary">Voir</a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->
