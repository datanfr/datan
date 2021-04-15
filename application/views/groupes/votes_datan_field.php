  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  </div>
  <div class="liseret-groupe" style="background-color: <?= $groupe['couleurAssociee'] ?>"></div>
  <div class="container pg-groupe-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4 ">
        <div class="sticky-top" style="margin-top: -110px; top: 110px;">
          <div class="card card-profile">
            <div class="card-body">
              <!-- IMAGE GROUPE -->
              <div class="img">
                <div class="d-flex justify-content-center">
                  <div>
                    <img src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['libelleAbrev'] ?>.png" alt="<?= $groupe['libelle'] ?>">
                  </div>
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
                    <div class="label">Création</div>
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
                      <div class="label">Positionnement</div>
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
      <!-- BIO VOTES -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5 bloc-votes-datan">
        <div class="row mt-4">
          <div class="col-12 btn-back text-center text-md-left">
            <a class="btn btn-outline-primary mx-2" href="<?= base_url() ?>groupes/<?= mb_strtolower($groupe['libelleAbrev']) ?>">
              <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
              Retour profil groupe
            </a>
          </div>
        </div>
        <div class="row mt-5">
          <div class="col-12">
            <h2 class="text-center text-md-left">Les votes du groupe <?= $groupe['libelleAbrev'] ?> concernant <?= $field['libelle'] ?></h2>
          </div>
        </div>
        <div class="row mt-4">
          <?php foreach ($votes as $vote): ?>
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
