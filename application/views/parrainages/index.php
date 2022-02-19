<div class="container pg-parrainages">
  <div class="row bloc-titre">
    <div class="col-12">
      <h1 class="text-center"><?= $title ?></h1>
    </div>
  </div>
</div>
<div class="container-fluid pg-parrainages infosIndividual py-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-7">
        <h2>Comment fonctionne le parrainage des candidats à la présidentielle ?</h2>
        <p>Les personnes souhaitant être candidates à l'élection présidentielle doivent réunir des parrainages de parlement ou d'élus locaux. Pour voir son élection valider par le Conseil constitutionnel, il faut réunir au moins 500 parrainages venant d'au moins 30 départements différents.</p>
        <p>Le système de parrainage a été mis en place afin d'éviter la multiplication des candidatures.</p>
        <p><b>Qui peut accorder son parrainage ?</b></p>
        <p>Plus de 40 000 élus ont la possibilité d'accorder leur parrainage à un candidat à l'élection présidentielle. Il s'agit des députés, des sénateurs, des élus régionaux et départementaux, ou encore des maires.</p>
      </div>
      <div class="col-lg-5 d-flex align-items-center justify-content-center mt-4 mt-lg-0">
        <div>
          <div class="card">
            <h3 class="card-header">Candidats sélectionnés</h3>
            <div class="card-body">
              <p class="font-weight-bold">Candidats ayant récolté plus de 500 signatures</p>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th scope="col"></th>
                    <th scope="col">Candidat</th>
                    <th scope="col">Parrainages</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; ?>
                  <?php foreach ($candidates_selected as $candidate): ?>
                    <tr>
                      <th><?= $i ?></th>
                      <td><?= $candidate['candidat'] ?></td>
                      <td class="text-center"><?= $candidate['n'] ?></td>
                    </tr>
                    <?php $i++; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <div class="d-flex justify-content-center">
                <a href="#" class="btn btn-primary">Plus d'infos</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container pg-parrainages mt-4 mb-5">
  <div class="row">
    <div class="col-12">
      <h2 class="my-4"><span class="text-primary"><?= count($parrainages) ?></span> députés ont parrainé un candidat à la présidentielle</h2>
      <p>Les députés font partie des élus pouvant accorder leur parrainage à un candidat pour l'élection présidentielle.</p>
      <p>
        Au total, <span class="font-weight-bold text-primary"><?= count($parrainages) ?> députés</span> ont accordé leur signature à un candidat.
        Les députés ont parrainé <?= count($candidates) ?> candidats.
        Le candidat qui arrive en tête auprès des députés est <b><?= $candidates[0]['name'] ?></b>, avec <?= $candidates[0]['parrainages'] ?> parrainages.
        Il est suivi de <b><?= $candidates[1]['name'] ?></b> (<?= $candidates[1]['parrainages'] ?> parrainages) et de <b><?= $candidates[2]['name'] ?></b> (<?= $candidates[2]['parrainages'] ?> parrainages).
      </p>
      <div class="card mt-5 py-3 px-md-4">
        <div class="card-body">
          <h3>Parrainages accordés par les députés</h3>
          <div class="wrapper mt-4">
            <canvas id="myChart" ></canvas>
          </div>

        </div>
      </div>
    </div>
    <div class="col-12 mt-4">
      <h2 class="my-5">Découvrez les parrainages accordés par les députés</h2>
      <table class="table table-striped table-vote-individual" id="table-parrainages-deputes" style="width: 100%">
        <thead>
          <tr>
            <th class="all">Député</th>
            <th class="text-center">Département</th>
            <th class="text-center">Groupe politique</th>
            <th class="all text-center">Parrainage</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($parrainages as $parrainage): ?>
            <tr>
              <td>
                <span class="url_obf no-decoration underline" url_obf="<?= url_obfuscation(base_url()."deputes/".$parrainage['dptSlug']."/depute_".$parrainage['nameUrl']) ?>">
                  <?= $parrainage['nameFirst'] ?> <?= $parrainage['nameLast'] ?>
                </span>
              </td>
              <td class="text-center"><?= $parrainage['departementNom'] ?> (<?= $parrainage['departementCode'] ?>)</td>
              <td class="text-center"><?= $parrainage['groupLibelle'] ?></td>
              <td class="text-center"><?= $parrainage['candidat'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
  const labels = [
    <?php foreach ($candidates as $x) {
      echo "[";
      echo '"'.$x['name'].'",';
      echo "],";
    } ?>
  ];
  var ctx = document.getElementById('myChart');
  var myChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
            label: 'Nombre de parrainages de députés',
            data: [
              <?php foreach ($candidates as $x) {
                echo '"'.$x['parrainages'].'",';
              } ?>
            ],
            backgroundColor: 'rgb(0, 183, 148)',
        }]
      },
      options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        },
        responsive: true,
        maintainAspectRatio: false,
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
          }
        }
      }
  });
</script>
