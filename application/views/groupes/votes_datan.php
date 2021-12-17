  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  </div>
  <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
  <div class="container pg-groupe-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4">
        <?php $this->load->view('groupes/partials/card_individual.php') ?>
      </div> <!-- END COL -->
      <!-- BLOC VOTES -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5 bloc-votes-datan">
        <div class="row mt-4">
          <div class="col-12 btn-back text-center text-lg-left">
            <a class="btn btn-outline-primary mx-2" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
              <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
              Retour profil
            </a>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h2>Découvrez les votes du groupe <?= $title ?></h2>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-md-7 col-lg-6">
            <p>
              L'équipe de Datan décrypte pour vous les votes les plus intéressants de la législature. Il s'agit des votes qui ont fait l'objet d'attention médiatique, ou sur lesquels un ou plusieurs groupes parlementaires étaient fortement divisés. Ces votes font l'objet d'une reformulation et d'une contextualisation, afin de les rendre plus compréhensibles.
            </p>
            <p>
              Vous trouverez sur cette page les positions de <b><?= $title ?></b> sur ces votes.
            </p>
            <p>
              Pour avoir accès à tous les votes de <?= $title ?> à l'Assemblée nationale, <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/votes/all">cliquez ici</a>.
            </p>
          </div>
          <div class="d-none d-md-block col-md-5 col-lg-6 vote-svg">
            <?= file_get_contents(asset_url()."imgs/svg/undraw_voting_nvu7.svg") ?>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12 badges-categories">
            <?php if($fields_voted) :
            foreach ($fields_voted as $field): ?>
              <a class="badge badge-primary no-decoration" href="#<?= $field['slug'] ?>">
                <?= $field['name'] ?>
              </a>
            <?php endforeach;
            endif ?>
          </div>
        </div>
        <?php if($fields_voted):
          foreach ($fields_voted as $field): ?>
          <div class="row mt-5">
            <div class="col-2 col-md-1 d-flex align-items-end justify-content-center p-0">
              <?php if ($field["logo"]): ?>
                <div class="logo">
                  <?= file_get_contents(asset_url().'imgs/fields/'.$field['slug'].'.svg') ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-10 col-md-11 d-flex align-items-end">
              <h3 class="anchor ml-4 mb-0" id="<?= $field['slug'] ?>"><?= $field['name'] ?></h3>
            </div>
          </div>
          <div class="row mt-4 votes">
            <div class="col-md-11 offset-md-1">
              <div class="row my-3">
                <?php foreach ($by_field[$field["slug"]] as $vote): ?>
                  <div class="col-md-6 d-flex justify-content-center">
                    <?php $this->load->view('groupes/partials/card_vote.php', array('vote' => $vote)) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-md-11 offset-md-1 d-flex justify-content-center">
              <a class="btn see-all-votes py-1" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/votes/<?= $field['slug'] ?>">
                <span>VOIR TOUS</span>
              </a>
            </div>
          </div>
        <?php endforeach;
                endif ?>
      </div>
    </div>
  </div> <!-- END CONTAINER -->
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid pg-groupe-individual bloc-others-container mt-5">
    <div class="container bloc-others">
      <?php if ($groupe['libelleAbrev'] != "NI"): ?>
        <div class="row">
          <div class="col-12">
            <h2>Président du groupe <?= $title ?></h2>
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
          <h2>Tous les députés membres du groupe <?= $title ?></h2>
          <div class="row mt-3">
            <?php foreach ($membres as $key => $membre): ?>
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $membre['dptSlug'] ?>/depute_<?= $membre['nameUrl'] ?>"><?= $membre['nameFirst']." ".$membre['nameLast'] ?></a>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3">
            <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés membres du groupe <?= $groupe['libelleAbrev'] ?></a>
          </div>
        </div>
      </div>
      <?php if (!empty($apparentes)): ?>
        <div class="row">
          <div class="col-12">
            <h2>Tous les députés apparentés du groupe <?= $title ?></h2>
            <div class="row mt-3">
              <?php foreach ($apparentes as $key => $mp): ?>
                <div class="col-6 col-md-3 py-2">
                  <a class="membre no-decoration underline" href="<?= base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"><?= $mp['nameFirst']." ".$mp['nameLast'] ?></a>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-3">
              <a href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">Voir tous les députés apparentés au groupe <?= $groupe['libelleAbrev'] ?></a>
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
                <a class="membre no-decoration underline" href="<?= base_url(); ?>groupes/<?= mb_strtolower($group['libelleAbrev']) ?>"><?= $group['libelle']." (".$group['libelleAbrev'].")" ?></a>
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
