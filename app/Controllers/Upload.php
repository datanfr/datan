<?php

  class Upload extends MY_Controller {

    public function __construct() {
      parent::__construct();
    }

    public function image(){
      $this->password_model->security_only_team();

      if (isset($_FILES['upload']['name'])) {
        $config['upload_path'] = './assets/imgs/posts/inside/';
        $config['allowed_types'] = 'jpg|jpeg|png|webp';
        $config['file_name'] = bin2hex(random_bytes(16));
        $config['max_size'] = 4096; // 4MB

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('upload')) {
          $uploadData = $this->upload->data();

          // Verify actual MIME type server-side
          $finfo = new finfo(FILEINFO_MIME_TYPE);
          $mime = $finfo->file($uploadData['full_path']);
          $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
          if (!in_array($mime, $allowed_mimes, true)) {
            unlink($uploadData['full_path']);
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file type.']);
            return;
          }

          $imageURL = asset_url() . 'imgs/posts/inside/' . $uploadData['file_name'];

          // CKEditor expects a JSON object with a `url` key
          header('Content-Type: application/json');
          echo json_encode(['url' => $imageURL]);
        } else {
          http_response_code(400);
          echo json_encode(['error' => $this->upload->display_errors()]);
        }
      } else {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded.']);
      }
    }

  }

 ?>
