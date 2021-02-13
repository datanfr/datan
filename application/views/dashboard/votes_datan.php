

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
                      <th>vote_id</th>
                      <th>title</th>
                      <th>cat.</th>
                      <th>state</th>
                      <th>Créé le</th>
                      <th>créé par</th>
                      <th>Modifié le</th>
                      <th>modifié par</th>
                      <th>mod.</th>
                      <th>supp.</th>
                    </tr>
                  </thead>
                <tbody>
                  <?php foreach ($votes as $vote): ?>
                    <tr>
                      <td><?= $vote['id'] ?></td>
                      <td><?= $vote['vote_id'] ?></td>
                      <td><?= $vote['title'] ?></td>
                      <td><?= $vote['category'] ?></td>
                      <td><?= $vote['state'] ?></td>
                      <td><?= $vote['created_at'] ?></td>
                      <td><?= $vote['created_by_name'] ?></td>
                      <td><?= $vote['modified_at'] ?></td>
                      <td><?= $vote['modified_by_name'] ?></td>
                      <td>
                        <?php if ($vote['state'] != "published"): ?>
                          <a href="<?= base_url() ?>admin/votes/modify/<?= $vote['id'] ?>" ?>modifier</a>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($usernameType == "admin"): ?>
                          <a href="<?= base_url() ?>admin/votes/delete/<?= $vote['id'] ?>">supprimer</a>
                        <?php endif; ?>
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
