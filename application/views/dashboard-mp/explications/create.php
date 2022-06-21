<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->

  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12 mt-5">
          <a class="btn btn-secondary" href="<?= base_url() ?>dashboard-mp/explications/liste">Retour</a>
          <h1 class="font-weight-bold mt-4"><?= $title ?></h1>
          <div class="card mt-5 card-primary card-outline">
            <div class="card-header">
              <h2 class="font-weight-bold text-primary h4"><?= $vote['title'] ?></h2>
            </div>
            <div class="card-body">
              <table class="table mt-1">
                <tbody>
                  <tr>
                    <th>Législature</th>
                    <td><?= $vote['legislature'] ?></td>
                  </tr>
                  <tr>
                    <th>Vote n°</th>
                    <td><?= $vote['voteNumero'] ?></td>
                  </tr>
                  <tr>
                    <th>Date du scrutin</th>
                    <td><?= $vote['dateScrutin'] ?></td>
                  </tr>
                  <tr>
                    <th>Sort du vote</th>
                    <td><?= ucfirst($vote['sortCode']) ?></td>
                  </tr>
                  <tr>
                    <th>Votre position</th>
                    <td><?= ucfirst($vote_depute['vote']) ?></td>
                  </tr>
                  <tr>
                    <th>Position de votre groupe (XX)</th>
                    <td><?= ucfirst($vote_depute['positionGroup']) ?></td>
                  </tr>
                  <tr>
                    <th>Titre du scrutin</th>
                    <td><?= ucfirst($vote['titre']) ?></td>
                  </tr>
                  <tr>
                    <th>Dossier</th>
                    <td>
                      <a href="<?= $vote['dossierUrl'] ?>" target="_blank"><?= $vote['dossier_titre'] ?></a>
                    </td>
                  </tr>
                  <tr>
                    <th>Catégorie</th>
                    <td><?= $vote['category'] ?></td>
                  </tr>
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
