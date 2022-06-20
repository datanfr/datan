<h2 class="my-4">Résultats des élections législatives de 2022</h2>
<p>Les élections législatives se sont déroulées en juin 2022, quelques semaines après <a href="<?= base_url() ?>elections/presidentielle-2022">l'élection présidentielle</a> et la victoire d'Emmanuel Macron.</p>
<p>Le scrutin a été marqué par un faible taux de participation. En effet, seulement 46% des inscrits ont voté pendant le second tour. L'abstention (54%) a toutefois reculé depuis les dernières élections législatives : elle était de 57% en 2017.</p>
<p>Le parti du nouveau président Emmanuel Macron, En Marche, a obtenu une majorité relative, avec 245 sièges (42 % des sièges). Sans majorité absolue, Emmanuel Macron et le gouvernement devront créer des alliances sur chaque texte afin de pouvoir faire voter des lois.</p>
<p>La Nouvelle Union populaire écologique et sociale (NUPES) est arrivée en second position, avec 131 sièges. Cette coalition de gauche comprend les partis La France insoumise, Europe Écologie Les Verts, le Parti socialiste et le Parti communiste.</p>
<p>Le Rassemblement national, qui n'avait qu'une dizaine de parlementaires dans l'Assemblée sortante, gagne 89 députés, un record pour le parti d'extrême droite.</p>
<p>Enfin, le parti de droite Les Républicains arrive en quatrième position et obtient 64 sièges.</p>
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
