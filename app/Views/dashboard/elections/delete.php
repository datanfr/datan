<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row my-4">
        <div class="col-sm-6">
          <h1 class="m-0 text-primary font-weight-bold" style="font-size: 2rem"><?= $title ?></h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row pb-4">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body py-4">
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
              <?php if (in_array('district', $requiredFields)): ?>
                <div class="form-group">
                  <label>Circonscription de candidature</label>
                  <input name="district" class="form-control" readonly type="text" value="<?= $candidat['district']?>" placeholder="ex: Pays de la Loire"></input>
                </div>
              <?php endif; ?>
              <?php if (in_array('position', $requiredFields)): ?>
                <div class="form-group">
                  <label>Position</label>
                  <input name="position" class="form-control" readonly type="text" value="<?= $candidat['position']?>" placeholder="ex: Tête de liste"></input>
                </div>
              <?php endif; ?>
              <button type="submit" class="btn btn-danger">Delete</button>
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
