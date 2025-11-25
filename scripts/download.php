<?php

class Script
{
    private $bdd;
    private $legislature_to_get;
    private $dateMaj;
    private $time_pre;

    // export the variables in environment
    public function __construct($legislature = 17)
    {
        ini_set('memory_limit', '2048M');
        $this->legislature_to_get = $legislature;
        $this->dateMaj = date('Y-m-d');
        echo date('Y-m-d') . " : Launching the download script for legislature " . $this->legislature_to_get . "\n";
        $this->time_pre = microtime(true);;
        try {
            $this->bdd = new PDO(
                'mysql:host=' . $_SERVER['DATABASE_HOST'] . ';dbname=' . $_SERVER['DATABASE_NAME'], $_SERVER['DATABASE_USERNAME'], $_SERVER['DATABASE_PASSWORD'],
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
    
    public function chunked_copy($from, $to, $max_retries = 10, $backoff = 2) {
      $attempt = 0;
      $start = file_exists($to) ? filesize($to) : 0;
      
      while ($attempt < $max_retries) {
        $beforeSize = file_exists($to) ? filesize($to) : 0;
        echo "File size before attempt: $beforeSize bytes\n";

        $appendMode = false;
        $file = null;
        $ch = null;

        if ($start > 0) {
          echo "Attempting ". ($attempt + 1) . " to resume download from byte: $start...\n";
          $headers['Range'] = "bytes=$start-";
          $appendMode = true;
        } else {
          echo "Attempt " . ($attempt + 1) . "...\n";
        }

        try {
          $ch = curl_init($from);
          curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
          curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; PHP Script)');
          curl_setopt($ch, CURLOPT_TIMEOUT, 300);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
          curl_setopt($ch, CURLOPT_BUFFERSIZE, 128 * 1024); // 128 KB chunks
          curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 1);
          curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 30);
          curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
          curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
          curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
          curl_setopt($ch, CURLOPT_ENCODING, 'identity');

          $file = fopen($to, $appendMode ? 'ab' : 'wb');
          if ($file === false) {
            throw new Exception("Failed to open destination :$to");
          }
          
          curl_setopt($ch, CURLOPT_FILE, $file);
          $result = curl_exec($ch);
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $afterSize = file_exists($to) ? filesize($to) : 0;
          echo "File size after attempt: $afterSize bytes\n";  

          if ($result === false) {
            $error = curl_error($ch);
            throw new Exception("cURL error: $error");
          }

          if ($appendMode && $httpCode !== 206) {
            if ($httpCode === 200) {
              // Server ignored Range header (Status 200). Restarting full download from byte 0.
              fclose($file);
              $file = fopen($to, 'wb');
              if ($file === false) {
                throw new Exception("Failed to open file $to for writing");
              }
              curl_setopt($ch, CURLOPT_FILE, $file);
              $result = curl_exec($ch);
              $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
              if ($result === false) {
                $error = curl_error($ch);
                throw new Exception("cURL error: $error");
              }
            } else {
              throw new Exception("Failed to resume fetch with status $httpCode for $from");
            }
          } elseif ($start === 0 && $httpCode !== 200) {
            throw new Exception("Failed to start fetch with status $httpCode for $from");
          }

          fclose($file);
          curl_close($ch);
          return;

        } catch (Exception $error) {
          if ($file !== null) fclose($file);
          if ($ch !== null) curl_close($ch);

          $afterSize = file_exists($to) ? filesize($to) : 0;          

          if($attempt >= $max_retries - 1){
            echo "Final fetch attempt for $from failed with error: " . $error->getMessage() . "\n";
            throw $error;
          } 

          echo "Fetch attempt " . ($attempt + 1) . " for $from failed with error: " . $error->getMessage() . "\n";

          $newStart = file_exists($to) ? filesize($to) : 0;
          if ($newStart > $start) {
            // Some data was written, resume from new offset
            $start = $newStart;
            $max_retries++; // Give an extra attempt since progress was made
            echo "Resuming fetch at offset $start...\n";
          } else {
            // No progress, exponential backoff
            echo "Nothing downloaded ⇒ retrying in " . ($backoff / 1000) . " s at offset $start...\n";
            usleep($backoff * 1000);
            $backoff = min($backoff * 2, 60); // Cap backoff at 60 seconds
          }
          $attempt++;          
        }
      }
      // Only delete the file if all retries failed and nothing was downloaded
      if (file_exists($to) && filesize($to) === 0) {
          unlink($to);
      }
      throw new Exception("Failed to fetch $from after $max_retries attempts");
    }

