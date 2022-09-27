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
          <h1 class="font-weight-bold mt-4"><?= $title ?></h1>
          <div class="card mt-5">
            <div class="card-body">
              <h5 class="font-weight-bold text-primary">Scrutin n° <?= $vote['voteNumero'] ?></h5>
              <h3 class="font-weight-bold"><?= $vote['title'] ?></h3>
              <p class="text-secondary mb-0"><?= ucfirst($vote['titre']) ?></p>
            </div>
          </div>
          <?php if (!empty(validation_errors())): ?>
            <div class="card bg-danger my-5">
              <div class="card-body">
                <?= validation_errors(); ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-6">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h2 class="font-weight-bold text-primary h4"><?= $page == "modify" ? "Modifiez l'explication de vote" : "Rédigez l'explication de vote" ?></h2>
            </div>
            <div class="card-body">
              <?php if ($page == 'create'): ?>
                <?= form_open_multipart('dashboard-mp/explications/create/l' . $legislature . 'v' . $voteNumero); ?>
                <?php else: ?>
                <?= form_open_multipart('dashboard-mp/explications/modify/l' . $legislature . 'v' . $voteNumero); ?>
              <?php endif; ?>
                <div class="form-group">
                  <label>Explication de vote (maximum 500 caractères)</label>
                  <textarea id="textbox" name="explication" class="form-control" placeholder="Votre explication de vote" rows="5"><?= $page == 'modify' ? $explication['text'] : '' ?></textarea>
                  <div class="d-flex justify-content-end">
                    <span id="char_count"><?= $page == 'modify' ? strlen($explication['text']) : 0 ?>/500</span>
                  </div>
                </div>
                <div class="form-group">
                  <?php if ($page == 'modify'): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="0" <?= $vote['state'] == 0 ? 'checked=""' : '' ?>>
                      <label class="form-check-label text-danger font-weight-bold">Brouillon</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="1" <?= $vote['state'] == 1 ? 'checked=""' : '' ?>>
                      <label class="form-check-label text-success font-weight-bold">Publié</label>
                    </div>
                  <?php else: ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="0" checked="">
                      <label class="form-check-label text-danger font-weight-bold">Brouillon</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="1">
                      <label class="form-check-label text-success font-weight-bold">Publié</label>
                    </div>
                  <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
              </form>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div id="accordion">
            <div class="card">
              <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                  <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Informations sur le scrutin
                  </button>
                </h5>
              </div>
              <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                  <table class="table table-striped mt-1">
                    <tbody>
                      <tr>
                        <th>Titre du scrutin sur Datan</th>
                        <th><?= $vote['title'] ?></th>
                      </tr>
                      <tr>
                        <th>Description</th>
                        <td><?= ucfirst($vote['titre']) ?></td>
                      </tr>
                      <tr>
                        <th>Infos</th>
                        <td>Scrutin n° <?= $vote['legislature'] ?> - Législature n° <?= $vote['legislature'] ?></td>
                      </tr>
                      <tr>
                        <th>Date du vote</th>
                        <td><?= $vote['dateScrutinFR'] ?></td>
                      </tr>
                      <tr>
                        <th>Sort</th>
                        <td class="color<?= $vote['sortCode'] == 'adopté' ? 'Pour' : 'Contre' ?> font-weight-bold"><?= ucfirst($vote['sortCode']) ?></td>
                      </tr>
                      <tr>
                        <th>Votre position</th>
                        <td class="color<?= ucfirst($vote_depute['vote']) ?> font-weight-bold"><?= ucfirst($vote_depute['vote']) ?></td>
                      </tr>
                      <tr>
                        <th>Position de votre groupe (<?= $vote_depute['libelleAbrev'] ?>)</th>
                        <td class="color<?= ucfirst($vote_depute['positionGroup']) ?> font-weight-bold"><?= ucfirst($vote_depute['positionGroup']) ?></td>
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
                <div class="card-footer d-flex justify-content-around">
                  <a class="btn btn-secondary mx-1" href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" target="_blank" role="button">
                    <?= file_get_contents(asset_url()."imgs/icons/box-arrow-up-right.svg") ?>
                    Lien Datan
                  </a>
                  <a class="btn btn-secondary mx-1" href="https://www2.assemblee-nationale.fr/scrutins/detail/(legislature)/<?= $vote['legislature'] ?>/(num)/<?= $vote['voteNumero'] ?>" target="_blank" role="button">
                    <?= file_get_contents(asset_url()."imgs/icons/box-arrow-up-right.svg") ?>
                    Lien Assemblée
                  </a>
                  <a class="btn btn-secondary mx-1" href="<?= $vote['dossierUrl'] ?>" target="_blank" role="button">
                    <?= file_get_contents(asset_url()."imgs/icons/box-arrow-up-right.svg") ?>
                    Dossier
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php if ($page == 'modify'): ?>
            <div class="d-flex justify-content-center my-5">
              <a class="btn btn-outline-danger font-weight-bold" href="<?= base_url() ?>dashboard-mp/explications/delete/l<?= $vote['legislature'] ?>v<?= $vote['voteNumero'] ?>">Supprimer cette explication</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
