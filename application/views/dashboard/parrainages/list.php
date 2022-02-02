<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <table id="table_votes_datan" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>id</th>
                    <th>nom</th>
                    <th>mandat</th>
                    <th>circo</th>
                    <th>dpt</th>
                    <th>candidat</th>
                    <th>mod.</th>
                  </tr>
                </thead>
              <tbody>
                <?php foreach ($parrainages as $x): ?>
                  <tr>
                    <td>x</td>
                    <td><?= $x['nameFirst'] ?> <?= $x['nameLast'] ?></td>
                    <td><?= $x['mandat'] ?></td>
                    <td><?= $x['circo'] ?></td>
                    <td><?= $x['dpt'] ?></td>
                    <td><?= $x['candidat'] ?></td>


                    <td>
                      <a href="<?= base_url() ?>admin/quizz/modify/" ?>modifier</a>
                    </td>
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
