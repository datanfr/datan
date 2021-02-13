    <div class="container mt-3">
      <h2><?= $title; ?></h2>
      <ul class="list-group">
        <?php foreach ($categories as $category): ?>
          <li class="list-group-item">
            <a href="<?php echo base_url('/categories/posts/'.$category['id']); ?>"><?php echo $category['name'] ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
