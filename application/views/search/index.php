<div class="container-fluid pg-search bg-info py-5">
  <div class="container test-border">
    <div class="row">
      <div class="col-12">
        <span>BARRE DE RECHERCHE</span>
      </div>
    </div>
  </div>
</div>
<div class="container pg-search test-border py-5">
  <div class="row">
    <div class="col-12">
      <h1>Résultats : « <?= $query ?> »</h1>
      <h3><?= $count ?> résultat<?= $count > 1 ? "s" : "" ?></h3>
    </div>
    <div class="col-12 mt-5">
      <?php foreach ($results as $key => $value): ?>
        <?php if ($value['results']): ?>
          <h2>
            <?= $value['name'] ?>
            <span>- <?= count($value['results']) ?> résultat<?= count($value['results']) > 1 ? "s" : "" ?></span>
          </h2>
          <?php foreach ($value['results'] as $x): ?>
            <p>
              <a href="<?= base_url() . "" . $x['url'] ?>" target="_blank"><?= $x['title'] ?> - <?= $x['source'] ?></a>
            </p>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>
