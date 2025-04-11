<div class="container pg-depute-individual">
  <div class="row">
    <div class="col-md-10 col-lg-8 offset-md-1 offset-lg-0 pl-lg-5">
      <?php
        if ($category === 'positions-importantes') {
            $this->view('deputes/partials/mp_individual/_key_positions.php');
        } elseif ($category === 'derniers-votes') {
            $this->view('deputes/partials/mp_individual/_votes.php');
        } elseif ($category === 'election') {
            $this->view('deputes/partials/mp_individual/_election.php');
        } elseif ($category === 'comportement-politique') {
            $this->view('deputes/partials/mp_individual/statistics/_index.php');
        }
      ?>
    </div>
  </div>
</div>

