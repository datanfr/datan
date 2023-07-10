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
                    <th>id</th>
                    <th>nom</th>
                    <th>mandat</th>
                    <th>circo</th>
                    <th>dpt</th>
                    <th>candidat</th>
                    <th>mpId</th>
                    <th>page Datan</th>
                    <th></th>
                  </tr>
                </thead>
              <tbody>
                <?php foreach ($parrainages as $x): ?>
                  <tr>
                    <td><?= $x['id'] ?></td>
                    <td><?= $x['nameFirst'] ?> <?= $x['nameLast'] ?></td>
                    <td><?= $x['mandat'] ?></td>
                    <td><?= $x['circo'] ?></td>
                    <td><?= $x['dpt'] ?></td>
                    <td><?= $x['candidat'] ?></td>
                    <td class="<?= $x['mpId'] ?: "bg-danger" ?>"><?= $x['mpId'] ?></td>
                    <td>
                      <a href="<?= base_url() ?>deputes/<?= $x['dptSlug'] ?>/depute_<?= $x['nameUrl'] ?>" target="_blank">Page datan</a>
                    </td>
                    <td>
                      <a href="<?= base_url() ?>admin/parrainages/modify/<?= $x['id'] ?>" ?>modifier</a>
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
