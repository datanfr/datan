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
                  <label for="">Numéro de vote</label>
                  <input type="text" class="form-control" autocomplete="off" name="vote_id" placeholder="Exemple: 156">
                </div>
                <div class="form-group">
                  <label>Description</label>
                  <textarea id="editor1" name="description" class="form-control" placeholder="Description du vote"></textarea>
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
                        ClassicEditor,
                        Essentials,
                        Paragraph,
                        Bold,
                        Italic,
                        Heading,
                        Link,
                        List,
                        Indent,
                        SourceEditing,
                        Undo,
                        Alignment,
                        SimpleUploadAdapter
                      } from 'ckeditor5';

                      ClassicEditor
                        .create( document.querySelector( '#editor1' ), {
                          licenseKey: 'GPL',
                          plugins: [
                            Essentials,
                            Paragraph,
                            Heading,
                            Bold,
                            Italic,
                            Link,
                            List,
                            Indent,
                            SourceEditing,
                            Alignment,
                            SimpleUploadAdapter
                          ],
                          toolbar: [
                            'undo', 'redo', '|',
                            'heading', '|',
                            'bold', 'italic', 'alignment', '|',
                            'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                            'link', '|',
                            'sourceEditing'
                          ],
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
                          },
                          simpleUpload: {
                            uploadUrl: '<?= base_url() ?>upload/image',
                            withCredentials: false,
                          }
                        }).then( editor => {
                          window.editor = editor;
                        }).catch( error => {
                          console.error( error );
                        });
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
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
