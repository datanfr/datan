<div style="width: 100%;">
  <canvas id="proximity"></canvas>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){

    const jsonfile = Object.values(JSON.parse(<?= json_encode($proximity_history_data) ?>));
    const months = Object.values(JSON.parse(<?= json_encode($proximity_history_months) ?>));

    var random = jsonfile[Math.floor(Math.random()*jsonfile.length)].groupeId;

    // Data
    const dataSets = [];

    jsonfile.forEach(o => dataSets.push({
      label: o.groupe,
      data: Object.entries(o.set_data).map( ([key_date, value]) => value.score),
      borderColor: o.color ? o.color : '#' + Math.floor(Math.random()*16777215).toString(16),
      borderWidth: 2,
      fill: false,
      tension: 0.4,
      hidden: o.groupeId != random ? true : false
    }));

    var ctx = document.getElementById('proximity');
    var chartOptions = {
      responsive: true
    }

    // Init the chart
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: months,
        datasets: dataSets
      },
      options: chartOptions,
    });

  });

</script>
