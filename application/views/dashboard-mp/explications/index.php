<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->

  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12 mt-5">
          <h1 class="font-weight-bold"><?= $title ?></h1>
          <p class="mt-5">Vous pouvez renseigner une explication pour chaque scrutin. Pourquoi avez-vous pris position en faveur de tel amendement ? Pourquoi avez-vous voté contre cette proposition de loi ?</p>
          <p>Les explications de vote  doivent permettre aux citoyens de mieux comprendre les motivations de votre position lors du scrutin. Sur Datan, cette explication est mis en avant avec un bouton en dessous de votre position (<i>pour</i>, <i>contre</i>, <i>abstention</i>).</p>
          <p>Vous pouvez renseigner une explication de vote sur les <b>votes contextualisés par Datan</b>. Ces votes sont expliqués, vulgarisés et contextualisés par notre équipe. Ce sont ces votes qui sont mis en avant sur votre page personnelle de député.e.</p>
          <a class="btn btn-primary font-weight-bold mt-3" href="<?= base_url() ?>dashboard-mp/explications/liste">Créez une nouvelle explication de vote</a>
          <div class="card mt-5 card-danger card-outline">
            <div class="card-header">
              <h2 class="font-weight-bold text-primary h4">Vos positions déjà renseignées</h2>
            </div>
            <?php $total = count($votes_explained) ?>
            <div class="card-body">
              <table class="table mt-5">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th>Vote</th>
                    <th class="text-center">Position</th>
                    <th class="text-center">Explication</th>
                    <th class="text-center">État</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($votes_explained as $key => $value): ?>
                    <tr>
                      <td><?= $total ?></td>
                      <td><a href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>" target="_blank"><?= $value['vote_titre'] ?></a></td>
                      <td class="text-center"><?= $value['vote_depute'] ?></td>
                      <td><?= word_limiter($value['explication'], 30) ?></td>
                      <td class="text-center"><?= ucfirst($value['state']) ?></td>
                      <td>
                        <a class="btn btn-primary d-flex align-items-center" href="<?= base_url() ?>dashboard-mp/explications/modify/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>">
                          <?= file_get_contents(asset_url()."imgs/icons/pencil-square.svg") ?>
                          <span class="ml-3">Modifier</span>
                        </a>
                      </td>
                    </tr>
                    <?php $total = $total - 1 ?>
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
