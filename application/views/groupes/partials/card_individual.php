<div class="sticky-top" style="margin-top: -150px; top: 80px;">
  <div class="card card-profile groupe">
    <div class="card-body">
      <!-- IMAGE GROUPE -->
      <div class="img">
        <div class="d-flex justify-content-center">
          <picture>
            <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/webp/<?= $groupe['libelleAbrev'] ?>.webp" type="image/webp">
            <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/<?= $groupe['libelleAbrev'] ?>.png" type="image/png">
            <img src="<?= asset_url(); ?>imgs/groupes/<?= $groupe['legislature'] ?>/<?= $groupe['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= name_group($groupe['libelle']) ?>">
          </picture>
        </div>
      </div>
      <!-- INFOS GENERALES -->
      <div class="bloc-infos mt-2">
        <<?= $tag ?> class="title d-block text-lg-left"><?= name_group($title) ?></<?= $tag ?>>
      </div>
      <!-- BIOGRAPHIE -->
      <div class="bloc-bref mt-3 d-flex justify-content-center justify-content-lg-start">
        <ul>
          <li class="first">
            <div class="label">Création</div>
            <div class="value"><?= $groupe['dateDebutFR'] ?></div>
          </li>
          <?php if ($groupe['dateFin']): ?>
            <li>
              <div class="label">Dissolution</div>
              <div class="value"><?= $groupe['dateFinFR'] ?></div>
            </li>
          <?php endif; ?>
          <?php if ($groupe['libelleAbrev'] != "NI"): ?>
            <li>
              <div class="label">Président<?= $president['civ'] == 'Mme' ? 'e' : '' ?></div>
              <div class="value">
                <a class="no-decoration underline" href="<?= base_url() ?>deputes/<?= $president['dptSlug'] ?>/depute_<?= $president['nameUrl'] ?>"><?= $president['nameFirst']." ".$president['nameLast'] ?></a>
              </div>
            </li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="text-center mt-2">
        <a class="btn btn-outline-primary" href="<?= base_url() ?>groupes/legislature-<?= $groupe['legislature'] ?>/<?= mb_strtolower($groupe['libelleAbrev']) ?>/membres">
          Voir les <?= $groupe['effectif'] ?> membres
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
