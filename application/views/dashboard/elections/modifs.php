

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row my-4">
          <div class="col-sm-6">
            <h1 class="m-0 text-primary font-weight-bold" style="font-size: 2rem"><?= $title ?></h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row pb-4">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body py-4">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Colonne</th>
                      <th>Utilisateur (username)</th>
                      <th>Utilisateur (député)</th>
                      <th>Ancienne valeur</th>
                      <th>Nouvelle valeur</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                <tbody>
                  <?php foreach ($modifs as $modif): ?>
                    <tr>
                      <td><?= $modif['col'] ?></td>
                      <td><?= $modif['username'] ?></td>
                      <td><?= $modif['nameFirst'].' '.$modif['nameLast'] ?></td>
                      <td><?= $modif['value_old'] ?></td>
                      <td><?= $modif['value_new'] ?></td>
                      <td><?= $modif['modified_at'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              </div>
            </div>
          </div>
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
