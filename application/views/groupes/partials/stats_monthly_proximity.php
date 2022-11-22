<div class="test-border" style="width: 100%;">
  <canvas id="proximity"></canvas>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){

    var jsonfile = JSON.parse(<?= json_encode($proximity_history) ?>);
    console.log(jsonfile);

    var labels = Object.keys(jsonfile);
    console.log(labels);

    var data = {
      labels: labels,
      datasets: [
        {
          data: [
            <?php foreach ($stats_history_chart as $value) {
              echo $value['value'].",";
            } ?>
          ]
        }
      ]
    };
    var ctx = document.getElementById('proximity');
    var chartOptions = {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      layout:{
        padding: 10
      },
      plugins: {
        legend: {
          display: false
        },
      },
      scales: {
        y: {
          afterDataLimits: (scale) => {
            scale.max = <?= $max ?>;
            scale.min = 0;
          }
        }
      }
    }

    // Init the chart
    var pieChart = new Chart(ctx, {
      type: 'line',
      data: data,
      options: chartOptions,
    });

  });

</script>
