<?php
include "lib/simplehtmldom_1_9/simple_html_dom.php";
include "include/json_minify.php";
class Script
{
    private $bdd;
    private $time_pre;
    private $legislature;

    // export the variables in environment
    public function __construct($legislature = 16)
    {
        ini_set('memory_limit', '2048M');
        $this->dateMaj = date('Y-m-d H:i:s');
        echo date('Y-m-d') . " : Launching the daily script for legislature " . $legislature . "\n";
        $this->time_pre = microtime(true);
        $this->legislature = $legislature;
        try {
            $this->bdd = new PDO(
                'mysql:host=' . getenv('DATABASE_HOST') . ';dbname=' . getenv('DATABASE_NAME'),
                getenv('DATABASE_USERNAME'),
                getenv('DATABASE_PASSWORD'),
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                )
            );
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }


    function __destruct()
    {
        $time_post = microtime(true);
        $exec_time = $time_post - $this->time_pre;
        echo ("Script is over ! It took: " . round($exec_time, 2) . " seconds.\n");
    }

    public function remove_deputes($folder){

      echo "\n\n Start moving images in " . $folder . " folder \n\n";

      $query = $this->bdd->query('
          SELECT d.mpId AS uid, d.legislature
          FROM deputes_last d
          WHERE legislature = "'. $this->legislature .'"
      ');

      $newfolder = __DIR__ . "/../assets/imgs/".$folder."/archives/";
      if (!file_exists($newfolder)) mkdir($newfolder);

      $i = 1;

      while ($d = $query->fetch()) {
        $uid = substr($d['uid'], 2);

        if ($folder == 'deputes_ogp') {
          $image_name = 'ogp_deputes_PA' . $uid . '.png';
        } elseif ($folder == 'deputes_webp') {
          $image_name = 'depute_' . $uid . '_webp.webp';
        } elseif ($folder == 'deputes_nobg_webp') {
          $image_name = 'depute_' . $uid . '_webp.webp';
        } else {
          $image_name = 'depute_' . $uid . '.png';
        }

        $filename = __DIR__ . "/../assets/imgs/".$folder."/" . $image_name;

        if (file_exists($filename)) {
          $exists = "yes";
          $filename_archive = __DIR__ . "/../assets/imgs/".$folder."/archives/" . $image_name;
          if (file_exists($filename_archive)) {
            $exists_archive = "yes";
            $changed = "";
          } else {
            $exists_archive = "no";
            // Cut and paste the file in archive folder
            rename($filename, $filename_archive);
            $changed = "file changed";
          }
        } else {
          $exists_archive = "no";
          $exists = "no";
          $changed = "";
        }

        echo $i . ' - ' . $uid . ' - exists in original: ' . $exists . ' - exists in archive: ' . $exists_archive . ' - ' . $changed . "\n";
        $i++;
      }
    }

}

// Specify the legislature
if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

$script->remove_deputes('deputes_original');
$script->remove_deputes('deputes_nobg');
$script->remove_deputes('deputes_nobg_import');
$script->remove_deputes('deputes_ogp');
$script->remove_deputes('deputes_webp');
$script->remove_deputes('deputes_nobg_webp');
