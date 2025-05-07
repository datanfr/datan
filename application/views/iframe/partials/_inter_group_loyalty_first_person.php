<!-- CARD MAJORITE -->
<?php if (!in_array($depute['groupeId'], $this->groupes_model->get_all_groupes_majority()) && $depute['legislature'] != 17): ?>
  <div class="card card-statistiques my-4">
    <div class="card-body">
      <div class="row">
        <div class="col-12 d-flex flex-row align-items-center">
          <div class="icon">
            <?= file_get_contents(base_url() . '/assets/imgs/icons/elysee.svg') ?>
          </div>
          <h3 class="ml-3 text-uppercase">Proximité avec la majorité gouvernementale
            <a tabindex="0" role="button" data-toggle="popover" class="no-decoration popover_focus" data-trigger="focus" aria-label="Tooltip majorité" title="Proximité avec la majorité gouvernementale" data-content="Le <b>taux de proximité avec la majorité gouvernementale</b> représente le pourcentage de fois où un député vote la même chose que le groupe présidentiel (La République en Marche).<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
          </h3>
        </div>
      </div>
      <div class="row">
        <?php if ($no_majorite) : ?>
          <div class="col-12 mt-2">
            <p>Cette statistique sera disponible dès qu'un nombre suffisant de votes sera atteint.</p>
          </div>
        <?php else : ?>
          <div class="col-lg-3 offset-lg-1 mt-2">
            <div class="d-flex justify-content-center align-items-center">
              <div class="c100 p<?= $majorite['score'] ?> m-0">
                <span><?= $majorite['score'] ?> %</span>
                <div class="slice">
                  <div class="bar"></div>
                  <div class="fill"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-8 infos mt-4 mt-lg-2">
            <div class="texte ml-md-3 pl-md-3 mt-md-0 mt-3">
              <p>
                J'ai voté comme la majorité gouvernementale dans <?= $majorite['score'] ?>% des cas. Les votes de <?= $title ?> sont comparés à ceux du groupe politique le plus gros de la majorité (<a href="<?= base_url() ?>groupes/legislature-<?= $groupMajority['legislature'] ?>/<?= mb_strtolower($groupMajority['libelleAbrev']) ?>"><?= name_group($groupMajority['libelle']) ?></a>).
              </p>
              <p>
                J’<?= $active ? "étais" : "suis" ?> <b><?= $edito_majorite['all'] ?></b> de la majorité gouvernementale que la moyenne des députés non membres de la majorité (<?= $majorite['all'] ?> %).
              </p>
              <?php if ($majorite['group']): ?>
              <p>
                De plus, j’<?= $active ? "étais" : "suis" ?> <b><?= $edito_majorite['group'] ?></b> de la majorité gouvernementale que la moyenne des députés de mon groupe politique (<?= $majorite['group'] ?> %).
              </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
<div class="card card-statistiques bloc-proximity my-4">
  <div class="card-body">
    <div class="row">
      <div class="col-12 d-flex flex-row align-items-center">
        <div class="icon">
          <?= file_get_contents(base_url() . '/assets/imgs/icons/group.svg') ?>
        </div>
        <h3 class="ml-3 text-uppercase">Proximité avec les groupes politiques
          <a tabindex="0" role="button" data-toggle="popover" data-trigger="focus" aria-label="Tooltip proximité" class="no-decoration popover_focus" title="Proximité avec les groupes politiques" data-content="Le <b>taux de proximité avec les groupes</b> représente le pourcentage de fois où un député vote la même chose qu'un groupe parlementaire. Chaque groupe se voit attribuer pour chaque vote une <i>position majoritaire</i>, en fonction du vote de ses membres. Cette position peut soit être 'pour', 'contre', ou 'absention'. Pour chaque vote, nous déterminons si le ou la députée a voté la même chose que la position majoritaire d'un groupe. Le taux de proximité est le pourcentage de fois où le ou la députée a voté de la même façon qu'un groupe.<br><br>Par exemple, si le taux est de 75%, cela signifie que <?= $title ?> a voté avec ce groupe dans 75% des cas.<br><br>Pour plus d'information, <a href='<?= base_url() ?>statistiques/aide#proximity' target='_blank'>cliquez ici</a>." id="popover_focus"><?= file_get_contents(asset_url() . "imgs/icons/question_circle.svg") ?></a>
        </h3>
      </div>
    </div>
    <?php if ($no_votes != TRUE) : ?>
      <div class="row">
        <div class="offset-2 col-10">
          <h4><?= $title ?> <?= $depute['legislature'] == legislature_current() ? "vote" : "votait" ?> <b>souvent</b> avec :</h4>
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

            <p>
              <?php if ($group['libelleAbrev'] != "NI") : ?>
                En plus de mon propre groupe,
              <?php else : ?>
                Le
              <?php endif; ?>
              je <?= $active ? "vote" : "votais" ?> souvent (dans <?= $proximite["first1"]["accord"] ?>% des cas) avec le groupe <a href="<?= base_url() ?>groupes/legislature-<?= $depute["legislature"] ?>/<?= mb_strtolower($proximite["first1"]["libelleAbrev"]) ?>"><?= $proximite["first1"]["libelleAbrev"] ?></a>, <?= $proximite["first1"]["maj_pres"] ?>
              <?php if ($proximite["first1"]["libelleAbrev"] != "NI") : ?>
                classé <?= $proximite["first1"]["ideologiePolitique"]["edited"] ?> de l'échiquier politique.
              <?php endif; ?>
            </p>
          </div>
        </div>
      <?php endif; ?>
      <div class="row mt-5">
        <div class="col-10 offset-2">
        <h4>Je <?= $depute['legislature'] == legislature_current() ? "vote" : "votais" ?> <b>souvent</b> avec :</h4>
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

            <p>
              À l'opposé, le groupe avec lequel <?= $active ? "je suis" : "j'étais" ?> le moins proche est <a href="<?= base_url() ?>groupes/legislature-<?= $depute["legislature"] ?>/<?= mb_strtolower($proximite["last1"]["libelleAbrev"]) ?>"><?= $proximite["last1"]["libelle"] ?></a>, <?= $proximite["last1"]["maj_pres"] ?>
              <?php if ($proximite["last1"]["libelleAbrev"] != "NI") : ?>
                classé <?= $proximite["last1"]["ideologiePolitique"]["edited"] ?> de l'échiquier politique.
              <?php endif; ?>
              Je <?= $active ? "ne vote" : "n'ai voté" ?> avec ce groupe que dans <b><?= $proximite["last1"]["accord"] ?>%</b> des cas.
            </p>
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
</div> <!-- // END BLOC PROXIMITY -->