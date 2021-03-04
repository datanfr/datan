  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" data-src="<?= asset_url() ?>imgs/cover/hemicycle-front.jpg" data-tablet="<?= asset_url() ?>imgs/cover/hemicycle-front-768.jpg" data-mobile="<?= asset_url() ?>imgs/cover/hemicycle-front-375.jpg" style="height: 13em"></div>
  <?php if (!empty($depute['couleurAssociee'])): ?>
    <div class="liseret-groupe" style="background-color: <?= $depute['couleurAssociee'] ?>"></div>
  <?php endif; ?>
  <div class="container pg-depute-individual pb-5">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0 px-lg-4 ">
        <div class="sticky-top" style="margin-top: -110px; top: 110px;">
          <div class="card card-profile">
            <div class="card-body">
              <!-- IMAGE MP -->
              <div class="img">
                <div class="d-flex justify-content-center">
                  <div class="depute-img-circle">
                    <?php if ($depute['img'] == 1): ?>
                      <picture>
                        <source srcset="<?= asset_url(); ?>imgs/deputes_webp/depute_<?= $depute['idImage'] ?>_webp.webp" alt="<?= $title ?>" type="image/webp">
                        <source srcset="<?= asset_url(); ?>imgs/deputes/depute_<?= $depute['idImage'] ?>.png" type="image/png">
                        <img src="<?= asset_url(); ?>imgs/deputes/depute_<?= $depute['idImage'] ?>.png" alt="<?= $title ?>">
                      </picture>
                      <?php else: ?>
                        <picture>
                          <source srcset="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" type="image/png">
                          <img src="<?= asset_url() ?>imgs/placeholder/placeholder-face.png" alt="<?= $title ?>">
                        </picture>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <!-- INFOS GENERALES -->
              <div class="bloc-infos">
                <h1 class="text-center text-lg-left"><?= $title ?></h1>
                <div class="link-group text-center text-lg-left mt-1">
                  <a href="<?= base_url() ?>groupes/<?= mb_strtolower($depute['libelleAbrev']) ?>" style="color: <?= $depute['couleurAssociee'] ?>; --color-group: <?= $depute['couleurAssociee'] ?>">
                    <?= $depute['libelle'] ?>
                  </a>
                </div>
              </div>
              <!-- BIOGRAPHIE -->
              <div class="bloc-bref mt-3 d-flex justify-content-center justify-content-lg-start">
                <ul>
                  <li class="first">
                    <div class="label"><?php echo file_get_contents(asset_url().'imgs/icons/geo-alt-fill.svg') ?></div>
                    <div class="value"><?= $depute['departementNom'].' ('.$depute['departementCode'].')'?></div>
                  </li>
                  <li>
                    <div class="label"><?php echo file_get_contents(asset_url().'imgs/icons/person-fill.svg') ?></div>
                    <div class="value"><?= $depute['age'] ?> ans</div>
                  </li>
                  <li class="mb-0">
                    <div class="label"><?php echo file_get_contents(asset_url().'imgs/icons/briefcase-fill.svg') ?></div>
                    <div class="value">Commission <?= $commission_parlementaire['commissionAbrege'] ?></div>
                  </ul>
              </div>
            </div>
            <?php if ($active == TRUE): ?>
              <div class="mandats d-flex justify-content-center align-items-center active">
                <span class="active"><?= mb_strtoupper($mandat_edito) ?> MANDAT</span>
              </div>
              <?php else: ?>
                <div class="mandats d-flex justify-content-center align-items-center inactive">
                  <span class="inactive">PLUS EN ACTIVITÉ</span>
                </div>
            <?php endif; ?>
          </div> <!-- END CARD PROFILE -->
        </div> <!-- END STICKY TOP -->
      </div> <!-- END COL -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5 bloc-votes-datan">
        <div class="row mt-4">
          <div class="col-12 btn-back text-center text-lg-left">
            <a class="btn btn-outline-primary mx-2" href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>">
              <?= file_get_contents(asset_url().'imgs/icons/arrow_left.svg') ?>
              Retour profil
            </a>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <h2>Tous les votes <?= $gender["du"] ?> député<?= $gender["e"] ?> <?= $title ?></h2>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-10">
            <p>
              Cette page présente les positions <?= $gender["du"] ?> député<?= $gender["e"] ?> <?= $title ?> sur tous les votes de l'Assemblée nationale.
            </p>
            <p>
              Les données de ces votes sont automatiquement récupérées sur le site de l'Assemblée, et ne font donc pas l'objet d'une analyse et d'une recontextualisation. La signification de ces votes peut donc être complexe à comprendre au premier abord.
            </p>
            <p>
              C'est pourquoi l'équipe de Datan contextualise et reformule certains scrutins. Pour découvrir les positions de <b><?= $title ?></b> sur ces votes, <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>/depute_<?= $depute['nameUrl'] ?>/votes">cliquez ici</a>.
            </p>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-12">
            <table class="table" id="table-depute-votes">
              <thead>
                <tr>
                  <th class="all">N°</th>
                  <th class="min-tablet">Date</th>
                  <th class="all">Titre</th>
                  <th class="text-center all">Résultat</th>
                  <th class="text-center all">Loyauté</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; ?>
                <?php foreach ($votes as $vote): ?>
                  <tr data-href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>">
                    <td>
                      <?php if ($i <= 30): ?>
                        <a href="<?= base_url() ?>votes/legislature-<?= $vote['legislature'] ?>/vote_<?= $vote['voteNumero'] ?>" class="no-decoration"><?= $vote['voteNumero'] ?></a>
                        <?php else: ?>
                        <?= $vote['voteNumero'] ?>
                      <?php endif; ?>
                    </td>
                    <td><?= date("d-m-Y", strtotime($vote['dateScrutin'])) ?></td>
                    <td>
                      <?php if (!empty($vote['title'])): ?>
                        <span class="vote-datan-bold"><?= mb_strtoupper($vote['title']) ?></span><br>
                        <span class="vote-datan-italic"><?= ucfirst($vote['titre']) ?></span>
                      <?php else: ?>
                      <?= ucfirst($vote['titre']) ?>
                      <?php endif; ?>
                    </td>
                    <td class="sort text-center sort-<?= $vote['vote'] ?>">
                      <?= mb_strtoupper($vote['vote']) ?><?= $vote['vote'] == 'absent' ? mb_strtoupper($gender['e']) : NULL ?>
                    </td>
                    <td class="sort text-center sort-<?= $vote['loyaute'] ?>">
                      <?= mb_strtoupper($vote['loyaute']) ?><?= !empty($vote['loyaute']) ? mb_strtoupper($gender['e']) : NULL ?>
                    </td>
                  </tr>
                  <?php $i++; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", () => {
      const rows = document.querySelectorAll("tr[data-href]");

      rows.forEach((row) => {
        row.addEventListener("click", () => {
          window.location.href = row.dataset.href;
        })
      });

    })
  </script>
