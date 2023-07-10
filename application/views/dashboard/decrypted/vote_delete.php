  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row my-4">
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
              <div class="card-body py-4">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width: 10px">Variable</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Id</td>
                      <td><?= $vote['id'] ?></td>
                    </tr>
                    <tr>
                      <td>Id vote_an</td>
                      <td><?= $vote['vote_id'] ?></td>
                    </tr>
                    <tr>
                      <td>title vote_an</td>
                      <td>to do</td>
                    </tr>
                    <tr>
                      <td>title vote_datan</td>
                      <td><?= $vote['title'] ?></td>
                    </tr>
                    <tr>
                      <td>category</td>
                      <td><?= $vote['category_name'] ?></td>
                    </tr>
                    <tr>
                      <td>reading</td>
                      <td><?= $vote['reading_name'] ?></td>
                    </tr>
                    <tr>
                      <td>description</td>
                      <td><?= $vote['description'] ?></td>
                    </tr>
                    <tr>
                      <td>state</td>
                      <td><?= $vote['state'] ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="card-footer">
                <div class="float-left">
                  <button type="button" onclick="window.location.href = '<?= base_url() ?>admin/votes';" class="btn btn-default"> Annuler</button>
                </div>
                <div class="float-right">
                  <?= form_open_multipart('admin/votes/delete/'.$vote['id']); ?>
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
  </div>
  <aside class="control-sidebar control-sidebar-dark">
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
