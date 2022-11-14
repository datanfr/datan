<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <?php $this->load->view('dashboard-mp/partials/breadcrumb.php', $breadcrumb) ?>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <a class="btn btn-outline-secondary font-weight-bold" href="<?= base_url() ?>dashboard-mp">
            <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
            Retour
          </a>
          <?php if ($this->session->flashdata('flash')): ?>
            <div class="alert alert-primary font-weight-bold text-center mt-4" role="alert"><?= $this->session->flashdata('flash') ?></div>
          <?php endif; ?>
        </div>
        <div class="col-lg-7 col-12 mt-5">
          <h1 class="font-weight-bold"><?= $title ?></h1>
          <div class="card mt-5">
            <div class="card-body">
              <h5 class="font-weight-bold">Infos</h5>
              <p>Vous pouvez rédiger une explication de vote pour expliquer à vos électeurs les <b>raisons de votre position</b>. Pourquoi avez-vous votez contre cet amendement ? Pourquoi avez-vous soutenu cette proposition de loi ? Cette explication sera visible sur votre page Datan.</p>
              <p>Cette fonctionnalité n'est disponible que pour les <b>votes contextualisés par Datan</b>. Les votes contextualisés sont les scrutins que l'équipe de Datan vulgarise et met en avant sur le site internet, et notamment sur les pages des députés.</p>
            </div>
          </div>
          <div class="mt-5 d-flex justify-content-center">
            <a class="h2 btn btn-primary font-weight-bold" style="font-size: 1.1rem" href="<?= base_url() ?>dashboard-mp/explications/liste">
              <?= file_get_contents(asset_url()."imgs/icons/pencil-square.svg") ?>
              <span class="ml-3">Créez une nouvelle explication de vote</span>
            </a>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="card mt-5 card-danger card-outline">
            <div class="card-header">
              <h4 class="font-weight-bold text-danger">Vos explications de vote en brouillon</h4>
            </div>
            <div class="card-body">
              <?php if ($votes_draft): ?>
                <div class="table-responsive">
                  <table class="table mt-2">
                    <thead class="thead-dark">
                      <tr>
                        <th>Législature</th>
                        <th>Scrutin</th>
                        <th class="text-center">Vote</th>
                        <th class="text-center d-none d-lg-table-cell">Explication</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votes_draft as $key => $value): ?>
                        <tr>
                          <td><?= $value['legislature'] ?></td>
                          <td class="font-weight-bold"><?= $value['vote_titre'] ?></td>
                          <td class="text-center"><span class="badge badge<?= ucfirst($value['vote_depute']) ?>" style="font-size: 16px"><?= ucfirst($value['vote_depute']) ?></span></td>
                          <td class="d-none d-lg-table-cell"><?= word_limiter($value['explication'], 30) ?></td>
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
                </div>
                <?php else: ?>
                <p>Vous n'avez pas encore d'explications de vote en brouillon.</p>
              <?php endif; ?>
            </div>
          </div>
          <div class="card mt-5 card-success card-outline">
            <div class="card-header">
              <h4 class="font-weight-bold text-success">Vos explications de vote publiées</h4>
            </div>
            <div class="card-body">
              <?php if ($votes_published): ?>
                <div class="table-responsive">
                  <table class="table mt-2">
                    <thead class="thead-dark">
                      <tr>
                        <th>Législature</th>
                        <th>Scrutin</th>
                        <th class="text-center">Vote</th>
                        <th class="text-center d-none d-lg-table-cell">Explication</th>
                        <th></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votes_published as $key => $value): ?>
                        <tr>
                          <td><?= $value['legislature'] ?></td>
                          <td class="font-weight-bold"><?= $value['vote_titre'] ?></td>
                          <td class="text-center"><span class="badge badge<?= ucfirst($value['vote_depute']) ?>" style="font-size: 16px"><?= ucfirst($value['vote_depute']) ?></span></td>
                          <td class="d-none d-lg-table-cell"><?= word_limiter($value['explication'], 30) ?></td>
                          <td>
                            <a class="btn btn-success d-flex align-items-center justify-content-center font-weight-bold" href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>" target="_blank">
                              <?= file_get_contents(asset_url()."imgs/icons/box-arrow-up-right.svg") ?>
                              <span class="ml-3">Vote publié</span>
                            </a>
                          </td>
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
                </div>
                <?php else: ?>
                <p>Vous n'avez pas encore publié d'explications de vote.</p>
              <?php endif; ?>
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
