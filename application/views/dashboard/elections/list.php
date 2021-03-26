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
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <a class="btn btn-primary" role="button" href="<?= base_url() ?>admin/elections/candidat/create">Ajouter un candidat</a>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <table id="table_votes_datan" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>id</th>
                <th>mpId</th>
                <th>election id</th>
                <th>district</th>
                <th>position</th>
                <th>nuance</th>
                <th>source</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!isset($votes)) $votes = [] ?>
              <?php foreach ($votes as $vote) : ?>
                <tr>
                  <td><?= $vote['id'] ?></td>
                  <td><?= $vote['mpId'] ?></td>
                  <td><?= $vote['election'] ?></td>
                  <td><?= $vote['district'] ?></td>
                  <td><?= $vote['position'] ?></td>
                  <td><?= $vote['nuance'] ?></td>
                  <td><?= $vote['source'] ?></td>
                  <td>
                    <?php if ($vote['state'] == "published" && $usernameType != "admin") : ?>
                    <?php else : ?>
                      <a href="<?= base_url() ?>admin/votes/modify/<?= $vote['id'] ?>" ?>modifier</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($usernameType == "admin") : ?>
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