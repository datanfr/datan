<div class="container my-5 pg-posts">
  <div class="row">
    <!-- Sticky Sidebar -->
    <div class="menu col-md-3">
      <div class="sticky-top" style="top: 80px;">
        <<?= $titleTag ?> class="title">Blog</<?= $titleTag ?>>
        <<?= $subtitleTag ?> class="mt-4 subtitle">Nos analyses et décryptages sur l'Assemblée nationale et les députés</<?= $subtitleTag ?>>
        <div class="mt-4 categories">
          <ul class="list-unstyled mt-4">
            <li class="<?= $page === 'index' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog" class="no-decoration underline">Derniers articles</a>
            </li>
            <li class="<?= $page === 'datan' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog/categorie/datan" class="no-decoration underline">Datan</a>
            </li>
            <li class="<?= $page === 'actualite-politique' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog/categorie/actualite-politique" class="no-decoration underline">Actualités politiques</a>
            </li>
            <li class="<?= $page === 'rapports' ? 'active' : '' ?>">
              <a href="<?= base_url() ?>blog/categorie/rapports" class="no-decoration underline">Rapports</a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 pl-5">
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
          <div class="col-6 mb-5">
            <?php $this->load->view('posts/partials/bloc-post.php', array('post' => $post, 'titleTag' => $postTitleTag, 'chapo' => TRUE)) ?>            
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>