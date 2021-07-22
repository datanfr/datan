<div class="container-fluid bloc-img-deputes bloc-img d-flex async_background" id="container-always-fluid">
  <div class="container d-flex flex-column justify-content-center py-2 pg-parties-individual">
    <div class="row">
      <div class="col-sm-9 col-lg-10 title-text">
        <h1 class="mb-0"><?= $title ?></h1>
        <span class="subtitle">
          <?php if ($party['libelle'] == "Non rattaché(s)" || $party['libelle'] == "Non déclaré(s)"): ?>
            <?= $party['effectif'] ?> député<?= $party['effectif'] > 1 ? 's' : FALSE ?>
          <?php else: ?>
            <?php if ($active): ?>
              Parti politique
              <?php else: ?>
              Ancien parti politique
            <?php endif; ?>
            -
            <?= $party['effectifSentence'] ?>
          <?php endif; ?>
        </span>
      </div>
      <?php if ($party["img"]): ?>
        <div class="col-sm-3 col-lg-2 d-flex justify-content-center">
          <div class="img-party">
            <img src="<?= asset_url() ?>imgs/partis/<?= mb_strtolower($party['libelleAbrev']) ?>.png" width="150" height="150" alt="<?= $party['libelle'] ?>">
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php if (!empty($party['couleurAssociee'])): ?>
  <div class="liseret-groupe" style="background-color: <?= $party['couleurAssociee'] ?>"></div>
<?php endif; ?>
<div class="container pb-4 pg-parties-individual">
  <div class="row <?= $party["img"] != TRUE ? 'mt-5' : 'first-row' ?>">
    <div class="col-lg-10">
      <?php if ($party['libelle'] == "Non rattaché(s)"): ?>
        <p>Découvrez sur cette page les députés qui déclarent ne pas être rattachés financièrement à un parti politique.</p>
        <p>Cela ne veut pas dire que ces députés ne sont pas membres d'un parti politique.</p>
      <?php elseif($party['libelle'] == "Non déclaré(s)"): ?>
        <p>Découvrez sur cette page les députés qui ne déclarent pas de rattachement financier à un parti politique.</p>
        <p>Cela ne veut pas dire que ces députés ne sont pas membres d'un parti politique.</p>
      <?php else: ?>
        <p>
          <b><?= $party['libelle'] ?> est un <?= $active ? null: 'ancien ' ?>parti politique français.</b>
        </p>
        <?php if (empty($party['effectif'])): ?>
          <p>
            Actuellement, aucun député n'est rattaché financièrement au parti <?= $party['libelle'] ?>. Les députés rattachés ne sont pas des députés membres du parti, même s'ils ont tendance à en être proches d'un point de vue idéologique.
          </p>
        <?php else: ?>
      <?php endif; ?>
        <p>
          Actuellement, <?= $party['effectif'] ?> <?= $party['effectif'] > 1 ? 'députés sont rattachés' : 'député est rattaché' ?> financièrement au parti <?= $party['libelle'] ?>. Cela ne veut pas dire que ces députés soient membres du parti, même s'ils ont tendance à en être proches d'un point de vue idéologique.
        </p>
      <?php endif; ?>
      <p>
        Le rattachement permet surtout aux parlementaires d'aider financièrement une formation politique. En effet, le montant des subventions publiques que reçoivent chaque année les partis politiques dépend du nombre de députés et sénateurs rattachés financièrement.
      </p>
      <p>
        Attention, <b>les partis politiques sont différents des groupes parlementaires</b>. Les groupes rassemblent, à l'Assemblée nationale, des députés selon leur affinité politique, et proposent aux parlementaires des ressources importantes, comme du temps de parole. Un groupe parlementaire peut donc réunir des députés venant de partis différents.
      </p>
      <p>
        Pour découvrir les groupes parlementaires à l'Assemblée nationale, <a href="<?= base_url() ?>groupes">cliquez ici</a>.
      </p>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <?php if (empty($deputesActive)): ?>
        <p class="my-3"><b>Aucun député n'est actuellement rattaché au parti politique <u><?= $party['libelle'] ?></u></b></p>
      <?php elseif($party['libelle'] == "Non rattaché(s)"): ?>
        <h2 class="my-5">Découvrez les députés non rattachés à un parti politique</h2>
      <?php elseif($party['libelle'] == "Non déclaré(s)"): ?>
        <h2 class="my-5">Découvrez les députés qui ne déclarent pas de rattachement à un parti</h2>
      <?php else: ?>
        <h2 class="my-5">Découvrez les députés rattachés au parti <?= $party['libelle'] ?></h2>
      <?php endif; ?>
    </div>
  </div>
  <?php if (!empty($deputesActive)): ?>
    <div class="row">
      <div class="col-12 d-flex flex-wrap justify-content-around">
        <?php foreach ($deputesActive as $mp): ?>
          <?php $this->load->view('deputes/partials/card_home.php', array('depute' => $mp, 'tag' => 'h2', 'cat' => false)) ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div> <!-- END CONTAINER -->
<!-- AUTRES DEPUTES -->
<div class="container-fluid pg-party-individual bloc-others-container">
  <div class="container bloc-others">
    <div class="row">
      <div class="col-12">
        <h2>Partis politiques avec au moins un député rattaché financièrement</h2>
        <div class="row mt-3">
          <?php foreach ($partiesActive as $party): ?>
            <div class="col-6 col-md-4 py-2">
              <a class="membre no-decoration underline" href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($party['libelleAbrev']) ?>"><?= $party['libelle'] ?> (<?= $party['libelleAbrev'] ?>)</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h2>Partis politiques sans député rattaché financièrement</h2>
        <div class="row mt-3">
          <?php foreach ($partiesOther as $party): ?>
            <?php if ($party['dateFin'] == NULL): ?>
              <div class="col-6 col-md-4 py-2">
                <a class="membre no-decoration underline" href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($party['libelleAbrev']) ?>"><?= $party['libelle'] ?> (<?= $party['libelleAbrev'] ?>)</a>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h2>Anciens partis politiques</h2>
        <div class="row mt-3">
          <?php foreach ($partiesOther as $party): ?>
            <?php if ($party['dateFin'] != NULL): ?>
              <div class="col-6 col-md-4 py-2">
                <a class="membre no-decoration underline" href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($party['libelleAbrev']) ?>"><?= $party['libelle'] ?> (<?= $party['libelleAbrev'] ?>)</a>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
