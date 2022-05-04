

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
                <?php echo form_open_multipart('dashboard-mp/elections/'.$election['slug'].'/modifier'); ?>
                  <div class="form-group">
                    <label for="">Département de candidature</label>
                    <select class="form-control" name="district">
                      <?php if ($candidate['district']): ?>
                        <option value="<?= $candidate['district']['id'] ?>" selected="selected">Selectionné : <?= $candidate['district']['libelle'] ?></option>
                      <?php endif; ?>
                      <option value=""></option>
                      <?php foreach ($districts as $district): ?>
                        <option value="<?= $district['id'] ?>"><?= $district['libelle'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="d-flex flex-column p-4" style="background-color: rgba(0, 183, 148, 0.3)">
                    <p class="font-weight-bold">Candidature</p>
                    <div class="d-flex">
                      <div class="form-check flex-fill font-weight-bold" >
                        <input class="form-check-input" type="radio" name="candidature" value="1" id="candidature1" <?= $candidate['candidature'] == 1 ? " checked" : NULL ?>>
                        <label class="form-check-label" for="candidature1">
                          Je suis candidat.e
                        </label>
                      </div>
                      <div class="form-check flex-fill">
                        <input class="form-check-input" type="radio" name="candidature" id="candidature2" value="0" <?= $candidate['candidature'] == 0 ? " checked" : NULL ?>>
                        <label class="form-check-label" for="candidature2">
                          Je ne suis pas candidat.e
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="mt-4 p-4" style="background-color: rgba(0, 183, 148, 0.3)">
                    <div class="form-group mt-3">
                      <label>Lien vers site de campagne</label>
                      <input type="text" class="form-control" autocomplete="off" name="link" value="<?= $candidate['link'] ?>">
                    </div>
                    <hr class="my-5">
                    <div class="row">
                      <div class="col-md-4">
                        <p class="font-weight-bold font-italic text-primary">Plus d'infos</p>
                        <p>Le lien vers le site de campagne permet de mettre, dans le bloc consacré aux élections sur votre page personnelle, d'ajouter un bouton vers votre site de campagne.</p>
                        <p>Ce lien peut être un lien vers votre site internet, votre page Facebook ou Twitter, ou vers un document de campagne.</p>
                      </div>
                      <div class="col-md-8">
                        <img src="<?= asset_url() ?>imgs/photos/election-example-btn.png" width="750px" alt="Example bouton" class="img-thumbnail">
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary mt-4">Submit</button>
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
