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
                <th>Député</th>
                <th>election id</th>
                <th>district</th>
                <th>position</th>
                <th>nuance</th>
                <th>source</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!isset($candidats)) $candidats = [] ?>
              <?php foreach ($candidats as $candidat) : ?>
                <tr>
                  <td><?= $candidat['id'] ?></td>
                  <td><a target="_blank" href="<?php echo base_url(); ?>deputes/<?php echo $candidat['dptSlug'].'/depute_'.$candidat['nameUrl'] ?>" class="stretched-link no-decoration"><?php echo $candidat['nameFirst'] .' ' . $candidat['nameLast'] ?></a></td>
                  <td><?= $candidat['election_libelle'] ?></td>
                  <td><?= $candidat['district'] ?></td>
                  <td><?= $candidat['position'] ?></td>
                  <td><?= $candidat['nuance'] ?></td>
                  <td><?= $candidat['source'] ?></td>
                  <td><a target="_blank" href="<?php echo base_url(); ?>deputes/<?php echo $candidat['dptSlug'].'/depute_'.$candidat['nameUrl'].'?regionales2021' ?>" class="stretched-link no-decoration">Preview</a></td>
                  <td>
                    <?php if ($usernameType != "admin") : ?>
                    <?php else : ?>
                      <a href="<?= base_url() ?>admin/elections/candidat/modify<?= $candidat['id'] ?>" ?>modifier</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($usernameType == "admin") : ?>
                      <a href="<?= base_url() ?>admin/votes/delete/<?= $candidat['id'] ?>">supprimer</a>
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