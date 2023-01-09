<div style="width: 100%;">
  <canvas id="members"></canvas>
</div>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){

  var options = {
    type: 'bar',
    data: {
      labels: [<?php foreach ($members_history['legislatures'] as $value) { echo '"'.$value['name'].'",'; } ?>],
      datasets: [{
          data: [<?php foreach ($members_history['data'][1]['data'] as $value) { echo '"'.$value.'",'; } ?>],
          year: [<?php foreach ($members_history['data'][1]['year'] as $value) { echo '"'.$value.'",'; } ?>],
        },
        {
          data: [<?php foreach ($members_history['data'][2]['data'] as $value) { echo '"'.$value.'",'; } ?>],
          year: [<?php foreach ($members_history['data'][2]['year'] as $value) { echo '"'.$value.'",'; } ?>],
        },
        {
          data: [<?php foreach ($members_history['data'][3]['data'] as $value) { echo '"'.$value.'",'; } ?>],
          year: [<?php foreach ($members_history['data'][3]['year'] as $value) { echo '"'.$value.'",'; } ?>],
        },
        {
          data: [<?php foreach ($members_history['data'][4]['data'] as $value) { echo '"'.$value.'",'; } ?>],
          year: [<?php foreach ($members_history['data'][4]['year'] as $value) { echo '"'.$value.'",'; } ?>],
        },
        {
          data: [<?php foreach ($members_history['data'][5]['data'] as $value) { echo '"'.$value.'",'; } ?>],
          year: [<?php foreach ($members_history['data'][5]['year'] as $value) { echo '"'.$value.'",'; } ?>],
        },
        {
          data: [<?php foreach ($members_history['data'][6]['data'] as $value) { echo '"'.$value.'",'; } ?>],
          year: [<?php foreach ($members_history['data'][6]['year'] as $value) { echo '"'.$value.'",'; } ?>],
        }
      ]
    },
    options: {
      backgroundColor: "#00b794",
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            title: function(context, data) {
              return context[0].dataset.year[context[0].dataIndex];
            }
          }
        }
      }
    }
  }

  var ctx = document.getElementById('members');
  new Chart(ctx, options);

});

</script>
