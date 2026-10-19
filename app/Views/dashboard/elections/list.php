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
  <div class="row pb-4">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body py-4">
          <a class="btn btn-primary" role="button" href="<?= base_url() ?>admin/elections/candidat/create?election=<?= $election['slug'] ?>">Ajouter un candidat</a>
          <a class="btn btn-warning" role="button" href="<?= base_url() ?>admin/elections/<?= $election['slug'] ?>/non-renseignes">Voir les députés non renseignés</a>
          <a class="btn btn-danger" href="<?= base_url() ?>cache/delete_all" role="button">Supprimer cache après avoir ajouté un candidat</a>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <table id="table_votes_datan" class="table table-bordered table-striped" data-order='[[6, "desc"]]'>
            <thead>
              <tr>
                <th>député</th>
                <th>district</th>
                <th>candidat ?</th>
                <th>position</th>
                <th>second round</th>
                <th>elected</th>
                <th>link</th>
                <th>visible</th>
                <th>dernière modif</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!isset($candidats)) $candidats = [] ?>
              <?php foreach ($candidats as $candidat) : ?>
                <tr>
                  <td><a target="_blank" href="<?= base_url(); ?>deputes/<?= $candidat['dptSlug'].'/depute_'.$candidat['nameUrl'] ?>"><?= $candidat['nameFirst'] .' ' . $candidat['nameLast'] ?></a></td>
                  <td><?= $candidat['districtLibelle'] ?></td>
                  <td>
                    <?php if ($candidat['candidature'] == 1): ?>
                      <span class="text-success font-weight-bold">Oui</span>
                    <?php endif; ?>
                    <?php if ($candidat['candidature'] == 0): ?>
                      <span class="text-danger font-weight-bold">Non</span>
                    <?php endif; ?>
                  </td>
                  <td><?= $candidat['position'] ?></td>
                  <td>
                    <?php if ($candidat['secondRound'] != NULL): ?>
                      <span class="<?= $candidat['secondRound'] == 1 ? "text-success" : "text-danger" ?> font-weight-bold"><?= $candidat['secondRound'] == 1 ? "Se maintient" : "Non" ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                  <?php if ($candidat['elected'] != NULL): ?>
                    <span class="<?= $candidat['elected'] == 1 ? "text-success" : "text-danger" ?> font-weight-bold"><?= $candidat['elected'] == 1 ? "Élu" : "Non" ?></span>
                  <?php endif; ?>
                  <td>
                    <?php if ($candidat['link']): ?>
                      <a href="<?= $candidat['link'] ?>" target="_blank"><?= $candidat['link'] ?></a>
                    <?php endif; ?>
                  <td><?= $candidat['visible'] ?></td>
                  <td><?= $candidat['modified_at'] ?></td>
                  <td>
                    <a class="btn btn-link" href="<?= base_url() ?>admin/elections/candidat/modify/<?= $candidat['mpId'] ?>?election=<?= $election['slug'] ?>" ?>modifier</a><br/>
                    <a class="btn btn-link" href="<?= base_url() ?>admin/elections/candidat/delete/<?= $candidat['mpId'] ?>?election=<?= $election['slug'] ?>">supprimer</a><br/>
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
