  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <a class="btn btn-primary font-weight-bold" href="<?= base_url() ?>votes/legislature-<?= $expose['legislature'] ?>/vote_<?= $expose['voteNumero'] ?>">Lien vers le vote</a>
                <hr>
                <h5 class="font-weight-bold">Exposé original</h5>
                <p><?= $expose['exposeOriginal'] ?></p>
                <hr>
                <div class="row">
                  <div class="col-lg-4 border">
                    <h6 class="font-weight-bold">Exposé n° 1</h6>
                    <p><?= $expose['exposeSummary1'] ?></p>
                  </div>
                  <div class="col-lg-4 border">
                    <h6 class="font-weight-bold">Exposé n° 2</h6>
                    <p><?= $expose['exposeSummary2'] ?></p>
                  </div>
                  <div class="col-lg-4 border">
                    <h6 class="font-weight-bold">Exposé final</h6>
                    <?php echo form_open_multipart('admin/exposes/modify/' . $expose['id']) ?>
                      <div class="form-group">
                        <textarea name="exposeSummary" class="form-control"><?= $expose['exposeSummaryPublished'] ?></textarea>
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
