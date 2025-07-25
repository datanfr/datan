<!-- // CARD GROUPS PROXIMITY -->

<?php

if ($first_person && $active) {
  $first_section_title = "Je vote <b>souvent</b> avec :";
  $first_section_content = "";
  if ($depute['libelleAbrev'] != 'NI') {
    $first_section_content .= "En plus de mon propre groupe, je ";
  } else {
    $first_section_content .= "Je ";
  }
  if (isset($proximite)) {
    $first_section_content .= "vote souvent (dans {$proximite['first1']['accord']}% des cas) avec le groupe " .
      "<a href='" . base_url() . "groupes/legislature-{$depute['legislature']}/" .
      mb_strtolower($proximite['first1']['libelleAbrev']) . "'>{$proximite['first1']['libelleAbrev']}</a>, " .
      "{$proximite['first1']['maj_pres']}";

    if ($proximite['first1']['libelleAbrev'] != 'NI') {
      $first_section_content .= " classé {$proximite['first1']['ideologiePolitique']['edited']} de l'échiquier politique.";
    }

    $second_section_title = "Je vote <b>rarement</b> avec :";

    $second_section_content = "";

    $second_section_content .= "À l'opposé, le groupe avec lequel je suis le moins proche est ";
    $second_section_content .= "<a href='" . base_url() . "groupes/legislature-{$depute['legislature']}/" . mb_strtolower($proximite['last1']['libelleAbrev']) . "'>{$proximite['last1']['libelle']}</a>, ";
    $second_section_content .= "{$proximite['last1']['maj_pres']}";

    if ($proximite['last1']['libelleAbrev'] != "NI") {
      $second_section_content .= " classé {$proximite['last1']['ideologiePolitique']['edited']} de l'échiquier politique.";
    }

    $second_section_content .= " Je ne vote avec ce groupe que dans <b>{$proximite['last1']['accord']}%</b> des cas.";
  }
} else {
  $first_section_title = $title . " " . ($depute['legislature'] == legislature_current() ? "vote" : "votait") . " <b>souvent</b> avec :";

  $first_section_content = "";

  if ($depute['libelleAbrev'] != "NI") {
    $first_section_content .= "En plus de son propre groupe, ";
  }
  if (isset($proximite)) {

    $first_section_content .= "<b>{$title}</b> " . ($active ? "vote" : "votait") . " souvent (dans {$proximite['first1']['accord']}% des cas) avec le groupe ";
    $first_section_content .= "<a href='" . base_url() . "groupes/legislature-{$depute['legislature']}/" . mb_strtolower($proximite['first1']['libelleAbrev']) . "'>{$proximite['first1']['libelleAbrev']}</a>, ";
    $first_section_content .= "{$proximite['first1']['maj_pres']}";

    if ($proximite['first1']['libelleAbrev'] != "NI") {
      $first_section_content .= " classé {$proximite['first1']['ideologiePolitique']['edited']} de l'échiquier politique.";
    }

    $second_section_title = ucfirst($gender['pronom']) . ' ' .
      ($depute['legislature'] == legislature_current() ? 'vote' : 'votait') . ' <b>rarement</b> avec :';

    $second_section_content = "";
    $second_section_content .= "À l'opposé, le groupe avec lequel {$title} " . ($active ? "est" : "était") . " le moins proche est ";
    $second_section_content .= "<a href='" . base_url() . "groupes/legislature-{$depute['legislature']}/" . mb_strtolower($proximite['last1']['libelleAbrev']) . "'>{$proximite['last1']['libelle']}</a>, ";
    $second_section_content .= "{$proximite['last1']['maj_pres']}";

    if ($proximite['last1']['libelleAbrev'] != "NI") {
      $second_section_content .= " classé {$proximite['last1']['ideologiePolitique']['edited']} de l'échiquier politique.";
    }

    $second_section_content .= " " . ucfirst($gender["pronom"]) . " " . ($active ? "ne vote" : "n'a voté") . " avec ce groupe que dans <b>{$proximite['last1']['accord']}%</b> des cas.";
  }
}
?>



