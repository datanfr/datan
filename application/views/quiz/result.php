<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
  <div class="container d-flex flex-column justify-content-center py-2">
    <div class="row">
      <div class="col-12">
        <h1><?= mb_strtoupper($title) ?></h1>
      </div>
    </div>
  </div>
</div>
<div class="container my-3 pg-posts">
  <div class="row row-grid">
    <div class="col-md-9">
      <canvas id="chartGroup" width="400" height="400"></canvas>
  </div>
    <div class="col-md-9">
      <canvas id="chartDepute" width="400" height="400"></canvas>
  </div>
</div>
<script>
  var groups = <?= json_encode($groupes)?>;
  var labels = [];
  var data = [];
  var colors = [];
  for(group of groups){
    labels.push(group.name);
    data.push(group.averageScore);
    colors.push(group.color);
  }
var ctx = document.getElementById('chartGroup').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: labels,
        datasets: [{
            label: 'score',
            data: data,
            backgroundColor: colors
        }]
    }
});
  var deputes = <?= json_encode($deputes)?>;
  var labels = [];
  var data = [];
  var colors = [];
  for(depute of deputes){
    labels.push(depute.name);
    data.push(depute.score);
    colors.push(depute.color);
  }
var ctx = document.getElementById('chartDepute').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: labels,
        datasets: [{
            label: 'score',
            data: data,
            backgroundColor: colors
        }]
    }
});
</script>