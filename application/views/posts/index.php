<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="height: 14em">
  <div class="container d-flex flex-column justify-content-center py-2">
    <div class="row">
      <div class="col-12">
        <h1><?= $title ?></h1>
      </div>
    </div>
  </div>
</div>
<div class="container my-3 pg-posts">
  <div class="row row-grid">
    <div class="col-md-9">
      <?php foreach ($posts as $post): ?>
        <div class="card card-post mt-3">
          <div class="row no-gutters">
            <div class="col-auto img-wrap d-none d-lg-block">
              <?php if(!empty($post['image_name'])): ?>
                <img src="<?= asset_url() ?>imgs/posts/<?= $post['image_name'] ?>" alt="<?= $post['title'] ?>" class="img-fluid">
              <?php else: ?>
                <picture>
                  <source srcset="<?= asset_url() ?>imgs/posts/webp/img_post_<?= $post['id'] ?>.webp" type="image/webp">
                  <source srcset="<?= asset_url() ?>imgs/posts/img_post_<?= $post['id'] ?>.png" type="image/png">
                  <img src="<?= asset_url() ?>imgs/posts/img_post_<?= $post['id'] ?>.png" alt="Image post">
                </picture>
              <?php endif; ?>
            </div>
            <div class="col">
              <div class="card-block p-3">
                <span class="category mr-2"><?= mb_strtoupper($post['category_name']) ?></span>
                <span class="date mr-2"><?= $post['created_at_fr'] ?></span>
                <h2 class="card-title">
                  <a href="<?= base_url() ?>blog/<?= $post['category_slug'] ?>/<?= $post['slug'] ?>" class="stretched-link">
                    <?= $post['title'] ?>
                  </a>
                </h2>
                <p class="card-text"><?= word_limiter(Strip_tags($post['body']), 25) ?></p>
                <?php if ($user == "admin" || $user == "writer"): ?>
                  <?php if ($post['state'] == "published"): ?>
                    <span class="badge badge-success">En ligne</span>
                    <?php else: ?>
                    <span class="badge badge-danger">Brouillon</span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="col-md-3">
      <div class="card card-right-posts mt-lg-3">
        <div class="card-header">
          Catégories
        </div>
        <ul class="list-group list-group-flush">
          <?php foreach ($categories as $category): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>blog/categorie/<?= $category['slug'] ?>" class="no-decoration underline"><?= $category['name'] ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php $this->load->view('posts/partials/follow_us.php') ?>
    </div>
  </div>
</div>
