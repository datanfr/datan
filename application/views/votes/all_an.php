    <div class="container-fluid bloc-img d-flex bloc-img-deputes async_background" id="container-always-fluid"  data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg">
      <div class="container d-flex flex-column justify-content-center py-2">
        <h1><?= mb_strtoupper($title) ?></h1>
      </div>
    </div>
    <div class="container pg-vote-all my-4">
      <div class="row mt-2 mb-3">
        <div class="col-12">
          <h2><?= $h2 ?></h2>
        </div>
      </div>
      <div class="row mt-2">
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
                <tr data-href="<?= base_url() ?>votes/vote_<?= $vote['voteNumero'] ?>">
                  <td>
                    <?php if ($archive == TRUE): ?>
                      <a href="<?= base_url() ?>votes/vote_<?= $vote['voteNumero'] ?>"><?= $vote['voteNumero'] ?></a>
                    <?php elseif ($i <= 30): ?>
                      <a href="<?= base_url() ?>votes/vote_<?= $vote['voteNumero'] ?>"><?= $vote['voteNumero'] ?></a>
                    <?php else: ?>
                      <?= $vote['voteNumero'] ?>
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
          <h2 class="surtitre">Archives</h2>
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
                      <span><a href="<?= base_url() ?>votes/all/<?= $year?>"><?= $year ?></a></span>
                    </div>
                    <?php else: ?>
                      <div class="d-flex justify-content-center align-items-center">
                        <span><a href="<?= base_url() ?>votes/all/<?= $year?>" class="no-decoration underline-blue"><?= $year ?></a></span>
                      </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="months mt-4 d-flex flex-column align-items-center">
                <?php foreach ($months as $month): ?>
                  <?php if ($month['years'] == $year): ?>
                    <div class="my-2 d-flex justify-content-center align-items-center">
                      <div class="<?= ($month['months'] == $m_index) && ($year == $y_index) ? "current" : "" ?> d-flex justify-content-center align-items-center">
                        <a href="<?= base_url() ?>votes/all/<?= $year?>/<?= $month['index'] ?>" class="no-decoration underline-blue"><?= ucfirst($month["month"]) ?></a>
                      </div>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
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
