<div style="width: 100%;">
  <canvas id="members"></canvas>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){

    const jsonlabels = Object.values(JSON.parse(<?= json_encode($members_history_labels) ?>));
    const jsondata = Object.values(JSON.parse(<?= json_encode($members_history_data) ?>));

    // Data
    const dataSets = [];
    jsondata.forEach(o => dataSets.push({
      label: o.groupe,
      data: o.set_data.map(row => {
        return {
          "x": row.dateValue,
          "y": row.effectif,
        }
      }),
      radius: 0,
      tension: 0.4,
      borderColor: o.color,
      borderWidth: 4,
      fill: false
    }));

    var ctx = document.getElementById('members');
    var chartOptions = {
      responsive: true,
      plugins: {
        tooltip: {enabled: false},
        hover: {mode: null}
      }
    }

    // Init the chart
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: jsonlabels,
        datasets: dataSets
      },
      options: chartOptions,
    });

  });

</script>
