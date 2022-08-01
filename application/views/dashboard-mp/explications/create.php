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
          <?php if ($page = 'modify'): ?>
            <h2 class="text-secondary"><?= ucfirst($vote['titre']) ?></h2>
          <?php endif; ?>
          <?php if (!empty(validation_errors())): ?>
            <div class="card bg-danger my-5">
              <div class="card-body">
                <?= validation_errors(); ?>
              </div>
            </div>
          <?php endif; ?>
          <div class="card mt-5 card-primary card-outline">
            <div class="card-header">
              <h2 class="font-weight-bold text-primary h4">Rappel du vote</h2>
            </div>
            <div class="card-body">
              <table class="table mt-1">
                <tbody>
                  <tr>
                    <th>Titre</th>
                    <th><?= $vote['title'] ?></th>
                  </tr>
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
          <div class="card mt-5 card-primary card-outline">
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
                  <label>Explication de vote (maximum 100 mots)</label>
                  <textarea id="textbox" name="explication" class="form-control" placeholder="Votre explication de vote" rows="5"><?= $page == 'modify' ? $explication['text'] : '' ?></textarea>
                  <div class="d-flex justify-content-end">
                    <span id="char_count"><?= $page == 'modify' ? strlen($explication['text']) : 0 ?>/500</span>
                  </div>
                </div>
                <div class="form-group">
                  <?php if ($page == 'modify'): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="0" <?= $vote['state'] == 0 ? 'checked=""' : '' ?>>
                      <label class="form-check-label">Brouillon</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="1" <?= $vote['state'] == 1 ? 'checked=""' : '' ?>>
                      <label class="form-check-label">Publié</label>
                    </div>
                  <?php else: ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="0" checked="">
                      <label class="form-check-label">Brouillon</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="state" value="1">
                      <label class="form-check-label">Publié</label>
                    </div>
                  <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
              </form>
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
