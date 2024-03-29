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
                <a class="btn btn-primary font-weight-bold" href="<?= base_url() ?>votes/legislature-<?= $expose['legislature'] ?>/vote_<?= $expose['voteNumero'] ?>">Lien vers le vote</a>
                <hr>
                <h5 class="font-weight-bold">Exposé original</h5>
                <p><?= $expose['exposeOriginal'] ?></p>
                <hr>
                <div class="row">
                  <div class="col-lg-6 border">
                    <h6 class="font-weight-bold">Exposé Open AI</h6>
                    <p><?= $expose['exposeSummary'] ?></p>
                  </div>
                  <div class="col-lg-6 border">
                    <h6 class="font-weight-bold">Exposé final</h6>
                    <?php echo form_open_multipart('admin/exposes/modify/' . $expose['id']) ?>
                      <div class="form-group">
                        <textarea name="exposeSummary" class="form-control" rows="10"><?= $expose['exposeSummaryPublished'] ?></textarea>
                        <div class="d-flex justify-content-end">
                          <span id="char_count"><?= $expose['exposeSummaryPublished'] ? strlen($expose['exposeSummaryPublished']) : 0 ?>/100</span>
                        </div>
                      </div>
                      <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
