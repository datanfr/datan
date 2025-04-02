<div class="container my-5 pg-posts">
  <div class="row">
    <!-- Sticky Sidebar -->
    <div class="menu col-md-3">
      <div class="sticky-top" style="top: 80px;">
        <h1><?= $title ?></h1>
        <h3 class="mt-4">Nos analyses et décryptages sur l'Assemblée nationale et les députés</h3>
        <div class="mt-4 categories">
          <ul class="list-unstyled mt-4">
            <li>
              <a href="<?= base_url() ?>blog" class="no-decoration underline">Derniers articles</a>
            </li>
            <li>
              <a href="<?= base_url() ?>blog/categorie/datan" class="no-decoration underline">Datan</a>
            </li>
            <li>
              <a href="<?= base_url() ?>blog/categorie/actualite-politique" class="no-decoration underline">Actualités politiques</a>
            </li>
            <li>
              <a href="<?= base_url() ?>blog/categorie/rapports" class="no-decoration underline">Rapports</a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 pl-5">
      <div class="row">
        <?php foreach ($posts as $post): ?>
          <div class="col-6 mb-5">
            <?php $this->load->view('posts/partials/bloc-post.php', array('post' => $post, 'chapo' => TRUE)) ?>            
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>