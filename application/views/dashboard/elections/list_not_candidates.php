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
          <a class="btn btn-primary" role="button" href="<?= base_url() ?>admin/elections/candidat/create?election=<?= $election['slug'] ?>">Ajouter un candidat</a>
          <a class="btn btn-warning" role="button" href="<?= base_url() ?>admin/elections/<?= $election['slug'] ?>">Voir les députés candidats</a>
          <a class="btn btn-danger" href="<?= base_url() ?>cache/delete_all" role="button">Supprimer cache après avoir ajouté un candidat</a>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <p>Au total, <span class="font-weight-bold text-primary"><?= count($mps) ?> députés</span> de la législature actuelle ne sont pas renseignés.</p>
          <table id="table_votes_datan" class="table table-bordered table-striped" data-order='[[6, "desc"]]'>
            <thead>
              <tr>
                <th>Député</th>
                <th>En activité</th>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!isset($mps)) $mp = [] ?>
              <?php foreach ($mps as $mp) : ?>
                <tr>
                  <td><?= $mp['nameFirst'] .' ' . $mp['nameLast'] ?></td>
                  <td class="font-weight-bold"><?= $mp['active'] == 0 ? 'Plus en activité' : '' ?></td>
                  <td><a class="btn btn-outline-primary" target="_blank" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'].'/depute_'.$mp['nameUrl'] ?>">Page Datan</a></td>
                  <td><a class="btn btn-outline-primary" href="<?= base_url(); ?>admin/elections/candidat/create?election=<?= $election['slug'] ?>&mp=<?= $mp['url'] ?>">Renseigner le statut</a></td>
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
