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
          <a class="btn btn-outline-secondary font-weight-bold" href="<?= base_url() ?>dashboard-mp/explications">
            <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
            Retour
          </a>
          <?php if ($this->session->flashdata('flash_failure')): ?>
            <div class="alert alert-danger font-weight-bold mt-4 text-center" role="alert"><?= $this->session->flashdata('flash_failure') ?></div>
          <?php endif; ?>
        </div>
        <div class="col-lg-7 col-12 mt-4">
          <h1 class="font-weight-bold"><?= $title ?></h1>
          <h5 class="mt-4 font-weight-bold">Infos</h5>
          <p>Vous pouvez rédiger une explication de vote pour expliquer à vos électeurs les <b>raisons de votre position</b>. Cette explication sera visible sur votre page Datan.</p>
          <p>Cette fonctionnalité n'est disponible que pour les <b>votes contextualisés par Datan</b>. Il s'agit des votes qui que notre équipe vulgarise et contextualise et qui sont mis en avant sur les pages personnelles des parlementaires.</p>
        </div>
      </div>
      <div class="row mt-4">
          <div class="col-12">
            <h5 class="font-weight-bold">Sélectionnez un vote à expliquer</h5>
          <table class="table mt-4" style="background-color: white">
            <thead class="thead-dark">
              <tr>
                <th scope="col">Législature</th>
                <th scope="col">Scrutin</th>
                <th scope="col" class="text-center">Vote</th>
                <th scope="col">Dossier</th>
                <th class="text-center">Date</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($votes_without as $key => $value): ?>
                <tr>
                  <td><?= $value['legislature'] ?></td>
                  <td class="font-weight-bold"><?= $value['vote_titre'] ?></td>
                  <td class="text-center"><span class="badge badge<?= ucfirst($value['vote_depute']) ?>" style="font-size: 16px"><?= ucfirst($value['vote_depute']) ?></span></td>
                  <td><?= $value['dossier'] ?></td>
                  <td class="text-center"><?= months_abbrev($value['dateScrutinFR']) ?></td>
                  <td>
                    <a class="btn btn-light d-flex align-items-center font-weight-bold" href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>" target="_blank">
                      <?= file_get_contents(asset_url()."imgs/icons/box-arrow-up-right.svg") ?>
                      <span class="ml-3">Infos</span>
                    </a>
                  </td>
                  <td>
                    <a class="btn btn-primary d-flex align-items-center font-weight-bold" href="<?= base_url() ?>dashboard-mp/explications/create/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>">
                      <?= file_get_contents(asset_url()."imgs/icons/pencil-square.svg") ?>
                      <span class="ml-3">Explication</span>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
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
