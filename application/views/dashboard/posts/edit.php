

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
                <?php if ($this->session->flashdata('error')): ?>
                  <div class="alert alert-danger">
                    <?= $this->session->flashdata('error') ?>
                  </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('post_updated')): ?>
                  <div class="alert alert-success">
                    <?= $this->session->flashdata('post_updated') ?>
                  </div>
                <?php endif; ?>
                <?= validation_errors();  ?>
                <?= form_open_multipart('posts/update'); ?>
                    <input type="hidden" name="id" value="<?= $post['id']; ?>">
                  <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" placeholder="Ajouter un titre" value="<?= $post['title']; ?>">
                  </div>
                  <div class="form-group">
                    <label>Corps du post</label>
                    <textarea id="editor1" name="body" class="form-control"><?= $post['body'] ?></textarea>
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
                      <option value="<?= $post['category_id'] ?>" selected="selected">Selected: <?= $post['category_name'] ?></option>
                      <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="mb-0">Image du post</label>
                    <span class="d-block font-italic mb-2">Taille : 1240px X 620px (ratio: 2:1)</span>
                    <?php if(!empty($post['image_name'])): ?>
                      <div class="mb-2">
                        <img src="<?= asset_url() ?>imgs/posts/<?= $post['image_name'] ?>" alt="Image du post" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                        <p class="mt-1">Image actuelle : <?= $post['image_name'] ?></p>
                      </div>
                    <?php endif; ?>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="post_image" name="post_image">
                      <label class="custom-file-label" for="post_image">Choisir une nouvelle image</label>
                      <small class="form-text text-muted">Formats acceptés : jpg, jpeg, png, gif, webp. Taille max : 2MB</small>
                    </div>
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
