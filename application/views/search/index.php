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
<div class="container pg-search py-5">
  <div class="row">
    <div class="col-12">
      <h2>Résultats : « <?= $query ?> »</h2>
      <p class="results mb-0"><?= $count ?> résultat<?= $count > 1 ? "s" : "" ?></p>
    </div>
    <div class="col-12 mt-4">
      <?php foreach ($results as $key => $value): ?>
        <?php if ($value['results']): ?>
          <?php $total = count($value['results']) ?>
          <?php $entries = count($value['results']) > $max_entries ? $max_entries : $total ?>
          <?php $progress = round($entries / $total * 100) ?>
          <div class="mb-5">
            <h3>
              <?= file_get_contents(asset_url() . "imgs/icons/" . $value['icon'] . ".svg") ?>
              <?= $value['name'] ?>
              <span>- <?= $total ?> résultat<?= count($value['results']) > 1 ? "s" : "" ?></span>
            </h3>
            <div class="card card_results mt-3">
              <div class="card-body p-0" id="results_<?= $key ?>">
                <?php for ($x = 0; $x < min(10, count($value['results'])); $x++): ?>
                  <a class="no-decoration" href="<?= base_url() . "" . $value['results'][$x]['url'] ?>">
                    <div class="text">
                      <span class="title"><?= $value['results'][$x]['title'] ?></span>
                      <span class="description"><?= $value['results'][$x]['description'] ?></span>
                    </div>
                    <div class="icon">
                    </div>
                  </a>
                <?php endfor; ?>
              </div>
              <div class="card-footer d-flex flex-column align-items-center bg-info py-3">
                <p class="text-center text-white mb-0">Vous avez vu <?= $entries ?> résultats sur <?= $total ?></p>
                <div class="progress mt-3">
                  <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <?php if ($entries != $total): ?>
                  <div class="d-flex justify-content-center mt-3">
                    <button class="btn btn-light" onclick="showMore('<?= $key ?>')" id="button_<?= $key ?>" type="button">Show More</button>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
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

  var max_entries = 10;
  function showMore(type){
    var results_name = "results_" + type;
    var button_name = "button_" + type;
    var container = document.getElementById(results_name);
    var elements = <?php echo json_encode($results); ?>;
    elements = elements[type].results;
    elements_sliced = elements.slice(max_entries);
    if (elements_sliced.length > 0) {
      for (var i = 0; i < Math.min(10, elements_sliced.length); i++) {
        var new_element = document.createElement("a");
        new_element.className = "no-decoration";
        new_element.href = get_base_url() + "/" + elements_sliced[i]['url'];

        var div_text = document.createElement("div");
        div_text.className = "text";

        var span_title = document.createElement("span");
        span_title.className = "title";
        span_title.textContent = elements_sliced[i]['title'];

        var span_description = document.createElement("span");
        span_description.className = "description";
        span_description.textContent = elements_sliced[i]['description'];

        var div_icon = document.createElement("div");
        div_icon.className = "icon";

        div_text.appendChild(span_title);
        div_text.appendChild(span_description);

        new_element.appendChild(div_text);
        new_element.appendChild(div_icon);
        container.appendChild(new_element);

        max_entries++;
      }

      if (max_entries >= elements.length) {
        document.getElementById(button_name).style.display = "none";
      }

    } else {
     document.getElementById(button_name).style.display = "none";
   }
  }
</script>
