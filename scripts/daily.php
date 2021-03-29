<?php
include "lib/simplehtmldom_1_9/simple_html_dom.php";
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
        echo date('Y-m-d') . " : Launching the daily script for legislature " . $this->legislature_to_get . "\n";
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

    private function insertAll($table, $fields, $datas)
    {
        if (count($datas) > 0) {
            try {
                $update = "";
                $values = substr(str_repeat("(" . substr(str_repeat('?,', count($fields)), 0, -1) . "),", count($datas) / count($fields)), 0, -1);
                foreach($fields as $field){
                    $update .= $field . "=VALUES(" . $field . "),";
                }
                $update = substr($update, 0, -1);
                // SQL //
                $sql = "INSERT INTO " . $table . " (" . implode(",", $fields) . ") VALUES " . $values
                . " ON DUPLICATE KEY UPDATE " . $update;
                $stmt = $this->bdd->prepare($sql);
                $stmt->execute($datas);
                echo $table . " inserted\n";
            } catch (Exception $e) {
                echo "Error inserting : " . $table . "\n" . $e->getMessage() . "\n";die;
            }
        } else {
            echo "Nothing to insert in " . $table . "\n";
        }
    }

    public function fillDeputes()
    {
        echo "fillDeputes starting \n";
        //Online file
        $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
        $file = trim($file);
        $newfile = __DIR__ . '/tmp_acteurs_organes.zip';
        if (!copy($file, $newfile)) {
            echo "failed to copy $file...\n";
        }
        $zip = new ZipArchive();
        if ($zip->open($newfile) !== TRUE) {
            exit("cannot open <$file>\n");
        } else {
            $deputeFields = array('mpId', 'civ', 'nameFirst', 'nameLast', 'nameUrl', 'birthDate', 'birthCity', 'birthCountry', 'job', 'catSocPro', 'dateMaj');
            $mandatFields = array('mandatId', 'mpId', 'legislature', 'typeOrgane', 'dateDebut', 'dateFin', 'preseance', 'nominPrincipale', 'codeQualite', 'libQualiteSex', 'organe', 'electionRegion', 'electionRegionType', 'electionDepartement', 'electionDepartementNumero', 'electionCirco', 'datePriseFonction', 'causeFin', 'premiereElection', 'placeHemicyle', 'dateMaj');
            $mandatGroupeFields = array('mandatId', 'mpId', 'legislature', 'typeOrgane', 'dateDebut', 'dateFin', 'preseance', 'nominPrincipale', 'codeQualite', 'libQualiteSex', 'organeRef', 'dateMaj');
            $organeFields = array('uid', 'coteType', 'libelle', 'libelleEdition', 'libelleAbrev', 'libelleAbrege', 'dateDebut', 'dateFin', 'legislature', 'positionPolitique', 'preseance', 'couleurAssociee', 'dateMaj');
            $deputes = [];
            $deputeContacts = [];
            $mandats = [];
            $mandatsGroupe = [];
            $mandatsSecondaire = [];
            $organes = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $sub = substr($filename, 0, 13);
                if ($sub == 'xml/acteur/PA') {
                    $xml_string = $zip->getFromName($filename);
                    if ($xml_string != false) {
                        $xml = simplexml_load_string($xml_string);
                        $mpId = str_replace("xml/acteur/", "", $filename);
                        $mpId = str_replace(".xml", "", $mpId);

                        //deputes
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

                        //depute contact
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

                        //mandats
                        foreach ($xml->mandats->mandat as $mandat) {
                            $mandatId = $mandat->uid;
                            $legislature = $mandat->legislature;
                            $typeOrgane = $mandat->typeOrgane;
                            $dateDebut = $mandat->dateDebut;
                            $dateFin = $mandat->dateFin;

                            if ($dateFin == "") {
                                $dateFin = NULL;
                            } else {
                                $dateFin = $dateFin;
                            }

                            $preseance = $mandat->preseance;
                            $nominPrincipale = $mandat->nominPrincipale;
                            $codeQualite = $mandat->infosQualite->codeQualite;
                            $libQualiteSex = $mandat->infosQualite->libQualiteSex;
                            $organe = $mandat->organes->organeRef;

                            if ($mandat->typeOrgane == "ASSEMBLEE") {
                                if (isset($mandat->election->lieu->region)) {
                                    $electionRegion = $mandat->election->lieu->region;
                                } else {
                                    $electionRegion = NULL;
                                }

                                if (isset($mandat->election->lieu->regionType)) {
                                    $electionRegionType = $mandat->election->lieu->regionType;
                                } else {
                                    $electionRegionType = NULL;
                                }

                                if (isset($mandat->election->lieu->departement)) {
                                    $departement = $mandat->election->lieu->departement;
                                    $numDepartement = $mandat->election->lieu->numDepartement;
                                    $numCirco = $mandat->election->lieu->numCirco;
                                } else {
                                    $departement = NULL;
                                    $numDepartement = NULL;
                                    $numCirco = NULL;
                                }

                                if (isset($mandat->mandature)) {
                                    $datePriseFonction = $mandat->mandature->datePriseFonction;
                                    $causeFin = $mandat->mandature->causeFin;
                                    $premiereElection = $mandat->mandature->premiereElection;
                                } else {
                                    $datePriseFonction = NULL;
                                    $causeFin = NULL;
                                    $premiereElection = NULL;
                                }

                                if (isset($mandat->mandature->placeHemicycle)) {
                                    $placeHemicycle = $mandat->mandature->placeHemicycle;
                                } else {
                                    $placeHemicycle = NULL;
                                }

                                if ($datePriseFonction == "") {
                                    $datePriseFonction = NULL;
                                } else {
                                    $datePriseFonction = $datePriseFonction;
                                }
                                $mandatPrincipal = array(
                                    'mandatId' => $mandatId,
                                    'mpId' => $mpId,
                                    'legislature' => $legislature,
                                    'type_organe' => $typeOrgane,
                                    'date_debut' => $dateDebut,
                                    'date_fin' => $dateFin,
                                    'preseance' => $preseance,
                                    'nomin_principale' => $nominPrincipale,
                                    'code_qualite' => $codeQualite,
                                    'libQualiteSex' => $libQualiteSex,
                                    'organe' => $organe,
                                    'election_region' => $electionRegion,
                                    'election_region_type' => $electionRegionType,
                                    'election_departement' => $departement,
                                    'election_departement_numero' => $numDepartement,
                                    'election_circo' => $numCirco,
                                    'prise_fonction' => $datePriseFonction,
                                    'causeFin' => $causeFin,
                                    'premiere_election' => $premiereElection,
                                    'placeHemicyle' => $placeHemicycle,
                                    'dateMaj' => $this->dateMaj
                                );
                                $mandats = array_merge($mandats, array_values($mandatPrincipal));
                            } else if ($mandat->typeOrgane == "GP") {
                                $mandatGroupe = array(
                                    'mandatId' => $mandatId,
                                    'mpId' => $mpId,
                                    'legislature' => $legislature,
                                    'type_organe' => $typeOrgane,
                                    'date_debut' => $dateDebut,
                                    'date_fin' => $dateFin,
                                    'preseance' => $preseance,
                                    'nomin_principale' => $nominPrincipale,
                                    'code_qualite' => $codeQualite,
                                    'libQualiteSex' => $libQualiteSex,
                                    'organe' => $organe,
                                    'dateMaj' => $this->dateMaj
                                );
                                $mandatsGroupe = array_merge($mandatsGroupe, array_values($mandatGroupe));
                            } else if (($mandat->typeOrgane == "COMPER") || ($mandat->typeOrgane == "DELEGBUREAU") || ($mandat->typeOrgane == "PARPOL")) {
                                $mandatSecondaire = array(
                                    'mandatId' => $mandatId,
                                    'mpId' => $mpId,
                                    'legislature' => $legislature,
                                    'type_organe' => $typeOrgane,
                                    'date_debut' => $dateDebut,
                                    'date_fin' => $dateFin,
                                    'preseance' => $preseance,
                                    'nomin_principale' => $nominPrincipale,
                                    'code_qualite' => $codeQualite,
                                    'libQualiteSex' => $libQualiteSex,
                                    'organe' => $organe,
                                    'dateMaj' => $this->dateMaj
                                );
                                $mandatsSecondaire = array_merge($mandatsSecondaire, array_values($mandatSecondaire));
                            }
                        }
                    }
                    try {
                        $depute = array('mpId' => $mpId, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'nameUrl' => $nameUrl, 'birthDate' => $birthDate, 'birthCity' => $birthCity, 'birthCountry' => $birthCountry, 'job' => $job, 'catSocPro' => $catSocPro, 'dateMaj' => $this->dateMaj);
                        $deputes = array_merge($deputes, array_values($depute));
                    } catch (Exception $e) {
                        var_dump($e->getMessage());
                    }
                } else if ($sub == 'xml/organe/PO') {
                    $xml_string = $zip->getFromName($filename);

                    if ($xml_string != false) {
                        $xml = simplexml_load_string($xml_string);
                        $uid = str_replace("xml/organe/", "", $filename);
                        $uid = str_replace(".xml", "", $uid);
                        $codeType = $xml->codeType;
                        $libelle = $xml->libelle;
                        $libelleEdition = $xml->libelleEdition;
                        $libelleAbrege = $xml->libelleAbrege;
                        $libelleAbrev = $xml->libelleAbrev;

                        if (isset($xml->viMoDe->dateDebut) && $xml->viMoDe->dateDebut != "") {
                            $dateDebut = $xml->viMoDe->dateDebut;
                        } else {
                            $dateDebut = NULL;
                        }

                        if (isset($xml->viMoDe->dateFin) && $xml->viMoDe->dateFin != "") {
                            $dateFin = $xml->viMoDe->dateFin;
                        } else {
                            $dateFin = NULL;
                        }

                        if (isset($xml->legislature) && $xml->legislature != "") {
                            $legislature = $xml->legislature;
                        } else {
                            $legislature = NULL;
                        }

                        $positionPolitique = $xml->positionPolitique;
                        $preseance = $xml->preseance;
                        $couleurAssociee = $xml->couleurAssociee;

                        $organe = array('uid' => $uid, 'coteType' => $codeType, 'libelle' => $libelle, 'libelleEdition' => $libelleEdition, 'libelleAbrev' => $libelleAbrev, 'libelleAbrege' => $libelleAbrege, 'dateDebut' => $dateDebut, 'dateFin' => $dateFin, 'legislature' => $legislature, 'positionPolitique' => $positionPolitique, 'preseance' => $preseance, 'couleurAssociee' => $couleurAssociee, 'dateMaj' => $this->dateMaj);
                        $organes = array_merge($organes, array_values($organe));
                    }
                }
                if (($i + 1) % 1000 === 0) {
                    echo "Let's insert until " . $i . "\n";
                    // insert deputes
                    $this->insertAll('deputes', $deputeFields, $deputes);
                    // insert mandat
                    $this->insertAll('mandat_principal', $mandatFields, $mandats);
                    // insert mandat grpupe
                    $this->insertAll('mandat_groupe', $mandatGroupeFields, $mandatsGroupe);
                    // insert mandat secondaire
                    $this->insertAll('mandat_secondaire', $mandatGroupeFields, $mandatsSecondaire);
                    // insert organes
                    $this->insertAll('organes', $organeFields, $organes);
                    $deputes = [];
                    $mandats = [];
                    $mandatsGroupe = [];
                    $mandatsSecondaire = [];
                    $organes = [];
                }
            }
            echo "Let's insert until the end : " . $i . "\n";
            // insert deputes
            $this->insertAll('deputes', $deputeFields, $deputes);
            // insert mandat
            $this->insertAll('mandat_principal', $mandatFields, $mandats);
            // insert mandat grpupe
            $this->insertAll('mandat_groupe', $mandatGroupeFields, $mandatsGroupe);
            // insert mandat secondaire
            $this->insertAll('mandat_secondaire', $mandatGroupeFields, $mandatsSecondaire);
            // insert organes
            $this->insertAll('organes', $organeFields, $organes);

            // update depute contacts
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

    public function downloadPictures()
    {
        echo "downloadPictures starting \n";
        if (!getenv('API_KEY_NOBG')) {
            echo "no API key for nobg\n";
        }
        $donnees = $this->bdd->query('
            SELECT d.mpId AS uid, d.legislature
            FROM deputes_last d
            WHERE legislature IN (14, 15)
        ');

        $originalFolder = __DIR__ . "/../assets/imgs/deputes_original/";
        if (!file_exists($originalFolder)) mkdir($originalFolder);
        while ($d = $donnees->fetch()) {
            $uid = substr($d['uid'], 2);
            $filename = __DIR__ . "/../assets/imgs/deputes_original/depute_" . $uid . ".png";
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
            $nobgFolder = __DIR__ . "/../assets/imgs/deputes_nobg_import/";
            if (!file_exists($nobgFolder)) mkdir($nobgFolder);
            $liveUrl = 'https://datan.fr/assets/imgs/deputes_nobg_import/depute_' . $uid . '.png';
            $nobgfilename = __DIR__ . '/../assets/imgs/deputes_nobg_import/depute_' . $uid . '.png';
            if (!file_exists($nobgfilename)) {
                $nobgLive = file_get_contents($liveUrl);
                if ($nobgLive) {
                    file_put_contents($nobgfilename, $nobgLive);
                    echo "one nobg image was just downloaded from datan.fr\n";
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
                        echo curl_error($ch);
                        var_dump($nobg);
                    }
                    curl_close($ch);
                }
            }
        }
    }

    public function webpPictures()
    {
        echo "webpPictures starting \n";
        $dir = __DIR__ . "/../assets/imgs/deputes_original/";
        $newdir = __DIR__ . "/../assets/imgs/deputes_webp/";
        $files = scandir($dir);
        unset($files[0]);
        unset($files[1]);
        echo "Number of photos in the deputes_original ==> " . count($files) . "\n";

        if (!file_exists($newdir)) mkdir($newdir);
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
        $dir = __DIR__ . "/../assets/imgs/deputes_nobg_import/";
        $newdir = __DIR__ . "/../assets/imgs/deputes_nobg_webp/";
        $files = scandir($dir);
        unset($files[0]);
        unset($files[1]);
        echo "Number of photos in the deputes_nobg_import ==> " . count($files) . "\n";

        if (!file_exists($newdir)) mkdir($newdir);
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

    public function resmushPictures()
    {
        echo "resmushPictures starting \n";
        $donnees = $this->bdd->query('
            SELECT d.mpId AS uid, d.legislature
            FROM deputes_last d
            WHERE legislature IN (14, 15)
        ');

        while ($d = $donnees->fetch()) {
            $uid = substr($d['uid'], 2);
            $output_filename = __DIR__ . "/../assets/imgs/deputes_nobg/depute_" . $uid . ".png";
            $input_filename = __DIR__ . "/../assets/imgs/deputes_nobg_import/depute_" . $uid . ".png";

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

    public function groupeEffectif()
    {
        echo "groupeEffectif starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS groupes_effectif;
            CREATE TABLE groupes_effectif AS
            SELECT @s:=@s+1 AS "classement", A.*, CURDATE() AS dateMaj
            FROM
            (
            SELECT t1.organeRef, o.libelle, count(t1.mpId) AS effectif, t1.legislature
            FROM mandat_groupe t1
            LEFT JOIN organes o ON t1.organeRef = o.uid
            WHERE t1.typeOrgane = "GP" AND t1.codeQualite != "Président" AND t1.dateFin IS NULL
            GROUP BY t1.organeRef, t1.legislature
            ) A,
            (SELECT @s:= 0) AS s
            ORDER BY A.effectif DESC;
            ALTER TABLE groupes_effectif ADD INDEX idx_organeRef (organeRef);
        ');
    }

    public function deputeAll()
    {
        echo "deputeAll starting \n";

        $query = $this->bdd->query('
            SELECT mp.mpId, mp.legislature, d.nameUrl, d.nameFirst, d.nameLast, d.civ,
            YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age
            FROM mandat_principal mp
            LEFT JOIN deputes d ON d.mpId = mp.mpId
            GROUP BY mp.mpId, mp.legislature
        ');

        $i = 1;
        $deputes = [];
        $depute = [];
        $deputeFields = array('mpId', 'legislature', 'nameUrl', 'civ', 'nameFirst', 'nameLast', 'age', 'dptSlug', 'departementNom', 'departementCode', 'circo', 'mandatId', 'libelle', 'libelleAbrev', 'groupeId', 'groupeMandat', 'couleurAssociee', 'datePriseFonction', 'dateFin', 'causeFin', 'img', 'imgOgp', 'dateMaj');
        while ($data = $query->fetch()) {
            $mpId = $data['mpId'];
            $legislature = $data['legislature'];
            $nameUrl = $data['nameUrl'];
            $nameFirst = $data['nameFirst'];
            $nameLast = $data['nameLast'];
            $civ = $data['civ'];
            $age = $data['age'];
            $img = file_exists(__DIR__ . "/../assets/imgs/deputes_nobg_webp/depute_" . substr($mpId, 2) . "_webp.webp") ? 1 : 0;
            $imgOgp = file_exists(__DIR__ . "/../assets/imgs/deputes_ogp/ogp_deputes_" . $mpId . ".png") ? 1 : 0;

            // Get the mandat_principal
            $mandatPrincipal = $this->bdd->query('
                SELECT mp.*, dpt.slug AS dptSlug, dpt.departement_nom AS departementNom, dpt.departement_code AS departementCode, mp.electionCirco AS circo, mp.causeFin, mp.datePriseFonction
                FROM mandat_principal mp
                LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
                WHERE mp.mpId = "' . $mpId . '" AND mp.preseance = 50 AND mp.legislature = ' . $legislature . '
                ORDER BY !ISNULL(mp.dateFin), mp.dateFin DESC
                LIMIT 1
            ');

            if ($mandatPrincipal->rowCount() > 0) {
                while ($mandat = $mandatPrincipal->fetch()) {
                    $dateFin = $mandat['dateFin'];
                    $mandatId = $mandat['mandatId'];
                    $dptSlug = $mandat['dptSlug'];
                    $departementNom = $mandat['departementNom'];
                    $departementCode = $mandat['departementCode'];
                    $circo = $mandat['circo'];
                    $causeFin = $mandat['causeFin'];
                    $datePriseFonction = $mandat['datePriseFonction'];
                }
            } else {
                echo "ERROR";
            }

            $mandatGroupes = $this->bdd->query('
                SELECT o.libelle, o.libelleAbrev, o.uid AS groupeId, o.couleurAssociee, mg.mandatId AS groupeMandat
                FROM mandat_groupe mg
                LEFT JOIN organes o ON o.uid = mg.organeRef
                WHERE mg.mpId = "' . $mpId . '" AND mg.legislature = ' . $legislature . ' AND mg.preseance >= 20
                ORDER BY !ISNULL(mg.dateFin), mg.dateFin DESC
                LIMIT 1
            ');

            if ($mandatGroupes->rowCount() > 0) {
                while ($mandatGroupe = $mandatGroupes->fetch()) {
                    $libelle = $mandatGroupe['libelle'];
                    $libelleAbrev = $mandatGroupe['libelleAbrev'];
                    $groupeId = $mandatGroupe['groupeId'];
                    $couleurAssociee = $mandatGroupe['couleurAssociee'];
                    $groupeMandat = $mandatGroupe['groupeMandat'];
                }
            } else {
                echo "ERROR -- ";
                echo $mpId . " -- " . $legislature . "\n";
                $libelle = NULL;
                $libelleAbrev = NULL;
                $groupeId = NULL;
                $couleurAssociee = NULL;
            }

            $depute = array('mpId' => $mpId, 'legislature' => $legislature, 'nameUrl' => $nameUrl, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'age' => $age, 'dptSlug' => $dptSlug, 'departementNom' => $departementNom, 'departementCode' => $departementCode, 'circo' => $circo, 'mandatId' => $mandatId, 'libelle' => $libelle, 'libelleAbrev' => $libelleAbrev, 'groupeId' => $groupeId, 'groupeMandat' => $groupeMandat, 'couleurAssociee' => $couleurAssociee, 'datePriseFonction' => $datePriseFonction, 'dateFin' => $dateFin, 'causeFin' => $causeFin, 'img' => $img, 'imgOgp' => $imgOgp, 'dateMaj' => $this->dateMaj);
            $deputes = array_merge($deputes, array_values($depute));
            if ($i % 1000 === 0) {
                echo "Let's import until vote n " . $i . "\n";
                $this->insertAll('deputes_all', $deputeFields, $deputes);
                $deputes = [];
            }
            $i++;
        }
        $this->insertAll('deputes_all', $deputeFields, $deputes);
    }

    public function deputeLast()
    {
        echo "deputeLast starting \n";
        $this->bdd->exec('DROP TABLE IF EXISTS deputes_last');
        $this->bdd->exec('
            CREATE TABLE deputes_last AS
            SELECT da.*, dpt.libelle_1, dpt.libelle_2,
            CASE WHEN (legislature = 15 AND dateFin IS NULL) THEN 1 ELSE 0 END AS active
            FROM deputes_all da
            JOIN (
            SELECT mpId, MAX(legislature) AS legislatureLast
            FROM deputes_all
            GROUP BY mpId
            ) x ON da.mpId = x.mpId AND da.legislature = x.legislatureLast
            LEFT JOIN departement dpt ON dpt.departement_code = da.departementCode
        ');

        $this->bdd->exec('CREATE INDEX idx_mp ON deputes_last (nameUrl);');
        $this->bdd->exec('CREATE INDEX idx_dptSlug ON deputes_last (dptSlug);');
        $this->bdd->exec('CREATE INDEX idx_mpId ON deputes_last(mpId)');
        $this->bdd->exec('CREATE INDEX idx_legislature ON deputes_last(legislature);');
        $this->bdd->exec('ALTER TABLE `deputes_last` ADD PRIMARY KEY(`mpId`, `legislature`);');
    }

    public function deputeJson()
    {
        echo "deputeJson starting \n";
        $reponse = $this->bdd->query('
        SELECT da.mpId, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug
        FROM deputes_all da
        WHERE da.legislature = 15
        ');

        $array = array();
        while ($data = $reponse->fetch()) {
            $id = $data['mpId'];
            $name = $data['nameFirst'] . ' ' . $data['nameLast'];
            $slug = $data['nameUrl'];
            $dpt = $data['dptSlug'];

            $array[] = [
                "id" => $id,
                "name" => $name,
                "slug" => $slug,
                "dpt" => $dpt
            ];
        }

        $json = json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // save file
        $file_destination = __DIR__ . "/../assets/data/deputes_json.json";
        $fp = fopen($file_destination, 'w');
        if (fputs($fp, $json)) {
            echo "JSON created \n";
        }
        fclose($fp);
    }

    public function groupeStats()
    {
        echo "groupeStats starting \n";
        $this->bdd->query("DROP TABLE IF EXISTS groupes_stats");
        $this->bdd->query('CREATE TABLE groupes_stats ( organeRef VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , womenPct DECIMAL(4,2) NULL , womenN INT(3) NULL  , age DECIMAL(4,2) NULL ) ENGINE = MyISAM;');

        $reponse = $this->bdd->query('
            SELECT *
            FROM organes
            WHERE legislature = 15 AND coteType = "GP"
        ');

        while ($data = $reponse->fetch()) {
            $groupeId = $data['uid'];

            if ($data['dateFin'] == NULL) {
                $age_response = $this->bdd->query('
                    SELECT da.groupeId AS organeRef, ROUND(AVG(age), 2) AS age, COUNT(age) as n
                    FROM deputes_all da
                    WHERE da.groupeId = "' . $groupeId . '" AND da.legislature = 15 AND da.dateFin IS NULL
                ');

                while ($age_data = $age_response->fetch()) {
                    $age = $age_data['age'];
                }

                $women_response = $this->bdd->query('
                    SELECT A.*,
                    ROUND(female / n * 100, 2) AS pct
                    FROM
                    (
                    SELECT groupeId, COUNT(mpId) AS n,
                    SUM(if(civ = "Mme", 1, 0)) AS female
                    FROM deputes_all
                    WHERE groupeId = "' . $groupeId . '" AND legislature = 15 AND dateFin IS NULL
                    GROUP BY groupeId
                    ) A
                ');

                while ($women_data = $women_response->fetch()) {
                    $womenPct = $women_data['pct'];
                    $womenN = $women_data['female'];
                }
            } else {
                $age_response = $this->bdd->query('
                    SELECT ROUND(avg(age), 2) AS age
                    FROM
                    (
                        SELECT mg.mpId,
                        YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age
                        FROM mandat_groupe mg
                        LEFT JOIN organes o ON mg.organeRef = o.uid
                        LEFT JOIN deputes d ON mg.mpId = d.mpId
                        WHERE mg.organeRef = "' . $groupeId . '" AND mg.dateFin = o.dateFin
                        GROUP BY mg.mpId
                    ) A
                ');

                while ($age_data = $age_response->fetch()) {
                    $age = $age_data['age'];
                }

                $women_response2 = $this->bdd->query('
                    SELECT A.*, ROUND(female / n * 100, 2) AS pct
                    FROM
                    (
                    SELECT o.uid, SUM(if(dl.civ = "Mme", 1, 0)) AS female, COUNT(dl.civ) AS n
                    FROM organes o
                    LEFT JOIN mandat_groupe mg ON o.uid = mg.organeRef AND o.dateFin = mg.dateFin
                    LEFT JOIN deputes_last dl ON mg.mpId = dl.mpId
                    WHERE o.uid = "' . $groupeId . '"
                    GROUP BY o.uid
                    ) A
                ');

                while ($women_data2 = $women_response2->fetch()) {
                    $womenPct = $women_data2['pct'];
                    $womenN = $women_data2['female'];
                }
            }

            // INSERT INTO DATABSSE //
            $sql = $this->bdd->prepare('INSERT INTO groupes_stats (organeRef, age, womenN, womenPct) VALUES (:organeRef, :age, :womenN, :womenPct)');
            $sql->execute(array('organeRef' => $groupeId, 'age' => $age, 'womenN' => $womenN, 'womenPct' => $womenPct));
        }
    }

    public function parties()
    {
        echo "parties starting \n";

        $this->bdd->exec('DROP TABLE IF EXISTS parties');

        $this->bdd->exec('
        CREATE TABLE parties AS
        SELECT A.*, B.effectif
        FROM
        (
        SELECT o.uid, o.libelleAbrev, o.libelle, o.dateFin, COUNT(ms.mpId) AS effectifTotal
        FROM organes o
        LEFT JOIN mandat_secondaire ms ON o.uid = ms.organeRef
        LEFT JOIN deputes_all da ON da.mpId = ms.mpId
        WHERE o.coteType = "PARPOL" AND da.legislature = 15
        GROUP BY o.uid
        ) A
        LEFT JOIN
        (
        SELECT o.uid, o.libelle, o.libelleAbrev, COUNT(ms.mpId) AS effectif
        FROM deputes_all da
        LEFT JOIN mandat_secondaire ms ON ms.mpId = da.mpId
        LEFT JOIN organes o ON o.uid = ms.organeRef
        WHERE ms.typeOrgane = "PARPOL" AND ms.dateFin IS NULL AND da.legislature = 15 AND da.dateFin IS NULL
        GROUP BY ms.organeRef
        ) B ON A.uid = B.uid
        ORDER BY B.effectif DESC
        ');

        $this->bdd->exec('
        CREATE INDEX idx_uid ON parties (uid);
        ');
    }

    public function legislature()
    {
        echo "legislature starting \n";
        $this->bdd->exec('
            CREATE TABLE IF NOT EXISTS legislature (
            id INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            organeRef VARCHAR(30) NOT NULL,
            libelle VARCHAR(255) NOT NULL,
            libelleAbrev VARCHAR(30) NOT NULL,
            name VARCHAR(255) NOT NULL,
            legislatureNumber TINYINT(1) NOT NULL,
            dateDebut DATE NOT NULL,
            dateFin DATE NULL,
            dateMaj DATE NOT NULL
            );
        ');

        $this->bdd->query('TRUNCATE TABLE legislature');

        $response = $this->bdd->query('
            SELECT *
            FROM organes
            WHERE coteType = "ASSEMBLEE"
        ');

        $legislature = [];
        $legislatures = [];
        $legislatureFields = array('organeRef', 'libelle', 'libelleAbrev', 'name', 'legislatureNumber', 'dateDebut', 'dateFin', 'dateMaj');

        while ($data = $response->fetch()) {
            $organeRef = $data['uid'];
            $libelle = $data['libelle'];
            $libelleAbrev = $data['libelleAbrev'];
            $number = $data['legislature'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];

            $name = $number . "ème législature";

            // INSERT INTO DATABSSE //
            $legislature = array('organeRef' => $organeRef, 'libelle' => $libelle, 'libelleAbrev' => $libelleAbrev, 'name' => $name, 'legislatureNumber' => $number, 'dateDebut' => $dateDebut, 'dateFin' => $dateFin, 'dateMaj' => $this->dateMaj);
            $legislatures = array_merge($legislatures, array_values($legislature));
        }
        $this->insertAll('legislature', $legislatureFields, $legislatures);
    }

    public function vote()
    {
        echo "vote starting \n";
        echo "starting vote\n";
        $response_vote = $this->bdd->query('
            SELECT voteNumero
            FROM votes
            WHERE legislature = "' . $this->legislature_to_get . '"
            ORDER BY voteNumero DESC
            LIMIT 1
        ');

        $dernier_vote = $response_vote->fetch();
        $number_to_import = isset($dernier_vote['voteNumero']) ? $dernier_vote['voteNumero'] + 1 : 1;
        echo "From " . $number_to_import . "\n";

        // SCRAPPING DEPENDING ON LEGISLATURE
        if ($this->legislature_to_get == 15) {

            $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/scrutins/Scrutins_XV.xml.zip';
            $file = trim($file);
            $newfile = __DIR__ . '/tmp_Scrutins_XV.xml.zip';
            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
            }
            $zip = new ZipArchive();
            if ($zip->open($newfile) !== TRUE) {
                exit("cannot open <$newfile>\n");
            } else {
                $voteMainFields = array('mpId', 'vote', 'voteNumero', 'voteId', 'legislature', 'mandatId', 'parDelegation', 'causePosition', 'voteType');
                $voteInfoFields =  array('voteId', 'voteNumero', 'organeRef', 'legislature', 'sessionREF', 'seanceRef', 'dateScrutin', 'quantiemeJourSeance', 'codeTypeVote', 'libelleTypeVote', 'typeMajorite', 'sortCode', 'titre', 'demandeur', 'modePublicationDesVotes', 'nombreVotants', 'suffragesExprimes', 'nbrSuffragesRequis', 'decomptePour', 'decompteContre', 'decompteAbs', 'decompteNv');
                $voteGroupeFields = array('voteId', 'voteNumero', 'legislature', 'organeRef', 'nombreMembresGroupe', 'positionMajoritaire', 'nombrePours', 'nombreContres', 'nombreAbstentions', 'nonVotants', 'nonVotantsVolontaires');
                $votesMain = [];
                $votesInfo = [];
                $votesGroupe = [];

                while (1) {
                    $file_to_import = 'VTANR5L15V' . $number_to_import ++;
                    $xml_string = $zip->getFromName('xml/' . $file_to_import . '.xml');
                    if ($xml_string != false) {
                        $xml = simplexml_load_string($xml_string);
                        //vote
                        foreach ($xml->xpath("//*[local-name()='votant']") as $votant) {
                            $mpId = $votant->xpath("./*[local-name()='acteurRef']");
                            $item['mpId'] = $mpId[0];

                            $mandatId = $votant->xpath("./*[local-name()='mandatRef']");
                            $item['mandatId'] = $mandatId[0];

                            $parDelegation = $votant->xpath("./*[local-name()='parDelegation']");
                            if (isset($parDelegation[0])) {
                                $item['parDelegation'] = $parDelegation[0];
                            } else {
                                $item['parDelegation'] = NULL;
                            }

                            $causePosition = $votant->xpath("./*[local-name()='causePositionVote']");
                            if (isset($causePosition[0])) {
                                $item['causePosition'] = $causePosition[0];
                            } else {
                                $item['causePosition'] = NULL;
                            }

                            $voteMp = $votant->xpath("./parent::*");
                            $item['voteMp'] = $voteMp[0]->getName();


                            if ($item['voteMp'] == 'pours' || $item['voteMp'] == 'pour') {
                                $vote = 1;
                            } elseif ($item['voteMp'] == 'contres' || $item['voteMp'] == 'contre') {
                                $vote = -1;
                            } elseif ($item['voteMp'] == 'abstentions' || $item['voteMp'] == 'abstention') {
                                $vote = 0;
                            } elseif ($item['voteMp'] == 'nonVotants' || $item['voteMp'] == 'nonVotant') {
                                $vote = 'nv';
                            } elseif ($item['voteMp'] == 'nonVotantsVolontaires' || $item['voteMp'] == 'nonVotantsVolontaire') {
                                $vote = 'nv';
                            } else {
                                $vote = NULL;
                            }

                            $voteId = $votant->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='uid']");
                            $item['voteId'] = $voteId[0];

                            $voteNumero = $votant->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='numero']");
                            $item['voteNumero'] = $voteNumero[0];

                            $legislature = $votant->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='legislature']");
                            $item['legislature'] = $legislature[0];

                            $miseaupoint = $votant->xpath("./../..");
                            $item['voteType'] = $miseaupoint[0]->getName();

                            $voteMain = array('mpId' => $item['mpId'], 'vote' => $vote, 'voteNumero' => $item['voteNumero'], 'voteId' => $item['voteId'], 'legislature' => $item['legislature'], 'mandatId' => $item['mandatId'], 'parDelegation' => $item['parDelegation'], 'causePosition' => $item['causePosition'], 'voteType' => $item['voteType']);
                            $votesMain = array_merge($votesMain, array_values($voteMain));
                        }
                        foreach ($xml->xpath("//*[local-name()='scrutin']") as $scrutin) {
                            $voteId = $scrutin->xpath("./*[local-name()='uid']");
                            $item['voteId'] = $voteId[0];

                            $voteNumero = $scrutin->xpath("./*[local-name()='numero']");
                            $item['voteNumero'] = $voteNumero[0];

                            $organeRef = $scrutin->xpath("./*[local-name()='organeRef']");
                            $item['organeRef'] = $organeRef[0];

                            $legislature = $scrutin->xpath("./*[local-name()='legislature']");
                            $item['legislature'] = $legislature[0];

                            $sessionRef = $scrutin->xpath("./*[local-name()='sessionRef']");
                            $item['sessionRef'] = $sessionRef[0];

                            $seanceRef = $scrutin->xpath("./*[local-name()='seanceRef']");
                            $item['seanceRef'] = $seanceRef[0];

                            $dateScrutin = $scrutin->xpath("./*[local-name()='dateScrutin']");
                            $item['dateScrutin'] = $dateScrutin[0];

                            $quantiemeJourSeance = $scrutin->xpath("./*[local-name()='quantiemeJourSeance']");
                            $item['quantiemeJourSeance'] = $quantiemeJourSeance[0];

                            $codeTypeVote = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='codeTypeVote']");
                            $item['codeTypeVote'] = $codeTypeVote[0];

                            $libelleTypeVote = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='libelleTypeVote']");
                            $item['libelleTypeVote'] = $libelleTypeVote[0];

                            $typeMajorite = $scrutin->xpath("./*[local-name()='typeVote']/*[local-name()='typeMajorite']");
                            $item['typeMajorite'] = $typeMajorite[0];

                            $sortCode = $scrutin->xpath("./*[local-name()='sort']/*[local-name()='code']");
                            $item['sortCode'] = $sortCode[0];

                            $titre = $scrutin->xpath("./*[local-name()='titre']");
                            $item['titre'] = $titre[0];

                            $demandeur = $scrutin->xpath("./*[local-name()='demandeur']/*[local-name()='texte']");
                            $item['demandeur'] = $demandeur[0];

                            $modePublicationDesVotes = $scrutin->xpath("./*[local-name()='modePublicationDesVotes']");
                            $item['modePublicationDesVotes'] = $modePublicationDesVotes[0];

                            $nombreVotants = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='nombreVotants']");
                            $item['nombreVotants'] = $nombreVotants[0];

                            $suffragesExprimes = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='suffragesExprimes']");
                            $item['suffragesExprimes'] = $suffragesExprimes[0];

                            $nbrSuffragesRequis = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='nbrSuffragesRequis']");
                            $item['nbrSuffragesRequis'] = $nbrSuffragesRequis[0];

                            $decomptePour = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='pour']");
                            $item['decomptePour'] = $decomptePour[0];

                            $decompteContre = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='contre']");
                            $item['decompteContre'] = $decompteContre[0];

                            $decompteAbs = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='abstentions']");
                            $item['decompteAbs'] = $decompteAbs[0];

                            $decompteNv = $scrutin->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='nonVotants']");
                            $item['decompteNv'] = $decompteNv[0];

                            $voteInfo = array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'organeRef' => $item['organeRef'], 'legislature' => $item['legislature'], 'sessionREF' => $item['sessionRef'], 'seanceRef' => $item['seanceRef'], 'dateScrutin' => $item['dateScrutin'], 'quantiemeJourSeance' => $item['quantiemeJourSeance'], 'codeTypeVote' => $item['codeTypeVote'], 'libelleTypeVote' => $item['libelleTypeVote'], 'typeMajorite' => $item['typeMajorite'], 'sortCode' => $item['sortCode'], 'titre' => $item['titre'], 'demandeur' => $item['demandeur'], 'modePublicationDesVotes' => $item['modePublicationDesVotes'], 'nombreVotants' => $item['nombreVotants'], 'suffragesExprimes' => $item['suffragesExprimes'], 'nbrSuffragesRequis' => $item['nbrSuffragesRequis'], 'decomptePour' => $item['decomptePour'], 'decompteContre' => $item['decompteContre'], 'decompteAbs' => $item['decompteAbs'], 'decompteNv' => $item['decompteNv']);
                            $votesInfo = array_merge($votesInfo, array_values($voteInfo));
                        }

                        foreach ($xml->xpath("//*[local-name()='groupe']") as $groupe) {
                            $voteId = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='uid']");
                            $item['voteId'] = $voteId[0];

                            $voteNumero = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='numero']");
                            $item['voteNumero'] = $voteNumero[0];

                            $legislature = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='legislature']");
                            $item['legislature'] = $legislature[0];

                            $organeRef = $groupe->xpath("./*[local-name()='organeRef']");
                            $item['organeRef'] = $organeRef[0];

                            $nombreMembresGroupe = $groupe->xpath("./*[local-name()='nombreMembresGroupe']");
                            $item['nombreMembresGroupe'] = $nombreMembresGroupe[0];

                            $positionMajoritaire = $groupe->xpath("./*[local-name()='vote']/*[local-name()='positionMajoritaire']");
                            $item['positionMajoritaire'] = $positionMajoritaire[0];

                            $nombrePours = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='pour']");
                            $item['nombrePours'] = $nombrePours[0];

                            $nombreContres = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='contre']");
                            $item['nombreContres'] = $nombreContres[0];

                            $nombreAbstentions = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='abstentions']");
                            $item['nombreAbstentions'] = $nombreAbstentions[0];

                            $nonVotants = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotants']");
                            $item['nonVotants'] = $nonVotants[0];

                            $nonVotantsVolontaires = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotantsVolontaires']");
                            $item['nonVotantsVolontaires'] = $nonVotantsVolontaires[0];

                            $total_votant = $item['nombrePours'] + $item['nombreContres'] + $item['nombreAbstentions'];
                            if ($total_votant == '0') {
                                $positionMajoritaire = 'nv';
                            } else {
                                $positionMajoritaire = $item['positionMajoritaire'];
                            }

                            $voteGroupe = array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'legislature' => $item['legislature'], 'organeRef' => $item['organeRef'], 'nombreMembresGroupe' => $item['nombreMembresGroupe'], 'positionMajoritaire' => $positionMajoritaire, 'nombrePours' => $item['nombrePours'], 'nombreContres' => $item['nombreContres'], 'nombreAbstentions' => $item['nombreAbstentions'], 'nonVotants' => $item['nonVotants'], 'nonVotantsVolontaires' => $item['nonVotantsVolontaires']);
                            $votesGroupe = array_merge($votesGroupe, array_values($voteGroupe));
                        }
                    } else {
                        break;
                    }
                    if ($number_to_import % 50 === 0) {
                        echo "Let's insert to scrutin from " . $number_to_import . "\n";
                        // insert votes
                        $this->insertAll('votes', $voteMainFields, $votesMain);
                        // insert votes infos
                        $this->insertAll('votes_info', $voteInfoFields, $votesInfo);
                        // insert votes groupes
                        $this->insertAll('votes_groupes', $voteGroupeFields, $votesGroupe);
                        $votesMain = [];
                        $votesInfo = [];
                        $votesGroupe = [];
                    }
                }
                if ($number_to_import % 50 !== 0) {
                    echo "Let's insert to scrutin until the end the : " . $number_to_import . "\n";
                    $this->insertAll('votes', $voteMainFields, $votesMain);
                    // insert votes infos
                    $this->insertAll('votes_info', $voteInfoFields, $votesInfo);
                    // insert votes groupes
                    $this->insertAll('votes_groupes', $voteGroupeFields, $votesGroupe);
                }
            }
        } elseif ($this->legislature_to_get == 14) {

            $file = 'http://data.assemblee-nationale.fr/static/openData/repository/14/loi/scrutins/Scrutins_XIV.xml.zip';
            $file = trim($file);
            $newfile = __DIR__ . '/tmp_Scrutins_XIV.xml.zip';
            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
            }
            $zip = new ZipArchive();

            if ($zip->open($newfile) !== TRUE) {
                exit("cannot open <$newfile>\n");
            } else {
                $voteMainFields = array('mpId', 'vote', 'voteNumero', 'voteId', 'legislature', 'mandatId', 'parDelegation', 'causePosition', 'voteType');
                $voteInfoFields =  array('voteId', 'voteNumero', 'organeRef', 'legislature', 'sessionREF', 'seanceRef', 'dateScrutin', 'quantiemeJourSeance', 'codeTypeVote', 'libelleTypeVote', 'typeMajorite', 'sortCode', 'titre', 'demandeur', 'modePublicationDesVotes', 'nombreVotants', 'suffragesExprimes', 'nbrSuffragesRequis', 'decomptePour', 'decompteContre', 'decompteAbs', 'decompteNv');
                $voteGroupeFields = array('voteId', 'voteNumero', 'legislature', 'organeRef', 'nombreMembresGroupe', 'positionMajoritaire', 'nombrePours', 'nombreContres', 'nombreAbstentions', 'nonVotants');
                $votesMain = [];
                $votesInfo = [];
                $votesGroupe = [];

                $xml_string = $zip->getFromName('Scrutins_XIV.xml');
                if ($xml_string != false) {
                    $xml = simplexml_load_string($xml_string);
                    foreach ($xml->xpath('//acteurRef/ancestor::scrutin[(numero>=' . 1350 . ')]') as $xml2) {

                        foreach ($xml2->xpath('.//acteurRef') as $mp) {
                            $item['mpId'] = $mp;

                            $mandatId = $mp->xpath("following-sibling::mandatRef");
                            $item['mandatId'] = $mandatId[0];

                            $vote = $mp->xpath('../..');
                            $item['vote'] = $vote[0]->getName();

                            if ($item['vote'] == 'pours' || $item['vote'] == 'pour') {
                                $vote = 1;
                            } elseif ($item['vote'] == 'contres' || $item['vote'] == 'contre') {
                                $vote = -1;
                            } elseif ($item['vote'] == 'abstentions' || $item['vote'] == 'abstention') {
                                $vote = 0;
                            } elseif ($item['vote'] == 'nonVotants' || $item['vote'] == 'nonVotant') {
                                $vote = 'nv';
                            } elseif ($item['vote'] == 'nonVotantsVolontaires' || $item['vote'] == 'nonVotantsVolontaire') {
                                $vote = 'nv';
                            } else {
                                $vote = NULL;
                            }

                            $voteId = $mp->xpath("./ancestor::scrutin/uid");
                            $item['voteId'] = $voteId[0];

                            $voteNumero = $mp->xpath("./ancestor::scrutin/numero");
                            $item['voteNumero'] = $voteNumero[0];

                            $miseaupoint = $mp->xpath("./../../..");
                            $item['voteType'] = $miseaupoint[0]->getName();

                            $voteMain = array('mpId' => $item['mpId'], 'vote' => $vote, 'voteNumero' => $item['voteNumero'], 'voteId' => $item['voteId'], 'legislature' => $this->legislature_to_get, 'mandatId' => $item['mandatId'], 'parDelegation' => null, 'causePosition' => null, 'voteType' => $item['voteType']);
                            $votesMain = array_merge($votesMain, array_values($voteMain));
                        }
                        $voteId = $xml2->xpath("./*[local-name()='uid']");
                        $item['voteId'] = $voteId[0];

                        $voteNumero = $xml2->xpath("./*[local-name()='numero']");
                        $item['voteNumero'] = $voteNumero[0];

                        $organeRef = $xml2->xpath("./*[local-name()='organeRef']");
                        $item['organeRef'] = $organeRef[0];

                        $sessionRef = $xml2->xpath("./*[local-name()='sessionRef']");
                        $item['sessionRef'] = $sessionRef[0];

                        $seanceRef = $xml2->xpath("./*[local-name()='seanceRef']");
                        $item['seanceRef'] = $seanceRef[0];

                        $dateScrutin = $xml2->xpath("./*[local-name()='dateScrutin']");
                        $item['dateScrutin'] = $dateScrutin[0];

                        $quantiemeJourSeance = $xml2->xpath("./*[local-name()='quantiemeJourSeance']");
                        $item['quantiemeJourSeance'] = $quantiemeJourSeance[0];

                        $codeTypeVote = $xml2->xpath("./*[local-name()='typeVote']/*[local-name()='codeTypeVote']");
                        $item['codeTypeVote'] = $codeTypeVote[0];

                        $libelleTypeVote = $xml2->xpath("./*[local-name()='typeVote']/*[local-name()='libelleTypeVote']");
                        $item['libelleTypeVote'] = $libelleTypeVote[0];

                        $typeMajorite = $xml2->xpath("./*[local-name()='typeVote']/*[local-name()='typeMajorite']");
                        $item['typeMajorite'] = $typeMajorite[0];

                        $sortCode = $xml2->xpath("./*[local-name()='sort']/*[local-name()='code']");
                        $item['sortCode'] = $sortCode[0];

                        $titre = $xml2->xpath("./*[local-name()='titre']");
                        $item['titre'] = $titre[0];

                        $demandeur = $xml2->xpath("./*[local-name()='demandeur']/*[local-name()='texte']");
                        $item['demandeur'] = $demandeur[0];

                        $modePublicationDesVotes = $xml2->xpath("./*[local-name()='modePublicationDesVotes']");
                        $item['modePublicationDesVotes'] = $modePublicationDesVotes[0];

                        $nombreVotants = $xml2->xpath("./*[local-name()='syntheseVote']/*[local-name()='nombreVotants']");
                        $item['nombreVotants'] = $nombreVotants[0];

                        $suffragesExprimes = $xml2->xpath("./*[local-name()='syntheseVote']/*[local-name()='suffragesExprimes']");
                        $item['suffragesExprimes'] = $suffragesExprimes[0];

                        $nbrSuffragesRequis = $xml2->xpath("./*[local-name()='syntheseVote']/*[local-name()='nbrSuffragesRequis']");
                        $item['nbrSuffragesRequis'] = $nbrSuffragesRequis[0];

                        $decomptePour = $xml2->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='pour']");
                        $item['decomptePour'] = $decomptePour[0];

                        $decompteContre = $xml2->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='contre']");
                        $item['decompteContre'] = $decompteContre[0];

                        $decompteAbs = $xml2->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='abstentions']");
                        $item['decompteAbs'] = $decompteAbs[0];

                        $decompteNv = $xml2->xpath("./*[local-name()='syntheseVote']/*[local-name()='decompte']/*[local-name()='nonVotants']");
                        $item['decompteNv'] = $decompteNv[0];

                        $voteInfo = array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'organeRef' => $item['organeRef'], 'legislature' => $this->legislature_to_get, 'sessionREF' => $item['sessionRef'], 'seanceRef' => $item['seanceRef'], 'dateScrutin' => $item['dateScrutin'], 'quantiemeJourSeance' => $item['quantiemeJourSeance'], 'codeTypeVote' => $item['codeTypeVote'], 'libelleTypeVote' => $item['libelleTypeVote'], 'typeMajorite' => $item['typeMajorite'], 'sortCode' => $item['sortCode'], 'titre' => $item['titre'], 'demandeur' => $item['demandeur'], 'modePublicationDesVotes' => $item['modePublicationDesVotes'], 'nombreVotants' => $item['nombreVotants'], 'suffragesExprimes' => $item['suffragesExprimes'], 'nbrSuffragesRequis' => $item['nbrSuffragesRequis'], 'decomptePour' => $item['decomptePour'], 'decompteContre' => $item['decompteContre'], 'decompteAbs' => $item['decompteAbs'], 'decompteNv' => $item['decompteNv']);
                        $votesInfo = array_merge($votesInfo, array_values($voteInfo));

                        foreach ($xml2->xpath('.//groupe') as $groupe) {

                            $voteId = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='uid']");
                            $item['voteId'] = $voteId[0];

                            $voteNumero = $groupe->xpath("./ancestor::*[local-name()='scrutin']/*[local-name()='numero']");
                            $item['voteNumero'] = $voteNumero[0];

                            $organeRef = $groupe->xpath("./*[local-name()='organeRef']");
                            $item['organeRef'] = $organeRef[0];

                            $nombreMembresGroupe = $groupe->xpath("./*[local-name()='nombreMembresGroupe']");
                            $item['nombreMembresGroupe'] = $nombreMembresGroupe[0];

                            $positionMajoritaire = $groupe->xpath("./*[local-name()='vote']/*[local-name()='positionMajoritaire']");
                            $item['positionMajoritaire'] = $positionMajoritaire[0];

                            $nombrePours = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='pour']");
                            $item['nombrePours'] = $nombrePours[0];

                            $nombreContres = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='contre']");
                            $item['nombreContres'] = $nombreContres[0];

                            $nombreAbstentions = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='abstention']");
                            $item['nombreAbstentions'] = $nombreAbstentions[0];

                            $nonVotants = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotant']");
                            if (isset($nonVotants[0])) {
                                $item['nonVotants'] = $nonVotants[0];
                            } else {
                                $nonVotants = $groupe->xpath("./*[local-name()='vote']/*[local-name()='decompteVoix']/*[local-name()='nonVotants']");
                                if (isset($nonVotants[0])) {
                                    $item['nonVotants'] = $nonVotants[0];
                                } else {
                                    $item['nonVotants'] = null;
                                }
                            }

                            $total_votant = $item['nombrePours'] + $item['nombreContres'] + $item['nombreAbstentions'];
                            if ($total_votant == '0') {
                                $positionMajoritaire = 'nv';
                            } else {
                                $positionMajoritaire = $item['positionMajoritaire'];
                            }
                            $voteGroupe = array('voteId' => $item['voteId'], 'voteNumero' => $item['voteNumero'], 'legislature' => $this->legislature_to_get, 'organeRef' => $item['organeRef'], 'nombreMembresGroupe' => $item['nombreMembresGroupe'], 'positionMajoritaire' => $positionMajoritaire, 'nombrePours' => $item['nombrePours'], 'nombreContres' => $item['nombreContres'], 'nombreAbstentions' => $item['nombreAbstentions'], 'nonVotants' => $item['nonVotants']);
                            $votesGroupe = array_merge($votesGroupe, array_values($voteGroupe));
                        }
                    }
                    $this->insertAll('votes', $voteMainFields, $votesMain);
                    $this->insertAll('votes_info', $voteInfoFields, $votesInfo);
                    $this->insertAll('votes_groupes', $voteGroupeFields, $votesGroupe);
                }
            }
        }
    }

    public function updateVoteInfo()
    {
        echo "updateVoteInfo starting \n";
        $results = $this->bdd->query('
            SELECT *
            FROM votes_info
            WHERE legislature = "' . $this->legislature_to_get . '"
            ORDER BY voteNumero DESC
        ');

        while ($data = $results->fetch()) {
            $num = $data["voteNumero"];
            $titre = $data["titre"];

            //variable type_vote
            if (strpos($titre, "ensemble d")) {
                $type_vote = "final";
            } elseif (strpos($titre, "sous-amendement") || strpos($titre, "sous-amendment")) {
                $type_vote = "sous-amendement";
            } elseif (strpos($titre, "'amendement")) {
                $type_vote = "amendement";
            } elseif (substr($titre, 0, 8) == "l'articl" || substr($titre, 0, 8) == " l'artic") {
                $type_vote = "article";
            } elseif (strpos($titre, "a motion de rejet prealable") || strpos($titre, "a motion de rejet préalable")) {
                $type_vote = "motion de rejet préalable";
            } elseif (strpos($titre, "a motion de renvoi en commi")) {
                $type_vote = "motion de renvoi en commission";
            } elseif (strpos($titre, "a motion de censure")) {
                $type_vote = "motion de censure";
            } elseif (strpos($titre, "motion référendaire")) {
                $type_vote = "motion référendaire";
            } elseif (strpos($titre, "a declaration de politique generale")) {
                $type_vote = "declaration de politique generale";
            } elseif (strpos($titre, "es crédits de la mission") || strpos($titre, "es credits de")) {
                $type_vote = "crédits de mission";
            } elseif (strpos($titre, "a déclaration du Gouvernement")) {
                $type_vote = "déclaration du gouvernement";
            } elseif (strpos($titre, "partie du projet de loi de finances")) {
                $type_vote = "partie du projet de loi de finances";
            } elseif (strpos($titre, "demande de constitution de commission speciale") | strpos($titre, "demande de constitution de la commission speciale")) {
                $type_vote = "demande de constitution de commission speciale";
            } elseif (strpos($titre, "demande de suspension de séance")) {
                $type_vote = "demande de suspension de séance";
            } elseif (strpos($titre, "motion d'ajournement")) {
                $type_vote = "motion d'ajournement";
            } elseif (strpos($titre, "conclusions de rejet de la commission")) {
                $type_vote = "conclusions de rejet de la commission";
            } else {
                $type_vote = substr($titre, 0, 8);
                //$type_vote = "REVOIR";
            }

            //variable amdt_n
            if ($type_vote == "amendement") {
                $amdt_n = substr($titre, 0, 25);
                $amdt_n = preg_replace("/[^0-9]/", "", $amdt_n);
            } else {
                $amdt_n = NULL;
            }

            //varible article_n
            if ($type_vote == "article") {
                $pos_article = NULL;
                if (strpos($titre, "article premier")) {
                    $article_n = 1;
                } else {
                    $article_n = substr($titre, 0, 20);
                    $article_n = preg_replace("/[^0-9]/", "", $article_n);
                }
            } elseif (strpos($titre, "a l'article")) {
                // "a l'article"
                $a_article = substr($titre, strpos($titre, "a l'article") + 1, 20);
                $pos_article = "a";
                if (strpos($a_article, "premier")) {
                    $article_n = 1;
                } else {
                    $article_n = preg_replace("/[^0-9]/", "", $a_article);
                }
            } elseif (strpos($titre, "apres l'article")) {
                // "apres l'article"
                $pos_article = "après";
                $a_article = substr($titre, strpos($titre, "apres l'article") + 1, 25);
                if (strpos($a_article, "premier")) {
                    $article_n = 1;
                } else {
                    $article_n = preg_replace("/[^0-9]/", "", $a_article);
                }
            } elseif (strpos($titre, "avant l'article")) {
                // "avant l'article"
                $pos_article = "avant";
                $a_article = substr($titre, strpos($titre, "avant l'article") + 1, 25);
                if (strpos($a_article, "premier")) {
                    $article_n = 1;
                } else {
                    $article_n = preg_replace("/[^0-9]/", "", $a_article);
                }
            } else {
                $article_n = NULL;
                $pos_article = NULL;
            }

            //variable "bister"
            if (strpos($titre, "bis")) {
                // BIS
                $b = substr($titre, strpos($titre, "bis") + -1, 9);
                if (strpos($b, "bis AA")) {
                    $bister = "bis AA";
                } elseif (strpos($b, "bis A ")) {
                    $bister = "bis A";
                } elseif (strpos($b, "bis B ")) {
                    $bister = "bis B";
                } elseif (strpos($b, "bis F ")) {
                    $bister = "bis F";
                } elseif (strpos($b, "bis D ")) {
                    $bister = "bis D";
                } elseif (strpos($b, "bis C")) {
                    $bister = "bis C";
                } elseif (strpos($b, "bis E")) {
                    $bister = "bis E";
                } elseif (strpos($b, "bis")) {
                    $bister = "bis";
                } else {
                    //$bister = "error".$b;
                    $bister = "error";
                }
            } elseif (strpos($titre, " ter ")) {
                // TER
                $ter = substr($titre, strpos($titre, " ter ") + -1, 9);
                if (strpos($ter, "ter B ")) {
                    $bister = "ter B";
                } elseif (strpos($ter, "ter C ")) {
                    $bister = "ter C";
                } elseif (strpos($ter, "ter A ")) {
                    $bister = "ter A";
                } elseif (strpos($ter, "ter D ")) {
                    $bister = "ter D";
                } elseif (strpos($ter, "ter B")) {
                    $bister = "ter B";
                } elseif (strpos($ter, "ter")) {
                    $bister = "ter";
                } else {
                    //$bister = "error".$ter;
                    $bister = "error";
                }
            } else {
                $bister = NULL;
            }

            // INSER INTO DATABASE.
            try {
                $sql = ("UPDATE votes_info SET
                    voteType = '" . addslashes($type_vote) . "',
                    amdt = '$amdt_n',
                    article = '$article_n',
                    bister = '$bister',
                    posArticle = '$pos_article'
                WHERE voteNumero = $num AND legislature = $this->legislature_to_get");
                $stmt = $this->bdd->prepare($sql);
                $stmt->execute();
            } catch (PDOException $e) {
                echo $sql . "\n" . $e->getMessage();
            }
        }
    }

    public function voteScore()
    {
        echo "voteScore starting \n";

        $reponse_last_vote = $this->bdd->query('
            SELECT voteNumero AS lastVote
            FROM votes_scores
            WHERE legislature = "' . $this->legislature_to_get . '"
            ORDER BY voteNumero DESC
            LIMIT 1
        ');

        $donnees_last_vote = $reponse_last_vote->fetch();
        $lastVote = isset($donnees_last_vote['lastVote']) ? $donnees_last_vote['lastVote'] + 1 : 1;
        echo "Vote score from " . $lastVote . "\n";

        $reponseVote = $this->bdd->query('
            SELECT B.voteNumero, B.legislature, B.mpId, B.vote, B.mandatId, B.sortCode, B.positionGroup, B.gvtPosition AS positionGvt,
            case when B.vote = B.positionGroup then 1 else 0 end as scoreLoyaute,
            case when B.vote = B.sortCode then 1 else 0 end as scoreGagnant,
            case when B.vote = B.gvtPosition then 1 else 0 end as scoreGvt,
            1 as scoreParticipation
            FROM
            (
            SELECT A.*,
            case
            when vg.positionMajoritaire = "pour" then 1
            when vg.positionMajoritaire = "contre" then -1
            when vg.positionMajoritaire = "abstention" then 0
            else "error" end as positionGroup,
            case
            when gvt.positionMajoritaire = "pour" then 1
            when gvt.positionMajoritaire = "contre" then -1
            when gvt.positionMajoritaire = "abstention" then 0
            else "error" end as gvtPosition
            FROM
            (
            SELECT v.voteNumero, v.mpId, v.vote,
            case
            when sortCode = "adopté" then 1
            when sortCode = "rejeté" then -1
            else 0 end as sortCode,
            v.legislature,
            mg.mandatId, mg.organeRef
            FROM votes v
            JOIN votes_info vi ON vi.voteNumero = v.voteNumero AND vi.legislature = v.legislature
            LEFT JOIN mandat_groupe mg ON mg.mpId = v.mpId
            AND ((vi.dateScrutin BETWEEN mg.dateDebut AND mg.dateFin ) OR (vi.dateScrutin >= mg.dateDebut AND mg.dateFin IS NULL))
            AND mg.codeQualite IN ("Membre", "Député non-inscrit", "Membre apparenté")
            LEFT JOIN organes o ON o.uid = vi.organeRef
            WHERE v.voteType = "decompteNominatif" AND v.voteNumero >= "' . $lastVote . '" AND v.legislature = "' . $this->legislature_to_get . '" AND vote != "nv"
            ) A
            LEFT JOIN votes_groupes vg ON vg.organeRef = A.organeRef AND vg.voteNumero = A.voteNumero AND vg.legislature = A.legislature
            LEFT JOIN votes_groupes gvt ON gvt.organeRef IN ("PO730964", "PO713077", "PO656002") AND gvt.voteNumero = A.voteNumero AND gvt.legislature = A.legislature
            ) B
        ');
        echo "requete ok\n";

        $votesScore = [];
        $voteScore = [];
        $voteScoreFields = array('voteNumero', 'legislature', 'mpId', 'vote', 'mandatId', 'sortCode', 'positionGroup', 'scoreLoyaute', 'scoreGagnant', 'scoreParticipation', 'positionGvt', 'scoreGvt', 'dateMaj');
        $i = 1;
        while ($x = $reponseVote->fetch(PDO::FETCH_ASSOC)) {
            echo "ok";
            $voteScore = array(
                'voteNumero' => $x['voteNumero'],
                'legislature' => $x['legislature'],
                'mpId' => $x['mpId'],
                'vote' => $x['vote'],
                'mandatId' => $x['mandatId'],
                'sortCode' => $x['sortCode'],
                'positionGroup' => $x['positionGroup'],
                'scoreLoyaute' => $x['scoreLoyaute'],
                'scoreGagnant' => $x['scoreGagnant'],
                'scoreParticipation' => $x['scoreParticipation'],
                'positionGvt' => $x['positionGvt'],
                'scoreGvt' => $x['scoreGvt'],
                'dateMaj' => $this->dateMaj
            );
            $votesScore = array_merge($votesScore, array_values($voteScore));
            if ($i % 1000 === 0) {
                echo "Let's import until vote n " . $i . "\n";
                $this->insertAll('votes_scores', $voteScoreFields, $votesScore);
                $votesScore = [];
                $voteScore = [];
            }
            echo $i++;
        }
        echo "Let's import until the end vote : " . $i . "\n";
        $this->insertAll('votes_scores', $voteScoreFields, $votesScore);
    }

    public function groupeCohesion()
    {
        echo "groupeCohesion starting \n";
        $reponse_last_vote = $this->bdd->query('
            SELECT voteNumero AS lastVote
            FROM groupes_cohesion
            WHERE legislature = "' . $this->legislature_to_get . '"
            ORDER BY voteNumero DESC
            LIMIT 1
        ');

        $donnees_last_vote = $reponse_last_vote->fetch();
        $lastVote = isset($donnees_last_vote['lastVote']) ? $donnees_last_vote['lastVote'] + 1 : 1;

        $reponseVote = $this->bdd->query('
            SELECT A.*,
            CASE
                WHEN A.positionGroup = A.voteResult THEN 1
                ELSE 0
            END AS scoreGagnant
            FROM
            (
                SELECT vg.voteNumero, vg.organeRef, o.libelle, vg.legislature, vg.nombrePours, vg.nombreContres, vg.nombreAbstentions,
                    ROUND((GREATEST(vg.nombrePours,nombreContres, nombreAbstentions)-0.5*((nombrePours + nombreContres + nombreAbstentions)-GREATEST(vg.nombrePours, vg.nombreContres, vg.nombreAbstentions)))/(vg.nombrePours + vg.nombreContres + vg.nombreAbstentions),3) as cohesion,
                CASE
                WHEN vg.positionMajoritaire = "pour" THEN 1
                WHEN vg.positionMajoritaire = "abstention" THEN 0
                WHEN vg.positionMajoritaire = "contre" THEN -1
                WHEN vg.positionMajoritaire = "nv" THEN "nv"
                ELSE "error"
                END AS positionGroup,
                CASE
                WHEN vi.sortCode = "adopté" THEN 1
                WHEN vi.sortCode = "rejeté" THEN -1
                ELSE vi.sortCode
                END AS voteResult
                FROM votes_groupes vg
                JOIN organes o ON vg.organeRef = o.uid
                JOIN votes_info vi ON vi.voteNumero = vg.voteNumero AND vi.legislature = vg.legislature
                WHERE vg.legislature = "' . $this->legislature_to_get . '" AND (vg.voteNumero >= "' . $lastVote . '")
            ) A
        ');

        $groupesCohesion = [];
        $groupeCohesion = [];
        $groupeCohesionFields = array('voteNumero', 'legislature', 'organeRef', 'cohesion', 'positionGroupe', 'voteSort', 'scoreGagnant');
        $i = 1;
        while ($donneesVote = $reponseVote->fetch()) {
            $groupeCohesion = array('voteNumero' => $donneesVote['voteNumero'], 'legislature' => $donneesVote['legislature'], 'organeRef' => $donneesVote['organeRef'], 'cohesion' => $donneesVote['cohesion'], 'positionGroupe' => $donneesVote['positionGroup'], 'voteSort' => $donneesVote['voteResult'], 'scoreGagnant' => $donneesVote['scoreGagnant']);
            $groupesCohesion = array_merge($groupesCohesion, array_values($groupeCohesion));
            if ($i % 1000 === 0) {
                echo "Let's insert until a pack of 1000 rows \n";
                $this->insertAll('groupes_cohesion', $groupeCohesionFields, $groupesCohesion);
                $groupeCohesion = [];
                $groupesCohesion = [];
            }
            $i++;
        }
        if ($i % 1000 !== 0) {
            echo "Let's insert what's left \n";
            $this->insertAll('groupes_cohesion', $groupeCohesionFields, $groupesCohesion);
        }
    }

    public function groupeAccord()
    {
        echo "groupeAccord starting \n";

        $this->bdd->query('
            CREATE TABLE IF NOT EXISTS groupes_accord (
                id INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                voteNumero INT(6) NOT NULL,
                legislature TINYINT(2) NOT NULL,
                organeRef VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                organeRefAccord VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                accord TINYINT(2) NULL,
                dateMaj DATE
            );
            CREATE INDEX idx_organeRef ON groupes_accord(organeRef);
            CREATE INDEX idx_organeRefAccord ON groupes_accord(organeRefAccord);
        ');

        $reponse_last_vote = $this->bdd->query('
            SELECT voteNumero AS lastVote
            FROM groupes_accord
            WHERE legislature = "' . $this->legislature_to_get . '"
            ORDER BY voteNumero DESC
            LIMIT 1
        ');

        $donnees_last_vote = $reponse_last_vote->fetch();
        $lastVote = isset($donnees_last_vote['lastVote']) ? $donnees_last_vote['lastVote'] + 1 : 1;
        echo "groupe accord from vote : " . $lastVote . "\n";


        $response = $this->bdd->query('
            SELECT A.*,
            CASE WHEN positionGroupe = positionGroupeAccord THEN 1 ELSE 0 END AS accord
            FROM
            (
                SELECT t1.voteNumero, t1.legislature, t1.organeRef, t1.positionGroupe, t2.organeRef AS organeRefAccord, t2.positionGroupe AS positionGroupeAccord
                FROM groupes_cohesion t1
                LEFT JOIN groupes_cohesion t2 ON t1.voteNumero = t2.voteNumero AND t1.legislature = t2.legislature
                WHERE t1.voteNUmero >= "' . $lastVote . '" AND t1.legislature = "' . $this->legislature_to_get . '"
            ) A
        ');

        $groupesAccord = [];
        $groupeAccord = [];
        $groupeAccordFields = array('voteNumero', 'legislature', 'organeRef', 'organeRefAccord', 'accord', 'dateMaj');
        $i = 1;
        while ($data = $response->fetch()) {
            $groupeAccord = array('voteNumero' => $data['voteNumero'], 'legislature' => $data['legislature'], 'organeRef' => $data['organeRef'], 'organeRefAccord' => $data['organeRefAccord'], 'accord' => $data['accord'], 'dateMaj' => $this->dateMaj);
            $groupesAccord = array_merge($groupesAccord, array_values($groupeAccord));
            if ($i % 1000 === 0) {
                echo "Let's insert until a pack of 1000 rows \n";
                $this->insertAll('groupes_accord', $groupeAccordFields, $groupesAccord);
                $groupesAccord = [];
                $groupeAccord = [];
            }
            $i++;
        }
        if ($i % 1000 !== 0) {
            echo "Let's insert what's left \n";
            $this->insertAll('groupes_accord', $groupeAccordFields, $groupesAccord);
        }
    }

    public function deputeAccord()
    {
        echo "deputeAccord starting \n";
        $reponse_last_vote = $this->bdd->query('
        SELECT voteNumero AS lastVote
        FROM deputes_accord
        WHERE legislature = "' . $this->legislature_to_get . '"
        ORDER BY voteNumero DESC
        LIMIT 1
        ');

        $donnees_last_vote = $reponse_last_vote->fetch();
        $lastVote = isset($donnees_last_vote['lastVote']) ? $donnees_last_vote['lastVote'] + 1 : 1;
        echo 'depute accord from vote : ' . $lastVote . "\n";

        $query = $this->bdd->query('
        SELECT vs.voteNumero, vs.legislature, vs.mpId, gc.organeRef,
        CASE WHEN vs.vote = gc.positionGroupe THEN 1 ELSE 0 END AS accord
        FROM votes_scores vs
        LEFT JOIN groupes_cohesion gc ON vs.voteNumero = gc.voteNumero AND vs.legislature = gc.legislature
        WHERE vs.legislature = "' . $this->legislature_to_get . '" AND vs.voteNumero >= "' . $lastVote . '"
        ');

        $deputesAccord = [];
        $deputeAccord = [];
        $deputeAccordFields = array('voteNumero', 'legislature', 'mpId', 'organeRef', 'accord');
        $i = 1;
        while ($group = $query->fetch()) {
            $deputeAccord = array(
                'voteNumero' => $group['voteNumero'],
                'legislature' => $group['legislature'],
                'mpId' => $group['mpId'],
                'organeRef' => $group['organeRef'],
                'accord' => $group['accord']
            );
            $deputesAccord = array_merge($deputesAccord, array_values($deputeAccord));
            if ($i % 1000 === 0) {
                echo "let's insert a pack of 1000\n";
                $this->insertAll('deputes_accord', $deputeAccordFields, $deputesAccord);
                $deputesAccord = [];
                $deputeAccord = [];
            }
            $i++;
        }
        $this->insertAll('deputes_accord', $deputeAccordFields, $deputesAccord);
    }

    public function voteParticipation()
    {
        echo "voteParticipation starting \n";

        if ($this->legislature_to_get == 14) {
            $votesLeft = $this->bdd->query('
                SELECT voteNumero
                FROM votes_info
                WHERE legislature = 14 AND codeTypeVote IN ("SAT", "SPS", "MOC") AND voteNumero NOT IN (
                    SELECT DISTINCT(voteNumero)
                    FROM votes_participation
                    WHERE legislature = 14 AND voteNumero
                )
                ORDER BY voteNumero ASC
            ');
        } elseif ($this->legislature_to_get == 15) {
            $votesLeft = $this->bdd->query('
                SELECT voteNumero
                FROM votes_info
                WHERE legislature = 15 AND voteNumero NOT IN (
                    SELECT DISTINCT(voteNumero)
                    FROM votes_participation
                    WHERE legislature = 15 AND voteNumero
                )
                ORDER BY voteNumero ASC
            ');
        }

        $i = 1;
        $votesParticipation = [];
        $voteParticipation = [];
        $voteParticipationFields = array('legislature', 'voteNumero', 'mpId', 'participation');
        while ($vote = $votesLeft->fetch()) {

            $voteQuery = $this->bdd->query('
                SELECT A.*, v.vote,
                CASE
                    WHEN vote IN ("1", "0", "-1") THEN 1
                    WHEN vote = "nv" THEN NULL
                    ELSE 0
                END AS participation
                FROM
                (
                SELECT vi.voteNumero, vi.legislature, mp.mpId
                FROM votes_info vi
                LEFT JOIN mandat_principal mp ON ((vi.dateScrutin BETWEEN mp.datePriseFonction AND mp.dateFin) OR (mp.datePriseFonction < vi.dateScrutin AND mp.dateFin IS NULL))
                WHERE vi.legislature = "' . $this->legislature_to_get . '" AND vi.voteNumero = "' . $vote['voteNumero'] . '"
                ) A
                LEFT JOIN votes_scores v ON A.mpId = v.mpId AND A.legislature = v.legislature AND A.voteNumero = v.voteNumero
            ');

            while ($mp = $voteQuery->fetch()) {
                $voteParticipation = array('legislature' => $mp['legislature'], 'voteNumero' => $mp['voteNumero'], 'mpId' => $mp['mpId'], 'participation' => $mp['participation']);
                $votesParticipation = array_merge($votesParticipation, array_values($voteParticipation));
                if ($i % 1000 === 0) {
                    echo "let's insert this pack of 1000\n";
                    $this->insertAll('votes_participation', $voteParticipationFields, $votesParticipation);
                    $votesParticipation = [];
                }
                $i++;
            }
        }
        $this->insertAll('votes_participation', $voteParticipationFields, $votesParticipation);
    }

    public function voteParticipationCommission()
    {
        echo "voteParticipationCommission starting \n";
        if ($this->legislature_to_get == 15) {
            $result = $this->bdd->query('
            SELECT voteNumero
            FROM votes_participation_commission
            ORDER BY voteNumero DESC
            LIMIT 1
            ');

            $last = $result->fetch();
            $last_vote = isset($last['voteNumero']) ? $last['voteNumero'] + 1 : 1;
            echo 'Vote participation commission from : ' . $last_vote . "\n";
            $legislature = 15;

            $votes = $this->bdd->query('
                SELECT vi.voteNumero, vi.legislature, vi.dateScrutin, d.*, o.libelleAbrev
                FROM votes_info vi
                LEFT JOIN votes_dossiers vd ON vi.voteNumero = vd.voteNumero AND vi.legislature = vd.legislature
                LEFT JOIN dossiers d ON vd.dossier = d.titreChemin AND d.legislature = vi.legislature
                LEFT JOIN organes o ON d.commissionFond = o.uid
                WHERE vi.voteNumero > "' . $last_vote . '" AND vi.legislature = 15
                ORDER BY vi.voteNumero ASC
            ');

            $votesCommissionParticipation = [];
            $voteCommissionParticipation = [];
            $voteCommissionParticipationFields = array('legislature', 'voteNumero', 'mpId', 'participation');
            $i = 1;
            while ($vote = $votes->fetch()) {
                $voteNumero = $vote['voteNumero'];
                $voteDate = $vote['dateScrutin'];
                $commissionFond = $vote['commissionFond'];

                if ($commissionFond != NULL) {
                  $deputes = $this->bdd->query('
                      SELECT *
                      FROM votes_participation vp
                      LEFT JOIN mandat_secondaire ms ON vp.mpId = ms.mpId
                      WHERE vp.voteNumero = "' . $voteNumero . '" AND ms.typeOrgane = "COMPER" AND ms.codeQualite = "Membre" AND ms.organeRef = "' . $commissionFond . '" AND ((DATE_ADD(ms.dateDebut, INTERVAL 1 MONTH) <= "' . $voteDate . '" AND ms.dateFin >= "' . $voteDate . '") OR (DATE_ADD(ms.dateDebut, INTERVAL 1 MONTH) <= "' . $voteDate . '" AND ms.dateFin IS NULL)) AND vp.participation IS NOT NULL
                  ');
                  if ($deputes->rowCount() > 0) {
                      while ($depute = $deputes->fetch()) {
                          $legislature = $depute['legislature'];
                          $voteNumero = $depute['voteNumero'];
                          $mpId = $depute['mpId'];
                          $participation = $depute['participation'];

                          $voteCommissionParticipation = array('legislature' => $legislature, 'voteNumero' => $voteNumero, 'mpId' => $mpId, 'participation' => $participation);
                          $votesCommissionParticipation = array_merge($votesCommissionParticipation, array_values($voteCommissionParticipation));
                      }
                  }
                }
                if ($i % 1000 === 0) {
                    echo "let's insert this pack of 1000\n";
                    $this->insertAll('votes_participation_commission', $voteCommissionParticipationFields, $votesCommissionParticipation);
                    $votesCommissionParticipation = [];
                    $voteCommissionParticipation = [];
                }
                $i++;
            }
            $this->insertAll('votes_participation_commission', $voteCommissionParticipationFields, $votesCommissionParticipation);
        }
    }

    public function classParticipation()
    {
        echo "classParticipation starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS class_participation;
        ');
        $this->bdd->query('
            CREATE TABLE class_participation AS
            SELECT A.*,
            CASE WHEN da.dateFin IS NULL THEN 1 ELSE 0 END AS active,
            curdate() AS dateMaj
            FROM
            (
            SELECT v.mpId, v.legislature, ROUND(AVG(v.participation),2) AS score, COUNT(v.participation) AS votesN, ROUND(COUNT(v.participation)/100) AS "index"
            FROM votes_participation v
            WHERE v.participation IS NOT NULL
            GROUP BY v.mpId, v.legislature
            ) A
            LEFT JOIN deputes_all da ON da.mpId = A.mpId AND da.legislature = A.legislature;
        ');

        $this->bdd->query('
            ALTER TABLE class_participation ADD INDEX idx_mpId (mpId);
            ALTER TABLE class_participation ADD INDEX idx_active (active);
        ');
    }

    public function classParticipationCommission()
    {
        echo "classParticipationCommission starting \n";
        if ($this->legislature_to_get == 15) {
            $this->bdd->query('
                DROP TABLE IF EXISTS class_participation_commission;
                CREATE TABLE class_participation_commission
                SELECT A.*, da.legislature,
                CASE WHEN da.dateFin IS NULL THEN 1 ELSE 0 END AS active,
                curdate() AS dateMaj
                FROM
                (
                SELECT v.mpId, ROUND(AVG(v.participation),2) AS score, COUNT(v.participation) AS votesN, ROUND(COUNT(v.participation)/100) AS "index"
                FROM votes_participation_commission v
                WHERE v.participation IS NOT NULL
                GROUP BY v.mpId
                ORDER BY ROUND(COUNT(v.participation)/100) DESC, AVG(v.participation) DESC
                ) A
                LEFT JOIN deputes_all da ON da.mpId = A.mpId AND da.legislature = 15;
                ALTER TABLE class_participation_commission ADD INDEX idx_mpId (mpId);
                ALTER TABLE class_participation_commission ADD INDEX idx_active (active);
            ');
        }
    }

    public function deputeLoyaute()
    {
        echo "deputeLoyaute starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS deputes_loyaute;
            CREATE TABLE deputes_loyaute
            (id INT(5) NOT NULL AUTO_INCREMENT,
            mpId VARCHAR(15)CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            mandatId VARCHAR(15)CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            score DECIMAL(4,3) NOT NULL,
            votesN INT(10) NOT NULL,
            legislature TINYINT NOT NULL,
            dateMaj DATE NOT NULL,
            PRIMARY KEY (id));
            ALTER TABLE deputes_loyaute ADD INDEX idx_mpId (mpId);
            ALTER TABLE deputes_loyaute ADD INDEX idx_mandatId (mandatId);
            ALTER TABLE deputes_loyaute ADD INDEX idx_legislature (legislature);
        ');
        $result = $this->bdd->query('
            SELECT v.mpId, v.mandatId, ROUND(AVG(v.scoreLoyaute),3) AS score, COUNT(v.scoreLoyaute) AS votesN, v.legislature
            FROM votes_scores v
            LEFT JOIN mandat_groupe mg ON mg.mandatId = v.mandatId
            WHERE v.scoreLoyaute IS NOT NULL AND mg.mandatId IS NOT NULL
            GROUP BY v.mandatId
            ORDER BY v.mpId
        ');

        $deputeLoyautes = [];
        $deputeLoyaute = [];
        $deputeLoyauteFields = array('mpId', 'mandatId', 'score', 'votesN', 'legislature', 'dateMaj');
        while ($depute = $result->fetch()) {
            $mpId = $depute["mpId"];
            $mandatId = $depute["mandatId"];
            $score = $depute["score"];
            $votesN = $depute["votesN"];
            $legislature = $depute["legislature"];

            $deputeLoyaute = array('mpId' => $mpId, 'mandatId' => $mandatId, 'score' => $score, 'votesN' => $votesN, 'legislature' => $legislature, 'dateMaj' => $this->dateMaj);
            $deputeLoyautes = array_merge($deputeLoyautes, array_values($deputeLoyaute));
        }
        $this->insertAll('deputes_loyaute', $deputeLoyauteFields, $deputeLoyautes);
    }

    public function classLoyaute()
    {
        echo "classLoyaute starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS class_loyaute;
            CREATE TABLE class_loyaute AS
            SELECT dl.mpId, dl.score, dl.votesN, dl.legislature,
            curdate() AS dateMaj
            FROM deputes_loyaute dl
            JOIN deputes_all da ON dl.mpId = da.mpId AND dl.mandatId = da.groupeMandat;
            ALTER TABLE class_loyaute ADD INDEX idx_mpId (mpId);
            ALTER TABLE class_loyaute ADD INDEX idx_legislature (legislature);
        ');
    }

    public function classMajorite()
    {
        echo "classMajorite starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS class_majorite;
            CREATE TABLE class_majorite
            SELECT A.*,
            curdate() AS dateMaj
            FROM
            (
            SELECT v.mpId, v.legislature, ROUND(AVG(v.scoreGvt),3) AS score, COUNT(v.scoreGvt) AS votesN
            FROM votes_scores v
            WHERE v.scoreGvt IS NOT NULL
            GROUP BY v.mpId, v.legislature
            ) A;
            ALTER TABLE class_majorite ADD INDEX idx_mpId (mpId);
            ALTER TABLE class_majorite ADD INDEX idx_legislature (legislature);
        ');
    }

    public function classGroups()
    {
        echo "classGroups starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS class_groups;
            CREATE TABLE class_groups AS
            SELECT c.organeRef, c.legislature, c.active, c.cohesion, c.votesN_cohesion, p.participation, p.votesN_participation, m.majoriteAccord, m.votesN AS votesN_majorite, curdate() AS dateMaj
            FROM
            (
                SELECT gc.organeRef, gc.legislature, ROUND(AVG(gc.cohesion),3) AS cohesion, COUNT(voteNumero) AS votesN_cohesion,
                CASE WHEN o.dateFin IS NULL THEN 1 ELSE 0 END AS active
                FROM groupes_cohesion gc
                LEFT JOIN organes o ON o.uid = gc.organeRef
                GROUP BY gc.organeRef
            ) c
            LEFT JOIN
            (
            SELECT B.organeRef, AVG(B.participation_rate) AS participation, COUNT(voteNumero) AS votesN_participation
            FROM
            (
                SELECT A.*, A.total / A.n AS participation_rate
                FROM
                (
                    SELECT voteNumero, organeRef, nombreMembresGroupe as n, nombrePours as pour, nombreContres as contre, nombreAbstentions as abstention, nonVotants as nv, nonVotantsVolontaires as nvv, nombrePours+nombreContres+nombreAbstentions as total
                    FROM votes_groupes
                ) A
            ) B
            GROUP BY B.organeRef
            ) p ON p.organeRef = c.organeRef
            LEFT JOIN
            (
                SELECT ga.organeRef, ROUND(AVG(ga.accord), 3) AS majoriteAccord, COUNT(ga.accord) AS votesN
                FROM groupes_accord ga
                LEFT JOIN organes o ON o.uid = ga.organeRef
                WHERE organeRefAccord IN ("PO730964", "PO713077", "PO656002")
                GROUP BY ga.organeRef
            ) m ON m.organeRef = c.organeRef;
            ALTER TABLE class_groups ADD INDEX idx_organeRef (organeRef);
            ALTER TABLE class_groups ADD INDEX idx_active (active);
            ALTER TABLE class_groups ADD INDEX idx_legislature (legislature);
        ');
    }

    public function classGroupsProximite()
    {
        echo "classGroupsProximite starting \n";
        $this->bdd->query('DROP TABLE IF EXISTS class_groups_proximite');

        $this->bdd->query('
            CREATE TABLE class_groups_proximite AS
            SELECT  ga.organeRef, ga.legislature, ga.organeRefAccord AS prox_group, ROUND(AVG(accord), 4) AS score, COUNT(accord) AS votesN, curdate() AS dateMaj
            FROM groupes_accord ga
            WHERE ga.organeRef != ga.organeRefAccord
            GROUP BY ga.organeRef, ga.organeRefAccord
        ');

        $this->bdd->query("ALTER TABLE class_groups_proximite ADD INDEX idx_organeRef (organeRef)");
        $this->bdd->query("ALTER TABLE class_groups_proximite ADD INDEX idx_legislature (legislature)");
    }

    public function votesDossiers()
    {
        echo "votesDossiers starting \n";
        $this->bdd->query('DELETE FROM votes_dossiers WHERE legislature = "' . $this->legislature_to_get . '"');

        //Until where to go?
        $until_html = file_get_html("http://www2.assemblee-nationale.fr/scrutins/liste/(legislature)/'.$this->legislature_to_get.'/(type)/TOUS/(idDossier)/TOUS");
        $pagination = $until_html->find('.pagination-bootstrap ul', 0);
        $last = $pagination->find('li', -2)->plaintext;
        $until = ($last - 1) * 100;

        //array urls to get
        $offsets = range(0, $until, 100);

        $voteDossiers = [];
        $voteDossier = [];
        $voteDossiersFields = array('offset_num', 'legislature', 'voteNumero', 'href', 'dossier');
        $i = 1;
        foreach ($offsets as $offset) {
            $url = "http://www2.assemblee-nationale.fr/scrutins/liste/(offset)/" . $offset . "/(legislature)/" . $this->legislature_to_get . "/(type)/TOUS/(idDossier)/TOUS";

            $html = file_get_html($url);
            foreach ($html->find('tbody tr') as $x) {
                //echo $x;
                $voteNumero = $x->find('.denom', 0)->plaintext;
                $voteNumero = str_replace("*", "", $voteNumero);
                $href = "";
                $dossier = "";
                foreach ($x->find('a') as $a) {
                    if ($a->plaintext == "dossier") {
                        $href = $a->href;
                        if (strpos($href, "/14/") !== false) {
                            if (strpos($href, ".asp") !== false) {
                                //echo "1";
                                $dossier1 = str_replace('https://www.assemblee-nationale.fr/14/dossiers/', '', $href);
                                $dossier = str_replace('.asp', '', $dossier1);
                            } else {
                                //echo "2";
                                $dossier = str_replace('https://www.assemblee-nationale.fr/dyn/14/dossiers/', '', $href);
                            }
                        } else {
                            if (strpos($href, ".asp") !== false) {
                                //echo "3";
                                $dossier1 = str_replace('https://www.assemblee-nationale.fr/15/dossiers/', '', $href);
                                $dossier = str_replace('.asp', '', $dossier1);
                            } else {
                                //echo "4";
                                $dossier = str_replace('https://www.assemblee-nationale.fr/dyn/15/dossiers/', '', $href);
                            }
                        }
                    }
                }

                $dossier = !empty($dossier) ? "$dossier" : NULL;
                $href = !empty($href) ? "$href" : NULL;

                $voteDossier = array('offset_num' => $offset, 'legislature' => $this->legislature_to_get, 'voteNumero' => $voteNumero, 'href' => $href, 'dossier' => $dossier);
                $voteDossiers = array_merge($voteDossiers, array_values($voteDossier));
                if ($i % 1000 === 0) {
                    echo "Let's insert 1000 rows\n";
                    $this->insertAll('votes_dossiers', $voteDossiersFields, $voteDossiers);
                    $voteDossiers = [];
                }
                $i++;
            }
            $html->clear();
        }
        $this->insertAll('votes_dossiers', $voteDossiersFields, $voteDossiers);
    }

    public function dossier()
    {
        echo "dossier starting \n";
        $this->bdd->query('
            DELETE FROM dossiers WHERE legislature = "' . $this->legislature_to_get . '"
        ');

        $dossierFields = array('dossierId', 'legislature', 'titre', 'titreChemin', 'senatChemin', 'procedureParlementaireCode', 'procedureParlementaireLibelle', 'commissionFond');
        $dossier = [];
        $dossiers = [];
        if ($this->legislature_to_get == 15) {
            // Online file
            $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/dossiers_legislatifs/Dossiers_Legislatifs_XV.xml.zip';
            $file = trim($file);
            $newfile = __DIR__ . '/tmp_dossiers.zip';
            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
            }

            $zip = new ZipArchive();
            if ($zip->open($newfile) !== TRUE) {
                exit("cannot open <$newfile>\n");
            } else {

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    //echo 'Filename: ' . $filename . '<br />';
                    $sub = substr($filename, 0, 13);

                    if ($sub == 'xml/dossierPa') {
                        $xml_string = $zip->getFromName($filename);

                        if ($xml_string != false) {
                            $xml = simplexml_load_string($xml_string);

                            $dossierId = $xml->uid;
                            $legislature = $xml->legislature;
                            $titre = $xml->titreDossier->titre;
                            $titreChemin = $xml->titreDossier->titreChemin;
                            $senatChemin = $xml->titreDossier->senatChemin;
                            $procedureParlementaireCode = $xml->procedureParlementaire->code;
                            $procedureParlementaireLibelle = $xml->procedureParlementaire->libelle;

                            $commissionFond = $xml->xpath("//*[text()='AN1-COM-FOND']/parent::*[local-name()='acteLegislatif']/*[local-name()='organeRef']");
                            if (!empty($commissionFond)) {
                                $commissionFond = $commissionFond[0];
                            } else {
                                $commissionFond = NULL;
                            }

                            $dossier = array('dossierId' => $dossierId, 'legislature' => $legislature, 'titre' => $titre, 'titreChemin' => $titreChemin, 'senatChemin' => $senatChemin, 'procedureParlementaireCode' => $procedureParlementaireCode, 'procedureParlementaireLibelle' => $procedureParlementaireLibelle, 'commissionFond' => $commissionFond);
                            $dossiers = array_merge($dossiers, array_values($dossier));
                        }
                    }
                }
            }
        } elseif ($this->legislature_to_get == 14) {

            // Online file
            $file = 'https://data.assemblee-nationale.fr/static/openData/repository/14/loi/dossiers_legislatifs/Dossiers_Legislatifs_XIV.xml.zip';
            $file = trim($file);
            $newfile = __DIR__ . '/tmp_dossiers_14.zip';
            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
            }

            $zip = new ZipArchive();
            if ($zip->open($newfile) !== TRUE) {
                exit("cannot open <$newfile>\n");
            } else {
                $xml_string = $zip->getFromName("Dossiers_Legislatifs_XIV.xml");
                if ($xml_string != false) {
                    $xml = simplexml_load_string($xml_string);

                    foreach ($xml->xpath("//*[local-name()='dossierParlementaire']") as $dossier) {
                        $dossierId = $dossier->uid;
                        $legislature = $dossier->legislature;
                        $titre = $dossier->titreDossier->titre;
                        $titreChemin = $dossier->titreDossier->titreChemin;
                        $senatChemin = $dossier->titreDossier->senatChemin;
                        $procedureParlementaireCode = $dossier->procedureParlementaire->code;
                        $procedureParlementaireLibelle = $dossier->procedureParlementaire->libelle;

                        $commissionFond = $dossier->xpath(".//*[text()='AN1-COM-FOND']/parent::*[local-name()='acteLegislatif']/*[local-name()='organeRef']");
                        if (!empty($commissionFond)) {
                            $commissionFond = $commissionFond[0];
                        } else {
                            $commissionFond = NULL;
                        }

                        $dossier = array('dossierId' => $dossierId, 'legislature' => $legislature, 'titre' => $titre, 'titreChemin' => $titreChemin, 'senatChemin' => $senatChemin, 'procedureParlementaireCode' => $procedureParlementaireCode, 'procedureParlementaireLibelle' => $procedureParlementaireLibelle, 'commissionFond' => $commissionFond);
                        $dossiers = array_merge($dossiers, array_values($dossier));
                    }
                }
            }
        }
        $this->insertAll('dossiers', $dossierFields, $dossiers);
    }

    public function classParticipationSix()
    {
        echo "classParticipationSix starting \n";
        if ($this->legislature_to_get == 15) {
            $this->bdd->query('
            DROP TABLE IF EXISTS class_participation_six;
            CREATE TABLE class_participation_six
            (id INT(5) NOT NULL AUTO_INCREMENT,
            classement INT(5) NOT NULL,
            mpId VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            score DECIMAL(3,2) NOT NULL,
            votesN INT(15) NOT NULL,
            dateMaj DATE NOT NULL,
            PRIMARY KEY (id));
            ALTER TABLE class_participation_six ADD INDEX idx_mpId (mpId);
        ');

            $result = $this->bdd->query('
            SELECT @s:=@s+1 AS "classement", C.*
            FROM
            (
                SELECT B.*
                FROM
                (
                    SELECT A.mpId, ROUND(AVG(A.participation),2) AS score, COUNT(A.participation) AS votesN, ROUND(COUNT(A.participation)/10) AS "index"
                    FROM
                    (
                        SELECT v.mpId, v.participation, vi.dateScrutin
                        FROM votes_participation_commission v
                        LEFT JOIN votes_info vi ON v.voteNumero = vi.voteNumero
                        WHERE vi.dateScrutin >= CURDATE() - INTERVAL 12 MONTH
                    ) A
                    WHERE A.participation IS NOT NULL
                    GROUP BY A.mpId
                    ORDER BY ROUND(COUNT(A.participation)/10) DESC, AVG(A.participation) DESC
                ) B
                WHERE B.mpId IN (
                    SELECT mpId
                FROM deputes_all
                WHERE legislature = 15 AND dateFin IS NULL
                )
            ) C,
            (SELECT @s:= 0) AS s
            WHERE C.votesN > 5
            ORDER BY C.score DESC, C.votesN DESC
        ');

            $participationFields = array('classement', 'mpId', 'score', 'votesN', 'dateMaj');
            $participation = [];
            $participations = [];
            while ($depute = $result->fetch()) {
                $classement = $depute["classement"];
                $mpId = $depute["mpId"];
                $score = $depute["score"];
                $votesN = $depute["votesN"];

                $participation = array('classement' => $classement, 'mpId' => $mpId, 'score' => $score, 'votesN' => $votesN, 'dateMaj' => $this->dateMaj);
                $participations = array_merge($participations, array_values($participation));
            }
            $this->insertAll('class_participation_six', $participationFields, $participations);
        }
    }

    public function classLoyauteSix()
    {
        echo "classLoyauteSix starting \n";
        if ($this->legislature_to_get == 15) {
            $this->bdd->query('
                DROP TABLE IF EXISTS class_loyaute_six;
                CREATE TABLE class_loyaute_six
                    (id INT(5) NOT NULL AUTO_INCREMENT,
                    classement INT(5) NOT NULL,
                    mpId VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                    score DECIMAL(4,3) NOT NULL,
                    votesN INT(15) NOT NULL,
                    dateMaj DATE NOT NULL,
                    PRIMARY KEY (id));
                    ALTER TABLE class_loyaute_six ADD INDEX idx_mpId (mpId);
            ');
            $result = $this->bdd->query('
                SELECT @s:=@s+1 AS "classement", B.*
                FROM (
                SELECT A.*
                FROM (
                    SELECT t1.mpId, ROUND(AVG(t1.scoreLoyaute),3) AS score, COUNT(t1.scoreLoyaute) AS votesN
                    FROM
                    (
                        SELECT v.mpId, v.scoreLoyaute, vi.dateScrutin
                        FROM votes_scores v
                        LEFT JOIN votes_info vi ON v.voteNumero = vi.voteNumero
                        WHERE vi.dateScrutin >= CURDATE() - INTERVAL 12 MONTH
                    ) t1
                    WHERE t1.scoreLoyaute IS NOT NULL
                    GROUP BY t1.mpId
                ) A
                WHERE A.mpId IN (
                    SELECT mpId
                    FROM deputes_all
                    WHERE legislature = 15 AND dateFin IS NULL
                )
                ) B,
                (SELECT @s:= 0) AS s
                ORDER BY B.score DESC, B.votesN DESC
            ');

            $loyauteFields = array('classement', 'mpId', 'score', 'votesN', 'dateMaj');
            $loyaute = [];
            $loyautes = [];
            while ($depute = $result->fetch()) {
                $classement = $depute["classement"];
                $mpId = $depute["mpId"];
                $score = $depute["score"];
                $votesN = $depute["votesN"];


                $loyaute = array('classement' => $classement, 'mpId' => $mpId, 'score' => $score, 'votesN' => $votesN, 'dateMaj' => $this->dateMaj);
                $loyautes = array_merge($loyautes, array_values($loyaute));
            }
            $this->insertAll('class_loyaute_six', $loyauteFields, $loyautes);
        }
    }

    public function deputeAccordCleaned()
    {
        echo "deputeAccordCleaned starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS deputes_accord_cleaned;
            CREATE TABLE deputes_accord_cleaned AS
            SELECT A.*
            FROM
            (
            SELECT da.mpId, da.legislature, da.organeRef, ROUND(AVG(da.accord)*100) AS accord, COUNT(da.accord) AS votesN
            FROM deputes_accord da
            GROUP BY da.mpId, da.organeRef
            ) A
            WHERE A.accord IS NOT NULL;
            ALTER TABLE deputes_accord_cleaned ADD INDEX idx_mpId (mpId);
            ALTER TABLE deputes_accord_cleaned ADD INDEX idx_legislature (legislature);
        ');
    }

    public function historyMpsAverage()
    {
        echo "historyMpsAverage starting \n";
        $this->bdd->query('DROP TABLE IF EXISTS history_mps_average;');
        $this->bdd->query('CREATE TABLE `history_mps_average` ( `id` TINYINT NOT NULL AUTO_INCREMENT , `legislature` TINYINT NOT NULL , `length` DECIMAL(4,2) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;');
        $terms = array(14, 15);
        foreach ($terms as $term) {
            echo "Getting average for term => " . $term . "\n";
            $this->bdd->query('
                INSERT INTO history_mps_average (legislature, length)
                SELECT "' . $term . '" AS legislature, ROUND(AVG(B.mpLength)/365, 2) as length
                FROM
                (
                    SELECT A.mpId, sum(A.duree) AS mpLength
                    FROM
                    (
                        SELECT m1.mpId, m1.legislature,
                        CASE
                        WHEN m1.dateFin IS NOT NULL THEN datediff(m1.dateFin, m1.datePriseFonction)
                        ELSE datediff(curdate(), m1.datePriseFonction)
                        END AS duree
                        FROM mandat_principal m1
                        LEFT JOIN deputes_all da ON m1.mpId = da.mpId AND da.legislature = "' . $term . '"
                        WHERE m1.codeQualite = "membre" AND m1.typeOrgane = "ASSEMBLEE" AND m1.legislature <= "' . $term . '"
                        ORDER BY m1.mpId
                    ) A
                    GROUP BY A.mpId
                ) B
            ');
        }

        $this->bdd->query('ALTER TABLE history_mps_average ADD INDEX idx_legislature (legislature)');
    }

    public function historyPerMpsAverage()
    {
        echo "historyPerMpsAverage starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS history_per_mps_average;
            CREATE TABLE history_per_mps_average AS
            SELECT B.*,
                    CASE
                    WHEN ROUND(B.mpLength/365) = 1 THEN CONCAT(ROUND(B.mpLength/365), " an")
                    WHEN ROUND(B.mpLength/365) > 1 THEN CONCAT(ROUND(B.mpLength/365), " ans")
                    WHEN ROUND(B.mpLength/30) != 0 THEN CONCAT(ROUND(B.mpLength/30), " mois")
                    ELSE CONCAT(B.mpLength, " jours")
                    END AS lengthEdited, CURDATE() AS dateMaj
                FROM
                (
                    SELECT A.mpId,
                        SUM(A.length) AS mpLength,
                        count(distinct(A.legislature)) AS mandatesN
                        FROM
                        (
                            SELECT mp.legislature, mp.mpId,
                                CASE
                                WHEN mp.dateFin IS NOT NULL THEN datediff(mp.dateFin, mp.datePriseFonction)
                                ELSE datediff(curdate(), mp.datePriseFonction)
                            END AS length
                            FROM mandat_principal mp
                            WHERE mp.typeOrgane = "ASSEMBLEE" AND mp.codeQualite = "membre"
                        ) A
                        GROUP BY A.mpId
                ) B;
            ALTER TABLE history_per_mps_average ADD INDEX idx_mpId (mpId);
        ');
    }

    public function createCsvFile()
    {
        echo "createCsvFile starting \n";
        // filename for export
        $csv_filename = 'deputes_15.csv';

        // query to get data from database
        $query = $this->bdd->query('
                SELECT
                    da.mpId AS id,
                    da.civ,
                    da.nameLast AS nom,
                    da.nameFirst AS prenom,
                    d.birthDate AS naissance,
                    da.age,
                    da.libelle AS groupe,
                    da.libelleAbrev AS groupeAbrev,
                    da.departementNom,
                    da.departementCode,
                    da.circo,
                    da.datePriseFonction,
                    d.job,
                    dc.mailAn AS mail,
                    dc.twitter,
                    dc.facebook,
                    dc.website,
                    h.mandatesN AS nombreMandats,
                    h.lengthEdited AS experienceDepute,
                    cp.score AS scoreParticipation,
                    cpm.score AS scoreParticipationSpecialite,
                    cl.score AS scoreLoyaute,
                    cm.score AS scoreMajorite,
                    CASE WHEN da.dateFin IS NULL THEN 1 ELSE 0 END AS "active",
                    da.dateMaj
                FROM deputes_all da
                LEFT JOIN class_participation cp ON da.mpId = cp.mpId
                LEFT JOIN class_participation_commission cpm ON da.mpId = cpm.mpId
                LEFT JOIN class_loyaute cl ON da.mpId = cl.mpId
                LEFT JOIN class_majorite cm ON da.mpId = cm.mpId
                LEFT JOIN deputes_contacts dc ON da.mpId = dc.mpId
                LEFT JOIN history_per_mps_average h ON da.mpId = h.mpId
                LEFT JOIN deputes d ON da.mpId = d.mpId
                WHERE da.legislature = 15
            ');

        // Fetch the result
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        // Create line with field names
        $fields = [];
        foreach ($results[0] as $key => $value) {
            $fields[] = $key;
        }

        // Export the data
        $dir = __DIR__ . "/../assets/opendata/";
        $fp = fopen($dir . "" . $csv_filename, "w");

        // Print the header
        fputcsv($fp, $fields);

        // Create new line with results
        foreach ($results as $key => $result) {
            fputcsv($fp, $result);
        }

        // CLose the file
        fclose($fp);
    }
}

// Specify the legislature
if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}
$script->fillDeputes();
$script->deputeAll();
$script->deputeLast();
$script->downloadPictures();
$script->webpPictures();
$script->resmushPictures();
$script->groupeEffectif();
$script->deputeJson();
$script->groupeStats();
$script->parties();
$script->legislature();
$script->vote();
$script->updateVoteInfo();
$script->voteScore();
$script->groupeCohesion();
$script->groupeAccord();
$script->deputeAccord();
$script->voteParticipation();
$script->votesDossiers();
$script->dossier();
$script->voteParticipationCommission();
$script->classParticipation();
$script->classParticipationCommission();
$script->deputeLoyaute();
$script->classLoyaute();
$script->classMajorite();
$script->classGroups();
$script->classGroupsProximite();
$script->classParticipationSix();
$script->classLoyauteSix();
$script->deputeAccordCleaned();
$script->historyMpsAverage();
$script->historyPerMpsAverage();
$script->createCsvFile();
