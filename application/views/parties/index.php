    <div class="container-fluid pg-parties-all mb-5" id="container-always-fluid">
      <div class="row">
        <div class="container">
          <div class="row row-grid bloc-titre">
            <div class="col-12">
              <h1><?= $title ?></h1>
            </div>
            <div class="col-lg-8 mt-5">
              <p>
                Les <b>partis politiques</b> sont des organisations rassemblant des personnes partageant des opinions similaires. Dans les sociétés démocratiques, leur objectif premier est d'accéder à des positions de pouvoir afin de mettre en oeuvre un programme politique.
              </p>
              <p>
                <b>La plupart des députés appartiennent à un parti politique</b>. Même si certains élus se font élire en tant qu'indépendant, la majorité d'entre eux ont eu le soutien d'un parti, et ont donc eu accès à certaines ressources, notamment militantes et financières.
              </p>
              <p>
                Attention, <b>les partis politiques sont différents des groupes parlementaires</b>. Les groupes rassemblent, à l'Assemblée nationale, des députés selon leur affinité politique. Un groupe parlementaire peut ainsi réunir des députés venant de partis différents.
              </p>
              <h2 class="mt-5 mb-3">Les députés rattachés financièrement à des partis</h2>
              <p>
                Les députés peuvent décider de se rattacher financièrement à un parti politique. <b>Cela ne veut pas dire que le député est membre du parti</b>, mais les députés rattachés à un parti sont pris en compte dans le calcul des aides publiques reversées chaque année aux partis politiques.
              </p>
              <p>
                Certains députés décident de ne pas se rattacher financièrement à un parti politique, ou ne le déclarent pas :
              </p>
              <ul>
                <li>
                  <a href="<?= base_url() ?>partis-politiques/nr" class="no-decoration underline">Découvrir les députés non rattachés</a>
                </li>
                <li>
                  <a href="<?= base_url() ?>partis-politiques/nd" class="no-decoration underline">Découvrez les députés qui ne déclarent pas de rattachement</a>
                </li>
              </ul>
              <p>
                Découvrez ci-dessous la liste de partis politiques ayant au moins un député rattaché financièrement.
              </p>
            </div>
          </div>
          <div class="row mt-4">
            <?php foreach ($partiesActive as $party): ?>
              <div class="col-lg-4 col-md-6 py-3">
                <div class="card card-groupe">
                  <div class="liseret" style="background-color: <?= $party['couleurAssociee'] ?>"></div>
                  <div class="card-avatar group">
                    <img src="<?= asset_url() ?>imgs/parties/<?= mb_strtolower($party['libelleAbrev']) ?>.png" alt="<?= $party['libelle'] ?>">
                  </div>
                  <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h2 class="d-block card-title">
                      <a href="<?= base_url(); ?>partis-politiques/<?= mb_strtolower($party['libelleAbrev']) ?>" class="stretched-link no-decoration"><?php echo $party['libelle'] ?></a>
                    </h2>
                    <span class="d-block"><?= $party["libelleAbrev"] ?></span>
                  </div>
                  <div class="card-footer d-flex justify-content-center align-items-center">
                    <span><?= $party["effectifSentence"] ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div> <!-- END CONTAINER -->
    <!-- AUTRES PARTIES -->
    <div class="container-fluid bloc-others-container">
      <div class="container bloc-others">
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
