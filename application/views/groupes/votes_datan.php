  <div class="container-fluid bloc-img-deputes async_background d-flex" id="container-always-fluid" style="height: 13em">
    <div class="container banner-groupe-mobile d-flex d-lg-none flex-column justify-content-center py-2">
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <a class="btn btn-primary text-border mb-2" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
            <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
            Retour profil
          </a>
          <span class="title d-block"><?= name_group($title) ?> (<?= $groupe['libelleAbrev'] ?>)</span>
        </div>
      </div>
    </div>
  </div>
  </div>
  <?php if (!empty($groupe['couleurAssociee'])): ?>
    <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
  <?php endif; ?>
  <div class="d-none d-lg-none justify-content-between align-items-center sticky-top p-3" data-toggle="modal" data-target="#filterModal" id="filterBanner" style="top: 100px">
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
  <div class="container pg-groupe-votes">
    <div class="row">
      <div class="col-lg-4 d-none d-lg-block"> <!-- CARD ONLY > lg -->
        <div style="margin-top: -125px; top: 125px;">
          <?php $this->load->view('groupes/partials/card_individual.php', array('tag' => 'span')) ?>
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
            <a class="btn btn-outline-primary mx-2" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
              <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
              Retour profil
            </a>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h1 class="mb-0">Les votes du groupe <?= name_group($title) ?></h1>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <p>
              L'équipe de Datan décrypte pour vous les votes les plus intéressants de la législature. Il s'agit des votes qui ont fait l'objet d'attention médiatique, ou sur lesquels un ou plusieurs groupes parlementaires étaient fortement divisés. Ces votes font l'objet d'une reformulation et d'une contextualisation, afin de les rendre plus compréhensibles.
            </p>
            <p>
              Vous trouverez sur cette page les positions de <b><?= name_group($title) ?></b> sur ces votes.
            </p>
            <p>
              Pour avoir accès à tous les votes de <?= name_group($title) ?> à l'Assemblée nationale, <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/votes/all">cliquez ici</a>.
            </p>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h2>Découvrez les <span class="text-primary"><?= count($votes) ?> votes</span> décryptés du groupe <?= $groupe['libelleAbrev'] ?></h2>
          </div>
        </div>
        <div class="row mt-4 sorting">
          <?php foreach ($votes as $vote): ?>
            <div class="col-md-6 sorting-item <?= $vote['category_slug'] ?>">
              <div class="d-flex justify-content-center my-3">
                <?php $this->load->view('groupes/partials/card_vote.php', array('vote' => $vote)) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div> <!-- END CONTAINER -->
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid bloc-others-container mt-5">
    <div class="container bloc-others">
      <?php if ($groupe['libelleAbrev'] != "NI"): ?>
        <div class="row">
          <div class="col-12">
            <h2>Président du groupe <?= name_group($title) ?></h2>
            <div class="row mt-3">
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $president['dptSlug'] ?>/depute_<?= $president['nameUrl'] ?>"><?= $president['nameFirst']." ".$president['nameLast'] ?></a>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="row">
        <div class="col-12">
          <h2>Tous les députés membres du groupe <?= name_group($title) ?></h2>
          <div class="row mt-3">
            <?php foreach ($membres as $key => $membre): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $membre['dptSlug'] ?>/depute_<?= $membre['nameUrl'] ?>"><?= $membre['nameFirst']." ".$membre['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= $groupe['libelleAbrev'] ?></a>
          </div>
        </div>
      </div>
      <?php if (!empty($apparentes)): ?>
        <div class="row">
          <div class="col-12">
            <h2>Tous les députés apparentés du groupe <?= name_group($title) ?></h2>
            <div class="row mt-3">
              <?php foreach ($apparentes as $key => $mp): ?>
                <div class="col-6 col-md-3 py-2">
                  <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"><?= $mp['nameFirst']." ".$mp['nameLast'] ?></a>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-3">
              <a href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés apparentés au groupe <?= $groupe['libelleAbrev'] ?></a>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="row">
        <div class="col-12">
          <h2>Tous les groupes parlementaires en activité de la 15e législature</h2>
          <div class="row mt-3">
            <?php foreach ($groupesActifs as $group): ?>
              <div class="col-6 col-md-4 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($group['libelleAbrev']) ?>"><?= $group['libelle']." (".$group['libelleAbrev'].")" ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>groupes">Voir tous les groupes parlementaires de la 15e législature</a>
          </div>
        </div>
      </div>
    </div>
  </div>
