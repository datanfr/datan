<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row my-4">
        <div class="col-sm-6">
          <h1 class="m-0 text-primary font-weight-bold" style="font-size: 2rem"><?= $title ?></h1>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="container-fluid">
      <div class="row pb-4">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body py-4">
              <table id="table_votes_datan" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Législature</th>
                    <th>Vote</th>
                    <th>Exposé original</th>
                    <th>Exposé publié</th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
              <tbody>
                <?php foreach ($exposes as $x): ?>
                  <tr>
                    <td><?= $x['legislature'] ?></td>
                    <td><?= $x['voteNumero'] ?></td>
                    <td><?= word_limiter($x['exposeOriginal'], 30) ?></td>
                    <td><?= word_limiter($x['exposeSummaryPublished'], 30) ?></td>
                    <td>
                      <a href="<?= base_url() ?>votes/legislature-<?= $x['legislature'] ?>/vote_<?= $x['voteNumero'] ?>" target="_blank">Voir le vote</a>
                    </td>
                    <td>
                      <a href="<?= base_url() ?>admin/exposes/modify/<?= $x['id'] ?>">Modifier</a>
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
</div>
