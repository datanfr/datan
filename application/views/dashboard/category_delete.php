

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
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 10px">Variable</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Id</td>
                      <td><?= $category['id'] ?></td>
                    </tr>
                    <tr>
                      <td>Nom</td>
                      <td><?= $category['name'] ?></td>
                    </tr>
                    <tr>
                      <td>slug</td>
                      <td><?= $category['slug'] ?></td>
                    </tr>
                    <tr>
                      <td>created_at</td>
                      <td><?= $category['created_at'] ?></td>
                    </tr>
                    <tr>
                      <td>modified_at</td>
                      <td><?= $category['modified_at'] ?></td>
                    </tr>
                    <tr>
                      <td>state</td>
                      <td><?= $category['state'] ?></td>
                    </tr>
                    <tr>
                      <td>created_by</td>
                      <td><?= $category['created_by_name'] ?></td>
                    </tr>
                    <tr>
                      <td>modified_by</td>
                      <td><?= $category['modified_by_name'] ?></td>
                    </tr>
                  </tbody>
                </table>
              </div> <!-- /.card-body -->
              <div class="card-footer">
                <div class="float-left">
                  <button type="button" onclick="window.location.href = '<?= base_url() ?>admin/categories';" class="btn btn-default"> Annuler</button>
                </div>
                <div class="float-right">
                  <?php echo form_open_multipart('admin/categories/delete/'.$category['id']); ?>
                    <input type="hidden" name="delete" value="deleted">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                  </form>
                </div>
              </div>
              </div> <!-- /.card-footer -->
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
