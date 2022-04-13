

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
                <?= form_open_multipart('dashboard/votes/modify/'.$vote['id']); ?>
                  <div class="form-group">
                    <label>Titre</label>
                    <input type="text" class="form-control" autocomplete="off" name="title" value="<?= $vote['title'] ?>">
                  </div>
                  <div class="form-group">
                    <label for="">Legislature</label>
                      <option value="<?= $vote['vote_id'] ?>"><?= $vote['legislature'] ?></option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="">Vote ID</label>
                      <option value="<?= $vote['vote_id'] ?>"><?= $vote['voteNumero'] ?></option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Description</label>
                    <textarea id="editor1" name="description" class="form-control"><?= $vote['description'] ?></textarea>
                    <script>
                      ClassicEditor
                              .create( document.querySelector( '#editor1' ), {
                                link: {
                                  decorators: {
                                    isExternal: {
                                      mode: 'automatic',
                                      callback: url => (!url.startsWith( 'https://datan.fr' )),
                                      attributes: {
                                        target: '_blank',
                                        rel: 'noopener noreferrer'
                                      }
                                    }
                                  }
                                }
                              } )
                              .then( editor => {
                                      console.log( editor );
                              } )
                              .catch( error => {
                                      console.error( error );
                              } );
                    </script>
                  </div>
                  <div class="form-group">
                    <label for="">Categorie</label>
                    <select class="form-control" name="category">
                      <option value="<?= $vote['category'] ?>" selected="selected">Selected: <?= $vote['category_name'] ?></option>
                      <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="">Lecture</label>
                    <select class="form-control" name="reading">
                      <option value="<?= $vote['reading'] ?>" selected="selected">Selected: <?= $vote['reading_name'] ?></option>
                      <option value=""></option>
                      <?php foreach ($readings as $reading): ?>
                        <option value="<?= $reading['id'] ?>"><?= $reading['name'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <?php if ($this->session->userdata('type') == 'admin'): ?>
                    <div class="form-group">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="state" value="draft" checked="">
                        <label class="form-check-label">Draft</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="state" value="published">
                        <label class="form-check-label">Published</label>
                      </div>
                    </div>
                  <?php endif; ?>
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
