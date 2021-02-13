

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
                <table id="table_analyses" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <?php foreach ($votes[0] as $key => $value): ?>
                        <th><?= $key ?></th>
                        <?php
                          echo $key;
                          $cols[] = $key;
                        ?>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                <tbody>
                  <?php foreach ($votes as $vote): ?>
                    <tr class="<?= $vote['vote_datan'] ?>">
                      <?php foreach ($cols as $col): ?>
                        <?php if ($col == "numero"): ?>
                          <td>
                            <a href="http://www2.assemblee-nationale.fr/scrutins/detail/(legislature)/15/(num)/<?= $vote[$col] ?>" target="_blank"><?= $vote[$col] ?></a>
                          </td>
                          <?php else: ?>
                          <td><?= $vote[$col] ?></td>
                        <?php endif; ?>
                      <?php endforeach; ?>
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
