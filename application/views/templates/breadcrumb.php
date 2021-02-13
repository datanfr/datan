<div class="container-fluid container-breadcrumb" id="container-always-fluid">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb my-1">
            <?php foreach ($breadcrumb as $key => $elmt): ?>
              <?php if ($elmt["active"] != "active"): ?>
                <li class="breadcrumb-item"><a href="<?= $elmt['url'] ?>" class="no-decoration underline"><?= $elmt['name'] ?></a></li>
                <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?= $elmt['name'] ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>
