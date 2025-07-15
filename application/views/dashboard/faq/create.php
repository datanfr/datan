<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row my-4">
        <div class="col-sm-6">
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
              <?php echo form_open_multipart('admin/faq/create'); ?>
              <div class="form-group">
                <label>Titre</label>
                <input type="text" class="form-control" autocomplete="off" name="title" placeholder="Ajouter un titre">
              </div>
              <div class="form-group">
                <label>Article</label>
                <textarea id="editor" name="article" class="form-control" placeholder="Article"></textarea>
              </div>
              <div class="form-group">
                <label for="">Categorie</label>
                <select class="form-control" name="category">
                  <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id'] ?>"><?php echo $category['name'] ?></option>
                  <?php endforeach; ?>
                </select>
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
  initCkeditor('#editor', 'simple');
</script>