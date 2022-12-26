<div style="width: 100%;">
  <canvas id="<?= $type ?>"></canvas>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){
    var data = {
      labels: [
        <?php foreach ($stats_history_chart as $value) {
          echo '"'.$value['dateValue'].'",';
        } ?>
      ],
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
    var ctx = document.getElementById("<?= $type ?>");
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
