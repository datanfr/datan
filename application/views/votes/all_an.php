    <div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid" style="height: 14em">
      <div class="container d-flex flex-column justify-content-center py-2">
        <h1 class="text-center"><?= $title ?></h1>
      </div>
    </div>
    <div class="container pg-vote-all my-5">
      <div class="row">
        <div class="col-12">
          <h2>Liste des scrutins</h2>
          <?php if(empty($votes)): ?>
            <p class="text-center mt-3 mb-0">Il n'y a pas encore eu de scrutins à l'Assemblée nationale pour la <?= $legislature ?><sup>ème</sup> législature.</p>
          <?php else: ?>
            <p class="text-center mt-3 mb-0"><?= $description ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-12">
          <table class="table" id="table-vote-all">
          	<thead>
              <tr>
                <th>N°</th>
                <th>Date</th>
                <th>Titre</th>
                <th class="text-center">Résultat</th>
              </tr>
          	</thead>
            <tbody>
              <?php $i = 1; ?>
              <?php foreach ($votes as $vote): ?>
                <tr data-href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/vote_<?= $vote['voteNumero'] ?>">
                  <td>
                    <?php if ($obfuscation_links && $i > 30): ?>
                      <?= str_replace("c", "", $vote['voteNumero']) ?>
                    <?php else: ?>
                      <a class="no-decoration" href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/vote_<?= $vote['voteNumero'] ?>"><?= str_replace("c", "", $vote['voteNumero']) ?></a>
                    <?php endif; ?>
                  </td>
                  <td><?= date("d-m-Y", strtotime($vote['dateScrutin'])) ?></td>
                  <td>
                    <?php if (!empty($vote['title'])): ?>
                      <span class="vote-datan-bold"><?= mb_strtoupper($vote['title']) ?></span><br>
                      <span class="vote-datan-italic"><?= ucfirst($vote['titre']) ?></span>
                    <?php else: ?>
                      <?= ucfirst($vote['titre']) ?>
                    <?php endif; ?>
                  </td>
                  <td class="text-center sort sort-<?= $vote['sortCode'] ?>"><?= mb_strtoupper($vote['sortCode']) ?></td>
                </tr>
                <?php $i++; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <hr class="mt-5">
      <div class="row my-3 py-3">
        <div class="col-12">
          <h2 class="surtitre">Archives de la <?= $legislature ?><sup>e</sup> législature</h2>
        </div>
      </div>
      <div class="row">
        <div class="col-12 d-flex flex-row flex-wrap">
          <?php foreach ($years as $year): ?>
            <div class="flex-fill text-center px-1 py-2">
              <div class="year d-flex flex-column align-items-center">
                <div class="my-2 d-flex justify-content-center align-items-center">
                  <?php if ($year == $y_index && $m_index == NULL): ?>
                    <div class="current d-flex justify-content-center align-items-center">
                      <span><a href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/<?= $year?>"><?= $year ?></a></span>
                    </div>
                    <?php else: ?>
                      <div class="d-flex justify-content-center align-items-center">
                        <span><a href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/<?= $year?>" class="no-decoration underline-blue"><?= $year ?></a></span>
                      </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="months mt-4 d-flex flex-column align-items-center">
                <?php foreach ($months as $month): ?>
                  <?php if ($month['years'] == $year): ?>
                    <div class="my-2 d-flex justify-content-center align-items-center">
                      <div class="<?= ($month['months'] == $m_index) && ($year == $y_index) ? "current" : "" ?> d-flex justify-content-center align-items-center">
                        <a href="<?= base_url() ?>votes/legislature-<?= $legislature ?>/<?= $year?>/<?= $month['index'] ?>" class="no-decoration underline-blue"><?= ucfirst($month["month"]) ?></a>
                      </div>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <hr class="mt-5">
      <div class="row my-3 py-3">
        <div class="col-12">
          <h2 class="surtitre">Archives d'autres législatures</h2>
        </div>
        <div class="col-12 mt-3">
          <?php for ($i = 14; $i <= legislature_current(); $i++): ?>
            <div class="d-flex flex-column align-items-center year text-center">
              <div class="my-1 d-flex justify-content-center align-items-center">
                <span><a href="<?= base_url() ?>votes/legislature-<?= $i ?>" class="no-decoration underline-blue"><?= $i ?><sup>e</sup> législature</a></span>                    
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      document.addEventListener("DOMContentLoaded", () => {
        const rows = document.querySelectorAll("tr[data-href]");

        rows.forEach((row) => {
          row.addEventListener("click", () => {
            window.location.href = row.dataset.href;
          })
        });

      })
    </script>
