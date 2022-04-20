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
                <h5 class="m-0 font-weight-bold"><?= $depute['nameFirst'].' '.$depute['nameLast'] ?></h5>
              </div>
              <div class="card-body">
                <p>Bienvenue sur l'espace dashboard de Datan. Cet espace est en construction.</p>
              </div>
              <div class="card-footer d-flex justify-content-around">
                <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>" class="btn btn-primary">Voir ma page Datan</a>
              </div>
            </div><!-- /.card -->
          </div>
          <div class="col-lg-6">
            <div class="card card-danger card-outline">
              <div class="card-header">
                <h5 class="m-0">Candidature aux élections législatives 2022</h5>
              </div>
              <div class="card-body">
                <p>Pour les élections législatives de 2022, le statut de votre candidature est :</p>
              </div>
              <div class="card-footer d-flex justify-content-around">
                <a href="<?= base_url() ?>xxx" class="btn btn-primary">Modifier le statut de ma candidature</a>
              </div>
            </div>
          </div>
        </div>
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
