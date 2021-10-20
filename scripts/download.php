<?php

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
        echo date('Y-m-d') . " : Launching the download script for legislature " . $this->legislature_to_get . "\n";
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

    public function amendements(){
      echo "downloading amendements starting \n";

      $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/amendements_legis/Amendements_XV.xml.zip';
      $file = trim($file);
      $newfile = __DIR__ . '/tmp_amendements.zip';
      if (!copy($file, $newfile)) {
          echo "failed to copy $file...\n";
      } else {
        echo 'working';
      }

    }

}

// Specify the legislature
if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

$script->amendements();
