<?php

/*

This script saves all 'professions de foi' for the following election: 2024 legislative election in France.
The id of this election on datan.fr is '6'

*/

include "../lib/simplehtmldom_1_9/simple_html_dom.php";

class Script
{
    private $bdd;
    private $legislature_to_get;
    private $electionId;
    private $intro;
    private $urlAjax;
    private $urlPdf;
    private $fields;

    // export the variables in environment
    public function __construct($legislature = 17)
    {
        date_default_timezone_set('Europe/Paris');
        ini_set('memory_limit', '2048M');
        $this->legislature_current = 17;
        $this->legislature_to_get = $legislature;
        $this->intro = "[" . date('Y-m-d h:i:s') . "] ";
        $this->electionId = 6;
        $this->fields = array("mpId", "file", "tour", "electionId");
        $this->time_pre = microtime(true);;
        echo $this->intro . "Launching the profession de foi script for legislature " . $this->legislature_to_get . "\n";
        
        $this->time_pre = microtime(true);;
        try {
          $this->bdd = new PDO('mysql:host='. $_SERVER['DATABASE_HOST'] . ';dbname='.$_SERVER['DATABASE_NAME'], $_SERVER['DATABASE_USERNAME'], $_SERVER['DATABASE_PASSWORD'], array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
          ));
        } catch (Exception $e) {
          die('Erreur : ' . $e->getMessage());
        }
    }

    function __destruct()
    {
        $time_post = microtime(true);
        $exec_time = $time_post - $this->time_pre;
        echo "Script is over ! It took: " . round($exec_time, 2) . " seconds.\n";
    }

    public function execute()
    {

        // TOUR 1
        $csv_1 = "data/legislatives-2024-candidatures-france-entiere-tour-1-2024-06-28.csv";
        $array = $this->csvToArray($csv_1);

        //$array = array_splice($array, 760, 80);


        echo "<br>";        
        foreach ($array as $candidate) {
            //echo "<br>";
            //print_r($candidate);
            //echo "<br>";
            $dpt_source = $candidate['Code département'];
            $circo_source = $candidate['Code circonscription'];
            $nameLast = $candidate['Nom du candidat'];
            $nameFirst = $candidate['Prénom du candidat'];
            $n = $candidate['Numéro de panneau'];

            // Format 'dpt' and 'circo'
            if ($dpt_source == '2A' || $dpt_source == '2B') {
                $dpt = $dpt_source;
                $circo = mb_substr($circo_source, 2);         
            } elseif ($dpt_source == "ZX") {
                $dpt = 977;
                $circo = mb_substr($circo_source, 2); 
            } elseif ($dpt_source == "ZZ") {
                $dpt = "099";
                $circo = mb_substr($circo_source, 2); 
            } else if ($dpt_source < 10) {
                $dpt = "0" . $dpt_source;
                $circo = mb_substr($circo_source, 1);
            } elseif (in_array($dpt_source, array(971, 972, 973, 974, 975, 976, 986, 987, 988))) {
                $dpt = $dpt_source;
                $circo = mb_substr($circo_source, 3);
            } else {
                $dpt = $dpt_source;
                $circo = mb_substr($circo_source, 2);
            }

            $circo = $this->removeLeadingZero($circo);            

            $query = $this->bdd->prepare("SELECT * FROM  deputes_last WHERE legislature IN (16, 17) AND departementCode=? AND circo=? AND LOWER(nameLast)=LOWER(?) AND LOWER(nameFirst)=LOWER(?)");
            $query->execute(array($dpt, $circo, $nameLast, $nameFirst));
            $depute = $query->fetch();

            if ($depute) {
                if ($dpt_source == "2A" || $dpt_source == "2B") {
                    $dpt_source = $dpt_source;
                    $circo_source = $circo_source;
                } elseif ($dpt_source < 10) {
                    $dpt_source = "0" . $dpt_source;
                    $circo_source = "0" . $circo_source;
                }

                $filename = "https://programme-candidats.interieur.gouv.fr/elections-legislatives-2024/data-pdf-propagandes/1-" . $dpt_source . "-" . $circo_source . "-" . $n . ".pdf";

                echo $dpt . " - " . $circo;
                echo " - " . $nameFirst . " " . $nameLast;
                echo " - <span style='color: red'>" . $depute['nameFirst'] . " " . $depute['nameLast'] . "</span>";
                echo " - <a href='".$filename."'>LINK</a>";
                echo "<br>";

                $this->saveProfession($depute, $filename, $tour);
            }            

        }
        die();
    }

    private function saveProfession($depute, $filename, $tour){
        try {
            $name = str_replace("https://programme-candidats.interieur.gouv.fr/elections-legislatives-2024/data-pdf-propagandes/", "", $filename);
            file_put_contents('assets/data/professions/election_' . $this->electionId . '/1.pdf', file_get_contents($filename));
            $sql = "INSERT INTO `profession_foi` (" . implode(",", $this->fields) . ") VALUES (?, ?, ?, ?)";
            $stmt = $this->bdd->prepare($sql);
            $stmt->execute(array($depute["mpId"], $name, $tour, $this->electionId));
            echo $name . " a ete ajoute pour " . $depute["nameLast"] . "\n";
        } catch (\Exception $e) {
            echo "Error : " . $e->getMessage() . "\n";
        }
    }

    private function csvToArray($filename, $delimiter = ';') {
        // Check if the file exists
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }
    
        $header = null;
        $data = array();
    
        // Open the CSV file
        if (($handle = fopen($filename, 'r')) !== false) {
            // Loop through each row
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                // If the header is null, use the first row as header
                if (!$header) {
                    $header = $row;
                } else {
                    // Combine header with row data
                    $data[] = array_combine($header, $row);
                }
            }
            // Close the file
            fclose($handle);
        }
    
        return $data;
    }

    private function removeLeadingZero($string) {
        // Check if the first character is '0'
        if ($string[0] === '0') {
            // Remove the first character
            return substr($string, 1);
        }
        // Return the original string if the first character is not '0'
        return $string;
    }
}

if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

$script->execute();
