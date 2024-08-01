<?php

/*

This script saves all 'professions de foi' for the following election: 2022 legislative election in France.
The id of this election on datan.fr is '4'

*/

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
    public function __construct($legislature = 16)
    {
        date_default_timezone_set('Europe/Paris');
        ini_set('memory_limit', '2048M');
        $this->legislature_current = 16;
        $this->legislature_to_get = $legislature;
        $this->intro = "[" . date('Y-m-d h:i:s') . "] ";
        $this->electionId = 4;
        $this->fields = array("mpId", "file", "tour", "electionId");
        $this->time_pre = microtime(true);;
        echo $this->intro . "Launching the profession de foi script for legislature " . $this->legislature_to_get . "\n";
        if ($legislature == 16) {
            $this->urlAjax = "https://programme-candidats.interieur.gouv.fr/elections-legislatives-2022/ajax/";
            $this->urlPdf = "https://programme-candidats.interieur.gouv.fr/elections-legislatives-2022/data-pdf-propagandes/";
        } else {
            $this->urlAjax = "https://programme-candidats.interieur.gouv.fr/elections-legislatives-2022/ajax/";
            $this->urlPdf = "https://programme-candidats.interieur.gouv.fr/elections-legislatives-2022/data-pdf-propagandes/";
        }
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
        $circos = $this->bdd->prepare('SELECT c.dpt, c.dpt_nom, c.insee, c.circo FROM circos c GROUP BY c.circo, c.dpt ORDER BY c.dpt, c.circo');
        $circos->execute();
        foreach ($circos as $circo) {
            for ($tour = 1; $tour < 3; $tour++) {
                $data = $this->getData($tour, $circo);
                if ($data) {
                    foreach ($data as $d) {
                        $q = $this->bdd->prepare("SELECT * FROM  deputes_last WHERE legislature IN (15, 16) AND departementCode=? AND circo=? AND LOWER(nameLast)=LOWER(?) AND LOWER(nameFirst)=LOWER(?)");
                        $q->execute(array($circo['dpt'], $circo['circo'], $d['candidatNom'], $d['candidatPrenom']));
                        $depute = $q->fetch();
                        $filename = $d['pdf'] != "0" ? $d['pdf'] : (isset($d['pdf_acc'])
                        && $d['pdf_acc'] != "0" ? $d['pdf_acc'] : false);
                        if($depute && $filename){
                            $this->saveProfession($depute, $filename, $tour);
                        }
                    }
                }
            }
        }
    }

    private function saveProfession($depute, $filename, $tour){
        try {
            $filename .= ".pdf";
            file_put_contents('assets/data/professions/election_' . $this->electionId . '/' . $filename, file_get_contents($this->urlPdf . $filename));
            $sql = "INSERT INTO `profession_foi` (" . implode(",", $this->fields) . ") VALUES (?, ?, ?, ?)";
            $stmt = $this->bdd->prepare($sql);
            $stmt->execute(array($depute["mpId"], $filename, $tour, $this->electionId));
            echo $filename . " a ete ajoute pour " . $depute["nameLast"] . "\n";
        } catch (\Exception $e) {
            echo "Error : " . $e->getMessage() . "\n";
        }
    }

    private function getData($tour, $circo)
    {
        if ($circo['circo'] < 10) {
          $circo['circo'] = '0' . $circo['circo'];
        }

        $url = $this->urlAjax . $tour . '_candidats_circo_' . $circo['dpt'] . '-' . $circo['circo'] . '.json';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $response_json = curl_exec($ch);
        // check if curl_exec was successful, else return an error message
        if(curl_exec($ch) === false) {
          echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        echo "check " .$url . "\n";
        return json_decode($response_json, true)["data"];
    }
}

if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

$script->execute();
