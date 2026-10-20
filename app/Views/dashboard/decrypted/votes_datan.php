<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row my-5">
        <div class="col-sm-7">
          <h1 class="m-0 text-primary font-weight-bold" style="font-size: 2rem"><?= $title ?></h1>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="container-fluid">
      <div class="row pb-5">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body py-5">
              <table id="table_votes_datan" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>id</th>
                    <th>vote_id</th>
                    <th>title</th>
                    <th>cat.</th>
                    <th>reading</th>
                    <th>state</th>
                    <th>Créé le</th>
                    <th>créé par</th>
                    <th>Modifié le</th>
                    <th>modifié par</th>
                    <th>mod.</th>
                    <th>supp.</th>
                  </tr>
                </thead>
              <tbody>
                <?php foreach ($votes as $vote): ?>
                  <tr>
                    <td><?= $vote['id'] ?></td>
                    <td><?= $vote['vote_id'] ?></td>
                    <td><?= $vote['title'] ?></td>
                    <td><?= $vote['category'] ?></td>
                    <td><?= $vote['reading'] ?></td>
                    <td><?= $vote['state'] ?></td>
                    <td><?= $vote['created_at'] ?></td>
                    <td><?= $vote['created_by_name'] ?></td>
                    <td><?= $vote['modified_at'] ?></td>
                    <td><?= $vote['modified_by_name'] ?></td>
                    <td>
                      <?php if ($vote['state'] == "published" && $usernameType != "admin"): ?>
                        <?php else: ?>
                        <a href="<?= base_url() ?>admin/votes/modify/<?= $vote['id'] ?>" ?>modifier</a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($usernameType == "admin"): ?>
                        <a href="<?= base_url() ?>admin/votes/delete/<?= $vote['id'] ?>">supprimer</a>
                      <?php endif; ?>
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
