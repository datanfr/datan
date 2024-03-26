<div class="container">
  <div class="row">
    <div class="col-12">
      <p>BLOC DE RECHERCHE ICI</p>
      <h1>Résultats : « query »</h1>
      <h2>XXX résultats</h2>
    </div>
    <div class="col-12">
      <?php foreach ($results as $key => $value): ?>
        <p>
          <a href="<?= base_url() . "" . $value['url'] ?>" target="_blank"><?= $value['title'] ?> - <?= $value['source'] ?></a>
        </p>
      <?php endforeach; ?>
    </div>
  </div>
</div>
