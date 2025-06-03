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
      <div class="row">
        <div class="col-12 col-lg-6">
          <div class="card">
            <div class="card-body py-4">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th style="width: 10px">Variable</th>
                    <th>Valeur</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>ID</td>
                    <td><?= $campaign['id'] ?></td>
                  </tr>
                  <tr>
                    <td>Message</td>
                    <td><?= $campaign['text'] ?></td>
                  </tr>
                  <tr>
                    <td>Date de début</td>
                    <td><?= $campaign['start_date'] ?></td>
                  </tr>
                  <tr>
                    <td>Date de fin</td>
                    <td><?= $campaign['end_date'] ?></td>
                  </tr>
                  <tr>
                    <td>Auteur</td>
                    <td><?= $campaign['author_name'] ?? $campaign['author'] ?></td>
                  </tr>
                  <tr>
                    <td>Position</td>
                    <td><?= $campaign['position'] ?></td>
                  </tr>
                  <tr>
                    <td>Page</td>
                    <td><?= $campaign['page'] ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="card-footer">
              <div class="float-left">
                <button type="button" onclick="window.location.href = '<?= base_url() ?>admin/campagnes';" class="btn btn-default"> Annuler</button>
              </div>
              <div class="float-right">
                <?php echo form_open_multipart('admin/campagnes/delete/' . $campaign['id']); ?>
                <input type="hidden" name="delete" value="deleted">
                <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<aside class="control-sidebar control-sidebar-dark">
  <div class="p-3">
    <h5>Title</h5>
    <p>Sidebar content</p>
  </div>
</aside>