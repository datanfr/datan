<div style="width: 100%;">
  <canvas id="proximity"></canvas>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){

    const jsonfile = Object.values(JSON.parse(<?= json_encode($proximity_history) ?>));

    // Labels
    const firstKey = Object.keys(jsonfile)[0];
    const labels = jsonfile[firstKey].set_data.map(v => v.month);

    // Data
    const dataSets = [];
    jsonfile.forEach(o => dataSets.push({
      label: o.groupe,
      data: o.set_data.map(v => v.score),
      borderColor: o.color,
      borderWidth: 1,
      fill: false
    }));

    var ctx = document.getElementById('proximity');
    var chartOptions = {
      responsive: true
    }

    // Init the chart
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: dataSets
      },
      options: chartOptions,
    });

  });

</script>
