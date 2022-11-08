<div class="row bar-container stats" style="margin-left: -20px; margin-right: -20px;">
  <div class="col-10 offset-2">
    <div class="chart">
      <div class="chart-grid">
        <div id="ticks">
          <div class="tick" style="height: 50%;"><p>100 %</p></div>
          <div class="tick" style="height: 50%;"><p>50 %</p></div>
          <div class="tick" style="height: 0;"><p>0 %</p></div>
        </div>
      </div>
      <div class="bar-chart d-flex flex-row justify-content-between align-items-end">
        <?php foreach ($stats_history_chart as $group): ?>
          <div class="bars mx-1 mx-md-3" style="height: <?= round($group['value'] * 100) ?>%">
            <span class="score"><?= round($group['value'] * 100) ?>%</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-10 offset-2 d-flex justify-content-between mt-2">
    <?php foreach ($stats_history_chart as $group): ?>
      <div class="legend-element text-center">
        <p class="font-weight-bold"><?= $group['libelleAbrev'] ?></p>
        <p class="font-italic h6 mb-0"><?= $group['legislature'] ?><sup>ème</sup> législature</p>
        <p class="font-italic h6">(<?= date('Y', strtotime($group['dateDebut'])) ?> - <?= $group['dateFin'] ? date('Y', strtotime($group['dateFin'])) : 'En cours' ?>)</p>
      </div>
    <?php endforeach; ?>
  </div>
</div>
