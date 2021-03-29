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
          <div class="card">
            <div class="card-body">
              <?php
                echo form_open_multipart('admin/elections/candidat/delete/'.$candidat['mpId'].'?election='.$election['slug']);
              ?>
              <div class="form-group">
                <label>Député</label>
                <input type="text" class="form-control" autocomplete="off" readonly value="<?= $candidat['nameFirst'] .' ' . $candidat['nameLast'] . ' ('. $candidat['mpId'] . ')' ?>">
                <input name="mpId" type="hidden" class="form-control" readonly value="<?= $candidat['mpId']?>">
              </div>
              <div class="form-group">
                <label>Election</label>
                <input name="election" class="form-control" type="text" readonly value="<?= $election['id'] ?>"></input>
              </div>
              <div class="form-group">
                <label>Région de candidature</label>
                <input name="district" class="form-control" readonly type="text" value="<?= $candidat['district']?>" placeholder="ex: Pays de la Loire"></input>
              </div>
              <div class="form-group">
                <label>Position</label>
                <input name="position" class="form-control" readonly type="text" value="<?= $candidat['position']?>" placeholder="ex: Tête de liste"></input>
              </div>
              <div class="form-group">
                <label>Nuance</label>
                <input name="nuance" class="form-control" readonly type="text" value="<?= $candidat['nuance']?>" placeholder="ex: ???"></input>
              </div>
              <div class="form-group">
                <label>Source</label>
                <input name="source" class="form-control" readonly type="text" value="<?= $candidat['source']?>" placeholder="ex:  https://www.ouest-france.fr/elections/regionales/elections-regionales-en-pays-de-la-loire-qui-seront-les-candidats-en-juin-2021-7190091"></input>
              </div>
              <button type="submit" class="btn btn-danger">Delete</button>
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
