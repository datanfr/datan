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
                <?php echo form_open_multipart('admin/quizz/create'); ?>
                  <div class="form-group">
                    <label>Titre de la question</label>
                    <input type="text" class="form-control" autocomplete="off" name="title" placeholder="Ajouter un titre">
                  </div>
                  <div class="form-group">
                    <label>N° du quizz</label>
                    <input type="text" class="form-control" autocomplete="off" name="quizzNumero" placeholder="Ajouter un numéro">
                  </div>
                  <div class="form-group">
                    <label>N° du vote</label>
                    <input type="text" class="form-control" autocomplete="off" name="voteNumero" placeholder="Ajouter un vote">
                  </div>
                  <div class="form-group">
                    <label>Législature</label>
                    <input type="text" class="form-control" autocomplete="off" name="legislature" placeholder="Ajouter une législature">
                  </div>
                  <div class="form-group">
                    <label for="">Categorie</label>
                    <select class="form-control" name="category">
                      <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id'] ?>"><?php echo $category['name'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="">Swap (changer "pours" et "contres")</label>
                    <input name="swap" class="form-control" type="checkbox" value="true"></input>
                  </div>
                  <hr>
                  <h4 class="text-success">Arguments pour</h4>
                  <div class="form-group">
                    <label>Argument 1</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="for1"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 2</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="for2"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 3</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="for3"></textarea>
                  </div>
                  <hr>
                  <h4 class="text-danger">Arguments contre</h4>
                  <div class="form-group">
                    <label>Argument 1</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="against1"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 2</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="against2"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 3</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="against3"></textarea>
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
