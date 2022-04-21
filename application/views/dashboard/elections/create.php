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
              <?php if (in_array('district', $requiredFields)): ?>
                <div class="form-group">
                  <label for="">Circonscription de candidature</label>
                  <select class="form-control" name="district">
                    <option value="0">Ne sais pas</option>
                    <?php foreach ($districts as $district): ?>
                      <option value="<?= $district['id'] ?>"><?= $district['libelle'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
              <?php if (in_array('position', $requiredFields)): ?>
                <div class="form-group">
                  <label for="">Position sur la liste</label>
                  <select class="form-control" name="position">
                    <?php foreach ($positions as $position): ?>
                      <option value="<?= $position ?>"><?= $position ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
              <div class="form-group">
                <label>Visible</label>
                <input name="visible" class="form-control" type="checkbox" value="true"></input>
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

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
  <div class="p-3">
    <h5>Title</h5>
    <p>Sidebar content</p>
  </div>
</aside>
<!-- /.control-sidebar -->
