
<?php
class Script
{
    private $bdd;

    // export the variables in environment
    public function __construct()
    {
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

    private function placeholders($text, $count = 0, $separator = ",")
    {
        $result = array();
        if ($count > 0) {
            for ($x = 0; $x < $count; $x++) {
                $result[] = $text;
            }
        }

        return implode($separator, $result);
    }

    public function fillDeputes()
    {
        $deputeFields = array('mpId', 'civ', 'nameFirst', 'nameLast', 'nameUrl', 'birthDate', 'birthCity', 'birthCountry', 'job', 'catSocPro', 'dateMaj');
        $this->bdd->query("TRUNCATE TABLE deputes");
        $dateMaj = date('Y-m-d');
        //Online file
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
        $file = trim($file);
        $newfile = 'tmp_acteurs_organes.zip';
        if (!copy($file, $newfile)) {
            echo "failed to copy $file...\n";
        }
        $zip = new ZipArchive();
        if ($zip->open($newfile) !== TRUE) {
            exit("cannot open <$file>\n");
        } else {
            $insert_values = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $sub = substr($filename, 0, 13);
                if ($sub == 'xml/acteur/PA') {
                    $xml_string = $zip->getFromName($filename);
                    if ($xml_string != false) {
                        $xml = simplexml_load_string($xml_string);

                        $mpId = str_replace("xml/acteur/", "", $filename);
                        $mpId = str_replace(".xml", "", $mpId);
                        $civ = $xml->etatCivil->ident->civ;
                        $nameFirst = $xml->etatCivil->ident->prenom;
                        $nameLast = $xml->etatCivil->ident->nom;
                        $birthDate = $xml->etatCivil->infoNaissance->dateNais;
                        $birthCity = $xml->etatCivil->infoNaissance->villeNais;
                        $birthCountry = $xml->etatCivil->infoNaissance->paysNais;
                        $job = $xml->profession->libelleCourant;
                        $catSocPro = $xml->profession->socProcINSEE->catSocPro;
                        $lastname = Transliterator::createFromRules(
                            ':: Any-Latin;'
                                . ':: NFD;'
                                . ':: [:Nonspacing Mark:] Remove;'
                                . ':: NFC;'
                                . ':: [:Space:] Remove;'
                                . ':: [:Punctuation:] Remove;'
                                . ':: Lower();'
                                . '[:Separator:] > \'-\';'
                        )->transliterate($nameLast);
                        $firstname = Transliterator::createFromRules(
                            ':: Any-Latin;'
                                . ':: NFD;'
                                . ':: [:Nonspacing Mark:] Remove;'
                                . ':: NFC;'
                                . ':: [:Punctuation:] Remove;'
                                . ':: Lower();'
                                . '[:Separator:] > \'-\';'
                        )->transliterate($nameFirst);
                        $nameUrl = "{$firstname}-{$lastname}";
                        try {
                            $depute = array('mpId' => $mpId, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'nameUrl' => $nameUrl, 'birthDate' => $birthDate, 'birthCity' => $birthCity, 'birthCountry' => $birthCountry, 'job' => $job, 'catSocPro' => $catSocPro, 'dateMaj' => $dateMaj);
                            $question_marks[] = '('  . $this->placeholders('?', sizeof($depute)) . ')';
                            $insert_values = array_merge($insert_values, array_values($depute));
                        } catch (Exception $e) {
                            echo '<pre>', var_dump($e->getMessage()), '</pre>';
                        }
                    }
                }
            }

            try {
                // SQL //
                $sql = "INSERT INTO deputes (" . implode(",", $deputeFields) . ") VALUES " . implode(',', $question_marks);
                $stmt = $this->bdd->prepare($sql);
                $stmt->execute($insert_values);
            } catch (Exception $e) {
                echo '<pre>', var_dump($e->getMessage()), '</pre>';
                die('');
            }
        }
    }
}

$script = new Script();
$script->fillDeputes();