

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
                <?php echo form_open_multipart('dashboard/quizz/modify/'.$question['id']); ?>
                  <div class="form-group">
                    <label>Titre de la question</label>
                    <input type="text" class="form-control" autocomplete="off" name="title" value="<?= $question['title'] ?>">
                  </div>
                  <div class="form-group">
                    <label>N° du quizz</label>
                    <input type="text" class="form-control" autocomplete="off" name="quizzNumero" value="<?= $question['quizz'] ?>">
                  </div>
                  <div class="form-group">
                    <label>N° du vote</label>
                    <input type="text" class="form-control" autocomplete="off" name="voteNumero" value="<?= $question['voteNumero'] ?>">
                  </div>
                  <div class="form-group">
                    <label>Législature</label>
                    <input type="text" class="form-control" autocomplete="off" name="legislature" value="<?= $question['legislature'] ?>">
                  </div>
                  <div class="form-group">
                    <label for="">Categorie</label>
                    <select class="form-control" name="category">
                      <option value="<?= $question['category'] ?>" selected="selected">Selected: <?= $question['category_name'] ?></option>
                      <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id'] ?>"><?php echo $category['name'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <hr>
                  <h4 class="text-success">Arguments pour</h4>
                  <div class="form-group">
                    <label>Argument 1</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="for1"><?= $question['for1'] ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 2</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="for2"><?= $question['for2'] ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 3</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="for3"><?= $question['for3'] ?></textarea>
                  </div>
                  <hr>
                  <h4 class="text-danger">Arguments contre</h4>
                  <div class="form-group">
                    <label>Argument 1</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="against1"><?= $question['against1'] ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 2</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="against2"><?= $question['against2'] ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Argument 3</label>
                    <textarea type="text" class="form-control" autocomplete="off" name="against3"><?= $question['against3'] ?></textarea>
                  </div>
                  <hr>
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
