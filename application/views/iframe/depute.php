
<?php
    if (in_array('positions-importantes', $categories)) {
        $this->load->view('iframe/sections/positions_importantes');
    }

    if (in_array('derniers-votes', $categories)) {
        $this->load->view('iframe/sections/derniers_votes');
    }

    if (in_array('proximite-groupe', $categories)) {
        $this->load->view('iframe/sections/proximite_groupe');
    }

    if (in_array('election', $categories)) {
        $this->load->view('iframe/sections/election');
    }
    ?>

