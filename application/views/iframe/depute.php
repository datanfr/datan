<?php
    if ($category === 'positions-importantes') {
        $this->view('deputes/partials/mp_individual/_key_positions.php');
    }

    if ($category === 'derniers-votes') {
        $this->view('deputes/partials/mp_individual/_votes.php');
    }

    if ($category === 'election') {
        $this->view('deputes/partials/mp_individual/_election.php');
    }

    if ($category === 'comportement-politique') {
        $this->view('deputes/partials/mp_individual/statistics/_index.php');
    }
?>

