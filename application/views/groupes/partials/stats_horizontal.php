<div class="row bar-container stats-horizontal" style="margin-left: -20px; margin-right: -20px;">
  <div class="col-12">
    <?php foreach ($stats_history_chart as $value): ?>
      <div class="row row-chart my-1">
        <div class="col-3 d-flex align-items-center justify-content-center">
          <div class="text-center">
            <p class="font-weight-bold mb-0">
              <span class="tooltipHelp tooltipDashed" data-toggle="tooltip" data-placement="top" title="<?= $value['libelle'] ?>"><?= remove_nupes($value['libelleAbrev']) ?></span>
            </p>
            <?php if ($terms): ?>
              <p class="font-italic h6 mb-0"><?= $value['legislature'] ?><sup>ème</sup> législature</p>
              <p class="font-italic h6">(<?= date('Y', strtotime($value['dateDebut'])) ?> - <?= $value['dateFin'] ? date('Y', strtotime($value['dateFin'])) : 'en cours' ?>)</p>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-9 d-flex align-items-center justify-content-start">
          <?php if ($type === 'pct'): ?>
            <div class="bars <?= $value['organeRef'] != $organeRef ? 'white' : '' ?>" style="width: <?= round($value['value'] / $divided_by * 100) ?>%">
              <span class="score"><?= round($value['value'] * 100) ?>%</span>
            </div>
          <?php endif; ?>
          <?php if ($type === 'score'): ?>
            <div class="bars <?= $value['organeRef'] != $organeRef ? 'white' : '' ?> mx-1 mx-md-3" style="width: <?= round($value['value'] / $divided_by * 100) ?>%">
              <span class="score"><?= round($value['value'], 2) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
