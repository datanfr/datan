<!-- BLOC ELECTION -->
<div class="bloc-election mt-5">
  <?php

  // Préparer les textes en fonction de $first_person
  if ($first_person) {
    $title_election = "Mon élection";
    $text_active = "Je suis député{$gender['e']} de la {$depute["circo"]}<sup>{$depute["circo_abbrev"]}</sup> circonscription {$depute['dptLibelle2']}{$depute['departementNom']} ({$depute['departementCode']}).";
    $text_inactive = "J'étais {$gender['le']} député{$gender['e']} de la {$depute["circo"]}<sup>{$depute["circo_abbrev"]}</sup> circonscription {$depute['dptLibelle2']}<a href=\"" . base_url() . "deputes/{$depute['dptSlug']}\">{$depute['departementNom']} ({$depute['departementCode']})</a>.";
    $text_elected = isset($election_result)
      ? "J'ai été élu{$gender['e']} {$gender['depute']} lors du {$election_result['tour_election']} tour"
        . ($election_result['partielle'] === true
          ? " d'une élection législative partielle "
          : " des élections législatives de 2024")
        . " avec <b>" . formatNumber($election_result['voix']) . "</b> voix, soit "
        . round($election_result['pct_exprimes']) . "% des suffrages exprimés."
      : null;
  } else {
    $title_election = "Son élection";
    $text_active = "Député{$gender['e']} de la {$depute["circo"]}<sup>{$depute["circo_abbrev"]}</sup> circonscription {$depute['dptLibelle2']}{$depute['departementNom']} ({$depute['departementCode']})";
    $text_inactive = "{$title} était {$gender['le']} député{$gender['e']} de la {$depute["circo"]}<sup>{$depute["circo_abbrev"]}</sup> circonscription {$depute['dptLibelle2']}<a href=\"" . base_url() . "deputes/{$depute['dptSlug']}\">{$depute['departementNom']} ({$depute['departementCode']})</a>.";
    $text_elected = isset($election_result)
    ? "{$title} a été élu{$gender['e']} {$gender['depute']} lors du {$election_result['tour_election']} tour"
      . ($election_result['partielle'] === true
         ? " d'une élection législative partielle"
         : " des élections législatives de 2024")
      . " avec <b>" . formatNumber($election_result['voix']) . "</b> voix, soit "
      . round($election_result['pct_exprimes']) . "% des suffrages exprimés."
    : null;
  }
  ?>


  <?php if (!isset($iframe_title_visibility) || $iframe_title_visibility !== 'hidden'): ?>
    <h2 class="mb-4 title-center"><?= $title_election ?></h2>
  <?php endif; ?>


  <div class="card">
    <div class="card-body">


      <!-- Actuel ou ancien député -->
      <?php if ($active) : ?>
        <p class="<?= $first_person ? "" : "subtitle" ?>"><?= $text_active ?></p>
      <?php else : ?>
        <p><?= $text_inactive ?></p>
      <?php endif; ?>

      <!-- Résultats de l'élection -->
      <?php if (isset($election_result)) : ?>
        <p><?= $text_elected ?></p>


        <!-- Taux de participation (hors iframe) -->
        <?php if (isset($election_infos) && (!isset($iframe) || !$iframe)) : ?>
          <p>
            La participation au <?= $election_result['tour_election'] ?> tour a atteint <?= $election_infos['participation'] ?>% dans cette circonscription, un taux <?= $this->functions_datan->compare_numbers_text($election_infos['participation'], 67) ?> à la moyenne nationale (<?= $election_result['tour'] == 1 ? 67 : 67 ?>%).
          </p>
        <?php endif; ?>

        <!-- Elections partielles -->
        <?php if ($election_result['partielle']): ?>
          <p><?= $first_person ? "J'ai été élu" . $gender["e"] : $title . " a été élu" . $gender["e"] ?> lors d'une élection partielle qui s'est tenue en <?= $election_result['dateFr'] ?>.</p>
        <?php endif; ?>


        <!-- Résultats détaillés -->
        <div class="mt-4">
          <?php if ($election_result['partielle']): ?>
            <p class="subtitle">Résultats du <?= $election_result['tour_election'] ?> tour - Élection législative partielle <?= date('Y', strtotime($election_result['date'])) ?></p>
          <?php else : ?>
            <p class="subtitle">Résultats du <?= $election_result['tour_election'] ?> tour - Élections législatives 2024</p>
          <?php endif; ?>


          <!-- Résultat du député élu -->
          <div class="border border-primary rounded px-3 py-4 mt-4" style="background-color: rgba(0, 183, 148, 0.15);">
            <div class="d-flex justify-content-between">
              <h6 class="mt-0 font-weight-bold"><?= $title ?><span class="badge badge-primary ml-2">Élu<?= $gender['e'] ?></span></h6>
              <strong><?= round($election_result['pct_exprimes'], 1) ?> %</strong>
            </div>
            <div class="d-flex align-items-center mb-1">
              <small class="text-muted"><?= formatNumber($election_result['voix']) ?> voix</small>
            </div>
            <div class="progress" style="height: 10px;">
              <div class="progress-bar bg-primary" role="progressbar" style="width: <?= round($election_result['pct_exprimes']) ?>%"></div>
            </div>
          </div>

          <!-- Résultats des autres candidats -->
          <?php if (isset($election_opponents)): ?>
            <?php foreach ($election_opponents as $opponent): ?>
              <div class="mt-4 px-3">
                <div class="d-flex justify-content-between">
                  <h6 class="mt-0 font-weight-bold"><?= $opponent['candidat'] ?></h6>
                  <strong><?= round($opponent['pct_exprimes'], 1) ?> %</strong>
                </div>
                <div class="d-flex align-items-center mb-1">
                  <small class="text-muted"><?= formatNumber($opponent['voix']) ?> voix</small>
                </div>
                <div class="progress" style="height: 10px;">
                  <div class="progress-bar bg-primary" role="progressbar" style="width: <?= round($opponent['pct_exprimes']) ?>%"></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>


          <!-- Lien vers les résultats officiels -->
          <div class="mt-4">
            <span class="url_obf" url_obf="<?= url_obfuscation("https://www.resultats-elections.interieur.gouv.fr/legislatives2024/ensemble_geographique/index.html") ?>">🔎 Consultez les résultats complets</span>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div> <!-- // END BLOC ELECTION -->
