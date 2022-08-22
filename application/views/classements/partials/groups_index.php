<div class="ranking-graph-group mt-4 py-4 row row-grid">
  <div class="col-sm-6 d-flex flex-column align-items-center">
    <div class="title text-center mb-4">
      <?= $first["title"] ?>
    </div>
    <a href="<?= base_url() ?>groupes/legislature-<?= $first["group"]["legislature"] ?>/<?= mb_strtolower($first["group"]["libelleAbrev"]) ?>">
      <div class="score mb-4">
        <div class="avatar">
          <picture>
            <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $first["group"]["legislature"] ?>/webp/<?= $first["group"]["libelleAbrev"] ?>.webp" type="image/webp">
            <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $first["group"]["legislature"] ?>/<?= $first["group"]["libelleAbrev"] ?>.png" type="image/png">
            <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $first["group"]["legislature"] ?>/<?= $first["group"]["libelleAbrev"] ?>.png" width="150" height="150" alt="<?= name_group($first["group"]["libelle"]) ?>">
          </picture>
        </div>
        <div class="age">
          <span class="badge badge-primary shadow"><?= $first["stat"] ?></span>
        </div>
      </div>
    </a>
    <div class="description text-center">
      <a href="<?= base_url() ?>groupes/legislature-<?= $first["group"]["legislature"] ?>/<?= mb_strtolower($first["group"]["libelleAbrev"]) ?>" class="no-decoration underline"><?= name_group($first["group"]["libelle"]) ?></a>
    </div>
  </div>
  <div class="col-sm-6 d-flex flex-column align-items-center">
    <div class="title text-center mb-4">
      <?= $second["title"] ?>
    </div>
    <a href="<?= base_url() ?>groupes/legislature-<?= $second["group"]["legislature"] ?>/<?= mb_strtolower($second["group"]["libelleAbrev"]) ?>">
      <div class="score mb-4">
        <div class="avatar">
          <picture>
            <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $second["group"]["legislature"] ?>/webp/<?= $second["group"]['libelleAbrev'] ?>.webp" type="image/webp">
            <source srcset="<?= asset_url(); ?>imgs/groupes/<?= $second["group"]["legislature"] ?>/<?= $second["group"]['libelleAbrev'] ?>.png" type="image/png">
            <img class="img" src="<?= asset_url(); ?>imgs/groupes/<?= $second["group"]["legislature"] ?>/<?= $second["group"]['libelleAbrev'] ?>.png" width="150" height="150" alt="<?= name_group($second['group']['libelle']) ?>">
          </picture>
        </div>
        <div class="age">
          <span class="badge badge-primary shadow"><?= $second["stat"] ?></span>
        </div>
      </div>
    </a>
    <div class="description text-center">
      <a href="<?= base_url() ?>groupes/legislature-<?= $second["group"]["legislature"] ?>/<?= mb_strtolower($second["group"]["libelleAbrev"]) ?>" class="no-decoration underline"><?= name_group($second['group']['libelle']) ?></a>
    </div>
  </div>
</div>
