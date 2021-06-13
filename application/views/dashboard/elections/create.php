<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <?php if (!empty(validation_errors())): ?>
            <p class="bg-danger">Certains champs n'ont pas été renseignés. Voir ci-dessous.</p>
            <?= validation_errors(); ?>
          <?php endif; ?>
          <div class="card">
            <div class="card-body">
              <?= form_open_multipart('admin/elections/candidat/create?election=' . $election['slug']); ?>
              <div class="form-group">
                <label>Député</label>
                <input name="depute_url" type="text" class="form-control" autocomplete="off" placeholder="ex: http://datan.fr/deputes/maine-et-loire-49/depute_matthieu-orphelin">
              </div>
              <div class="form-group">
                <label>Election</label>
                <input name="election" class="form-control" type="text" value="<?= $election['id'] ?>" placeholder="<?= $election['libelle'] ?> <?= $election['dateYear'] ?>" readonly></input>
              </div>
              <div class="form-group">
                <label for="">Région de candidature</label>
                <select class="form-control" name="district">
                  <?php foreach ($districts as $district): ?>
                    <option value="<?= $district['id'] ?>"><?= $district['libelle'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="">Position (tête de liste ou colistier)</label>
                <select class="form-control" name="position">
                  <?php foreach ($positions as $position): ?>
                    <option value="<?= $position ?>"><?= $position ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Nuance (ne pas remplir)</label>
                <input name="nuance" class="form-control" type="text" placeholder="Ne pas remplir pour le moment" readonly>
              </div>
              <div class="form-group">
                <label>Source (article de presse)</label>
                <input name="source" class="form-control" type="text" placeholder="ex:  https://www.ouest-france.fr/elections/regionales/elections-regionales-en-pays-de-la-loire-qui-seront-les-candidats-en-juin-2021-7190091"></input>
              </div>
              <div class="form-group">
                <label>Visible</label>
                <input name="visible" class="form-control" type="checkbox" value="true"></input>
              </div>
              <button type="submit" class="btn btn-primary">Submit</button>
              </form>

              <!--
                <p class="card-text">
                  Some quick example text to build on the card title and make up the bulk of the card's
                  content.
                </p>
                <a href="#" class="card-link">Card link</a>
                <a href="#" class="card-link">Another link</a>
              -->
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

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
  <div class="p-3">
    <h5>Title</h5>
    <p>Sidebar content</p>
  </div>
</aside>
<!-- /.control-sidebar -->
