  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold">Espace personnel - <?= $depute['nameFirst'] ?> <?= $depute['nameLast'] ?></h1>
            <p class="mt-3">Bienvenu sur votre espace personnel sur Datan. Cet espace vous permet d'avoir accès à des fonctionnalités dédiés aux parlementaires. Si vous avez des questions, n'hésitez pas à nous contacter : <i>info@datan.fr</i></p>
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
          <div class="col-lg-8">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="mt-0 font-weight-bold">Vos explications de vote en brouillon</h5>
              </div>
              <div class="card-body">
                <?php if ($votes_explained): ?>
                  <p>Vous avez une ou plusieurs explications de vote en brouillon. N'hésitez pas à les terminer pour les publier sur votre page Datan.</p>
                  <table class="table mt-4">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col">Scrutin</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votes_explained as $key => $value): ?>
                        <tr>
                          <td class="font-weight-bold"><?= $value['vote_titre'] ?></td>
                          <td class="text-center"><span class="badge badge<?= ucfirst($value['vote_depute']) ?>" style="font-size: 16px"><?= ucfirst($value['vote_depute']) ?></span></td>
                          <td>
                            <a class="btn btn-primary d-flex align-items-center justify-content-center font-weight-bold" href="<?= base_url() ?>dashboard-mp/explications/modify/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>">
                              <?= file_get_contents(asset_url()."imgs/icons/pencil-square.svg") ?>
                              <span class="ml-3">Modifier</span>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php else: ?>
                  <p>Vous n'avez pas d'explication de vote en brouillon. N'hésitez pas à en créer une nouvelle.</p>
                <?php endif; ?>
              </div>
              <div class="card-footer d-flex justify-content-center align-items-center">
                <a class="btn btn-primary font-weight-bold" href="<?= base_url() ?>dashboard-mp/explications" role="button">Voir toutes vos explications de vote</a>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card card-danger card-outline">
              <div class="card-header">
                <h5 class="m-0">Candidature aux élections législatives 2022</h5>
              </div>
              <div class="card-body">
                <p>Pour les élections législatives de 2022, le statut de votre candidature est</p>
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
                  <a href="<?= base_url() ?>dashboard-mp/elections/legislatives-2022/modifier" class="btn btn-primary">Modifier le statut de ma candidature</a>
                </div>
              <?php endif; ?>
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
