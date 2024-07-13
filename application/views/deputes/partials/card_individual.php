<div class="card card-profile">
  <div class="card-body">
    <!-- IMAGE MP -->
    <div class="d-flex justify-content-center">
      <div class="<?= !$photo_square ? "depute-img-circle" : "depute-img-square" ?>">
        <?php if ($depute['img'] && $this->config->item('mp_photos')) : ?>
          <picture>
            <source srcset="<?= asset_url(); ?>imgs/<?= !$photo_square ? "deputes_nobg_webp/" : "deputes_webp/" ?>depute_<?= $depute['idImage'] ?>_webp.webp" type="image/webp">
            <source srcset="<?= asset_url(); ?>imgs/<?= !$photo_square ? "deputes_nobg/" : "deputes_original/" ?>depute_<?= $depute['idImage'] ?>.png" type="image/png">
            <img src="<?= asset_url(); ?>imgs/<?= !$photo_square ? "deputes_original/" : "deputes_original/" ?>depute_<?= $depute['idImage'] ?>.png" width="150" height="192" alt="<?= $title ?>">
          </picture>
        <?php else : ?>
          <picture>
            <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" type="image/png">
            <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" alt="<?= $title ?>">
          </picture>
        <?php endif; ?>
      </div>
    </div>
    <!-- INFOS GENERALES -->
    <div class="bloc-infos">
      <<?= $tag ?> class="title d-block text-center"><?= $title ?></<?= $tag ?>>
      <?php if (!empty($depute['libelle'])) : ?>
        <div class="link-group text-center mt-1">
          <?php if ($legislature >= 14): ?>
            <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($depute['libelleAbrev']) ?>" style="color: <?= $depute['couleurAssociee'] ?>; --color-group: <?= $depute['couleurAssociee'] ?>">
              <?= name_group($depute['libelle']) ?>
            </a>
            <?php else: ?>
            <span style="color: <?= $depute['couleurAssociee'] ?>">
              <?= name_group($depute['libelle']) ?>
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
          <?php if ($commission_parlementaire): ?>
            <li class="mb-0">
              <div class="label"><?= file_get_contents(base_url() . '/assets/imgs/icons/briefcase-fill.svg') ?></div>
              <div class="value">Commission <?= $commission_parlementaire['commissionAbrege'] ?></div>
            </li>
          <?php endif; ?>
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
