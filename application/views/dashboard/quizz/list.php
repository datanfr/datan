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
              <table id="table_votes_datan" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Quizz</th>
                    <th>Vote</th>
                    <th>Législature</th>
                    <th>Title</th>
                    <th>Catégorie</th>
                    <th>State</th>
                    <th>créé le</th>
                    <th>créé par</th>
                    <th>modifié le</th>
                    <th>modifié par</th>
                    <th>mod.</th>
                    <th>supp.</th>
                  </tr>
                </thead>
              <tbody>
                <?php foreach ($questions as $x): ?>
                  <tr>
                    <td><?= $x['quizz'] ?></td>
                    <td><?= $x['voteNumero'] ?></td>
                    <td><?= $x['legislature'] ?></td>
                    <td><?= $x['title'] ?></td>
                    <td><?= $x['category_name'] ?></td>
                    <td><?= $x['state'] ?></td>
                    <td><?= $x['created_at'] ?></td>
                    <td><?= $x['created_by_name'] ?></td>
                    <td><?= $x['modified_at'] ?></td>
                    <td><?= $x['modified_by_name'] ?></td>
                    <td>
                      <?php if ($x['state'] == "published" && $usernameType != "admin"): ?>
                        <?php else: ?>
                        <a href="<?= base_url() ?>admin/quizz/modify/<?= $x['id'] ?>" ?>modifier</a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($usernameType == "admin"): ?>
                        <a href="<?= base_url() ?>admin/quizz/delete/<?= $x['id'] ?>">supprimer</a>
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
