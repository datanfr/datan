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
          <a class="btn btn-outline-secondary font-weight-bold" href="<?= base_url() ?>dashboard">
            <?= file_get_contents(asset_url() . "imgs/icons/arrow_left.svg") ?>
            Retour
          </a>
        </div>
        <div class="col-lg-7 my-5">
          <?php if ($this->session->flashdata('flash')) : ?>
            <div class="alert alert-primary font-weight-bold text-center mt-4" role="alert"><?= $this->session->flashdata('flash') ?></div>
          <?php endif; ?>
          <h1 class="font-weight-bold mb-0 text-dark"><?= $title ?></h1>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="card mb-0">
            <div class="card-body py-4">
              <h5 class="font-weight-bold text-primary">Infos</h5>
              <p>Vous pouvez rédiger une explication de vote pour indiquer à vos électeurs les <b>raisons de votre position</b>. Ces explications seront visibles sur votre profil Datan ainsi que sur les pages des scrutins.</p>
              <p class="mb-0">Cette fonctionnalité n'est disponible que pour les <b>votes contextualisés par Datan</b>. Les votes contextualisés sont les scrutins que l'équipe de Datan vulgarise et contextualise et qui sont mis en avant sur le site.</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 px-lg-5 px-md-3 py-md-5 py-lg-0 mt-md-0 mt-4">
          <a class="btn btn-primary font-weight-bold d-flex justify-content-center align-items-center" style="font-size: 1.5rem; height: 100%; width: 100%; display: block" href="<?= base_url() ?>dashboard/explications/liste">
            Je créé une nouvelle explication de vote
          </a>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4 class="font-weight-bold text-danger">Mes explications en brouillon</h4>
            </div>
            <div class="card-body">
              <?php if ($votes_draft) : ?>
                <div class="table-responsive">
                  <table class="table">
                    <thead class="thead-dark">
                      <tr>
                        <th>Leg.</th>
                        <th>Scrutin</th>
                        <th class="text-center">Position</th>
                        <th class="text-center d-none d-lg-table-cell">Explication</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votes_draft as $key => $value) : ?>
                        <tr>
                          <td><?= $value['legislature'] ?></td>
                          <td class="font-weight-bold"><?= $value['vote_titre'] ?></td>
                          <td class="text-center"><span class="badge badge<?= ucfirst($value['vote_depute']) ?>" style="font-size: 16px"><?= ucfirst($value['vote_depute']) ?></span></td>
                          <td class="d-none d-lg-table-cell"><?= word_limiter($value['explication'], 30) ?></td>
                          <td class="d-flex flex-column">
                            <a class="btn btn-secondary d-flex align-items-center justify-content-center font-weight-bold mb-1" href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>" style="width: 100%" target="_blank">
                              <?= file_get_contents(asset_url() . "imgs/icons/box-arrow-up-right.svg") ?>
                              <span class="ml-3">Scrutin</span>
                            </a>
                            <a class="btn btn-primary d-flex align-items-center justify-content-center font-weight-bold mb-1" href="<?= base_url() ?>dashboard/explications/modify/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>" style="width: 100%">
                              <?= file_get_contents(asset_url() . "imgs/icons/pencil-square.svg") ?>
                              <span class="ml-3">Modifier</span>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else : ?>
                <p>Vous n'avez pas d'explications de vote en brouillon.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-12 mt-4 mb-4">
          <div class="card">
            <div class="card-header">
              <h4 class="font-weight-bold text-success">Mes explications publiées</h4>
            </div>
            <div class="card-body">
              <?php if ($votes_published) : ?>
                <div class="table-responsive">
                  <table class="table">
                    <thead class="thead-dark">
                      <tr>
                        <th>Leg.</th>
                        <th>Scrutin</th>
                        <th class="text-center">Position</th>
                        <th class="text-center d-none d-lg-table-cell">Explication</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($votes_published as $key => $value) : ?>
                        <tr>
                          <td><?= $value['legislature'] ?></td>
                          <td class="font-weight-bold"><?= $value['vote_titre'] ?></td>
                          <td class="text-center"><span class="badge badge<?= ucfirst($value['vote_depute']) ?>" style="font-size: 16px"><?= ucfirst($value['vote_depute']) ?></span></td>
                          <td class="d-none d-lg-table-cell"><?= word_limiter($value['explication'], 30) ?></td>
                          <td class="d-flex flex-column">
                            <a class="btn btn-secondary d-flex align-items-center justify-content-center font-weight-bold mb-1" href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] ?>" target="_blank" style="width: 100%">
                              <?= file_get_contents(asset_url() . "imgs/icons/box-arrow-up-right.svg") ?>
                              <span class="ml-3">Scrutin</span>
                            </a>
                            <a class="btn btn-primary d-flex align-items-center justify-content-center font-weight-bold mb-1" href="<?= base_url() ?>dashboard/explications/modify/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>" style="width: 100%">
                              <?= file_get_contents(asset_url() . "imgs/icons/pencil-square.svg") ?>
                              <span class="ml-3">Modifier</span>
                            </a>
                            <a type="button" class="btn twitter-bg d-flex align-items-center justify-content-center font-weight-bold social-share text-white mb-1" data-toggle="modal" data-target="#modal_l<?= $value['legislature'] ?>_v<?= $value['voteNumero'] ?>" style="width: 100%">
                              <img src="<?= asset_url() ?>imgs/logos/twitter-no-round.png" alt="Partagez sur Twitter">
                              <span class="ml-2">Lien partage</span>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else : ?>
                <p>Vous n'avez pas encore publié d'explications de vote.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
<?php foreach ($votes_published as $key => $value): ?>
  <div class="modal fade" id="modal_l<?= $value['legislature'] ?>_v<?= $value['voteNumero'] ?>" tabindex="-1" role="dialog" aria-labelledby="Modal législature <?= $value['legislature'] ?> - vote <?= $value['voteNumero'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title font-weight-bold" id="exampleModalLabel">Lien de partage pour réseaux sociaux</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p><b>Vote : <?= $value['vote_titre'] ?></b></p>
          <p>Partagez sur les réseaux sociaux votre explication de vote avec ce lien.</p>
          <p class="font-italic"><?= $value['socialMediaUrl'] ?></p>
          <div class="d-flex">
            <a class="btn btn-primary d-flex align-items-center justify-content-center font-weight-bold" href="<?= $value['socialMediaUrl'] ?>" target="_blank">
              <?= file_get_contents(asset_url() . "imgs/icons/box-arrow-up-right.svg") ?>
              <span class="ml-3">Lien de partage</span>
            </a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
