<div class="container-fluid pg-search bg-info py-5">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1 class="text-center mb-3">Recherchez sur Datan</h1>
      </div>
    </div>
      <?= form_open('/search', 'id="searchForm" method="GET" autocomplete="off"'); ?>
        <div class="row">
          <div class="col-lg-8 offset-lg-1">
            <input class="form-control" id="searchInput" type="text" value="<?= $query ?>">
          </div>
          <div class="col-lg-2 mt-3 mt-lg-0 d-flex justify-content-center">
            <button type="submit" class="btn btn-primary d-flex justify-content-center align-items-center">
              <?= file_get_contents(asset_url() . "imgs/icons/bi-search.svg") ?><span class="ml-2">Rechercher</span>
            </button>
          </div>
        </div>
      <?= form_close() ?>
      <div class="row">
        <div class="col-12">
          <p class="text-center mt-3 mb-0 text-white font-italic">Recherchez un député, un groupe politique, une ville, un vote</p>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container pg-search test-border py-5">
  <div class="row">
    <div class="col-12">
      <h2>Résultats : « <?= $query ?> »</h2>
      <p class="results mb-0"><?= $count ?> résultat<?= $count > 1 ? "s" : "" ?></p>
    </div>
    <div class="col-12 mt-5">
      <?php foreach ($results as $key => $value): ?>
        <?php if ($value['results']): ?>
          <h3>
            <?= $value['name'] ?>
            <span>- <?= count($value['results']) ?> résultat<?= count($value['results']) > 1 ? "s" : "" ?></span>
          </h3>
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

<script>
    document.getElementById('searchForm').addEventListener('submit', function(event) {
      event.preventDefault(); // Prevent default form submission

      var query = document.getElementById('searchInput').value.trim();
      if (query !== '') {
        window.location.href = encodeURIComponent(query); // Redirect to the desired URL
      }
    });
  </script>