<div class="card card-statistiques bloc-proximity my-4">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex flex-row align-items-center">
        <div class="icon">
          <?= file_get_contents(base_url() . '/assets/imgs/icons/group.svg') ?>
        </div>
        <h3 class="ml-3 text-uppercase">
          Proximité avec les groupes politiques
          <a
            tabindex="0"
            role="button"
            data-toggle="popover"
            data-trigger="focus"
            aria-label="Tooltip proximité"
            class="no-decoration popover_focus"
            title="Proximité avec les groupes politiques"
            data-content="Le <b>taux de proximité avec les groupes</b> représente le pourcentage de fois où un député 
            vote de la même manière qu'un groupe parlementaire. Chaque groupe se voit attribuer pour chaque vote 
            une <i>position majoritaire</i>, en fonction du vote de ses membres. Cette position peut soit être 'pour',
            'contre', ou 'absention'. Pour chaque vote, nous vérifions si le député a voté en accord avec la position 
            majoritaire d'un groupe. Le taux de proximité correspond au pourcentage de votes où le député a voté conformément
            à ce groupe.<br><br>Par exemple, si le taux est de 75%, cela signifie que <?= $title ?> 
            a voté avec ce groupe dans 75% des cas.<br><br>Pour plus d'information, 
            <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>."
            id="popover_focus">
            <?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?>
          </a>
        </h3>

      </div>
    </div>
    <?php if ($no_votes != TRUE) : ?>
      <div class="row">
        <div class="offset-2 col-10">
          <!-- // Première phrase-->
          <h4><?= $first_section_title ?></h4>
        </div>
      </div>
      <div class="row mt-1 bar-container stats pr-2">
        <div class="offset-2 col-10">
          <div class="chart">
            <div class="chart-grid">
              <div id="ticks">
                <div class="tick" style="height: 50%;">
                  <p>100 %</p>
                </div>
                <div class="tick" style="height: 50%;">
                  <p>50 %</p>
                </div>
                <div class="tick" style="height: 0;">
                  <p>0 %</p>
                </div>
              </div>
            </div>
            <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
              <?php foreach ($accord_groupes_first as $group) : ?>
                <div class="bars mx-1 mx-md-3" style="height: <?= $group['accord'] ?>%">
                  <span class="score"><?= $group['accord'] ?>%</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="offset-2 col-10 d-flex justify-content-between mt-2">
          <?php foreach ($accord_groupes_first as $group) : ?>
            <div class="legend-element d-flex align-items-center justify-content-center">
              <span><?= $group['libelleAbrev'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if ($depute['legislature'] == legislature_current() && dissolution() === false): ?>
        <div class="row mt-3">
          <div class="offset-2 col-10">
            <!-- 1ère section content-->
            <p> <?= $first_section_content ?></p>
          </div>
        </div>
      <?php endif; ?>
      <div class="row mt-5">
        <div class="col-10 offset-2">
          <h4><?= $second_section_title ?></h4>
        </div>
      </div>
      <div class="row mt-1 bar-container stats pr-2">
        <div class="offset-2 col-10">
          <div class="chart">
            <div class="chart-grid">
              <div id="ticks">
                <div class="tick" style="height: 50%;">
                  <p>100 %</p>
                </div>
                <div class="tick" style="height: 50%;">
                  <p>50 %</p>
                </div>
                <div class="tick" style="height: 0;">
                  <p>0 %</p>
                </div>
              </div>
            </div>
            <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
              <?php foreach ($accord_groupes_last_sorted as $group) : ?>
                <div class="bars mx-1 mx-md-3" style="height: <?= $group['accord'] ?>%">
                  <span class="score"><?= $group['accord'] ?>%</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="offset-2 col-10 d-flex justify-content-between mt-2">
          <?php foreach ($accord_groupes_last_sorted as $group) : ?>
            <div class="legend-element d-flex align-items-center justify-content-center">
              <span><?= $group['libelleAbrev'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if ($depute['legislature'] == legislature_current() && dissolution() === false): ?>
        <div class="row mt-3">
          <div class="col-10 offset-2 ">
            <!-- 2nd section content-->
            <p><?= $second_section_content ?></p>
          </div>
        </div>
      <?php endif; ?>
      <div class="row mt-4">
        <div class="col-12">
          <div class="text-center">
            <a class="btn btn-primary" id="btn-ranking" data-toggle="collapse" href="#collapseProximity" role="button" aria-expanded="false" aria-controls="collapseProximity">
              Voir tout le classement
            </a>
          </div>
          <div class="collapse mt-3" id="collapseProximity">
            <table class="table">
              <caption class="sr-only">Taux de proximité de <?= $title ?> avec tous les groupes politiques</caption>
              <thead>
                <tr>
                  <th scope="col"></th>
                  <th scope="col">Groupe</th>
                  <th scope="col">Taux de proximité</th>
                  <th scope="col">Groupe dissout ?</th>
                  <th scope="col">Nbr de votes</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; ?>
                <?php foreach ($accord_groupes_all as $group) : ?>
                  <tr>
                    <th scope="row"><?= $i ?></th>
                    <td><?= name_group($group['libelle']) ?> (<?= $group['libelleAbrev'] ?>)</td>
                    <td><?= $group['accord'] ?> %</td>
                    <td><?= $group['ended'] == 1 ? "Oui" : "" ?></td>
                    <td><?= $group['votesN'] ?></td>
                  </tr>
                  <?php $i++ ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php else : ?>
      <p>Cette statistique sera disponible dès qu'un nombre suffisant de votes sera atteint pour <?= $title ?>.</p>
    <?php endif; ?>
  </div>
</div> <!-- // END BLOC GROUPS PROXIMITY -->