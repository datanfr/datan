<?php

  class Upload extends CI_Controller {

    public function __construct() {
      parent::__construct();
    }

    public function image(){

      

      if (isset($_FILES['upload']['name'])) {
        $config['upload_path'] = './assets/imgs/posts/inside/';
        $config['allowed_types'] = 'jpg|jpeg|png|webp';
        $config['file_name'] = uniqid('img_');

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('upload')) {
          $uploadData = $this->upload->data();
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
