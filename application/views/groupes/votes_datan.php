  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  </div>
  <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
  <div class="container pg-groupe-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4">
        <div class="sticky-top" style="margin-top: -110px; top: 110px;">
          <div class="card card-profile">
            <div class="card-body">
              <!-- IMAGE MP -->
              <div class="img">
                <div class="d-flex justify-content-center">
                  <picture>
                    <source srcset="<?= asset_url(); ?>imgs/groupes/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
                    <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
                    <img src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= $groupe['libelle'] ?>">
                  </picture>
                </div>
              </div>
              <!-- INFOS GENERALES -->
              <div class="bloc-infos">
                <h1 class="text-center text-lg-left"><?= $title ?></h1>
              </div>
              <!-- BIOGRAPHIE -->
              <div class="bloc-bref mt-3 d-flex justify-content-center justify-content-lg-start">
                <ul>
                  <li class="first">
                    <div class="label">Créationd</div>
                    <div class="value"><?= $dateDebut ?></div>
                  </li>
                  <li>
                    <div class="label">Effectif</div>
                    <div class="value"><?= $groupe['effectif'] ?> membres</div>
                  </li>
                  <?php if ($groupe['libelleAbrev'] != "NI"): ?>
                    <li>
                      <div class="label">Président</div>
                      <div class="value"><?= $president['nameFirst']." ".$president['nameLast'] ?></div>
                    </li>
                    <li>
                      <div class="label">Position</div>
                      <div class="value"><?= ucfirst($edito['ideology']) ?></div>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
              <div class="text-center mt-4">
                <a class="btn btn-outline-primary" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">
                  Voir tous les membres
                </a>
              </div>
            </div>
            <?php if ($active): ?>
              <div class="mandats d-flex justify-content-center align-items-center active">
                <span class="active">EN ACTIVITÉ</span>
              </div>
            <?php else: ?>
              <div class="mandats d-flex justify-content-center align-items-center inactive">
                <span class="inactive">PLUS EN ACTIVITÉ</span>
              </div>
            <?php endif; ?>
          </div> <!-- END CARD PROFILE -->
        </div> <!-- END STICKY TOP -->
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
                  <?php echo file_get_contents(asset_url().'imgs/fields/'.$field['slug'].'.svg') ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-10 col-md-11 d-flex align-items-end">
              <h3 class="anchor ml-4 mb-0" id="<?= $field['slug'] ?>"><?= $field['name'] ?></h3>
            </div>
          </div>
          <div class="row mt-4 votes">
            <div class="col-md-11 offset-md-1">
              <div class="row">
                <?php foreach ($by_field[$field["slug"]] as $vote): ?>
                  <div class="col-md-6 d-flex justify-content-center">
                    <div class="card card-vote my-3">
                      <?php if ($vote['vote'] == 'absent'): ?>
                        <div class="thumb absent d-flex align-items-center">
                          <div class="d-flex align-items-center">
                            <span>ABSENT</span>
                          </div>
                        </div>
                        <?php else: ?>
                          <div class="thumb d-flex align-items-center <?= $vote['vote'] ?>">
                            <div class="d-flex align-items-center">
                              <span><?= mb_strtoupper($vote['vote']) ?></span>
                            </div>
                          </div>
                      <?php endif; ?>
                      <div class="card-header d-flex flex-row justify-content-between">
                        <span class="date"><?= $vote['dateScrutinFRAbbrev'] ?></span>
                      </div>
                      <div class="card-body d-flex align-items-center">
                        <span class="title">
                          <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="stretched-link no-decoration"></a>
                          <?= $vote['vote_titre'] ?>
                        </span>
                      </div>
                      <div class="card-footer">
                        <span class="field badge badge-primary py-1 px-2"><?= $vote['category_libelle'] ?></span>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-md-11 offset-md-1 d-flex justify-content-center">
              <div class="btn-all">
                <a class="btn py-1" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>/votes/<?= $field['slug'] ?>">
                  <span>VOIR TOUS</span>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach;
                endif ?>
      </div>
    </div>
  </div> <!-- END CONTAINER -->
  <!-- AUTRES DEPUTES -->
  <div class="container-fluid pg-groupe-individual bloc-others-container">
    <div class="container bloc-others">
      <?php if ($groupe['libelleAbrev'] != "NI"): ?>
        <div class="row">
          <div class="col-12">
            <h2>Président du groupe <?= $title ?></h2>
            <div class="row mt-3">
              <div class="col-6 col-md-3 py-2">
                <a class="membre no-decoration underline" href="<?php echo base_url(); ?>deputes/<?= $president['dptSlug'] ?>/depute_<?= $president['nameUrl'] ?>"><?= $president['nameFirst']." ".$president['nameLast'] ?></a>
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
                <a class="membre no-decoration underline" href="<?php echo base_url(); ?>deputes/<?= $membre['dptSlug'] ?>/depute_<?= $membre['nameUrl'] ?>"><?= $membre['nameFirst']." ".$membre['nameLast'] ?></a>
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
                  <a class="membre no-decoration underline" href="<?php echo base_url(); ?>deputes/<?= $mp['dptSlug'] ?>/depute_<?= $mp['nameUrl'] ?>"><?= $mp['nameFirst']." ".$mp['nameLast'] ?></a>
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
                <a class="membre no-decoration underline" href="<?php echo base_url(); ?>groupes/<?= mb_strtolower($group['libelleAbrev']) ?>"><?= $group['libelle']." (".$group['libelleAbrev'].")" ?></a>
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
