<?php
include "include/pdf2text.php";

class Script
{
    private $bdd;
    private $legislature_to_get;
    private $dateMaj;
    private $time_pre;
    private $legislature_current;
    private $intro;
    private $urlPDF;

    // export the variables in environment
    public function __construct($legislature = 16)
    {
        date_default_timezone_set('Europe/Paris');
        ini_set('memory_limit', '2048M');
        $this->legislature_current = 16;
        $this->legislature_to_get = $legislature;
        $this->intro = "[" . date('Y-m-d h:i:s') . "] ";
        echo $this->intro . "Launching the profession de foi script for legislature " . $this->legislature_to_get . "\n";
        echo getenv('DATABASE_USERNAME');
        if ($legislature == 16) {
            $this->urlPDF = "https://programme-candidats.interieur.gouv.fr/elections-legislatives-2022/data-pdf-propagandes/";
        } else {
            $this->urlPDF = "https://programme-candidats.interieur.gouv.fr/elections-legislatives-2022/data-pdf-propagandes/";
        }
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

    public function execute()
    {
        $circos = $this->bdd->prepare('SELECT * FROM circos GROUP BY circo, dpt');
        $circos->execute();
        $fields = array("depute_mpid", "file", "tour", "score");
        foreach ($circos as $key => $circo) {
            $deputes = $this->bdd->prepare("SELECT * FROM `deputes_all` WHERE `legislature`=" . $this->legislature_to_get . " AND `departementCode`=" . $circo['dpt'] . " AND `circo`=" . $circo['circo']);
            $deputes->execute();
            $deputes = $deputes->fetchAll();
            $programs = [];
            if (count($deputes) > 0) {
                for ($tour = 1; $tour < 3; $tour++) {
                    for ($i = 1; $i < 10; $i++) {
                        $url = $this->urlPDF . $tour . "-" . $circo['dpt'] . "-" . sprintf('%02d', $circo['circo']) . "-" . $i . ".pdf";
                        echo "checking " . $url . "\n";
                        $a = new PDF2Text();
                        $a->setFilename($url);
                        $a->decodePDF();
                        if ($text = strtolower($a->output())) {
                            foreach ($deputes as $depute) {
                                echo $depute["mpId"];
                                echo "with  " . $depute['nameLast'] . "\n";
                                $scoreMatch = $this->searchText($text, $depute);
                                if (!isset($programs[$depute["mpId"]]["score"]) || $scoreMatch > $programs[$depute["mpId"]]["score"]){
                                    $programs[$depute["mpId"]]["depute_mpid"] = $depute["mpId"];
                                    $programs[$depute["mpId"]]["file"] = $url;
                                    $programs[$depute["mpId"]]["tour"] = $tour;    
                                    $programs[$depute["mpId"]]["score"] = $scoreMatch;
                                }
                            }
                        } else {
                            echo "This url doesn't exists \n";
                        }
                    }
                }
            }
            foreach($programs as $program){
                try{
                    $filename = basename($program["file"]);
                    file_put_contents('assets/data/professions/' . $filename, file_get_contents($program["file"]));
                    $sql = "INSERT INTO `profession_foi` (" . implode(",", $fields) . ") VALUES (?, ?, ?, ?)";
                    $stmt = $this->bdd->prepare($sql);
                    $stmt->execute(array($program["depute_mpid"],$filename, $program["score"], $program["tour"]));
                    echo $program["file"] . " a ete ajoute pour " . $program["mpId"];
                } catch (\Exception $e){
                    echo "Error : " . $e->getMessage(). "\n";
                }
            }
        }
    }
    private function searchText($text, $depute)
    {
        $firstname = strtolower($depute['nameFirst']);
        $lastname = strtolower($depute['nameLast']);
        if (strpos($text, $firstname . ' ' . $lastname) !== false) {
            echo "This work for " . $firstname . ' ' . $lastname . "\n";
            return 10;
        } else if (strpos($text, $lastname) !== false) {
            echo "This may work for " . $firstname . ' ' . $lastname . "\n";
            return 3;
        }
        return 1;
    }
}

if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}

$script->execute();
