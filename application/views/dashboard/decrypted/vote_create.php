

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
                <?php echo form_open_multipart('admin/votes/create'); ?>
                  <div class="form-group">
                    <label>Titre</label>
                    <input type="text" class="form-control" autocomplete="off" name="title" placeholder="Ajouter un titre">
                  </div>
                  <div class="form-group">
                    <div class="form-group">
                      <label>Legislature</label>
                      <select class="form-control" name="legislature">
                        <option>15</option>
                    </select>
                  </div>
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
                        <option value="<?php echo $category['id'] ?>"><?php echo $category['name'] ?></option>
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
