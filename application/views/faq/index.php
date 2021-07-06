<div class="container-fluid pg-faq faq-header py-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 col-md-8 offset-lg-3 offset-md-2">
        <h1 class="text-center">Une question sur Datan ?<br>Sur l'Assemblée nationale ?</h1>
        <div class="form-group mt-4">
          <div class="input-group">
            <input class="form-control form-control-lg filled-input" id="searchfaq" placeholder="Cherchez par mots clés" type="text">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container pg-faq my-5">
  <div class="row">
    <div class="col-md-4">
      <div class="card card-categories">
        <h3 class="card-header">Catégories</h3>
        <ul class="list-group">
          <?php foreach ($categories as $category): ?>
            <a href="#<?= $category['slug'] ?>" class="no-decoration">
              <li class="list-group-item d-flex align-items-center rounded-0 border">
                <?= $category['name'] ?> <span class="badge badge-primary badge-pill ml-3"><?= $category['n'] ?></span>
              </li>
            </a>
          <?php endforeach; ?>
        </ul>
      </div>
    </div> <!-- END CATEGORIES -->
    <div class="col-md-8">
      <?php $x = 1; ?>
      <?php foreach ($categories as $category): ?>
        <div class="anchor" id="<?= $category['slug'] ?>">
          <h2 class="mb-0"><?= $category['name'] ?></h2>
          <div class="accordion my-4" id="accordion_<?= $x ?>">
            <?php $y = 1 ?>
            <?php foreach ($articles[$category['id']] as $article): ?>
              <div class="card card-question">
                <div class="card-header d-flex justify-content-between">
                  <a role="button" data-toggle="collapse" href="#collapse_<?= $x ?>_<?= $y ?>" aria-expanded="true" class="no-decoration">
                    <?= file_get_contents(base_url() . '/assets/imgs/icons/plus.svg') ?>
                    <span class="ml-3"><?= $article['title'] ?></span>
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
