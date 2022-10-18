<ol class="breadcrumb float-sm-right">
  <?php foreach ($breadcrumb as $key => $value): ?>
    <?php if ($value['active'] != 'active'): ?>
      <li class="breadcrumb-item"><a href="<?= $value['url'] ?>" class="no-decoration underline"><?= $value['name'] ?></a></li>
      <?php else: ?>
      <li class="breadcrumb-item active" aria-current="page"><?= $value['name'] ?></li>
    <?php endif; ?>
  <?php endforeach; ?>
</ol>
