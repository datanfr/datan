<!-- BLOC ELECTION -->
<div class="row bloc-election" id="pattern_background">
  <div class="container p-md-0">
    <div class="row py-4">
      <div class="col-12">
        <h2 class="text-center my-4">Élections municipales 2026</h2>
        <h3 class="mt-3">Découvrez les députés candidats aux municipales</h3>
      </div>
    </div>
    <div class="row pt-2 pb-5">
      <div class="col-md-5 d-flex flex-column justify-content-center">
        <p>Les élections municipales se dérouleront les dimanches <b>15 et 22 mars 2026</b>.</p>
        <p>Notre équipe a répertorié <?= $candidatsN ?> député<?= $candidatsN > 1 ? "s" : "" ?> candidat<?= $candidatsN > 1 ? "s" : "" ?> aux élections municipales de 2026.</p>
        <p>Le groupe politique <a href="<?= base_url() ?>groupes/legislature-<?= $election_groups[0]['legislature'] ?>/<?= mb_strtolower($election_groups[0]['libelleAbrev'] )?>"><?= $election_groups[0]['libelleAbrev'] ?></a> compte la plus forte proportion de députés candidats aux municipales 2026 : <b><?= $election_groups[0]['candidates_n'] ?> de ses <?= $election_groups[0]['effectif'] ?> membres se présentent</b>, soit <?= $election_groups[0]['candidates_pct'] ?>%.</p>
      </div>
      <div class="col-md-7 mt-5 mt-md-0">
        <div class="card card-election-groups">
          <div class="card-header font-weight-bold text-center">
            Pourcentage de députés candidats aux municipales 2026 par groupe politique
          </div>
          <div class="card-body">
            <canvas id="chart_election"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="row pb-5">
      <div class="col-12 d-flex justify-content-center">
        <a href="<?= base_url();?>elections/municipales-2026" class="no-decoration">
          <button type="button" class="btn btn-primary">Découvrez les députés candidats</button>
        </a>
      </div>
    </div>
  </div>
</div> <!-- END BLOC ELECTIONS -->
