<h2 class="my-4">Résultats des élections législatives de 2024</h2>
<p>Les élections législatives se sont déroulées en juin 2024, suite à la <a href="https://datan.fr/blog/actualite-politique/le-president-emmanuel-macron-annonce-la-dissolution-de-lassemblee-nationale">dissolution de l'Assemblée nationale</a> par le président de la République Emmanuel Macron.</p>
<p class="font-weight-bold text-info mb-0">Premier tour</p>
<p>Le premier tour a été marqué par une forte mobilisation, avec un taux de participation de 67% (+ 19 points par rapport à 2022).</p>
<p>Le Rassemblement national (RN) est arrivé en tête du 1er tour (33% des voix), suivi par le Nouveau Front Populaire (NFP), la coalition des partis de gauche (28%). Renaissance, le parti présidentiel, a obtenu 22% des voix, se plaçant en troisième position, suivi des Républicains (7% des voix).</p>
<p class="font-weight-bold text-info mb-0">Composition de l'Assemblée nationale</p>
<p>Le Nouveau Front Populaire, coalition des partis de gauche, a remporté le plus grand nombre de sièges avec 182 députés élus. La coalition présidentielle, Ensemble, suit avec 168 sièges.
<p>Le Rassemblement national a obtenu 143 sièges, soit une cinquantaine de plus qu'en 2022. Malgré cette progression, le parti d'extrême droite n'a pas atteint la majorité absolue, en raison du front républicain et des reports de voix entre les électeurs de gauche et de Renaissance.
<h2 class="mt-5">Résultats des législatives 2024 en nombre de sièges</h2>
<p class="font-italic">Source : Le Monde</p>
<div class="mt-1 mb-1">
  <canvas width="100" height="100" id="chartHemicycle"></canvas>
</div>

<script type="text/javascript">

document.addEventListener('DOMContentLoaded', function(){

  Chart.register(ChartDataLabels);
  var libelles = [
    <?php
    foreach ($groupesSorted as $groupe) {
      echo '"'.$groupe["libelle"].'",';
    }
     ?>
  ];

  var data = {
      labels: [
        <?php
        foreach ($groupesSorted as $groupe) {
          echo '"'.$groupe["libelle"].'",';
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

  var ctx = document.getElementById("chartHemicycle");

  // And for a doughnut chart
  var chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    rotation: 270,
    circumference: 180,
    aspectRatio: 2,
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
      },
      legend: {
        display: false
      },
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
