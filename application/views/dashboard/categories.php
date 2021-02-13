

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
                      <th>slug</th>
                      <th>état</th>
                      <th>créé le</th>
                      <th>créé par</th>
                      <th>modifié le</th>
                      <th>modifié par</th>
                      <th>Modifier</th>
                      <th>Supprimer</th>
                    </tr>
                  </thead>
                <tbody>
                  <?php foreach ($categories as $category): ?>
                    <tr>
                      <td><?= $category['id'] ?></td>
                      <td><?= $category['name'] ?></td>
                      <td><?= $category['slug'] ?></td>
                      <td><?= $category['state'] ?></td>
                      <td><?= $category['created_at'] ?></td>
                      <td><?= $category['created_by_name'] ?></td>
                      <td><?= $category['modified_at'] ?></td>
                      <td><?= $category['modified_by_name'] ?></td>
                      <td><a href="<?= base_url() ?>admin/categories/modify/<?= $category['id'] ?>" ?>modifier</a></td>
                      <td><a href="<?= base_url() ?>admin/categories/delete/<?= $category['id'] ?>">supprimer</a></td>
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
