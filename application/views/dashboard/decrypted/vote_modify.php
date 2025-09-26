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
      <div class="row pb-5">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body py-4">
              <?= form_open_multipart('admin/votes/modify/' . $vote['id']); ?>
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
                <textarea id="editor" name="description" class="form-control"><?= $vote['description'] ?></textarea>
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