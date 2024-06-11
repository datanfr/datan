// Chart 1 => Composition of National Assembly
  var data = {
    labels: [
      <?php
        foreach ($groupesSorted as $groupe) {
          echo '"'.name_group($groupe["libelle"]).' ('.$groupe['libelleAbrev'].')",';
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
      }
    ]
  };

  var ctx = document.getElementById("chartHemicycle");
  var chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    circumference: 180,
    rotation: 270,
    layout:{
      padding: {
        top: 0,
        bottom: 0,
        left: 15,
        right: 15
      }
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
          size: 14
        }
      },
      legend: {
        display: false
      },
    }
  }

  // Initiate the chart
  var pieChart = new Chart(ctx, {
    plugins: [
      ChartDataLabels,
    ],
    type: 'doughnut',
    data: data,
    options: chartOptions,
  });