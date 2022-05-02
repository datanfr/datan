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
              <?php
                echo form_open_multipart('admin/elections/candidat/modify/'.$candidat['mpId'].'?election='.$election['slug']);
              ?>
              <div class="form-group">
                <label>Député</label>
                <input type="text" class="form-control" autocomplete="off" readonly value="<?= $candidat['nameFirst'] .' ' . $candidat['nameLast'] . ' ('. $candidat['mpId'] . ')' ?>">
                <input name="mpId" type="hidden" class="form-control" autocomplete="off" readonly value="<?= $candidat['mpId']?>">
              </div>
              <div class="form-group">
                <label>Election</label>
                <input name="election" class="form-control" type="text" readonly value="<?= $election['id'] ?>"></input>
              </div>
              <?php if (in_array('district', $requiredFields)): ?>
                <div class="form-group">
                  <label for="">Circonscription de candidature</label>
                  <select class="form-control" name="district">
                    <option value="<?= $candidat['districtId'] ?>" selected="selected">Selectionné : <?= $candidat['districtLibelle'] ?></option>
                    <?php foreach ($districts as $district): ?>
                      <?php if ($district['libelle'] !== $candidat['regionLibelle']): ?>
                        <option value="<?= $district['id'] ?>"><?= $district['libelle'] ?></option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
              <?php if (in_array('position', $requiredFields)): ?>
                <div class="form-group">
                  <label for="">Position sur la liste</label>
                  <select class="form-control" name="position">
                    <option value="<?= $candidat['position'] ?>" selected="selected">Selectionné : <?= $candidat['position'] ?></option>
                    <?php foreach ($positions as $position): ?>
                      <?php if ($position !== $candidat['position']): ?>
                        <option value="<?= $position ?>"><?= $position ?></option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
              <div class="d-flex p-3 my-3" style="background-color: rgba(0, 183, 148, 0.3)">
                <div class="form-check flex-fill font-weight-bold" >
                  <input class="form-check-input" type="radio" name="candidature" value="1" id="candidature1" <?= $candidat['candidature'] == 1 ? " checked" : NULL ?>>
                  <label class="form-check-label" for="candidature1">
                    Candidat
                  </label>
                </div>
                <div class="form-check flex-fill">
                  <input class="form-check-input" type="radio" name="candidature" id="candidature2" value="0" <?= $candidat['candidature'] == 0 ? " checked" : NULL ?>>
                  <label class="form-check-label" for="candidature2">
                    Non candidat
                  </label>
                </div>
              </div>
              <div class="form-group p-3" style="background-color: rgba(0, 183, 148, 0.3)">
                <label>Se maintient au 2nd tour ?</label>
                <select class="form-control" name="secondRound">
                  <option value="99" <?= $candidat['secondRound'] === NULL ? " selected='selected'" : NULL ?>>Ne sait pas</option>
                  <option value="1" <?= $candidat['secondRound'] === "1" ? " selected='selected'" : NULL ?>>Oui</option>
                  <option value="0" <?= $candidat['secondRound'] === "0" ? " selected='selected'" : NULL ?>>Non</option>
                </select>
              </div>
              <div class="form-group p-3" style="background-color: rgba(0, 183, 148, 0.3)">
                <label>Elu ?</label>
                <select class="form-control" name="elected">
                  <option value="99" <?= $candidat['elected'] == NULL ? " selected='selected'" : NULL ?>>Ne sait pas</option>
                  <option value="1" <?= $candidat['elected'] === "1" ? " selected='selected'" : NULL ?>>Oui</option>
                  <option value="0" <?= $candidat['elected'] === "0" ? " selected='selected'" : NULL ?>>Non</option>
                </select>
              </div>
              <div class="form-group p-3" style="background-color: rgba(0, 183, 148, 0.3)">
                <label>Visible</label>
                <input name="visible" class="form-control" type="checkbox" <?= $candidat['visible'] ? 'checked': ''?> value="true"></input>
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
