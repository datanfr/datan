<div class="row">
    <div class="container py-4">
      <div class="row">
        <div class="col-12">
          <h2 class="text-center my-4">Résultats des élections législatives 2024</h2>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-5 col-12 d-flex flex-column justify-content-center">
          <p>Suite à la <a href="https://datan.fr/blog/actualite-politique/le-president-emmanuel-macron-annonce-la-dissolution-de-lassemblee-nationale" target="_blank"> dissolution de l'Assemblée nationale</a> par le président Emmanual Macron, des élections législatives se sont déroulées en juin 2024.</p>
          <p>C'est le Nouveau Front Populaire (NFP), la coalition des partis de gauche, qui est arrivée en tête avec 182 sièges.</p>
          <p>Découvrez la composition de la nouvelle Assemblée nationale.</p>
        </div>
        <div class="col-lg-7 col-12">
          <div class="my-1">
            <canvas width="100" height="100" id="chartLegislatives"></canvas>
          </div>
        </div>
      </div>
      <div class="row mt-4 mb-4">
        <div class="col-12 d-flex justify-content-center">
          <a href="<?= base_url();?>elections/legislatives-2024" class="btn btn-outline-primary">
            En savoir plus
          </a>
        </div>
      </div>
    </div>
  </div>

  <script type="text/javascript">

document.addEventListener('DOMContentLoaded', function(){

  Chart.register(ChartDataLabels);

  // Chart 1 ==> hemicycle (has been removed because of dissolution)

  // Chart 2 ==> Results of 2024 Legislative elections
  var libelles = [
    <?php
    foreach ($legislatives2024 as $groupe) {
      echo '"'.$groupe["libelle"].'",';
    }
     ?>
  ];

  var data = {
    labels: [
      <?php
        foreach ($legislatives2024 as $groupe) {
          echo '"'.$groupe["libelle"].'",';
        }
      ?>
    ],
    datasets: [
      {
        data: [
          <?php
            foreach ($legislatives2024 as $groupe) {
              echo $groupe["effectif"].",";
            }
          ?>
        ],
        backgroundColor: [
          <?php
            foreach ($legislatives2024 as $groupe) {
              echo '"'.$groupe["couleurAssociee"].'",';
            }
          ?>
        ],
        hoverBackgroundColor: [
          <?php
            foreach ($legislatives2024 as $groupe) {
              echo '"'.$groupe["couleurAssociee"].'",';
            }
          ?>
        ]
      }
    ]
  };

  var ctx = document.getElementById("chartLegislatives");

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