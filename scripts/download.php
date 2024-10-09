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

    public function chunked_copy($from, $to) {
      # 1 meg at a time, you can adjust this.
      $buffer_size = 1048576;
      $ret = 0;
      $fin = fopen($from, "rb");
      $fout = fopen($to, "w");
      while(!feof($fin)) {
        $ret += fwrite($fout, fread($fin, $buffer_size));
      }
      fclose($fin);
      fclose($fout);
      echo 'size = ' . $ret . '\n'; # return number of bytes written
      return TRUE;
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

      if ($this->legislature_to_get == 16) {
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
//$script->amendements();
