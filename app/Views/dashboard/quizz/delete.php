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
                    <td><?= $question['id'] ?></td>
                  </tr>
                  <tr>
                    <td>Title</td>
                    <td><?= $question['title'] ?></td>
                  </tr>
                  <tr>
                    <td>Quizz</td>
                    <td><?= $question['quizz'] ?></td>
                  </tr>
                  <tr>
                    <td>Vote</td>
                    <td><?= $question['voteNumero'] ?></td>
                  </tr>
                  <tr>
                    <td>Législature</td>
                    <td><?= $question['legislature'] ?></td>
                  </tr>
                  <tr>
                    <td>Category</td>
                    <td><?= $question['category_name'] ?></td>
                  </tr>
                  <tr>
                    <td>state</td>
                    <td><?= $question['state'] ?></td>
                  </tr>
                </tbody>
              </table>
            </div> <!-- /.card-body -->
            <div class="card-footer">
              <div class="float-left">
                <button type="button" onclick="window.location.href = '<?= base_url() ?>admin/quizz';" class="btn btn-default"> Annuler</button>
              </div>
              <div class="float-right">
                <?php echo form_open_multipart('admin/quizz/delete/'.$question['id']); ?>
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
