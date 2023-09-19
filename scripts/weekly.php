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
        $this->dateMaj = date('Y-m-d H:i:s');
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
      $fields = array('mpId', 'url', 'category', 'value', 'employeur', 'conservee', 'dateDebut', 'dateFin');
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
                $employeur = $activity->employeur;
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
                  'employeur' => $employeur,
                  'conservee' => $conservee,
                  'dateDebut' => $dateDebut,
                  'dateFin' => $dateFin
                );
                $array = array_merge($array, array_values($item));
                if ($i % 20 === 0) {
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

    public function mayors(){
      echo "mayors starting \n";

      $this->bdd->query('DROP TABLE IF EXISTS cities_mayors');
      $this->bdd->query('
        CREATE TABLE IF NOT EXISTS `cities_mayors` (
          `dpt` varchar(5) DEFAULT NULL,
          `libelle_dpt` text,
          `insee` varchar(6) DEFAULT NULL,
          `libelle_commune` text NOT NULL,
          `nameLast` text,
          `nameFirst` text,
          `gender` varchar(2) DEFAULT NULL,
          `birthDate` date DEFAULT NULL,
          `profession` smallint(6) DEFAULT NULL,
          `libelle_profession` text,
          `dateMaj` date NOT NULL,
          KEY `idx_dpt` (`dpt`),
          KEY `idx_insee` (`insee`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
      ');

      $importArray = [];
      $fields = array('dpt', 'libelle_dpt', 'insee', 'libelle_commune', 'nameLast', 'nameFirst', 'gender', 'birthDate', 'profession', 'libelle_profession', 'dateMaj');

      $url = "https://www.data.gouv.fr/fr/datasets/r/2876a346-d50c-4911-934e-19ee07b0e503";
      $lines = file($url, FILE_IGNORE_NEW_LINES);

      $i = 1;

      foreach ($lines as $key => $value) {
        $value = utf8_encode($value);
        $array = preg_split("/[\t]/", $value);
        if ($array[0] != "Titre du rapport" && $array[0] != "Code du département (Maire)" && $array[0] != "code_departement") {

          $dpt = utf8_decode($array[0]);
          $libelle_dpt = utf8_decode($array[1]);
          $insee = utf8_decode($array[4]);
          $libelle_commune = utf8_decode($array[5]);
          $nameLast = utf8_decode($array[6]);
          $nameFirst = utf8_decode($array[7]);
          $gender = utf8_decode($array[8]);
          $birthDate = utf8_decode($array[9]);
          $profession = utf8_decode($array[11]);
          $libelle_profession = utf8_decode($array[12]);

          $birthDate = str_replace("/", "-", $birthDate);
          $birthDate = date("Y-m-d", strtotime($birthDate));

          if ($profession == "") $profession = null;

          if ($libelle_profession == "") $libelle_profession = null;

          switch ($dpt) {
            case '2A':
              $dpt = '2a';
              break;
            case '2B':
              $dpt = '2b';
              break;
            case 'ZA':
              $dpt = 971;
              break;
            case 'ZB':
              $dpt = 972;
              break;
            case 'ZC':
              $dpt = 973;
              break;
            case 'ZD':
              $dpt = 974;
              break;
            case 'ZM':
              $dpt = 976;
              break;
            case 'ZN':
              $dpt = 988;
              break;
            case 'ZP':
              $dpt = 987;
              break;
            case 'ZS':
              $dpt = 975;
              break;
            case 'ZW':
              $dpt = 986;
              break;
            case 'ZX':
              $dpt = 977;
              break;
            case 'ZZ':
              $dpt = "099";
              break;
            default:
              $dpt;
              break;
          }


          //echo $i." - ".$dpt." - ".$libelle_dpt." - ".$insee." - ".$libelle_commune." - ".$nameLast." - ".$nameFirst." - ".$gender." - ".$birthDate." - ".$profession." - ".$libelle_profession."<br>";

          $item = array(
            'dpt' => $dpt,
            'libelle_dpt' => $libelle_dpt,
            'insee' => $insee,
            'libelle_commune' => $libelle_commune,
            'nameLast' => $nameLast,
            'nameFirst' => $nameFirst,
            'gender' => $gender,
            'birthDate' => $birthDate,
            'profession' => $profession,
            'libelle_profession' => $libelle_profession,
            'dateMaj' => $this->dateMaj
          );
          $importArray = array_merge($importArray, array_values($item));

          if ($i % 250 === 0) {
            echo "Let's import until n " . $i . "\n";
            $this->insertAll('cities_mayors', $fields, $importArray);
            $importArray = [];
          }
          $i++;
        }
      }
      $this->insertAll('cities_mayors', $fields, $importArray);
    }

}

// Specify the legislature
if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

$script->hatvpScrapping();
$script->mayors();
