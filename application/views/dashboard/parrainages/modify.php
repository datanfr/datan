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
                <h2>Modifier le parrainage pour</h2>
                <p><b>Prénom et nom : </b><?= $parrainage['nameFirst'] ?> <?= $parrainage['nameLast'] ?></p>
                <p><b>Mandat : </b><?= $parrainage['mandat'] ?></p>
                <p><b>Circonscription : </b><?= $parrainage['circo'] ?></p>
                <p><b>Département : </b><?= $parrainage['dpt'] ?></p>
                <p><b>Candidat parrainé : </b><?= $parrainage['candidat'] ?></p>
                <p><b>Année de l'élection : </b><?= $parrainage['year'] ?></p>
                <hr>
                <?php echo form_open_multipart('dashboard/parrainages/modify/'.$parrainage['id']); ?>
                  <div class="form-group">
                    <label>ID du député</label>
                    <input type="text" class="form-control" autocomplete="off" name="mpId" value="<?= $parrainage['mpId'] ?>">
                  </div>
                  <button type="submit" class="btn btn-primary">Submit</button>
              </form>
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
