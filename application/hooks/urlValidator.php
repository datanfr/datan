<?php 

class urlValidator {
    public function check_multiple_slashes() {
        // En CLI, REQUEST_URI n'existe pas
        if (is_cli()) {
            return; // Skip cette vérification en CLI
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        if ($uri && preg_match('#(?<!:)//+#', $uri)) {
            show_404();
        }
    }
}