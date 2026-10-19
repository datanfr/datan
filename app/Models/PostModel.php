<?php

namespace App\Models;

use CodeIgniter\Model;

class PostModel extends Model{

  private $target_widths = [360, 420, 730, 1240];

  private function categories(): array
  {
    return [
      1 => [
        'name' => 'Actualité politique',
        'slug' => 'actualite-politique',
        'subtitle' => "Nos analyses sur l'actualité de l'Assemblée nationale",
        'description_meta' => "Découvrez nos analyses approfondies sur l'actualité des députés et de l'Assemblée nationale."
      ],
      2 => [
        'name' => 'Datan',
        'slug' => 'datan',
        'subtitle' => 'Les nouvelles du projet Datan',
        'description_meta' => 'Vous voulez tout savoir sur le projet Datan ? Découvrez nos dernières nouvelles dans ce blog.'
      ],
      3 => [
        'name' => 'Rapports',
        'slug' => 'rapports',
        'subtitle' => "Nos études sur l'Assemblée nationale et les députés",
        'description_meta' => 'Retrouvez nos études et analyses sur le travail parlementaire.'
      ],
    ];
  }

  private function category_by_id($id): ?array
  {
    $categories = $this->categories();
    return $categories[$id] ?? null;
  }

  private function category_by_slug($slug): ?array
  {
    foreach ($this->categories() as $id => $category) {
      if ($category['slug'] === $slug) {
        $category['id'] = $id;
        return $category;
      }
    }

    return null;
  }

  private function convert_to_webp($source_path, $destination_path, $quality = 80): void
  {
    $image = new \Imagick($source_path);
    $image->setImageFormat('webp');
    $image->setImageCompressionQuality($quality);
    $image->writeImage($destination_path);
    $image->clear();
    $image->destroy();
  }

  private function resize_images($original_path, $base_name) {
    foreach ($this->target_widths as $width) {
      $resized_png_path = ROOTPATH . 'assets/imgs/posts/' . $base_name . '-' . $width . '.png';
      $resized_webp_path = ROOTPATH . 'assets/imgs/posts/webp/' . $base_name . '-' . $width . '.webp';

      $image = new \Imagick($original_path);
      $targetHeight = (int) ($width * $image->getImageHeight() / $image->getImageWidth());
      $image->resizeImage($width, $targetHeight, \Imagick::FILTER_LANCZOS, 1);
      $image->writeImage($resized_png_path);
      $image->destroy();
      $this->convert_to_webp($resized_png_path, $resized_webp_path);
    }
  }

  private function upload_png(bool $required, string $suffix): ?array
  {
    $file = service('request')->getFile('post_image_png');
    if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
      return $required ? ['error' => 'Un fichier PNG est requis.'] : null;
    }

    if (!$file->isValid()) {
      return ['error' => $file->getErrorString() . ' (image png) '];
    }

    if (strtolower($file->getClientExtension()) !== 'png' || $file->getMimeType() !== 'image/png' || $file->getSize() > 2048 * 1024) {
      return ['error' => "Le fichier n'a pas l'extension correcte. Veuillez télécharger un fichier PNG valide."];
    }

    $filename = pathinfo($file->getClientName(), PATHINFO_FILENAME);
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
    $base_name = $filename . '_' . time();
    $new_png_name = $base_name . $suffix . '.png';
    $upload_directory = ROOTPATH . 'assets/imgs/posts' . DIRECTORY_SEPARATOR;

    if (!is_dir($upload_directory) || !is_writable($upload_directory)) {
      throw new \RuntimeException('The post image upload directory is not available.');
    }

    $file->move($upload_directory, $new_png_name);

