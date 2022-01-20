  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
  <div class="container pg-groupe-votes pb-5">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4">
        <?php $this->load->view('groupes/partials/card_individual.php', array('tag' => 'span')) ?>
      </div> <!-- END COL -->
      <!-- BLOC VOTES -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5 bloc-votes-datan">
        <div class="row mt-4">
          <div class="col-12 btn-back text-center text-lg-left">
            <a class="btn btn-outline-primary mx-2" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
              <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
              Retour profil
            </a>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h1>Tous les votes du groupe <?= $title ?> à l'Assemblée</h1>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-10">
            <p>
              Cette page présente les positions du groupe <?= $title ?> (<?= $groupe["libelleAbrev"] ?>) sur tous les votes de l'Assemblée nationale.
            </p>
            <p>
              Les données de ces votes sont automatiquement récupérées sur le site de l'Assemblée, et ne font donc pas l'objet d'une analyse et d'une recontextualisation. La signification de ces votes peut donc être complexe à comprendre au premier abord.
            </p>
            <p>
              C'est pourquoi l'équipe de Datan contextualise et reformule certains scrutins. Pour découvrir les positions du groupe <b><?= $title ?></b> sur ces votes, <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/votes">cliquez ici</a>.
            </p>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h2>Les positions du groupe <?= $groupe['libelleAbrev'] ?> sur <span class="text-primary"><?= count($votes) ?> scrutins</span></h2>
          </div>
        </div>
        <div class="row mt-4">
          <table class="table" id="table-deputes-groupes-votes-all">
            <thead>
              <tr>
                <th class="all">N°</th>
                <th class="min-tablet">Date</th>
                <th class="all">Titre</th>
                <th class="text-center all">Résultat</th>
                <th class="text-center all">
                  Cohésion
                  <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Taux de cohésion" data-content="Le taux de cohésion représente <b>l'unité d'un groupe politique</b> lorsqu'il vote. Il peut prendre des mesures allant de 0 à 1. Un taux proche de 1 signifie que le groupe est très uni.<br><br>Attention, dans beaucoup de parlements, y compris l'Assemblée nationale, les députés suivent dans la plupart des cas la ligne officielle du groupe, expliquant des taux de cohésion très élevés. Le mesure proposée ici est intéressante quand elle est comparée avec les mesures de cohésion des autres groupes.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#cohesion' title='Lien'>cliquez ici.</a>" id="popover_focus"><?= file_get_contents(asset_url()."imgs/icons/question_circle.svg") ?></a>
                </th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; ?>
              <?php foreach ($votes as $vote): ?>
                <tr data-href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>">
                  <td>
                    <?php if ($i <= 30): ?>
                      <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="no-decoration"><?= $vote['voteNumero'] ?></a>
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
                  <td class="sort text-center sort-<?= $vote['vote'] ?>">
                    <?= mb_strtoupper($vote['vote']) ?>
                  </td>
                  <td class="sort text-center">
                    <?= $vote['cohesion'] ?>
                  </td>
                </tr>
                <?php $i++; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
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
