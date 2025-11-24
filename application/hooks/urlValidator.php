<?php 

class urlValidator {
    public function check_multiple_slashes() {
        $uri = $_SERVER['REQUEST_URI'];

        if (preg_match('#(?<!:)//+#', $uri)) {
            show_404();
        }

    }
}