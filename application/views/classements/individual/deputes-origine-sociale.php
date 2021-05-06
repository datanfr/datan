      <div class="row mt-4">
        <div class="col-md-10">
          <p>Les députés sont-ils représentatifs de la population française ?</p>
          <p>Pas vraiment. <span class="url_obf" url_obf="<?= url_obfuscation("https://www.lemonde.fr/les-decodeurs/article/2017/06/26/quelles-professions-exercent-nos-deputes_5151288_4355770.html") ?>">Le Monde</span> note que <b>les députés viennent surtout des catégories professionnelles favorisées</b>. Beaucoup étaient avocats, médecins ou professeurs. Une tendance qui ne s'améliore pas : <span class="url_obf" url_obf="<?= url_obfuscation("https://www.publicsenat.fr/article/politique/une-assemblee-nationale-tres-csp-74986") ?>">Public Sénat</span> indique que les classes supérieures étaient plus présentes à l'Assemblée en 2017 qu'en 2012.</p>
          <p>La grande majorité des parlementaires étaient des cadres ou exerçaient une profession intellectuelle supérieure. Ils représentent <b><?= round($famSocPro_cadres['mps']) ?> % des députés</b>, alors que seulement <?= round($famSocPro_cadres['population']) ?> % de la population française appartient à cette catégorie.</p>
          <p>Au contraire, l'Assemblée ne compte très peu de députés ouvriers ou employés.</p>
          <p><b>Cela a-t-il un impact ?</b> Les citoyens ne votent pas pour une origine sociale mais pour des idées et un programme politique. Cependant, la sous représentation de certaines catégories de la population pose question. <span class="url_obf" url_obf="<?= url_obfuscation("https://onlinelibrary.wiley.com/doi/abs/10.1111/ajps.12112") ?>">Plusieurs chercheurs</span> ont montré que l'origine sociale d'un parlementaire a un impact sur ses idées et la façon dont il vote.</p>
        </div>
      </div>
      <div class="mt-5 test-border">
        <h2 class="mb-5">Titre de la section</h2>
        <div style="min-height: 425px">
          <canvas id="chartOrigineSociale"></canvas>
        </div>
      </div>
      <div class="mt-5 test-border">
        <h2 class="mb-5">Titre de la section</h2>
        <table class="table table-stats" id="table-stats">
          <thead>
            <tr>
              <th class="text-center all">Famille socioprofessionnelle</th>
              <th class="text-center min-tablet">Nombre de députés</th>
              <th class="text-center all">Pourcentage de députés</th>
              <th class="text-center all">Pourcentage dans la population</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; ?>
            <?php foreach ($famSocPro as $x): ?>
              <tr>
                <td class="text-center"><?= $x["famille"] ?></td>
                <td class="text-center"><?= $x["mpsCount"] ? $x["mpsCount"] : 0 ?></td>
                <td class="text-center"><?= $x["mps"] ? round($x["mps"]) : 0 ?> %</td>
                <td class="text-center"><?= $x["population"] ? round($x["population"]) : 0 ?> %</td>
              </tr>
              <?php $i++; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="mt-5 test-border">
        <h2 class="mb-5">Titre de la section</h2>
      </div>
    </div>
    <script type="text/javascript">
      var colorMp = "rgba(0, 183, 148, 1)";
      var colorPop = "rgba(255, 102, 26, 1)";
      const labels = [
        <?php foreach ($famSocPro as $x) {
          echo "[";
          foreach ($x['familleCut'] as $y) {
            if ($y) {
              echo '"'.$y.'",';
            }
          }
          echo "],";
        } ?>
      ];
      const data = {
        labels: labels,
        datasets: [
          {
            label: 'Députés',
            data: [
              <?php foreach ($famSocPro as $fam) {
                echo '"'.$fam['mps'].'",';
              } ?>
            ],
            borderColor: colorMp,
            backgroundColor: colorMp
          },
          {
            label: 'Population',
            data: [
              <?php foreach ($famSocPro as $fam) {
                echo '"'.$fam['population'].'",';
              } ?>
            ],
            borderColor: colorPop,
            backgroundColor: colorPop
          }
        ]
      };
      const options = {
        responsive: true,
        maintainAspectRatio: false,
        tooltips: {
          callbacks: {
            label: function(tooltipItem, data) {
              return data.datasets[tooltipItem.datasetIndex]['data'][tooltipItem.index] + ' %';
            }
          }
        }
      };
      const cut = 10;
      var ctx = document.getElementById('chartOrigineSociale');
      var myChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: data,
        options: options
      });
    </script>
