

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row my-4">
          <div class="col-sm-7">
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
                <table id="table_votes_an*" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Numéro</th>
                      <th>Député</th>
                      <th>Pseudo Twitter</th>
                      <th>Lien</th>
                    </tr>
                  </thead>
                <tbody>
                  <?php $i = 1 ?>
                  <?php foreach ($deputes as $row): ?>
                    <tr>
                      <td><?= $i ?></td>
                      <td><?= $row['nameFirst'] ?> <?= $row['nameLast'] ?></td>
                      <td><?= $row['twitter'] ?></td>
                      <td>
                        <?php if ($row['twitter']): ?>
                          <a class="btn btn-primary" href="https://twitter.com/<?= $row['twitter'] ?>" target="_blank">Lien</a>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php $i++ ?>
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
