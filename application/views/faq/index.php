<div class="container-fluid pg-faq py-5" style="background-color: #00b794; color: #fff">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1><?= $title ?></h1>
        <div class="form-group">
          <div class="input-group">
            <input class="form-control form-control-lg filled-input" id="searchfaq" placeholder="Cherchez ..." type="text">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container pg-faq my-5">
  <div class="row">
    <div class="col-lg-4">
      <div class="card card-categories">
        <h2 class="card-header">Categories</h2>
        <ul class="list-group">
          <?php foreach ($categories as $category): ?>
            <li class="list-group-item d-flex align-items-center rounded-0 border">
              <?= $category['name'] ?> <span class="badge badge-primary badge-pill ml-3"><?= $category['n'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div> <!-- END CATEGORIES -->
    <div class="col-lg-8">
      <?php $x = 1; ?>
      <?php foreach ($categories as $category): ?>
        <div class="card card-category mb-5">
          <h2 class="card-header header-category"><?= $category['name'] ?></h2>
          <div class="accordion" id="accordion_<?= $x ?>">
            <?php $y = 1 ?>
            <?php foreach ($articles[$category['id']] as $article): ?>
              <div class="card card-question">
                <div class="card-header d-flex justify-content-between">
                  <a role="button" data-toggle="collapse" href="#collapse_<?= $x ?>_<?= $y ?>" aria-expanded="true" class="no-decoration">
                    <?= file_get_contents(base_url() . '/assets/imgs/icons/plus.svg') ?><span class="ml-3"><?= $article['title'] ?></span>
                  </a>
                </div>
                <div id="collapse_<?= $x ?>_<?= $y ?>" class="collapse" data-parent="#accordion_<?= $x ?>" role="tabpanel">
                  <div class="card-body"><?= $article['text'] ?></div>
                </div>
              </div>
              <?php $y++ ?>
            <?php endforeach; ?>
          </div>
        </div>
        <?php $x++ ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>
