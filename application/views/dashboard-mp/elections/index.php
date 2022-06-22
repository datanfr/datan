<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->

  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-6">
          <div class="card mt-5 card-danger card-outline">
            <div class="card-header">
              <h5 class="m-0">Candidature aux <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?></h5>
            </div>
            <div class="card-body">
              <p>Pour les <?= mb_strtolower($election['libelle']) ?> de <?= $election['dateYear'] ?>, le statut de votre candidature est</p>
              <?php if ($candidate): ?>
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <td colspan="2" class="text-center font-weight-bold"><?= $candidate['candidature'] == 1 ? 'Candidat' : 'Non candidat' ?><?= $depute['gender']['e'] ?></td>
                    </tr>
                    <tr>
                      <th scope="row">Département de candidature</th>
                      <td><?= $candidate['district']['libelle'] ? $candidate['district']['libelle'] : 'Non renseigné' ?></td>
                    </tr>
                    <tr>
                      <th scope="row">Lien vers site de campagne</th>
                      <td>
                        <?php if ($candidate['link']): ?>
                          <a href="<?= $candidate['link'] ?>" target="_blank"><?= $candidate['link'] ?></a>
                          <?php else: ?>
                          Non renseigné
                        <?php endif; ?>
                      </td>
                    </tr>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="font-weight-bold">Non renseigné</p>
              <?php endif; ?>
              <p class="font-italic mt-3">Attention, vous pouvez modifier le statut de votre candidature uniquement jusqu'au vendredi précédent le premier tour des élections.</p>
            </div>
            <?php if ($candidate['modify'] == 1): ?>
              <div class="card-footer d-flex justify-content-around">
                <a href="<?= base_url() ?>dashboard-mp/elections/<?= $election['slug'] ?>/modifier" class="btn btn-primary">Modifier le statut de ma candidature</a>
              </div>
            <?php endif; ?>
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
