  <div class="container-fluid bloc-img-deputes d-flex async_background" id="container-always-fluid" style="min-height: 13em">
    <div class="container banner-depute-mobile d-flex d-lg-none flex-column justify-content-center py-2">
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <a class="btn btn-primary text-border mb-2" href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>">
            <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
            Retour profil
          </a>
          <span class="title d-block"><?= $title ?></span>
          <p class="subtitle"><?= name_group($depute['libelle']) ?></p>
          <p><?= $depute['departementNom'] ?> (<?= $depute['departementCode'] ?>)</p>
        </div>
      </div>
    </div>
  </div>
  <?php if (!empty($depute['couleurAssociee'])): ?>
    <div class="liseret-groupe" style="background-color: <?= $depute['couleurAssociee'] ?>"></div>
  <?php endif; ?>
  <div class="d-none d-lg-none justify-content-between align-items-center sticky-top p-3" data-toggle="modal" data-target="#filterModal" id="filterBanner" style="top: 50px">
    <span class="text-white font-weight-bold">Filtrer par catégorie</span>
    <?= file_get_contents(asset_url().'imgs/icons/funnel-fill.svg') ?>
  </div>
  <!-- Modal filter only on mobile & tablet -->
  <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content badges-filter">
        <div class="modal-header">
          <span class="title">Filtrer par catégorie</span>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="filters">
            <div class="mt-2">
              <?php foreach ($fields as $field): ?>
                <button type="button" class="badge badge-field popover_focus is-selected" value=".<?= strtolower($field['slug']) ?>"><?= $field['name'] ?></button>
              <?php endforeach; ?>
            </div>
            <div class="mt-2">
              <button type="button" class="btn btn-primary all-categories" value="*">Toutes les catégories</button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>
  <div class="container pg-depute-votes">
    <div class="row">
      <div class="col-lg-4 d-none d-lg-block"> <!-- CARD ONLY > lg -->
        <div style="margin-top: -125px; top: 125px;">
          <?php $this->load->view('deputes/partials/card_individual.php', array('historique' => FALSE, 'last_legislature' => $depute['legislature'], 'legislature' => $depute['legislature'], 'tag' => 'span')) ?>
        </div>
        <div class="sticky-top mt-5" style="margin-top: -125px; top: 125px;">
          <div class="card">
            <div class="card-body badges-filter px-4 py-3">
              <span class="title">Filtrer par catégorie</span>
              <div class="filters">
                <div class="mt-2">
                  <?php foreach ($fields as $field): ?>
                    <button type="button" class="badge badge-field popover_focus is-selected" value=".<?= strtolower($field['slug']) ?>"><?= $field['name'] ?></button>
                  <?php endforeach; ?>
                </div>
                <div class="mt-2">
                  <button type="button" class="btn btn-primary all-categories" value="*">Toutes les catégories</button>
                </div>
              </div>
            </div>
          </div>
          <div class="card mt-3">
            <div class="card-body badges-filter px-4 py-3">
              <input type="text" id="quicksearch" placeholder="Cherchez un vote..." />
            </div>
          </div>
        </div>
      </div> <!-- END COL -->
      <!-- BLOC VOTES -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5 bloc-votes-datan">
        <div class="row mt-4 d-none d-lg-block">
          <div class="col-12 btn-back text-center text-lg-left">
            <a class="btn btn-outline-primary mx-2" href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>">
              <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
              Retour profil
            </a>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h1 class="mb-0">Les votes de <?= $title ?> à l'Assemblée nationale</h1>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <p>
              Les députés jouent un rôle fondamental dans le processus législatif. <?= $title ?>, comme ses collègues, participe à la création, la modification et l'adoption des lois. À travers ses votes, <?= $gender['pronom'] ?> prend position sur des mesures, amendements ou projets de loi.
            </p>
            <p>
              L'équipe de Datan décrypte les votes les plus marquants de l'Assemblée nationale. Nous mettons en lumière les votes ayant suscité une forte attention médiatique ou provoqué des divisions au sein des groupes parlementaires. Chaque vote est expliqué et replacé dans son contexte pour vous permettre de mieux les comprendre.
            </p>
            <p>
              Découvrez sur cette  page les positions de <b><?= $title ?></b> sur ces votes clés.
            </p>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h2>Découvrez les <span class="text-primary"><?= count($votes) ?> votes</span> décryptés de <?= $title ?></h2>
          </div>
        </div>
        <div class="row mt-4 sorting">
          <?php foreach ($votes as $vote): ?>
            <div class="col-md-6 sorting-item <?= $vote['category_slug'] ?>">
              <div class="d-flex justify-content-center my-3">
                <?php $this->load->view('deputes/partials/card_vote.php', array('vote' => $vote)) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>  
  </div> <!-- END CONTAINER -->
  <div class="container-fluid mt-5 py-5 pg-depute-votes-all" id="pattern_background">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h2 class="text-center" style="font-size: 1.6rem">Tous les votes de <?= $title ?></h2>
          <p class="text-center">L'équipe de Datan n'analyse qu'une partie des votes de l'Assemblée nationale. Pour une vision complète, découvrez ici l'ensemble des scrutins auxquels Thierry Benoit a pris part dans l'hémicycle.</p>
        </div>
      </div>
      <div class="row mt-4"> <!-- ROW WITH TABLE WITH ALL VOTES -->
        <div class="col-12">
          <table class="table" id="table-deputes-groupes-votes-all">
            <thead>
              <tr>
                <th class="all">Leg.</th>
                <th class="all">N°</th>
                <th class="min-tablet">Date</th>
                <th class="all">Titre</th>
                <th class="text-center all">Résultat</th>
                <th class="text-center all">Loyauté</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; ?>
              <?php foreach ($votes_all as $vote): ?>
                <tr data-href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>">
                  <td><?= $vote['legislature'] ?></td>
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
                    <?= mb_strtoupper($vote['vote']) ?><?= $vote['vote'] == 'absent' ? mb_strtoupper($gender['e']) : NULL ?>
                  </td>
                  <td class="sort text-center sort-<?= $vote['loyaute'] ?>">
                    <?= mb_strtoupper($vote['loyaute']) ?><?= !empty($vote['loyaute']) ? mb_strtoupper($gender['e']) : NULL ?>
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
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid bloc-others-container">
    <div class="container bloc-others">
      <div class="row">
        <div class="col-12">
          <?php if ($active): ?>
            <h2>Les autres députés <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>)</h2>
            <?php else: ?>
            <h2>Les autres députés plus en activité</h2>
          <?php endif; ?>
          <div class="row mt-3">
            <?php foreach ($other_deputes as $mp): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'].' '.$mp['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <?php if ($active): ?>
              <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($depute['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>)</a>
              <?php else: ?>
              <a href="<?= base_url(); ?>deputes/inactifs">Tous les députés plus en activité</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-12">
          <h2>Les députés en activité du département <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></h2>
          <div class="row mt-3">
            <?php foreach ($other_deputes_dpt as $mp): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"> <?= $mp['nameFirst'].' '.$mp['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>">Voir tous les députés élus dans le département <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] ?></a>
          </div>
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
