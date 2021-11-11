<div class="container">
  <div class="row">
    <div class="col-12">
      <p><a href="<?= base_url(); ?>admin/">Back to admin</a></p>
      <h2><?= $title?></h2>

      <?= validation_errors();  ?>

      <?= form_open_multipart('admin/votes/create'); ?>
        <div class="form-group">
          <label>Titre</label>
          <input type="text" class="form-control" name="title" placeholder="Ajouter un titre">
        </div>
        <div class="form-group">
          <label for="">Vote ID</label>
          <select class="form-control" name="vote_id">
            <?php foreach ($votes_id as $vote): ?>
              <option value="<?= $vote['uid'] ?>"><?= $vote['voteNumero'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea id="editor" name="description" class="form-control" placeholder="Description du vote"></textarea>
        </div>
        <div class="form-group">
          <label for="">Categorie</label>
          <select class="form-control" name="category">
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
      </form>
    </div>
  </div>
</div>
