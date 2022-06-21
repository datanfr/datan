<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->

  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12 mt-5">
          <a class="btn btn-secondary" href="<?= base_url() ?>dashboard-mp/explications">Retour</a>
          <?php if ($this->session->flashdata('flash_failure')): ?>
            <div class="alert alert-danger font-weight-bold my-4 text-center" role="alert"><?= $this->session->flashdata('flash_failure') ?></div>
          <?php endif; ?>
          <h1 class="font-weight-bold mt-4"><?= $title ?></h1>
          <p>Pour rappel, vous pouvez renseigner une explication de vote sur les <b>votes contextualisés par Datan</b>. Ces votes sont expliqués, vulgarisés et contextualisés par notre équipe. Ce sont ces votes qui sont mis en avant sur votre page personnelle de député.e.</p>
          <table class="table mt-5">
            <thead>
              <tr>
                <th scope="col">Vote</th>
                <th scope="col">Dossier</th>
                <th class="text-center">Date</th>
                <th class="text-center">Position</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($votes_without as $key => $value): ?>
                <tr>
                  <td><a href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>" target="_blank"><?= $value['vote_titre'] ?></a></td>
                  <td><?= $value['dossier'] ?></td>
                  <td class="text-center"><?= $value['dateScrutinFR'] ?></td>
                  <td class="text-center"><?= $value['vote_depute'] ?></td>
                  <td><a class="btn btn-secondary" href="https://www2.assemblee-nationale.fr/scrutins/detail/(legislature)/<?= $value['legislature'] ?>/(num)/<?= $value['voteNumero'] ?>" target="_blank">Lien AN</a></td>
                  <td><a class="btn btn-primary" href="<?= base_url() ?>dashboard-mp/explications/create/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>">Explication de vote</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Modal Create new vote -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title font-weight-bold h5" id="exampleModalLongTitle">Cherchez un scrutin à expliquer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Législature - numéro de vote</th>
              <th scope="col">Titre vote Datan</th>
              <th scope="col">Dossier</th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($votes_without as $key => $value): ?>
              <tr>
                <th scope="row"><?= $value['legislature'] ?> - <?= $value['voteNumero'] ?></th>
                <td><?= $value['vote_titre'] ?></td>
                <td>Dossier (A faire)</td>
                <td>Lien AN</td>
                <td>Lien Datan</td>
                <td>Créez une explication</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
