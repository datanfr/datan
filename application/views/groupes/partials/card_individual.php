<div class="sticky-top" style="margin-top: -110px; top: 110px;">
  <div class="card card-profile">
    <div class="card-body">
      <!-- IMAGE GROUPE -->
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
              <div class="label">Position</div>
              <div class="value"><?= ucfirst($infos_groupes[$groupe['libelleAbrev']]['libelle']) ?></div>
            </li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="text-center mt-4">
        <a class="btn btn-outline-primary" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">
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
