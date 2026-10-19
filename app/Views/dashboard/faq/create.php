

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
                    <textarea id="editor1" name="article" class="form-control" placeholder="Article"></textarea>
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
