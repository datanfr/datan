<div class="bloc-post">
    <div class="image">
        <img class="img-lazy" src="<?= asset_url() ?>imgs/placeholder/placeholder.png" data-src="<?= asset_url() ?>imgs/posts/img_post_<?= $post['id'] ?>.png" alt="Image post <?= $post['id'] ?>" width="480" height="240">
    </div>
    <div class="mt-2 mb-1">
        <span class="category mr-2"><?= mb_strtoupper($post['category_name']) ?></span>
    </div>
    <<?= $postTitleTag ?> class="title mb-0">
        <a href="<?= base_url() ?>blog/<?= $post['category_slug'] ?>/<?= $post['slug'] ?>" class="stretched-link no-decoration underline"><?= $post['title'] ?></a>
    </<?= $postTitleTag ?>>
    <div>
        <span class="date"><?= $post['created_at_fr'] ?></span>
    </div>
    <?php if ($chapo): ?>
        <p class="chapo"><?= word_limiter(Strip_tags($post['body']), 25) ?></p>
    <?php endif; ?>
    <?php if ($user == "admin" || $user == "writer"): ?>
        <?php if ($post['state'] == "published"): ?>
            <span class="badge badge-success">En ligne</span>
        <?php else: ?>
            <span class="badge badge-danger">Brouillon</span>
        <?php endif; ?>
    <?php endif; ?>
</div>