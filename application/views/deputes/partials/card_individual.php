<div class="card card-profile">
  <div class="card-body">
    <!-- IMAGE MP -->
    <div class="img">
      <div class="d-flex justify-content-center">
        <div class="depute-img-circle">
          <?php if ($depute['img']) : ?>
            <picture>
              <source srcset="<?= asset_url(); ?>imgs/deputes_nobg_webp/depute_<?= $depute['idImage'] ?>_webp.webp" type="image/webp">
              <source srcset="<?= asset_url(); ?>imgs/deputes_nobg/depute_<?= $depute['idImage'] ?>.png" type="image/png">
              <img src="<?= asset_url(); ?>imgs/deputes_original/depute_<?= $depute['idImage'] ?>.png" width="150" height="192" alt="<?= $title ?>">
            </picture>
          <?php else : ?>
            <picture>
              <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" type="image/png">
              <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face-2.png" alt="<?= $title ?>">
            </picture>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- INFOS GENERALES -->
    <div class="bloc-infos">
      <h1 class="text-center"><?= $title ?></h1>
      <?php if (!empty($depute['libelle'])) : ?>
        <div class="link-group text-center mt-1">
          <?php if ($legislature >= 15): ?>
            <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($depute['libelleAbrev']) ?>" style="color: <?= $depute['couleurAssociee'] ?>; --color-group: <?= $depute['couleurAssociee'] ?>">
              <?= $depute['libelle'] ?>
            </a>
            <?php else: ?>
            <span style="color: <?= $depute['couleurAssociee'] ?>">
              <?= $depute['libelle'] ?>
            </span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <!-- BIOGRAPHIE -->
    <div class="bloc-bref mt-3 d-flex justify-content-center justify-content-lg-start">
      <ul>
        <li class="first">
          <div class="label"><?= file_get_contents(base_url() . '/assets/imgs/icons/geo-alt-fill.svg') ?></div>
          <div class="value"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></div>
        </li>
        <?php if ($active && !$historique) : ?>
          <li>
            <div class="label"><?= file_get_contents(base_url() . '/assets/imgs/icons/person-fill.svg') ?></div>
            <div class="value"><?= $depute['age'] ?> ans</div>
          </li>
          <li class="mb-0">
            <div class="label"><?= file_get_contents(base_url() . '/assets/imgs/icons/briefcase-fill.svg') ?></div>
            <div class="value">Commission <?= $commission_parlementaire['commissionAbrege'] ?></div>
          </li>
        <?php endif; ?>
        <?php if (!$active || $historique): ?>
          <li class="mb-0">
            <div class="label"><?= file_get_contents(base_url() . '/assets/imgs/icons/calendar-date-fill.svg') ?></div>
            <div class="value">Dernier mandat : <?= $last_legislature ?><sup>e</sup> législature</div>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <?php if ($active) : ?>
    <div class="mandats d-flex justify-content-center align-items-center active">
      <span class="active">EN ACTIVITÉ</span>
    </div>
  <?php else : ?>
    <div class="mandats d-flex justify-content-center align-items-center inactive">
      <span class="inactive">PLUS EN ACTIVITÉ</span>
    </div>
  <?php endif; ?>
</div> <!-- END CARD PROFILE -->
