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
        echo "fillDeputes starting \n";
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
            $deputeContacts = [];
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
                        foreach ($xml->adresses->adresse as $adresses) {
                            $type = $adresses->type;
                            $valElec = $adresses->valElec;
                            if (!isset($deputeContacts[$mpId])) {
                                $deputeContacts[$mpId] = array();
                            }
                            switch ($type) {
                                case 22:
                                    $deputeContacts[$mpId]["website"] = $valElec;
                                    break;
                                case 15:
                                    if (strpos($valElec, '@assemblee-nationale.fr') !== false) {
                                        $deputeContacts[$mpId]["mailAn"] = $valElec;
                                    } else {
                                        $deputeContacts[$mpId]["mailPerso"] = $valElec;
                                    }
                                    break;
                                case 24:
                                    $deputeContacts[$mpId]["twitter"] = $valElec;
                                    break;
                                case 25:
                                    $deputeContacts[$mpId]["facebook"] = $valElec;
                                    break;
                            }
                        }
                    }
                    try {
                        $depute = array('mpId' => $mpId, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'nameUrl' => $nameUrl, 'birthDate' => $birthDate, 'birthCity' => $birthCity, 'birthCountry' => $birthCountry, 'job' => $job, 'catSocPro' => $catSocPro, 'dateMaj' => $dateMaj);
                        $question_marks[] = '('  . $this->placeholders('?', sizeof($depute)) . ')';
                        $insert_values = array_merge($insert_values, array_values($depute));
                    } catch (Exception $e) {
                        echo '<pre>', var_dump($e->getMessage()), '</pre>';
                    }
                }
            }
            
            // insert deputes
            try {
                // SQL //
                $sql = "INSERT INTO deputes (" . implode(",", $deputeFields) . ") VALUES " . implode(',', $question_marks);
                $stmt = $this->bdd->prepare($sql);
                $stmt->execute($insert_values);
            } catch (Exception $e) {
                echo '<pre>', var_dump($e->getMessage()), '</pre>';
                die('');
            }

            $dbDeputeContacts = $this->bdd->prepare('SELECT * FROM deputes_contacts');
            $dbDeputeContacts->execute();
            $fields = array("website", "mailAn", "mailPerso", "twitter", "facebook");

            // update table depute contact from array $deputeContacts
            while ($dbDeputeContact = $dbDeputeContacts->fetch()) {
                $updateFields = [];
                $updateValues = [];
                $toUpdate = false;
                foreach ($fields as $field) {
                    if (
                        isset($deputeContacts[$dbDeputeContact["mpId"]][$field])
                        && $deputeContacts[$dbDeputeContact["mpId"]][$field]
                        && $deputeContacts[$dbDeputeContact["mpId"]][$field] != $dbDeputeContact[$field]
                    ) {
                        $updateFields[] = $field;
                        $updateValues[] = $deputeContacts[$dbDeputeContact["mpId"]][$field];
                        $toUpdate = true;
                    }
                }
                if ($toUpdate) {
                    $set = "";
                    for ($i = 0; count($updateFields) > $i; $i++) {
                        $set .= "{$updateFields[$i]} = \"{$updateValues[$i]}\",";
                    }
                    $set = substr($set, 0, -1);
                    $sql = $this->bdd->prepare('UPDATE deputes_contacts SET ' . $set . ', dateMaj=CURDATE() WHERE mpId = "' . $dbDeputeContact["mpId"] . '"');
                    $sql->execute();
                }
                unset($deputeContacts[$dbDeputeContact["mpId"]]);
            }
            // if new deputes add contact
            foreach ($deputeContacts as $key => $deputeContact) {
                $sql = $this->bdd->prepare("INSERT INTO deputes_contacts (
                  mpId,
                  website,
                  mailAn,
                  mailPerso,
                  twitter,
                  facebook,
                  dateMaj)
                  VALUES (
                  :mpId,
                  :website,
                  :mailAn,
                  :mailPerso,
                  :twitter,
                  :facebook,
                  CURDATE()
                  )");


                $sql->execute(array(
                    'mpId' => $key,
                    'website' => isset($deputeContact['website']) ? $deputeContact['website'] : null,
                    'mailAn' => isset($deputeContact['mailAn']) ? $deputeContact['mailAn'] : null,
                    'mailPerso' => isset($deputeContact['mailPerso']) ? $deputeContact['mailPerso'] : null,
                    'twitter' => isset($deputeContact['twitter']) ? $deputeContact['twitter'] : null,
                    'facebook' => isset($deputeContact['facebook']) ? $deputeContact['facebook'] : null,
                ));
            }
        }

        echo "fill Depute finished \n";
    }

    function downloadPictures()
    {
        if (!getenv('API_KEY_NOBG')) {
            echo "no API key for nobg\n";
        }
        $donnees = $this->bdd->query('
            SELECT d.mpId AS uid, d.legislature
            FROM deputes_last d
            WHERE legislature IN (14, 15)
        ');

        while ($d = $donnees->fetch()) {
            $uid = substr($d['uid'], 2);
            $filename = "../assets/imgs/deputes_original/depute_" . $uid . ".png";
            $legislature = $d['legislature'];
            $url = 'http://www2.assemblee-nationale.fr/static/tribun/' . $legislature . '/photos/' . $uid . '.jpg';

            if (!file_exists($filename)) {
                if (substr(get_headers($url)[12], 9, 3) != '404' && substr(get_headers($url)[0], 9, 3) != '404') {
                    $content = file_get_contents($url);
                    if ($content) {
                        $img = imagecreatefromstring($content);
                        $width = imagesx($img);
                        $height = imagesy($img);
                        $newwidth = $width;
                        $newheight = $height;
                        $quality = 0;
                        $thumb = imagecreatetruecolor($newwidth, $newheight);
                        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                        imagepng($thumb, '../assets/imgs/deputes_original/depute_' . $uid . '.png', $quality);
                        echo "one image was just downloaded\n";
                    }
                }
            }
            //$nobg => no background
            $lcdggithuburl = 'https://raw.githubusercontent.com/brissa-a/lcdg-data/main/img-nobg/PA' . $uid . '.png';
            $nobgfilename = '../assets/imgs/deputes_nobg_import/depute_' . $uid . '.png';
            if (!file_exists($nobgfilename)) {
                if (substr(get_headers($lcdggithuburl)[0], 9, 3) != '404') {
                    $nobg = file_get_contents($lcdggithuburl);
                    file_put_contents($nobgfilename, $nobg);
                    echo "one nobg image was just downloaded from lcdg\n";
                } else if (getenv('API_KEY_NOBG')) {
                    $ch = curl_init('https://api.remove.bg/v1.0/removebg');
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    echo "URL:" . $url . "\n";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'X-Api-Key:' . getenv('API_KEY_NOBG')
                    ]);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                        'image_url' => $url,
                        'size' => 'preview'
                    ));
                    $nobg = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $version = curl_getinfo($ch, CURLINFO_HTTP_VERSION);
                    echo "VERSON" . $version . "\n";
                    if ($nobg && $httpCode == 200) {
                        file_put_contents($nobgfilename, $nobg);
                        echo "one nobg image was just downloaded from remove.bg\n";
                    } else {
                        echo "Error while downloading from remove.bg httpCode:" . $httpCode . "\n";
                        echo "<pre>";
                        echo curl_error($ch);
                        echo "</pre>";
                        var_dump($nobg);
                    }
                    curl_close($ch);
                } else {
                    echo "API_KEY_NOBG not set nothing was downloaded\n";
                }
            }
        }
    }

    function webpPictures()
    {
        $dir = "../assets/imgs/deputes_original/";
        $newdir = "../assets/imgs/deputes_webp/";
        $files = scandir($dir);
        unset($files[0]);
        unset($files[1]);
        echo "Number of photos in the deputes_original ==> " . count($files) . "\n";

        foreach ($files as $file) {
            $newfile = str_replace(".png", "", $file);
            $newfile = $newfile . "_webp.webp";

            if (!file_exists($newdir . "" . $newfile)) {
                $img = imagecreatefrompng($dir . $file);
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                imagewebp($img, $newdir . $newfile, 80);
                imagedestroy($img);
                echo $newfile . " image was just converted into webp\n";
            }
        }

        //Same for nobg png
        $dir = "../assets/imgs/deputes_nobg_import/";
        $newdir = "../assets/imgs/deputes_nobg_webp/";
        $files = scandir($dir);
        unset($files[0]);
        unset($files[1]);
        echo "Number of photos in the deputes_nobg_import ==> " . count($files) . "\n";

        foreach ($files as $file) {
            $newfile = str_replace(".png", "", $file);
            $newfile = $newfile . "_webp.webp";

            if (!file_exists($newdir . "" . $newfile)) {
                $img = imagecreatefrompng($dir . $file);
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                imagewebp($img, $newdir . $newfile, 80);
                imagedestroy($img);
                echo $file . " image was just converted into webp\n";
            }
        }
    }

    function resmushPictures()
    {
        $donnees = $this->bdd->query('
            SELECT d.mpId AS uid, d.legislature
            FROM deputes_last d
            WHERE legislature IN (14, 15)
        ');

        while ($d = $donnees->fetch()) {
            $uid = substr($d['uid'], 2);
            $output_filename = "../assets/imgs/deputes_nobg/depute_" . $uid . ".png";
            $input_filename = "../assets/imgs/deputes_nobg_import/depute_" . $uid . ".png";

            if (!file_exists($output_filename)) {
                $filename = realpath($input_filename);
                if (file_exists($input_filename)) {
                    $mime = mime_content_type($input_filename);
                    $info = pathinfo($input_filename);
                    $name = $info['basename'];
                    $output = new CURLFile($filename, $mime, $name);
                    $data = array(
                        "files" => $output,
                    );
                    // 2.
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/');
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    $result = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $result = curl_error($ch);
                    }
                    curl_close($ch);

                    $arr_result = json_decode($result);

                    if ($arr_result->dest) {
                        file_put_contents($output_filename, file_get_contents($arr_result->dest));
                        $reducedBy = ($arr_result->src_size - $arr_result->dest_size) / $arr_result->src_size * 100;
                        echo "file size reduced by " . $reducedBy . "% = (src_size-dest_size)/src_size";
                    }
                } else {
                    echo $input_filename . " doesn't exists\n";
                }
            }
        }
    }
}

$script = new Script();
$script->fillDeputes();
// $script->downloadPictures();
// $script->webpPictures();
// $script->resmushPictures();