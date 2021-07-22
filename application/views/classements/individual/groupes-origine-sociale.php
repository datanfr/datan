      <div class="row mt-4">
        <div class="col-md-10">
          <p>Les députés sont-ils représentatifs de la population française ?</p>
          <p>Pas vraiment. <span class="url_obf" url_obf="<?= url_obfuscation("https://www.lemonde.fr/les-decodeurs/article/2017/06/26/quelles-professions-exercent-nos-deputes_5151288_4355770.html") ?>">Le Monde</span> note que <b>les députés viennent surtout des catégories professionnelles favorisées</b>. Beaucoup étaient avocat, médecin ou professeur. Une tendance qui ne s'améliore pas : <span class="url_obf" url_obf="<?= url_obfuscation("https://www.publicsenat.fr/article/politique/une-assemblee-nationale-tres-csp-74986") ?>">Public Sénat</span> indique que les classes supérieures étaient plus présentes à l'Assemblée en 2017 qu'en 2012.</p>
          <p>La grande majorité des parlementaires étaient des cadres ou exerçaient une profession intellectuelle supérieure. Ils représentent <b><?= round($famSocPro_cadres['mps']) ?> % des députés</b>, alors que seulement <?= round($famSocPro_cadres['population']) ?> % de la population française appartient à cette catégorie.</p>
          <p>Au contraire, l'Assemblée ne compte très peu de députés ouvriers ou employés.</p>
          <p><b>Cela a-t-il un impact ?</b> Les citoyens ne votent pas pour une origine sociale mais pour des idées et un programme politique. Cependant, la sous-représentation de certaines catégories de la population pose question. <span class="url_obf" url_obf="<?= url_obfuscation("https://onlinelibrary.wiley.com/doi/abs/10.1111/ajps.12112") ?>">Plusieurs chercheurs</span> ont montré que l'origine sociale d'un parlementaire a un impact sur ses idées et la façon dont il vote.</p>
        </div>
      </div>
      <div class="row row-grid5">
        <div class="col-12">
          <h2 class="my-5">Le groupe politique le plus représentatif de la population</h2>
          <p>Nous utilisons <span class="url_obf" url_obf="<?= url_obfuscation("https://rdrr.io/rforge/polrep/man/Rose.html") ?>">l'indicateur  de Rose</span> pour mesurer la représentativité sociale des groupes politiques.</p>
          <p>Cet indicateur compare le pourcentage de députés issus des différentes catégories professionnelles avec le pourcentage de la population dans ces mêmes catégories.</p>
          <p>Un groupe politique est parfaitement représentatif si 15% de ces députés étaient des ouvriers et qu'il y a 15% d'ouvriers dans la population, et ainsi de suite. Un groupe parfaitement représentatif aura une valeur de 1, tandis qu'un groupe non représentatif aura une valeur de 0.</p>
          <p>Le groupe politique de l'Assemblée nationale le plus représentatif socialement de la population est <a href="<?= base_url() ?>groupes/<?= $rose_first['libelleAbrev'] ?>"><?= $rose_first['libelle'] ?> (<?= $rose_first['libelleAbrev'] ?>)</a>, avec un score de <?= $rose_first['rose_index'] ?>.</p>
        </div>
        <div class="col-12">
          <?php $this->load->view('groupes/partials/card_home.php', array('groupe' => $rose_first, 'tag' => 'span', 'active' => TRUE, 'stats' => "Score : " . $rose_first['rose_index'] . " ans", 'cat' => true)) ?>
        </div>
        <div class="col-12 mt-5">
          <p>Découvrez ci-dessous le classement des groupes en fonction de leur représentativité sociale.</p>
          <table class="table table-striped table-stats mt-4">
            <thead class="thead-dark">
              <tr>
                <th class="text-center all">Groupe politique</th>
                <th class="text-center min-tablet">Nombre de députés</th>
                <th class="text-center all">Score de représentativité</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($groups_rose as $x): ?>
                <tr>
                  <td class="text-center"><?= $x["libelle"] ?> (<?= $x["libelleAbrev"] ?>)</td>
                  <td class="text-center"><?= $x["effectif"] ?></td>
                  <td class="text-center"><?= $x["rose_index"] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="col-12 mt-5">
          <h2>Part des catégories professionnelles dans chaque groupe</h2>
          <p>Dans quel groupe politique y a-t-il le plus de députés avec un parcours d'agriculteur ? Avec le plus de parlementaires ayant effectué une profession de cadre ? D'ouvriers ? Découvrez-le dans le tableau ci-dessous !</p>
          <table class="table table-striped table-stats mt-4">
            <thead class="thead-dark table-sm">
              <tr>
                <th>Catégorie</th>
                <?php foreach ($groups as $key => $value): ?>
                  <th class="text-center"><small><?= $key ?></small></th>
                  <?php
                    $cols[] = $key;
                  ?>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($groups_rows as $row): ?>
                <tr>
                  <td><?= $row ?></td>
                  <?php foreach ($groups as $key => $value): ?>
                    <?php if (isset($groups[$key][$row])): ?>
                      <td class="text-center"><?= $groups[$key][$row] ?>%</td>
                      <?php else: ?>
                      <td class="text-center">0%</td>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
