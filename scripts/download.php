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
    
    public function chunked_copy($from, $to, $max_retries = 50) {
      $retry_count = 0;
      $backoff = 2; // initial backoff in seconds
      
      while ($retry_count < $max_retries) {
        try {
          echo "Attempt " . ($retry_count + 1) . " of $max_retries...\n";

          // Determine start byte if file already partially exists
          $start = file_exists($to) ? filesize($to) : 0;
          $appendMode = $start > 0;

          // Open destination file in append or write mode
          $fout = fopen($to, $appendMode ? 'ab' : 'wb');
          if ($fout === false) {
            throw new Exception("Failed to open destination: $to");
          }
          
          // Initialise cURL 
          $ch = curl_init($from);
          curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
          //curl_setopt($ch, CURLOPT_FILE, $fout);
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
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Connection: close',
            'Accept-Encoding: identity',
            'Expect:'
          ]);
          curl_setopt($ch, CURLOPT_ENCODING, 'identity');
          $downloaded = 0;
          curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use (&$downloaded, $fout) {
            $len = strlen($chunk);
            $downloaded += $len;
            
            fwrite($fout, $chunk);
            echo "Chunk: $len bytes, total: $downloaded bytes\n";
            
            return $len;
          });

          // Resume download if needed
          if ($appendMode && $start > 0) {
            echo "start === $start...\n";
            curl_setopt($ch, CURLOPT_RESUME_FROM, $start);
          }

          // Execute download
          $success = curl_exec($ch);
          if (!$success) {
            $error = curl_error($ch);
            curl_close($ch);
            fclose($fout);
            throw new Exception("cURL error: $error");
          }

          // Check HTTP status code
          $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          fclose($fout);

          if ($http_code >= 400) {
            throw new Exception("HTTP error $http_code when downloading $from");
          }

          // Verify file
          $filesize = filesize($to);
          if ($filesize === 0) {
            throw new Exception("Download resulted in empty file");
          }

          echo "Download complete. Size = $filesize bytes (" . round($filesize / 1048576, 2) . " MB)\n";
          return true;
            
        } catch (Exception $e) {
          echo "Error: " . $e->getMessage() . "\n";
          $retry_count++;

          if ($retry_count < $max_retries) {
            echo "Waiting {$backoff} seconds before retry...\n";
            sleep($backoff);
            //$backoff += 5; // exponential backoff
          } else {
            echo "Failed after $max_retries attempts\n";
            return false;
          }
        }
      }
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