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
          <a class="btn btn-secondary float-left" href="<?= base_url(); ?>posts/edit/<?= $post['slug']; ?>">Edit</a>
          <?= form_open('posts/delete/'.$post['id']);  ?>
            <input type="submit" name="" value="delete" class="btn btn-danger">
          </form>
        </div>
      </div>
      <div class="col-12 mt-3">
        <p class="text-center">ID du blog = <?= $post['id'] ?></p>
      </div>
    </div>
  <?php endif; ?>
  <div class="row"> <!-- Title -->
    <div class="col-lg-2">
      <a href="<?= base_url() ?>blog" class="btn btn-outline-dark font-weight-normal px-2">
        <?= file_get_contents(asset_url()."imgs/icons/arrow_left.svg"); ?>
        Tous les articles
      </a>
    </div>
    <div class="col-lg-10">
      <div class="mt-lg-0 mt-4">
        <span>Publié le <?= $post['created_at_fr'] ?></span> -
        <span><a class="no-decoration underline" href="<?= base_url() ?>blog/categorie/<?= $post['category_slug'] ?>"><?= $post['category_name'] ?></a></span>
      </div>
      <h1 class="mt-2 mb-0"><?= $title ?></h1>
    </div>
  </div>
  <div class="row body mt-5"> <!-- Body of article -->
    <div class="col-lg-8 offset-lg-2">
      <div class="img">
        <picture>
          <!-- Mobile WebP --> 
          <source
            media="(max-width: 575.99px)"
            srcset="<?= asset_url() ?>imgs/posts/webp/<?= $post['image_url'] ?>-360.webp" type="image/webp">

          <!-- Desktop/Tablet WebP -->
          <source 
            media="(min-width: 576px)" 
            srcset="<?= asset_url() ?>imgs/posts/webp/<?= $post['image_url'] ?>-730.webp" 
            type="image/webp"
          >

          <!-- Mobile PNG fallback -->
          <source 
            media="(max-width: 575.99px)" 
            srcset="<?= asset_url() ?>imgs/posts/<?= $post['image_url'] ?>-360.png" 
            type="image/png"
          >

          <!-- Desktop PNG fallback -->
          <source 
            media="(min-width: 600px)" 
            srcset="<?= asset_url() ?>imgs/posts/<?= $post['image_url'] ?>-730.png" 
            type="image/png"
          >

          <!-- Fallback img -->
          <img src="<?= asset_url() ?>imgs/posts/<?= $post['image_url'] ?>-730.png" alt="Image post" width="730" height="365" />
        </picture>
      </div>
      <div class="text mt-5">
        <?= $post['body'] ?>
      </div>   
    </div>
  </div>
</div>
