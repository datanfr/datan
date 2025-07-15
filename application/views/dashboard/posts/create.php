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
                  <textarea id="editor" name="body" class="form-control" placeholder="Corps du blog post"></textarea>

                </div>
                <div class="form-group">
                  <label for="">Categorie</label>
                  <select class="form-control" name="category_id">
                    <?php foreach ($categories as $id => $category): ?>
                      <option value="<?= $id ?>"><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <hr class="mt-4">
                <div class="font-weight-bold">Image du post</div>
                <div class="font-italic">Taille : 1240px X 620px (ratio: 2:1)</div>
                <div class="form-group mt-3">
                  <label class="mb-0">Image PNG</label>
                  <div class="custom-file">
                    <input type="file" accept="image/png" class="custom-file-input" id="post_image_png" name="post_image_png">
                    <label class="custom-file-label" for="post_image_png">Choisir une image</label>
                    <small class="form-text text-muted">Format accepté : png uniquement. Taille max : 2MB</small>
                  </div>
                  <button type="button" id="remove_image_png" class="btn btn-danger mt-2" style="display: none;">Supprimer l'image</button>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Submit</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      function setupImageUpload(inputId, removeBtnId, defaultLabel = "Choisir une image") {
        const fileInput = document.getElementById(inputId);
        const removeBtn = document.getElementById(removeBtnId);

        if (!fileInput || !removeBtn) {
          console.warn(`Missing element: ${inputId} or ${removeBtnId}`);
          return;
        }

        fileInput.addEventListener('change', function(e) {
          const fileName = e.target.files[0] ? e.target.files[0].name : defaultLabel;
          fileInput.nextElementSibling.textContent = fileName;
          removeBtn.style.display = fileName !== defaultLabel ? 'block' : 'none';
        });

        removeBtn.addEventListener('click', function() {
          fileInput.value = '';
          fileInput.nextElementSibling.textContent = defaultLabel;
          this.style.display = 'none';
        });
      }

      setupImageUpload('post_image_png', 'remove_image_png');
    });
  </script>

  <script type="importmap">
    {
  "imports": {
    "ckeditor5": "<?= asset_url() ?>js/libraries/ckeditor/ckeditor5.js",
    "ckeditor5/": "<?= asset_url() ?>js/libraries/ckeditor/"
      }
    }
  </script>

  <script type="module">
    import {
      initCkeditor
    } from "<?= asset_url() ?>js/dashboard/init-ckeditor.js";
    initCkeditor('#editor', 'advanced');
  </script>