    public function dossiers(){
      echo "downloading dossiers starting \n";

      if ($this->legislature_to_get == 16) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/16/loi/dossiers_legislatifs/Dossiers_Legislatifs.xml.zip';
        $newfile = __DIR__ . '/Dossiers_Legislatifs_XVI.xml.zip';
      } elseif($this->legislature_to_get == 17) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/loi/dossiers_legislatifs/Dossiers_Legislatifs.xml.zip';
        $newfile = __DIR__ . '/Dossiers_Legislatifs_XVII.xml.zip';
      } else {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/15/loi/dossiers_legislatifs/Dossiers_Legislatifs_XV.xml.zip';
        $newfile = __DIR__ . '/Dossiers_Legislatifs_XV.xml.zip';
      }

      if ($this->chunked_copy($file, $newfile)) {
        echo "Success. Copied $newfile \n";
      } else {
        echo "failed to copy $newfile \n";
      }
    }

    public function scrutins(){
      echo "downloading scrutins starting \n";

      if ($this->legislature_to_get == 16) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/16/loi/scrutins/Scrutins.xml.zip';
        $newfile = __DIR__ . '/Scrutins_XVI.xml.zip';
      } elseif($this->legislature_to_get == 17) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/loi/scrutins/Scrutins.xml.zip';
        $newfile = __DIR__ . '/Scrutins_XVII.xml.zip';
      } else {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/15/loi/scrutins/Scrutins_XV.xml.zip';
        $newfile = __DIR__ . '/Scrutins_XV.xml.zip';
      }

      if ($this->chunked_copy($file, $newfile)) {
        echo "Success. Copied $newfile \n";
      } else {
        echo "failed to copy $newfile \n";
      }
    }

    public function acteurs_organes(){
      echo "downloading acteurs_organes starting \n";

      if ($this->legislature_to_get == 17) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
        $newfile = __DIR__ . '/AMO30_tous_acteurs_tous_mandats_tous_organes_historique_XVII.xml.zip';
      } elseif ($this->legislature_to_get == 16) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/16/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
        $newfile = __DIR__ . '/AMO30_tous_acteurs_tous_mandats_tous_organes_historique_XVI.xml.zip';
      } else {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/15/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
        $newfile = __DIR__ . '/AMO30_tous_acteurs_tous_mandats_tous_organes_historique_XV.xml.zip';
      }

      if ($this->chunked_copy($file, $newfile)) {
        echo "Success. Copied $newfile \n";
      } else {
        echo "failed to copy $newfile \n";
      }
    }

    public function amendements(){
      echo "downloading amendements starting \n";

      if ($this->legislature_to_get == 17) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/loi/amendements_div_legis/Amendements.xml.zip';
        $newfile = __DIR__ . '/Amendements_XVII.xml.zip';
      } elseif ($this->legislature_to_get == 16) {
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/16/loi/amendements_div_legis/Amendements.xml.zip';
        $newfile = __DIR__ . '/Amendements.xml.zip';
      } elseif ($this->legislature_to_get == 15) {
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/amendements_legis/Amendements_XV.xml.zip';
        $newfile = __DIR__ . '/Amendements_XV.xml.zip';
      }

      if ($this->chunked_copy($file, $newfile)) {
        echo "Success. Copied $newfile \n";
      } else {
        echo "failed to copy $newfile \n";
      }
    }

    public function comptes_rendus(){
      echo "downloading comptes rendus starting \n";
      $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/vp/syceronbrut/syseron.xml.zip';
      $newfile = __DIR__ . '/comptes_rendus_XVII.xml.zip';

      if ($this->chunked_copy($file, $newfile)) {
        echo "Success. Copied $newfile \n";
      } else {
        echo "failed to copy $newfile \n";
      }
    }

    public function reunions(){
      echo "downloading reunions starting \n";
      $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/vp/reunions/Agenda.xml.zip';
      $newfile = __DIR__ . '/reunions_XVII.xml.zip';

      if ($this->chunked_copy($file, $newfile)) {
        echo "Success. Copied $newfile \n";
      } else {
        echo "failed to copy $newfile \n";
      }
    }

    public function questions_gvt() {
      echo "downloading questions_gvt starting \n";

      if ($this->legislature_to_get == 17) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/questions/questions_gouvernement/Questions_gouvernement.xml.zip';
        $newfile = __DIR__ . '/questions_gvt_XVII.xml.zip';
        if ($this->chunked_copy($file, $newfile)) {
          echo "Success. Copied $newfile \n";
        } else {
          echo "failed to copy $newfile \n";
        }
      }      
    }

    public function questions_orales() {
      echo "downloading questions_orales starting \n";

      if ($this->legislature_to_get == 17) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/questions/questions_orales_sans_debat/Questions_orales_sans_debat.xml.zip';
        $newfile = __DIR__ . '/questions_orales_XVII.xml.zip';
        if ($this->chunked_copy($file, $newfile)) {
          echo "Success. Copied $newfile \n";
        } else {
          echo "failed to copy $newfile \n";
        }
      }      
    }

    public function questions_ecrites() {
      echo "downloading questions_ecrites starting \n";

      if ($this->legislature_to_get == 17) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/17/questions/questions_ecrites/Questions_ecrites.xml.zip';
        $newfile = __DIR__ . '/questions_ecrites_XVII.xml.zip';
        if ($this->chunked_copy($file, $newfile)) {
          echo "Success. Copied $newfile \n";
        } else {
          echo "failed to copy $newfile \n";
        }
      }      
    }
}

// Specify the legislature
if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

//$script->dossiers();
//$script->scrutins();
$script->acteurs_organes();
/*
$script->amendements();
$script->comptes_rendus();
$script->reunions();
$script->questions_gvt();
$script->questions_orales();
$script->questions_ecrites();