<div class="bloc-bio mt-5">
  <!-- For critical css -->
  <div class="card card-election-feature not-candidate d-none"></div>
  <div class="card card-election-feature candidate d-none"></div>
  <h2 class="mb-4 title-center">Qui est-<?= ($gender['pronom']) ?> ?</h2>
  <!-- Paragraphe introductif -->
  <?php if ($active) : ?>
    <p>
      <b><?= $title ?></b>, né<?= $gender['e'] ?> le <?= $depute['dateNaissanceFr'] ?> à <?= $depute['birthCity'] ?>,
      est <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?>
      </sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>">
        <?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
    </p>
  <?php else : ?>
    <p>
      <b><?= $title ?></b>, né<?= $gender['e'] ?> le <?= $depute['dateNaissanceFr'] ?> à <?= $depute['birthCity'] ?>,
      était un<?= $gender['e'] ?> député<?= $gender['e'] ?> de l'Assemblée nationale.
      Pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $gender['pronom'] ?> a été élu<?= $gender['e'] ?>
      dans le département <?= $depute['dptLibelle2'] ?> <a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>">
        <?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
    </p>
  <?php endif; ?>
  <!-- Paragraphe historique -->
  <?php if ($active) : ?>
    <p>
      <?= ucfirst($gender['pronom']) ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>
      et en est à son <?= $mandat_edito ?> mandat.
      Au total, <?= $title ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale, soit <?= $history_edito ?>
      des députés, qui est de <?= $history_average ?> ans.
    </p>
  <?php elseif ($depute['legislature'] == legislature_current()) : ?>
    <p>
      Pour son dernier mandat, pendant la <?= legislature_current() ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
      <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
      Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
    </p>
  <?php else : ?>
    <p>
      Pour son dernier mandat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> est entré<?= $gender['e'] ?> en fonction en <?= $depute['datePriseFonctionLettres'] ?>.
      <?= ucfirst($gender['pronom']) ?> en était à son <?= $mandat_edito ?> mandat.
      Au total, <?= $gender['pronom'] ?> a passé <?= $depute['lengthEdited'] ?> sur les bancs de l’Assemblée nationale.
    </p>
  <?php endif; ?>
  <!-- Paragraphe end -->
  <?php if (!$active): ?>
    <?php if ($depute['legislature'] == legislature_current()) : ?>
      <p>
        <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?> <?= $this->depute_edito->get_end_mandate($depute) ?>.
      </p>
    <?php else : ?>
      <p>
        <?= ucfirst($gender['pronom']) ?> a quitté l'Assemblée nationale le <?= $depute['dateFinMpFR'] ?>.
      </p>
    <?php endif; ?>
  <?php endif; ?>
  <!-- Paragraphe groupe parlementaire -->
  <?php if ($active) : ?>
    <?php if ($depute['libelleAbrev'] == "NI") : ?>
      <p>
        À l'Assemblée nationale, <?= $title ?> n'est pas membre d'un groupe parlementaire, et siège donc en non-inscrit<?= $gender['e'] ?>.
      </p>
    <?php else : ?>
      <p>
        À l'Assemblée, <?= $title ?> siège avec le groupe <a href="<?= base_url() ?>groupes/legislature-<?= $depute['legislature'] ?>/<?= mb_strtolower($depute['libelleAbrev']) ?>"><?= name_group($depute['libelle']) ?></a> (<?= $depute["libelleAbrev"] ?>), un groupe <b>classé <?= $infos_groupes[$depute['libelleAbrev']]['edited'] ?></b> de l'échiquier politique.
        <?php if ($depute['preseanceGroupe'] == 24): ?><?= $title  ?> n'est pas membre mais <b>apparenté<?= $gender['e'] ?></b> au groupe <?= $depute['libelleAbrev'] ?> : <?= $gender['pronom'] ?> est donc associé<?= $gender['e'] ?> au groupe tout en gardant une marge de liberté.<?php endif; ?>
        <?php if ($isGroupPresident) : ?><?= $title ?> en est <?= $gender['le'] ?> <b>président<?= $gender['e'] ?></b>.<?php endif; ?>
      </p>
    <?php endif; ?>
  <?php else : ?>
    <?php if (!empty($depute['libelle'])) : ?>
      <p>
        Au cours de son dernier mandat, pendant la <?= $depute['legislature'] ?><sup>e</sup> législature, <?= $title ?> a siégé avec le groupe <?= name_group($depute['libelle']) ?> (<?= $depute['libelleAbrev'] ?>).
      </p>
    <?php endif; ?>
  <?php endif; ?>
  <!-- Paragraphe commission parlementaire -->
  <?php if ($active && !empty($commission_parlementaire)) : ?>
    <p><?= $title ?> est <?= mb_strtolower($commission_parlementaire['commissionCodeQualiteGender']) ?> de la <?= $commission_parlementaire['commissionLibelle'] ?>.</p>
  <?php endif; ?>
  <!-- Paragraphe parti politique -->
  <?php if ($politicalParty && $politicalParty['libelle'] != "") : ?>
    <?php if ($active) : ?>
      <p>
        <?= $title ?> est rattaché<?= $gender['e'] ?> financièrement au parti politique <a href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($politicalParty['libelleAbrev']) ?>"><?= $politicalParty['libelle'] ?> (<?= $politicalParty['libelleAbrev'] ?>)</a>.
        Le rattachement permet aux partis politiques de recevoir, pour chaque député, une subvention publique.
      </p>
    <?php else : ?>
      <p>
        Quand <?= $gender['pronom'] ?> était <?= $gender['depute'] ?>, <?= $title ?> était rattaché<?= $gender['e'] ?> financièrement au parti politique <a href="<?= base_url() ?>partis-politiques/<?= mb_strtolower($politicalParty['libelleAbrev']) ?>"><?= $politicalParty['libelle'] ?> (<?= $politicalParty['libelleAbrev'] ?>)</a>.
        Le rattachement permet aux partis politiques de recevoir, pour chaque député, une subvention publique.
      </p>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($depute['job'] && !in_array(mb_strtolower($depute['job']), $no_job)): ?>
    <p>
      Avant de devenir <?= $gender['depute'] ?>, <?= $title ?> exerçait le metier <b><?= mb_strtolower($depute['job']) ?></b>.
      <?php if ($famSocPro !== null): ?>
        Comme <?= round($famSocPro['pct']) ?>% des députés, <?= $gender['pronom'] ?> fait partie de la famille professionnelle <?= mb_strtolower($depute['famSocPro']) ?>.
      <?php endif; ?>
      Pour en savoir plus sur l'origine sociale des parlementaires, <a href="<?= base_url() ?>statistiques">cliquez ici</a>.
    </p>
    <?php if ($hatvpJobs): ?>
      <p>
        Certains députés ne déclarent pas leur dernière activité professionnelle mais un métier exercé il y a plusieurs années. La <span class="url_obf" url_obf="<?= url_obfuscation("https://www.hatvp.fr/") ?>">Haute Autorité pour la transparence de la vie publique</span> publie au contraire les dernier métier des élus.
        Pour découvrir les dernières activités de <?= $title ?>, <a href="#modalHatvp" data-toggle="modal" data-target="#modalHatvp">cliquez ici</a>.
      </p>
      <!-- modalHatvp -->
      <div class="modal fade modalDatan" id="modalHatvp" tabindex="-1" role="dialog" aria-labelledby="modalHatvpLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <span class="modal-title" id="modalHatvpLabel">Les dernières activités professionnelles de <?= $title ?></span>
              <span class="close cursor-pointer" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </span>
            </div>
            <div class="modal-body">
              <table class="table table-sm mt-3">
                <thead>
                  <tr>
                    <th scope="col">Métier</th>
                    <th scope="col" class="text-center">Organisation</th>
                    <th scope="col" class="text-center">Début</th>
                    <th scope="col" class="text-center">Fin</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($hatvpJobs as $job): ?>
                    <tr>
                      <td><?= ucfirst(strtolower($job['value'])) ?></td>
                      <td class="text-center"><?= ucfirst(strtolower($job['employeur'])) ?></td>
                      <td class="text-center"><?= $job['dateDebut'] ?></td>
                      <td class="text-center"><?= $job['dateFin'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <p class="mt-4 source">Ces données viennent de la déclaration de <?= $title ?> à la <span class="url_obf" url_obf="<?= url_obfuscation("https://www.hatvp.fr/") ?>">Haute Autorité pour la transparence de la vie publique</span> (HATVP).</p>
              <p class="source">Pour découvrir la déclaration de <?= $title ?>, <span class="url_obf" url_obf="<?= url_obfuscation($depute['hatvp']) ?>">cliquez ici</span>.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($depute['mailAn'] !== NULL && $active): ?>
    <div class="d-flex justify-content-center mt-5">
      <a href="mailto:<?= $depute['mailAn'] ?>" class="btn btn-primary">
        <?= file_get_contents(asset_url() . "imgs/icons/envelope.svg") ?>
        <span class="ml-2">Contacter <?= $title ?></span>
      </a>
    </div>
  <?php endif; ?>
</div>