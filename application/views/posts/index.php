<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
  <div class="container d-flex flex-column justify-content-center py-2">
    <div class="row">
      <div class="col-12">
        <h1><?= mb_strtoupper($title) ?></h1>
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
              <img src="<?= asset_url() ?>imgs/posts/img_post_<?= $post['id'] ?>.png" alt="Image post">
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
      <div class="card card-right-posts mt-3 card-social-media-posts">
        <div class="card-header">
          Suivez-nous
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <a href="https://www.facebook.com/datanFR/" class="no-decoration underline" target="_blank">
              <img src="<?= asset_url() ?>imgs/logos/facebook.png" alt="logo Facebook">Facebook</a>
          </li>
          <li class="list-group-item">
            <a href="https://twitter.com/datanFR" class="no-decoration underline" target="
            ">
              <img src="<?= asset_url() ?>imgs/logos/twitter.png" alt="logo Twitter">Twitter</a>
          </li>
        </ul>
      </div>
      <div class="card card-right-posts mt-3" style="">
        <div class="card-header">
          Derniers tweets
        </div>
        <div class="card-body m-0 p-0">
          <a class="twitter-timeline" data-lang="fr" data-theme="light" data-tweet-limit="2" data-chrome="nofooter noborders noheader transparent" href="https://twitter.com/datanFR?ref_src=twsrc%5Etfw">Tweets by datanFR</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        </div>
      </div>
    </div>
  </div>
</div>
