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
          <a class="btn btn-primary font-weight-bold mt-3" href="#">Créer une nouvelle explication de vote</a>
          <button type="button" class="btn btn-primary font-weight-bold" data-toggle="modal" data-target="#exampleModalCenter">
            Créer une nouvelle explication de vote
          </button>
          <div class="card mt-5 card-danger card-outline">
            <div class="card-header">
              <h2 class="font-weight-bold text-primary h4">Vos positions déjà renseignées</h2>
            </div>
            <div class="card-body">
              <p>A faire</p>
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
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
