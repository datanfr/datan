

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
                    <label class="mb-0">Image du post</label>
                    <span class="d-block font-italic mb-2">Taille : 1240px X 620px (ratio: 2:1)</span>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="post_image" name="post_image">
                      <label class="custom-file-label" for="post_image">Choisir une image</label>
                      <small class="form-text text-muted">Formats acceptés : jpg, jpeg, png, gif, webp. Taille max : 2MB</small>
                    </div>
                    <button type="button" id="remove_image" class="btn btn-danger mt-2" style="display: none;">Supprimer l'image</button>
                  </div>
                  <button type="submit" class="btn btn-primary">Submit</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<script>
  document.getElementById('post_image').addEventListener('change', function(e) {
      var fileName = e.target.files[0] ? e.target.files[0].name : "Choisir une image";
      this.nextElementSibling.textContent = fileName;

      // Show the remove button if a file is selected
      document.getElementById('remove_image').style.display = fileName !== "Choisir une image" ? 'block' : 'none';
  });

  document.getElementById('remove_image').addEventListener('click', function() {
      var fileInput = document.getElementById('post_image');
      fileInput.value = ""; // Clear the file input
      fileInput.nextElementSibling.textContent = "Choisir une image"; // Reset label
      this.style.display = 'none'; // Hide remove button
  });
</script>