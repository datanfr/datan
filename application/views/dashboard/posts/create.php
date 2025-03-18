

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row my-4">
          <div class="col-sm-7">
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
                <?php if ($this->session->flashdata('error')): ?>
                  <div class="alert alert-danger">
                    <?= $this->session->flashdata('error') ?>
                  </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('post_created')): ?>
                  <div class="alert alert-success">
                    <?= $this->session->flashdata('post_created') ?>
                  </div>
                <?php endif; ?>
                <?= validation_errors();  ?>
                <?= form_open_multipart('posts/create'); ?>
                  <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" placeholder="Ajouter un titre">
                  </div>
                  <div class="form-group">
                    <label>Corps du post</label>
                    <textarea id="editor1" name="body" class="form-control" placeholder="Corps du blog post"></textarea>
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
                    <select class="form-control" name="category_id">
                      <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Image du post</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="post_image" name="post_image">
                      <label class="custom-file-label" for="post_image">Choisir une image</label>
                      <small class="form-text text-muted">Formats acceptés : jpg, jpeg, png, gif, webp. Taille max : 2MB</small>
                    </div>
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
