  <div class="container-fluid bloc-img-deputes async_background" id="container-always-fluid" style="height: 13em"></div>
  <?php if (!empty($depute['couleurAssociee'])) : ?>
    <div class="liseret-groupe" style="background-color: <?= $depute['couleurAssociee'] ?>"></div>
  <?php endif; ?>
  <div class="container pg-depute-individual">
    <div class="row">
      <div class="col-12 col-md-8 col-lg-4 offset-md-2 offset-lg-0">
        <div class="sticky-top" style="margin-top: -150px; top: 80px;">
          <?php $this->load->view('deputes/partials/card_individual.php', array('historique' => FALSE, 'last_legislature' => $depute['legislature'], 'legislature' => $depute['legislature'], 'tag' => 'h1')) ?>
        </div>
      </div> <!-- END COL -->
      <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5">
        <?php $this->view('deputes/partials/voteFeature.php') ?>
        <?php $this->view('deputes/partials/mp_individual/_bio.php') ?>
        <?php $this->view('deputes/partials/mp_individual/_key_positions.php') ?>
        <?php $this->view('deputes/partials/mp_individual/_explanation.php') ?>
        <?php $this->view('deputes/partials/mp_individual/_votes.php') ?>
        <?php $this->view('deputes/partials/mp_individual/_election.php') ?>
        <?php $this->view('deputes/partials/mp_individual/statistics/_index.php') ?>
        <?php $this->view('deputes/partials/mp_individual/_elections_participation') ?>
        <?php $this->view('deputes/partials/mp_individual/_manifesto') ?>
        <?php $this->view('deputes/partials/mp_individual/_parrainages') ?>
        <?php $this->view('deputes/partials/mp_individual/_mandate_history') ?>
        <!-- BLOC PARTAGEZ -->
        <div class="bloc-social mt-5">
          <h2 class="title-center mb-4">Partagez cette page</h2>
          <?php $this->load->view('partials/share.php') ?>
        </div>
        <?php $this->view('deputes/partials/mp_individual/_social_media') ?>
        <?php $this->view('deputes/partials/mp_individual/_contact') ?>
      </div>
    </div>
  </div> <!-- END ROW -->
  </div> <!-- END CONTAINER -->
  <!-- CONTAINER FOLLOW US -->
  <?php $this->load->view('partials/follow-us.php') ?>
  <?php $this->view('deputes/partials/mp_individual/_other_mps') ?>
  <!-- EXPLICATIONS DE VOTE -->
  <?php if (is_iterable($votes_datan)): ?>
    <?php foreach ($votes_datan as $key => $value): ?>
      <?php if ($value['explication']): ?>
        <!-- Modal explain -->
        <?php $this->load->view(
          'votes/modals/explain.php',
          [
            'id' => 'explication-l' . $value['legislature'] . '-v' . $value['voteNumero'],
            'title' => "L'avis de " . $title,
            'value' => $value,
            'vote_titre' => $value['vote_titre'],
            'explication' => $value['explication'],
            'img' => $depute['idImage'],
            'photoSquare' => $photo_square
          ]
        );
        ?>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>