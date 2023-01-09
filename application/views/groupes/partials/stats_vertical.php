<div class="row bar-container stats pr-2" style="margin-left: -20px; margin-right: -20px;">
  <div class="<?= $grid ? 'col-10 offset-2' : 'col-12' ?>">
    <div class="chart">
      <?php if ($grid): ?>
        <div class="chart-grid">
          <div id="ticks">
            <?php if ($type === 'pct'): ?>
              <div class="tick" style="height: 50%;"><p>100 %</p></div>
              <div class="tick" style="height: 50%;"><p>50 %</p></div>
              <div class="tick" style="height: 0;"><p>0 %</p></div>
            <?php endif; ?>
            <?php if ($type === 'score'): ?>
              <div class="tick" style="height: 50%;"><p>1</p></div>
              <div class="tick" style="height: 50%;"><p>0.5</p></div>
              <div class="tick" style="height: 0;"><p>0</p></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
      <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
        <?php foreach ($stats_history_chart as $value): ?>
          <?php if ($type === 'pct'): ?>
            <div class="bars <?= $tooltip ? 'tooltipHelp' : '' ?> <?= $value['organeRef'] != $organeRef ? 'white' : '' ?> mx-1 mx-md-3" <?= $tooltip ? 'data-toggle="tooltip" data-placement="top" title="'.$value['libelle'].'"' : '' ?> style="height: <?= round($value['value'] / $divided_by * 100) ?>%">
              <span class="score"><?= round($value['value'] * 100) ?>%</span>
            </div>
          <?php endif; ?>
          <?php if ($type === 'score'): ?>
            <div class="bars <?= $tooltip ? 'tooltipHelp' : '' ?> <?= $value['organeRef'] != $organeRef ? 'white' : '' ?> mx-1 mx-md-3" <?= $tooltip ? 'data-toggle="tooltip" data-placement="top" title="'.$value['libelle'].'"' : '' ?> style="height: <?= round($value['value'] / $divided_by * 100) ?>%">
              <span class="score"><?= round($value['value'], 2) ?></span>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="<?= $grid ? 'col-10 offset-2' : 'col-12' ?> d-flex justify-content-between mt-2">
    <?php foreach ($stats_history_chart as $value): ?>
      <div class="legend-element text-center">
        <p class="font-weight-bold">
          <span <?= $tooltip ? 'class="tooltipHelp tooltipDashed" data-toggle="tooltip" data-placement="bottom" title="'.$value['libelle'].'" ' : '' ?> ><?= $value['libelleAbrev'] ?></span>
        </p>
        <?php if ($terms): ?>
          <p class="font-italic small mb-0">Leg. <?= $value['legislature'] ?></p>
          <p class="font-italic small">(<?= substr(date('Y', strtotime($value['dateDebut'])), 2, 2) ?>-<?= $value['dateFin'] ? substr(date('Y', strtotime($value['dateFin'])), 2, 2) : 'en cours' ?>)</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
