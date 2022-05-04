<h2 class="my-4">Résultats des élections législatives de 2017</h2>
<p>Les élections législatives de 2017 se sont déroulées 11 et 18 juin 2017, quelques semaines après l'élection présidentielle et la victoire d'Emmanuel Macron.</p>
<p>Le scrutin a été marqué par un taux d'abstention record. En effet, 51 % des inscrits ne se sont pas rendus aux urnes lors du premier tour, un taux qui monte à 57 % pour le second tour.</p>
<p>Le parti du nouveau président Emmanuel Macron, En Marche, a obstenu la majorité absolue, avec 308 sièges (53 % des sièges).</p>
<p>Les Républicains, le deuxième parti arrivé en tête, a obtenu 112 députés (19 %). Le Parti Socialiste, majoritaire lors de la législature précédente, n'a obtenu que 30 sièges (5 %).</p>
<p>Avec l'arrivée du nouveau parti En Marche dans le jeu politique français, l'Assemblée nationale a été fortement renouvelée. En effet, la grande majorité des députés élus (415 sur 577) n'avaient jamais siégé à l'Assemblée nationale auparavent. L'Assemblée nationale s'est également fortement féminisée. Aujourd'hui, <a href="<?= base_url() ?>statistiques"><?= $women_mean ?> % des députés sont des femmes</a>.</p>
<h2 class="mt-5">La composition de l'Assemblée nationale aujourd'hui</h2>
<div class="mt-3 mb-4">
  <canvas id="chartHemycicle"></canvas>
</div>

<script type="text/javascript">

document.addEventListener('DOMContentLoaded', function(){

  Chart.plugins.unregister(ChartDataLabels);
  var libelles = [
    <?php
    foreach ($groupesSorted as $groupe) {
      echo '"'.$groupe["libelleAbrev"].'",';
    }
     ?>
  ];

  var data = {
      labels: [
        <?php
        foreach ($groupesSorted as $groupe) {
          echo '"'.$groupe["libelle"].' ('.$groupe['libelleAbrev'].')",';
        }
         ?>
      ],
      datasets: [
          {
              data: [
                <?php
                foreach ($groupesSorted as $groupe) {
                  echo $groupe["effectif"].",";
                }
                 ?>
              ],
              backgroundColor: [
                <?php
                foreach ($groupesSorted as $groupe) {
                  echo '"'.$groupe["couleurAssociee"].'",';
                }
                 ?>
              ],
              hoverBackgroundColor: [
                <?php
                foreach ($groupesSorted as $groupe) {
                  echo '"'.$groupe["couleurAssociee"].'",';
                }
                 ?>
              ]
          }]
  };

  var ctx = document.getElementById("chartHemycicle");

  // And for a doughnut chart
  var chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    rotation: 1 * Math.PI,
    circumference: 1 * Math.PI,
    legend: false,
    layout: {
      padding: 10
    },
    plugins: {
      datalabels: {
        anchor: "end",
        backgroundColor: function(context){
          return context.dataset.backgroundColor;
        },
        borderColor: "white",
        borderRadius: 25,
        borderWidth: 1,
        color: "white",
        font: {
          size: 10
        }
      }
    },
    onClick: function(e){
      if (screen.width >= 960) {
        var element = this.getElementsAtEvent(e);
        var idx = element[0]['_index'];
        var group = libelles[idx];
        location.href = 'https://datan.fr/groupes/' + group;
      }
    },
    hover: {
      onHover: function(x, y){
        const section = y[0];
        const currentStyle = x.target.style;
        currentStyle.cursor = section ? 'pointer' : 'default';
      }
    }
  }

  // Init the chart
  var pieChart = new Chart(ctx, {
    plugins: [
      ChartDataLabels,
      {
        beforeLayout: function(chart) {
          var showLabels = (chart.width) > 500 ? true : false;
          chart.options.plugins.datalabels.display = showLabels;
        }
      },
      {
        onresize: function(chart) {
          var showLabels = (chart.width) > 500 ? true : false;
          chart.options.plugins.datalabels.display = showLabels;
        }
      }
    ],
    type: 'doughnut',
    data: data,
    options: chartOptions,
  });

});

</script>
