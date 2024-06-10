<h2 class="my-4">Résultats des élections européennes de 2024</h2>
<p>Les élections législatives se sont déroulées en France le 9 juin 2024. Elles ont été marquées par la victoire du Rassemblement national, événement politique qui a conduit Emmanuel Macron <a href="https://datan.fr/blog/actualite-politique/le-president-emmanuel-macron-annonce-la-dissolution-de-lassemblee-nationale">à dissoudre l'Assemblée nationale</a> le soir des élections.</p>
<p>Le scrutin européen a été caractérisé en France par une hausse de la participation. En effet, 51,5% des inscrits se sont rendus aux urnes, contre 50,1% en 2019 et 42,4% en 2014.</p>
<p>La liste du Rassemblement national, menée par Jordan Bardella, est arrivée en tête, avec 31,5% des voix. En deuxième position, la liste Renaissance (Valérie Hayer), a obtenu 14,6% des voix.</p>
<p>La liste socialiste, conduite par Raphaël Glucksmann, a récolté 13,8% des voix, se classant en troisième position. Elle est suivie de la liste France insoumise de Manon Aubry, qui reçoit 9,90% des voix.</p>
<p>Les Républicains, dirigés par François-Xavier Bellamy, sont arrivés en cinquième position avec 7,2% des voix.</p>
<h2 class="mt-5">Résultats des européennes 2024 en nombre de sièges</h2>
<div class="mt-1 mb-1">
  <canvas width="100" height="100" id="chartResults"></canvas>
</div>
<p class="small font-italic">Source : Verian pour le Parlement européen</p>

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

  var ctx = document.getElementById("chartResults");

  // And for a doughnut chart
  var chartOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: true,
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
                size: 12,
                weight: 'bold',
            }
        },
        legend: {
            display: false
        }
    }
  }

  // Init the chart
  var chart = new Chart(ctx, {
    type: 'bar',
    data: data,
    options: chartOptions,
  });

});

</script>