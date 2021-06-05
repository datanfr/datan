<?php
  class Meta_model extends CI_Model {
    public function get_url(){
      $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      return $url;
    }

    public function get_ogp($type, $title, $description, $url, $data){
      //print_r($data);

      $array['title'] = $title;
      $array['description'] = $description;
      $array['url'] = $url;
      $array['site_name'] = "Datan.fr";

      // Twitter
      $array['twitter_card'] = "summary_large_image"; // or summary
      $array['twitter_title'] = $title;
      $array['twitter_description'] = $description;


      if ($type == "deputes/individual" || $type == "deputes/historique") {
        $uid = $data['depute']['mpId'];
        if ($data['depute']['imgOgp']) {
          $array['img'] = asset_url()."imgs/deputes_ogp/ogp_deputes_".$uid.".png";
          $array['twitter_img'] = asset_url()."imgs/deputes_ogp/ogp_deputes_".$uid.".png";
        } else {
          $array['img'] = asset_url()."imgs/datan/logo_social_media.png";
          $array['twitter_img'] = asset_url()."imgs/datan/logo_social_media.png";
        }
        $array['img_width'] = 1200;
        $array['img_height'] = 630;
        $array['img_type'] = "image/png";
        // PROFILE
        $array['type'] = 'profile';
        $array['type_first_name'] = $data['depute']['nameFirst'];
        $array['type_last_name'] = $data['depute']['nameLast'];
      } elseif ($type == "posts/view") {
        $id = $data['post']['id'];
        $slug = $data['post']['slug'];
        $array['img'] = asset_url()."imgs/posts/img_post_".$id.".png";
        $array['twitter_img'] = asset_url()."imgs/posts/img_post_".$id.".png";
        $array['img_width'] = NULL;
        $array['img_height'] = NULL;
        $array['img_type'] = "image/png";
        $array['type'] = 'website';
      } elseif ($type == "votes/individual") {
        if (isset($data['vote']['og_image'])) {
          $array['img'] = $data['vote']['og_image'];
          $array['twitter_img'] = $data['vote']['og_image'];
          $array['img_width'] = 2048;
          $array['img_height'] = 1170;
        } else {
          $array['img'] = asset_url()."imgs/datan/logo_social_media.png";
          $array['twitter_img'] = asset_url()."imgs/datan/logo_social_media.png";
          $array['img_width'] = 1200;
          $array['img_height'] = 630;
        }
        $array['img_type'] = "image/png";
        $array['type'] = 'website';
      } else {
        $array['img'] = asset_url()."imgs/datan/logo_social_media.png";
        $array['twitter_img'] = asset_url()."imgs/datan/logo_social_media.png";
        $array['img_width'] = 1200;
        $array['img_height'] = 630;
        $array['img_type'] = "image/png";
        $array['type'] = 'website';
      }


      return $array;
    }
  }
?>
