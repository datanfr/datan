<?php
include "lib/simplehtmldom_1_9/simple_html_dom.php";
include "include/json_minify.php";
class Script
{
    private $bdd;
    private $legislature_to_get;
    private $dateMaj;
    private $time_pre;

    // export the variables in environment
    public function __construct($legislature = 15)
    {
        ini_set('memory_limit', '2048M');
        $this->legislature_to_get = $legislature;
        $this->dateMaj = date('Y-m-d');
        echo date('Y-m-d') . " : Launching the daily script for legislature " . $this->legislature_to_get . "\n";
        $this->time_pre = microtime(true);;
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

    private function insertAll($table, $fields, $datas)
    {
        if (count($datas) > 0) {
            try {
                $update = "";
                $values = substr(str_repeat("(" . substr(str_repeat('?,', count($fields)), 0, -1) . "),", count($datas) / count($fields)), 0, -1);
                foreach ($fields as $field) {
                    $update .= $field . "=VALUES(" . $field . "),";
                }
                $update = substr($update, 0, -1);
                // SQL //
                $sql = "INSERT INTO " . $table . " (" . implode(",", $fields) . ") VALUES " . $values
                    . " ON DUPLICATE KEY UPDATE " . $update;
                $stmt = $this->bdd->prepare($sql);
                $stmt->execute($datas);
                echo $table . " inserted\n";
            } catch (Exception $e) {
                echo "Error inserting : " . $table . "\n" . $e->getMessage() . "\n";
                die;
            }
        } else {
            echo "Nothing to insert in " . $table . "\n";
        }
    }

    public function hatvpScrapping(){
      echo "hatvpScrapping starting \n";
      $fields = array('mpId', 'url', 'category', 'value', 'conservee', 'dateDebut', 'dateFin');
      $array = [];
      $i = 1;
      // Context for 403 security on the website
      $context = stream_context_create(
          array(
              "http" => array(
                  "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
              )
          )
      );
      $this->bdd->query('TRUNCATE TABLE hatvp');
      $query_mps = $this->bdd->query('SELECT d.mpId, d.hatvp
        FROM deputes d
        LEFT JOIN deputes_last dl ON d.mpId = dl.mpId
        WHERE hatvp != "" AND dl.legislature = "'.$this->legislature_to_get.'"
      ');

      while ($mp = $query_mps->fetch()) {
        $mpId = $mp['mpId'];
        $hatvpUrl = $mp['hatvp'];
        //echo $hatvpUrl . '<br>';

        // Scrapping
        $content = file_get_contents($hatvpUrl, false, $context);
        $html = str_get_html($content);

        // Check if the button to the XML file exists
        if (!empty($html->find('.download-declaration-buttons', 0))) {
          $xmlUrl = $html->find('.download-declaration-buttons', 0)->find('a', 1)->href;
          //echo $xmlUrl . '<br>';

          // Check if the XML file exists
          $xml = @simplexml_load_file($xmlUrl);
          if ($xml) {
            $xml = simplexml_load_file($xmlUrl);

            $activities = $xml->activProfCinqDerniereDto->items;
            if (count($activities) > 0) {
              foreach ($activities->items as $activity) {
                //echo $mpId;
                $description = (string) $activity->description;
                $description = str_replace("[Données non publiées]", "", $description);
                $description = preg_replace('/\s+/', ' ', $description);
                $conservee = $activity->conservee;
                $conservee = $conservee == "true" ? 1 : 0;
                $dateDebut = (string) $activity->dateDebut;
                if ($dateDebut !== "") {
                  $dateDebut = substr($dateDebut, 3, 4) . '-' . substr($dateDebut, 0, 2). '-01';
                } else {
                  $dateDebut = NULL;
                }
                $dateFin = (string) $activity->dateFin;
                if ($dateFin !== "") {
                  $dateFin = substr($dateFin, 3, 4) . '-' . substr($dateFin, 0, 2). '-01';
                } else {
                  $dateFin = NULL;
                }

                //echo $description . '<br>';
                //echo $dateDebut . '<br>';
                //echo $dateFin . '<br>';

                $item = array(
                  'mpId' => $mpId,
                  'url' => $xmlUrl,
                  'category' => 'activProf',
                  'value' => $description,
                  'conservee' => $conservee,
                  'dateDebut' => $dateDebut,
                  'dateFin' => $dateFin
                );
                $array = array_merge($array, array_values($item));
                if ($i % 100 === 0) {
                  echo "Let's import until n " . $i . "\n";
                  $this->insertAll('hatvp', $fields, $array);
                  $array = [];
                }
                $i++;

              }
            }
          }
        }
      }
      $this->insertAll('hatvp', $fields, $array);
    }

    public function hatvpCleaning(){

      // A faire :) 

    }
}

// Specify the legislature
if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

$script->hatvpScrapping();
$script->hatvpCleaning();
