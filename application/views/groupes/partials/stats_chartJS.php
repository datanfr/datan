<div style="width: 100%;">
  <canvas id="<?= $type ?>"></canvas>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){
    var data = {
      labels: [
        <?php foreach ($stats_history_chart as $value) {
          echo '"'.$value['dateValue_edited'].'",';
        } ?>
      ],
      datasets: [
        {
          data: [
            <?php foreach ($stats_history_chart as $value) {
              echo $value['value'].",";
            } ?>
          ],
          borderColor: "#00b794"
        }
      ]
    };
    var ctx = document.getElementById("<?= $type ?>");
    var chartOptions = {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      tension: 0.4,
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
        },
        x: {
          grid: {
            display: false
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
