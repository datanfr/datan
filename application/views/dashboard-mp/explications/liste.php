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
          <p>Cette fonctionnalité n'est disponible que pour les <b>votes contextualisés par Datan</b>. Les votes contextualisés sont les scrutins que l'équipe de Datan vulgarise et met en avant sur le site internet, et notamment sur les pages des députés.</p>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-12">
          <h5 class="font-weight-bold">Sélectionnez un vote à expliquer</h5>
          <div class="table-responsive">
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
</div>
