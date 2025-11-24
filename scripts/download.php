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
    
    public function chunked_copy($from, $to, $max_retries = 3) {
      $buffer_size = 1048576; // 1 MB chunks
      $retry_count = 0;
      
      while ($retry_count < $max_retries) {
        try {
          echo "Attempt " . ($retry_count + 1) . " of $max_retries...\n";
            
          // Create stream context with proper settings
          $context = stream_context_create([
            'http' => [
              'timeout' => 300,  // 5 minutes timeout
              'user_agent' => 'Mozilla/5.0 (compatible; PHP Script)',
              'follow_location' => true,
              'max_redirects' => 5,
              'ignore_errors' => false
            ]
          ]);
          
          // Open source with context
          $fin = @fopen($from, "rb", false, $context);
          if ($fin === false) {
            throw new Exception("Failed to open source: $from");
          }
            
          // Set read timeout on the stream
          stream_set_timeout($fin, 300);
           
          // Open destination
          $fout = @fopen($to, "w");
          if ($fout === false) {
            fclose($fin);
            throw new Exception("Failed to open destination: $to");
          }
            
          $ret = 0;
          $last_progress = 0;
            
          while (!feof($fin)) {
            // Check for timeout
            $meta = stream_get_meta_data($fin);
            if ($meta['timed_out']) {
              throw new Exception("Stream timeout during download");
            }
                
            // Read chunk
            $data = fread($fin, $buffer_size);
            if ($data === false) {
              throw new Exception("Read error during download");
            }
                
            // Write chunk
            $written = fwrite($fout, $data);
            if ($written === false) {
              throw new Exception("Write error during download");
            }
                
            $ret += $written;
                
            // Show progress every 10MB
            if ($ret - $last_progress >= 10 * 1048576) {
              echo "Downloaded: " . round($ret / 1048576, 2) . " MB\n";
              $last_progress = $ret;
            }
          }
            
          fclose($fin);
          fclose($fout);
            
          echo "Download complete. Size = " . $ret . " bytes (" . round($ret / 1048576, 2) . " MB)\n";
            
          // Verify file was actually written
          if ($ret == 0 || !file_exists($to)) {
            throw new Exception("Download resulted in empty file");
          }
            
          return true;
            
        } catch (Exception $e) {
          echo "Error: " . $e->getMessage() . "\n";
            
          // Clean up partial file
          if (isset($fout) && is_resource($fout)) {
            fclose($fout);
          }
          if (isset($fin) && is_resource($fin)) {
            fclose($fin);
          }
          if (file_exists($to)) {
            unlink($to);
          }
            
          $retry_count++;
            
          if ($retry_count < $max_retries) {
            $wait_time = $retry_count * 5; // 5s, 10s, 15s backoff
            echo "Waiting {$wait_time} seconds before retry...\n";
            sleep($wait_time);
          }
        }
      }
      
      echo "Failed after $max_retries attempts\n";
      return false;
    }

    public function dossiers(){
      echo "downloading dossiers starting \n";

      if ($this->legislature_to_get == 16) {
        $file = 'https://data.assemblee-nationale.fr/static/openData/repository/16/loi/dossiers_legislatifs/Dossiers_Legislatifs.xml.zip';
        $newfile = __DIR__ . '/Dossiers_Legislatifs_XVI.xml.zip';
      } elseif($this->legislature_to_get == 17) {
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/17/loi/dossiers_legislatifs/Dossiers_Legislatifs.xml.zip';
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
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/17/loi/scrutins/Scrutins.xml.zip';
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
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/17/loi/amendements_div_legis/Amendements.xml.zip';
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
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/17/questions/questions_gouvernement/Questions_gouvernement.xml.zip';
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
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/17/questions/questions_orales_sans_debat/Questions_orales_sans_debat.xml.zip';
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
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/17/questions/questions_ecrites/Questions_ecrites.xml.zip';
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

$script->dossiers();
$script->scrutins();
$script->acteurs_organes();
$script->amendements();
$script->comptes_rendus();
$script->reunions();
$script->questions_gvt();
$script->questions_orales();
$script->questions_ecrites();