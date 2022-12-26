<div class="row bar-container stats" style="margin-left: -20px; margin-right: -20px;">
  <div class="<?= $grid ? 'col-11 offset-1' : 'col-12' ?>">
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
        <?php foreach ($stats_history_chart as $group): ?>
          <?php if ($type === 'pct'): ?>
            <div class="bars <?= $group['organeRef'] != $organeRef ? 'white' : '' ?> mx-1 mx-md-3" style="height: <?= round($group['value'] / $divided_by * 100) ?>%">
              <span class="score"><?= round($group['value'] * 100) ?>%</span>
            </div>
          <?php endif; ?>
          <?php if ($type === 'score'): ?>
            <div class="bars <?= $group['organeRef'] != $organeRef ? 'white' : '' ?> mx-1 mx-md-3" style="height: <?= round($group['value'] / $divided_by * 100) ?>%">
              <span class="score"><?= round($group['value'], 2) ?></span>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="<?= $grid ? 'col-11 offset-1' : 'col-12' ?> d-flex justify-content-between mt-2">
    <?php foreach ($stats_history_chart as $group): ?>
      <div class="legend-element text-center">
        <p class="font-weight-bold"><?= $group['libelleAbrev'] ?></p>
        <?php if ($terms): ?>
          <p class="font-italic h6 mb-0"><?= $group['legislature'] ?><sup>ème</sup> législature</p>
          <p class="font-italic h6">(<?= date('Y', strtotime($group['dateDebut'])) ?> - <?= $group['dateFin'] ? date('Y', strtotime($group['dateFin'])) : 'en cours' ?>)</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
