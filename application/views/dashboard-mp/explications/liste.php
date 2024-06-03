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
          <a class="btn btn-outline-secondary font-weight-bold" href="<?= base_url() ?>dashboard/explications">
            <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg") ?>
            Retour
          </a>
          <?php if ($this->session->flashdata('flash_failure')): ?>
            <div class="alert alert-danger font-weight-bold mt-4 text-center" role="alert"><?= $this->session->flashdata('flash_failure') ?></div>
          <?php endif; ?>
        </div>
        <div class="col-lg-7 my-5">
          <h1 class="font-weight-bold mb-0 text-dark"><?= $title ?></h1>
        </div>
      </div>
      <div class="d-flex flex-column justify-content-center align-items-center bg-primary py-5" style="margin-left: -31.5px; margin-right: -31.5px">
        <h2 class="font-weight-bold text-white text-center">Suggestion de votes à expliquer</h2>
        <?php $this->load->view('dashboard-mp/explications/partials/suggestions.php') ?>
      </div>
      <div class="row mt-5">
        <div class="col-12 mb-5">
          <h3 class="font-weight-bold text-black">Je sélectionne un vote à expliquer</h3>
          <div class="table-responsive mt-4">
            <table id="table_votes_datan" class="table mt-4" style="background-color: white">
              <thead class="thead-dark">
                <tr>
                  <th scope="col">Législature</th>
                  <th scope="col">Scrutin</th>
                  <th scope="col" class="text-center">Vote</th>
                  <th scope="col">Dossier</th>
                  <th class="text-center">Date</th>
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
                    <td class="d-flex flex-column">
                      <a class="btn btn-secondary d-flex align-items-center justify-content-center font-weight-bold mb-1" href="<?= base_url() ?>votes/legislature-<?= $value['legislature'] ?>/vote_<?= $value['voteNumero'] < 0 ? "c" . abs($value['voteNumero']) : $value['voteNumero'] ?>" target="_blank" style="width: 100%">
                        <?= file_get_contents(asset_url() . "imgs/icons/box-arrow-up-right.svg") ?>
                        <span class="ml-3">Scrutin</span>
                      </a>
                      <a class="btn btn-primary d-flex align-items-center font-weight-bold" href="<?= base_url() ?>dashboard/explications/create/l<?= $value['legislature'] ?>v<?= $value['voteNumero'] ?>" style="width: 100%">
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
