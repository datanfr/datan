<?php
  class Meta_model extends CI_Model {
    public function get_url(){
      $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      return $url;
    }

    public function get_ogp($type, $title, $description, $url, $data){

      $array['title'] = $title;
      $array['description'] = $description;
      $array['url'] = $url;
      $array['site_name'] = "Datan.fr";

      // Twitter
      $array['twitter_card'] = "summary_large_image"; // or summary
      $array['twitter_title'] = $title;
      $array['twitter_description'] = $description;


      if ($type == "deputes/individual" || $type == "deputes/historique") {

        /// --- IF MP PAGE --- ///
        $uid = $data["depute"]["mpId"];
        $gender['e'] = $data['depute']['civ'] == 'M.' ? '' : 'e';
        $gender['ancien'] = $data['depute']['civ'] == 'M.' ? 'ancien' : 'ancienne';
        $dpt = 'député' . $gender['e'] . ' '  . $data['depute']['dptLibelle2'] . '' . $data['depute']['departementNom'] . ' (' . $data['depute']['departementCode'] . ')';
        if ($data['depute']['active'] == 0) {
          $dpt = ucfirst($gender['ancien']) . ' ' . $dpt;
        } else {
          $dpt = ucfirst($dpt);
        }

        $img = "https://og-image-datan.vercel.app/" . str_replace(" ", "%20", $dpt);
        $img .= "?prenom=" . str_replace(" ", "%20", $data["depute"]["nameFirst"]);
        $img .= "&nom=" . str_replace(" ", "%20", $data["depute"]["nameLast"]);
        $img .= "&group=" . str_replace(" ", "%20", $data["depute"]["libelle"]);
        $img .= "&couleur=" . str_replace("#", "", $data["depute"]["couleurAssociee"]);
        $img .= "&template=mp";
        $img .= "&id=" . $data["depute"]["mpId"];
        if ($data["depute"]["img"]) {
          $img .= "&img=1";
        } else {
          $img .= "&img=0";
        }
        $array['img'] = $img;
        $array['twitter_img'] = $img;
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
      } elseif ($type == "newsletter/register") {
        $array['img'] = asset_url()."imgs/datan/newsletter_social_media.png";
        $array['twitter_img'] = asset_url()."imgs/datan/newsletter_social_media.png";
        $array['img_width'] = 1200;
        $array['img_height'] = 630;
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
