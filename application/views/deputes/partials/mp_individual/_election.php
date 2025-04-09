<!-- BLOC ELECTION -->
<div class="bloc-election mt-5">
  <h2 class="mb-4 title-center">Son élection</h2>
  <div class="card">
    <div class="card-body pb-0">
      <?php if ($active) : ?>
        <p>
          <?= $title ?> est <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
        </p>
      <?php else : ?>
        <p>
          <?= $title ?> était <?= $gender['le'] ?> député<?= $gender['e'] ?> de la <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><a href="<?= base_url() ?>deputes/<?= $depute['dptSlug'] ?>"><?= $depute['departementNom'] . ' (' . $depute['departementCode'] . ')' ?></a>.
        </p>
      <?php endif; ?>
      <?php if ($election_canceled && $election_canceled['cause']): ?>
        <p><?= $election_canceled['cause'] ?></p>
        <p>Pour découvrir les résultats des élection législatives partielles, organisées après l'invalidation par le Conseil constitutionnel, <span class="url_obf" url_obf="<?= url_obfuscation("https://www.interieur.gouv.fr/Elections/Les-resultats/Partielles/Legislatives") ?>">cliquez ici</span>.</p>
      <?php endif; ?>
      <?php if (isset($election_result)) : ?>
        <p>
          <?= ucfirst($gender['pronom']) ?> a été élu<?= $gender['e'] ?> au <b><?= $election_result['tour_election'] ?> tour</b> avec <?= formatNumber($election_result['voix']) ?> voix, soit <?= round($election_result['pct_exprimes']) ?>% des suffrages exprimés.
        </p>
        <div class="chart-election mt-4">
          <div class="majority d-flex align-items-center" style="flex-basis: <?= round($election_result['pct_exprimes']) ?>%">
            <span><?= round($election_result['pct_exprimes']) ?>%</span>
          </div>
          <div class="line">
          </div>
          <div class="minority" style="flex-basis: <?= 100 - round($election_result['pct_exprimes']) ?>%">
          </div>
        </div>
        <div class="legend d-flex justify-content-center mt-1">
          <span>50%</span>
        </div>
        <h3 class="mt-4">L'élection de <?= $title ?> en détail</h3>
        <span class="subtitle"><?= $election_result['tour_election'] ?> tour des élections législatives de 2024 - <?= $depute["circo"] ?><sup><?= $depute["circo_abbrev"] ?></sup> circonscription <?= $depute['dptLibelle2'] ?><?= $depute['departementNom'] ?></span>
        <div class="d-flex flex-column mt-4">
          <div class="d-flex flex-column justify-content-center">
            <p>Il y avait dans la circonscription <b><?= formatNumber($election_infos['inscrits']) ?> personnes inscrites</b> sur les listes électorales.</p>
            <p>Pendant le <?= $election_result['tour_election'] ?> tour, le taux de participation était de <?= $election_infos['participation'] ?>%. Au niveau national, il était de <?= $election_result['tour'] == 1 ? 67 : 67 ?>%.</p>
            <p><?= $title ?> a été élu<?= $gender['e'] ?> avec <?= formatNumber($election_result['voix']) ?> voix, soit <?= round($election_result['voix'] * 100 / $election_infos['inscrits']) ?>% des inscrits.</p>
            <p>Plus d'information ? <span class="url_obf" url_obf="<?= url_obfuscation("https://www.resultats-elections.interieur.gouv.fr/legislatives2024/ensemble_geographique/index.html") ?>">Cliquez ici.</span></p>
          </div>
          <div class="bar-container stats election mt-3 p-3" id="pattern_background">
            <p class="text-center title mb-0">Le choix électoral des <u><?= formatNumber($election_infos['inscrits']) ?> inscrits</u></p>
            <div class="chart">
              <div class="bar-chart d-flex justify-content-between align-items-end">
                <div class="bars mx-1 mx-md-3" style="height: <?= round($election_result['voix'] / $election_infos['inscrits'] * 100) ?>%">
                  <span class="score text-center"><?= formatNumber($election_result['voix']) ?></span>
                </div>
                <?php if (isset($election_opponents)): ?>
                  <?php foreach ($election_opponents as $opponent): ?>
                    <div class="bars mx-1 mx-md-3" style="height: <?= round($opponent['voix'] / $election_infos['inscrits'] * 100) ?>%">
                      <span class="score text-center"><?= formatNumber($opponent['voix']) ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
                <div class="bars mx-1 mx-md-3" style="height: <?= round(($election_infos['blancs'] + $election_infos['nuls']) / $election_infos['inscrits'] * 100) ?>%">
                  <span class="score text-center"><?= formatNumber($election_infos['blancs'] + $election_infos['nuls']) ?></span>
                </div>
                <div class="bars mx-1 mx-md-3" style="height: <?= round($election_infos['abstentions'] / $election_infos['inscrits'] * 100) ?>%">
                  <span class="score text-center"><?= formatNumber($election_infos['abstentions']) ?></span>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-between mt-2">
              <div class="legend-element text-center mx-1"><?= $title ?></div>
              <?php if (isset($election_opponents)): ?>
                <?php foreach ($election_opponents as $opponent): ?>
                  <div class="legend-element text-center mx-1"><?= $opponent['candidat'] ?></div>
                <?php endforeach; ?>
              <?php endif; ?>
              <div class="legend-element text-center mx-1">Blancs et nuls</div>
              <div class="legend-element text-center mx-1">Abstentions</div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div><!-- // END BLOC ELECTION -->