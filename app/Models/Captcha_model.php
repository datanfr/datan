<?php
class Captcha_model extends CI_Model {

  public function __construct() {
    $this->load->helper('captcha');
  }

  public function generateCaptcha(){
    $this->session->unset_userdata('captchaCode');
    $config = array(
      'img_path'      => './assets/imgs/captcha/',
      'img_url'       => asset_url().'imgs/captcha/',
      //'font_path'     => 'system/fonts/texb.ttf',
      'img_width'     => '160',
      'img_height'    => 50,
      'word_length'   => 8,
      'font_size'     => 55,
      'expiration'    => 7200,
      'pool'          => '0123456789abcdefghijklmnopqrstuvwxyz',
    );
    $captcha = create_captcha($config);
    $this->session->set_userdata('captchaCode', $captcha['word']);
    return $captcha['image'];
  }
}