    return [
      'name' => $base_name,
      'path' => $upload_directory . $new_png_name,
    ];
  }

  public function get_posts($slug, $user, $category_slug){
    if (empty($slug)) {
      $builder = $this->db->table('posts p')
        ->select('p.id, date_format(p.created_at, "%d %M %Y") as created_at_fr, p.user_id, p.title, p.slug, p.body, p.created_at, p.modified_at, p.state, p.category_id, p.image_name')
        ->orderBy('p.created_at', 'DESC');
      if ($user !== 'admin' && $user !== 'writer') {
        $builder->where('p.state', 'published');
      }
      $posts = $builder->get()->getResultArray();

      foreach ($posts as &$post) {
        $cat = $this->category_by_id($post['category_id']);
        $post['category_name'] = $cat['name'] ?? '';
        $post['category_slug'] = $cat['slug'] ?? '';
      }

      return $posts;
    } else {
      $category = $this->category_by_slug($category_slug);
      if ($category === null) {
        return null;
      }
      $builder = $this->db->table('posts p')
        ->select('p.id, date_format(p.created_at, "%d %M %Y") as created_at_fr, p.user_id, p.title, p.slug, p.body, p.created_at, p.modified_at, p.state, p.category_id, p.image_name')
        ->where(['p.slug' => $slug, 'p.category_id' => $category['id']]);
      if ($user !== 'admin' && $user !== 'writer') {
        $builder->where('p.state', 'published');
      }
      $post = $builder->get(1)->getRowArray();

      if ($post) {
        $cat = $this->category_by_id($post['category_id']);

        if (!$cat || $cat['slug'] !== $category_slug) {
            return null;
        }

        $post['category_name'] = $cat['name'];
        $post['category_slug'] = $cat['slug'];
    }

      return $post;
    }
  }

  public function get_post_by_id($id){
    return $this->db->table('posts')
      ->where('id', $id)
      ->get()
      ->getRowArray();
  }

  public function get_post_edit($slug){
    $sql = 'SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.id, p.user_id, p.title, p.slug, p.body, p.created_at, p.state, p.category_id, p.image_name
      FROM posts p
      WHERE p.slug = ?
      LIMIT 1
    ';
    $post = $this->db->query($sql, [$slug])->getRowArray();

    if ($post) {
      $cat = $this->category_by_id($post['category_id']);
      $post['category_name'] = $cat['name'] ?? 'Inconnue';
      $post['category_slug'] = $cat['slug'] ?? '';
  }

    return $post;
  }

  public function get_last_posts($limit = FALSE){
    $builder = $this->db->table('posts p')
      ->select('p.id, p.category_id, DATE_FORMAT(p.created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.slug, p.state, p.image_name')
      ->where('p.state', 'published')
      ->orderBy('p.created_at', 'DESC');
    if ($limit){
      $builder->limit($limit);
    }
    $posts = $builder->get()->getResultArray();

    foreach ($posts as &$post) {
      $category = $this->category_by_id($post['category_id']);
      $post['category_name'] = $category['name'] ?? 'Inconnue';
      $post['category_slug'] = $category['slug'] ?? 'inconnue';
    }

    return $posts;
  }

  public function create_post(){
    helper(['text', 'url']);
    $request = service('request');
    $title = $request->getPost('title');
    $slug = url_title(convert_accented_characters($title), 'dash', true);

    $upload = $this->upload_png(true, '-source');
    if (isset($upload['error'])) {
      return ['error' => $upload['error']];
    }

    $image_name = $upload['name'];
    $original_path = $upload['path'];
    $this->convert_to_webp(
      $original_path,
      ROOTPATH . 'assets/imgs/posts/webp/' . $image_name . '.webp'
    );
    $this->resize_images($original_path, $image_name);
    
    $data = array(
      'title' => $title,
      'slug' => $slug,
      'body' => $request->getPost('body'),
      'category_id' => $request->getPost('category_id'),
      'user_id' => session()->get('user_id'),
      'state' => 'draft',
      'image_name' => $image_name
    );
    return $this->db->table('posts')->insert($data);
  }

  public function delete_post($id, $image_name){
    $this->db->table('posts')
      ->where('id', $id)
      ->delete();

    if($image_name){
      $png_path = ROOTPATH . 'assets/imgs/posts/' . $image_name . '.png';
      $webp_path = ROOTPATH . 'assets/imgs/posts/webp/' . $image_name . '.webp';

      if (file_exists($png_path)) {
        unlink($png_path);
      }

      if (file_exists($webp_path)) {
        unlink($webp_path);
      }
    }
  }

  public function update_post(){
    helper(['text', 'url']);
    $request = service('request');
    $id = $request->getPost('id');
    $title = $request->getPost('title');
    $slug = url_title(convert_accented_characters($title), 'dash', true);

    $current_post = $this->db->table('posts')
      ->where('id', $id)
      ->get()
      ->getRowArray();

    if ($current_post === null) {
      return ['slug' => $slug, 'error' => 'Post not found.'];
    }

    $image_name = $current_post['image_name'];
    $upload = $this->upload_png(false, '');
    if (isset($upload['error'])) {
      return ['slug' => $slug, 'error' => $upload['error']];
    }

    if ($upload !== null) {
      $original_path = $upload['path'];
      if (!empty($current_post['image_name'])) {
        $old_image_paths = [
          ROOTPATH . 'assets/imgs/posts/' . $current_post['image_name'] . '.png',
          ROOTPATH . 'assets/imgs/posts/webp/' . $current_post['image_name'] . '.webp',
        ];
        foreach ($this->target_widths as $w) {
          $old_image_paths[] = ROOTPATH . 'assets/imgs/posts/' . $current_post['image_name'] . '-' . $w . '.png';
          $old_image_paths[] = ROOTPATH . 'assets/imgs/posts/webp/' . $current_post['image_name'] . '-' . $w . '.webp';
        }
        foreach ($old_image_paths as $path) {
          if (is_file($path)) {
            unlink($path);
          }
        }
      }

      $image_name = $upload['name'];
      $this->convert_to_webp(
        $original_path,
        ROOTPATH . 'assets/imgs/posts/webp/' . $image_name . '.webp'
      );
      $this->resize_images($original_path, $image_name);
    }
    
    $data = array(
      'title' => $title,
      'slug' => $slug,
      'body' => $request->getPost('body'),
      'category_id' => $request->getPost('category_id'),
      'modified_at' => date('Y-m-d H:i:s'),
      'state' => $request->getPost('state'),
      'image_name' => $image_name
    );
    return $this->db->table('posts')
      ->where('id', $id)
      ->update($data);
  }

  public function get_posts_by_category($category){
    $sql = ' SELECT p.id, date_format(created_at, "%d %M %Y") as created_at_fr, p.title, p.body, p.user_id, p.slug, p.state, p.image_name
      FROM posts p
      WHERE p.state = "published" AND p.category_id = ?
      ORDER BY created_at DESC
    ';
    $posts = $this->db->query($sql, [$category['id']])->getResultArray();

    foreach ($posts as &$post) {
      $post['category_name'] = $category['name'];
      $post['category_slug'] = $category['slug'];
    }

    return $posts;
  }

  private function skip_accents( $str, $charset='utf-8' ) {
    $str = htmlentities( $str, ENT_NOQUOTES, $charset );
    $str = preg_replace( '#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str );
    $str = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $str );
    $str = preg_replace( '#&[^;]+;#', '', $str );
    return $str;
  }
}