<div class="container my-5 pg-posts">
  <div class="row">
    <!-- Sticky Sidebar -->
    <div class="menu col-md-4 col-lg-3">
      <div class="sticky-top" style="top: 80px;">
        <<?= $titleTag ?> class="title">Blog</<?= $titleTag ?>>
        <<?= $subtitleTag ?> class="my-4 subtitle">Nos analyses et décryptages sur l'Assemblée nationale et les députés</<?= $subtitleTag ?>>
        <div class="categories">
          <ul class="list-unstyled mt-md-4 mt-3 row row-cols-2 row-cols-md-1">
            <li class="col <?= $page === 'index' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog" class="no-decoration underline">Derniers articles</a>
            </li>
            <li class="col mt-md-2 <?= $page === 'datan' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog/categorie/datan" class="no-decoration underline">Datan</a>
            </li>
            <li class="col mt-md-2 mt-1 <?= $page === 'actualite-politique' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog/categorie/actualite-politique" class="no-decoration underline">Actualités politiques</a>
            </li>
            <li class="col mt-md-2 mt-1 <?= $page === 'rapports' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog/categorie/rapports" class="no-decoration underline">Rapports</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <!-- Main Content -->
    <div class="col-md-8 col-lg-9 pl-md-5 mt-md-0 mt-4">
      <?php if ($page != 'index'): ?>
        <div class="row">
          <div class="col-12 mb-5">
            <h1 class="text-center category_title"><?= $category['name'] ?></h1>
            <h2 class="text-center category_subtitle"><?= $category['subtitle'] ?></h2>
          </div>
        </div>
      <?php endif; ?>
      <div class="row">
        <?php foreach ($posts as $post): ?>
          <div class="col-lg-6 col-12 mb-5">
            <?php $this->load->view('posts/partials/bloc-post.php', array('post' => $post, 'titleTag' => $postTitleTag, 'chapo' => TRUE)) ?>            
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>