<div class="test-border" style="width: 100%;">
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
      data: o.set_data.map(v => v.effectif),
      borderColor: o.color,
      borderWidth: 1,
      fill: false
    }));

    var ctx = document.getElementById('members');
    var chartOptions = {
      responsive: true
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
