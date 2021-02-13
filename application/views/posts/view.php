<div class="container pg-post-view my-5">
  <?php if ($user == "admin" || $user == "writer"): ?>
    <div class="row py-3" style="background-color: rgba(255, 102, 26, 0.1)">
      <div class="col-12">
        <div class="d-flex align-items-center justify-content-around">
          <?php if ($post['state'] == "published"): ?>
            <span class="badge badge-success">En ligne</span>
            <?php else: ?>
            <span class="badge badge-danger">Brouillon</span>
          <?php endif; ?>
          <a class="btn btn-secondary float-left" href="<?php echo base_url(); ?>posts/edit/<?php echo $post['slug']; ?>">Edit</a>
          <?php echo form_open('posts/delete/'.$post['id']);  ?>
            <input type="submit" name="" value="delete" class="btn btn-danger">
          </form>
        </div>
      </div>
      <div class="col-12 mt-3">
        <p class="text-center">ID du blog = <?= $post['id'] ?></p>
      </div>
    </div>
  <?php endif; ?>
  <div class="row">
    <a href="<?= base_url() ?>blog" class="retour"><i class="fas fa-arrow-left"></i> Tous les articles</a>
  </div>
  <div class="row mt-4">
    <div class="col-md-9">
      <h1 class="text-center"><?= $title ?></h1>
      <div class="text-center">
        <span class="date mr-3"><?= $post['created_at_fr'] ?></span>
        <span class="category mr-3"><?= mb_strtoupper($post['category_name']) ?></span>
      </div>
      <div class="img mt-3">
        <img src="<?php echo asset_url(); ?>imgs/posts/img_post_<?php echo $post['id'] ?>.png" alt="Image article">
      </div>
      <div class="body mt-3">
        <?= $post['body'] ?>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-right-posts">
        <div class="card-header">
          Derniers articles
        </div>
        <ul class="list-group list-group-flush">
          <?php foreach ($last_posts as $post): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>blog/<?= $post['category_slug'] ?>/<?= $post['slug'] ?>" class="no-decoration underline-blue"><?= $post['title'] ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="card card-right-posts mt-3">
        <div class="card-header">
          Catégories
        </div>
        <ul class="list-group list-group-flush">
          <?php foreach ($categories as $category): ?>
            <li class="list-group-item">
              <a href="<?= base_url() ?>blog/categorie/<?= $category['slug'] ?>" class="no-decoration underline-blue"><?= $category['name'] ?></a>
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
            <a href="https://www.facebook.com/datanFR/" class="no-decoration underline-blue" target="_blank">
              <img src="<?= asset_url() ?>imgs/logos/facebook.png" alt="logo Facebook">Facebook</a>
          </li>
          <li class="list-group-item">
            <a href="https://twitter.com/datanFR" class="no-decoration underline-blue" target="_blank">
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
