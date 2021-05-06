      <div class="row mt-4">
        <div class="col-md-10">
          <p>Les députés sont-ils représentatifs de la population française ?</p>
          <p>Pas vraiment. <span class="url_obf" url_obf="<?= url_obfuscation("https://www.lemonde.fr/les-decodeurs/article/2017/06/26/quelles-professions-exercent-nos-deputes_5151288_4355770.html") ?>">Le Monde</span> note que <b>les députés viennent surtout des catégories professionnelles favorisées</b>. Beaucoup étaient avocats, médecins ou professeurs. Une tendance qui ne s'améliore pas : <span class="url_obf" url_obf="<?= url_obfuscation("https://www.publicsenat.fr/article/politique/une-assemblee-nationale-tres-csp-74986") ?>">Public Sénat</span> indique que les classes supérieures étaient plus présentes à l'Assemblée en 2017 qu'en 2012.</p>
          <p>La grande majorité des parlementaires étaient des cadres ou exerçaient une profession intellectuelle supérieure. Ils représentent <b><?= round($famSocPro_cadres['mps']) ?> % des députés</b>, alors que seulement <?= round($famSocPro_cadres['population']) ?> % de la population française appartient à cette catégorie.</p>
          <p>Au contraire, l'Assemblée ne compte très peu de députés ouvriers ou employés.</p>
          <p><b>Cela a-t-il un impact ?</b> Les citoyens ne votent pas pour une origine sociale mais pour des idées et un programme politique. Cependant, la sous représentation de certaines catégories de la population pose question. <span class="url_obf" url_obf="<?= url_obfuscation("https://onlinelibrary.wiley.com/doi/abs/10.1111/ajps.12112") ?>">Plusieurs chercheurs</span> ont montré que l'origine sociale d'un parlementaire a un impact sur ses idées et la façon dont il vote.</p>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-4">Les 10 métiers les plus communs à l'Assemblée nationale</h2>
        <div class="row">
          <div class="col-md-10">
            <p>Découvrez sur ce graphique les métiers les plus communs parmi les députés.</p>
            <p>À la première place se trouve le métier : <b><?= mb_strtolower($jobs[0]["job"]) ?></b>. Au total, <?= $jobs[0]["total"] ?> parlementaires avaient ce métier avant de devenir député. En deuxième position se trouve le métier de <b><?= lcfirst($jobs[1]["job"]) ?></b> (<?= $jobs[1]["total"] ?> députés).</p>
          </div>
        </div>
        <div class="mt-4" style="min-height: 425px">
          <canvas id="chartJobs"></canvas>
        </div>
      </div>
      <div class="mt-5">
        <h2 class="mb-5">Les catégories professionnelles à l'Assemblée et dans la population</h2>
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
      <div class="mt-5">
        <h2 class="mb-5">Les métiers et catégories professionnelles des députés</h2>
        <table class="table table-stats" id="table-stats">
          <thead>
            <tr>
              <th class="text-center all">Député</th>
              <th class="text-center min-tablet">Groupe</th>
              <th class="text-center all">Ancien métier</th>
              <th class="text-center all">Catégorie socioprofessionnelle</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; ?>
            <?php foreach ($deputes as $mp): ?>
              <tr>
                <td class="text-center"><?= $mp['nameFirst']. " ".$mp['nameLast'] ?></td>
                <td class="text-center"><?= $mp['libelleAbrev'] ?></td>
                <td class="text-center"><?= $mp['job'] ?></td>
                <td class="text-center"><?= $mp['famSocPro'] ?></td>
              </tr>
              <?php $i++; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <script type="text/javascript">
      var colorMp = "rgba(0, 183, 148, 1)";
      const labels = [
        <?php foreach ($jobs as $x) {
          echo '"'.$x['job'].'",';
        } ?>
      ];
      const data = {
        labels: labels,
        datasets: [
          {
            label: 'Députés',
            data: [
              <?php foreach ($jobs as $x) {
                echo '"'.$x['total'].'",';
              } ?>
            ],
            borderColor: colorMp,
            backgroundColor: colorMp
          }
        ]
      };
      const options = {
        responsive: true,
        maintainAspectRatio: false,
        tooltips: {
          callbacks: {
            label: function(tooltipItem, data) {
              return data.datasets[tooltipItem.datasetIndex]['data'][tooltipItem.index] + ' députés';
            }
          }
        }
      };
      const cut = 10;
      var ctx = document.getElementById('chartJobs');
      var myChart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: options
      });
    </script>
