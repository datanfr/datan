<h2 class="my-4">Résultats des élections législatives de 2022</h2>
<p>Les élections législatives se sont déroulées en juin 2022, quelques semaines après <a href="<?= base_url() ?>elections/presidentielle-2022">l'élection présidentielle</a> et la victoire d'Emmanuel Macron.</p>
<p>Le scrutin a été marqué par un faible taux de participation. En effet, seulement 46% des inscrits ont voté pendant le second tour. L'abstention (54%) a toutefois reculé depuis les dernières élections législatives : elle était de 57% en 2017.</p>
<p>Le parti du nouveau président Emmanuel Macron, En Marche, a obtenu une majorité relative, avec 245 sièges (42 % des sièges). Sans majorité absolue, Emmanuel Macron et le gouvernement devront créer des alliances sur chaque texte afin de pouvoir faire voter des lois.</p>
<p>La Nouvelle Union populaire écologique et sociale (NUPES) est arrivée en second position, avec 131 sièges. Cette coalition de gauche comprend les partis La France insoumise, Europe Écologie Les Verts, le Parti socialiste et le Parti communiste.</p>
<p>Le Rassemblement national, qui n'avait qu'une dizaine de parlementaires dans l'Assemblée sortante, gagne 89 députés, un record pour le parti d'extrême droite.</p>
<p>Enfin, le parti de droite Les Républicains arrive en quatrième position et obtient 64 sièges.</p>
<h2 class="mt-5">Résultats des législatives 2022 en nombre de sièges</h2>
<p class="font-italic">Source : Le Monde</p>
<div class="mt-1 mb-1">
  <canvas width="100" height="100" id="chartHemycicle"></canvas>
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

  var ctx = document.getElementById("chartHemycicle");

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
