<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row my-4">
        <div class="col-sm-7">
          <h1 class="m-0 text-primary font-weight-bold" style="font-weight: 2rem"><?= $title ?></h1>
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
              <?= form_open_multipart('admin/votes/create'); ?>
                <div class="form-group">
                  <label>Titre</label>
                  <input type="text" class="form-control" autocomplete="off" name="title" placeholder="Ajouter un titre">
                </div>
                <div class="form-group">
                  <label>Legislature</label>
                  <select class="form-control" name="legislature">
                    <?php for ($i = 15; $i <= legislature_current(); $i++) : ?>
                      <option value="<?= $i ?>" <?= $i == legislature_current() ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="">Vote ID --> <a href="<?= base_url() ?>admin/votes_an/position/" target="_blank">check list of votes</a></label>
                  <input type="text" class="form-control" autocomplete="off" name="vote_id" placeholder="Exemple: 156">
                </div>
                <div class="form-group">
                  <label>Description</label>
                  <textarea id="editor1" name="description" class="form-control" placeholder="Description du vote"></textarea>
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
                    <?php foreach ($categories as $category): ?>
                      <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="">Lecture</label>
                  <select class="form-control" name="reading">
                    <option value=""></option>
                    <?php foreach ($readings as $reading): ?>
                      <option value="<?= $reading['id'] ?>"><?= $reading['name'] ?></option>
                    <?php endforeach; ?>
                  </select>
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
    </div>
  </div>
</div>
