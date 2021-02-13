

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
                <table id="table_votes_an" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>num</th>
                      <th>date</th>
                      <th>titre</th>
                      <th>adopté</th>
                      <th>%pour</th>
                      <th>%abs</th>
                      <th>%contres</th>
                      <th>cohesion</th>
                      <?php foreach ($groupes_libelle as $groupe): ?>
                        <th><?= $groupe["libelle"] ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                <tbody>
                  <?php foreach ($votes as $vote): ?>
                    <tr class="<?= $vote['vote_datan'] ?>">
                      <td><a href="http://www2.assemblee-nationale.fr/scrutins/detail/(legislature)/15/(num)/<?= $vote['voteNumero'] ?>" target="_blank"><?= $vote['voteNumero'] ?></a></td>
                      <td><?= $vote['dateScrutin'] ?></td>
                      <td><?= $vote['titre'] ?></td>
                      <td><?= $vote['voteSort'] ?></td>
                      <td><?= $vote['pours'] ?></td>
                      <td><?= $vote['abstentions'] ?></td>
                      <td><?= $vote['contres'] ?></td>
                      <td><?= $vote['cohesion'] ?></td>
                      <?php foreach ($groupes_libelle as $groupe): ?>
                        <td>
                          <?php if ($vote[$groupe["uid"]] == NULL): ?>
                          <?php else: ?>
                          <?= round($vote[$groupe["uid"]],2) ?>
                          <?php endif; ?>
                        </td>
                      <?php endforeach; ?>
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
