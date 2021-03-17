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

    private function insertAll($table, $fields, $question_marks, $datas){
        try {
            // SQL //
            $sql = "INSERT INTO " . $table . " (" . implode(",", $fields) . ") VALUES " . implode(',', $question_marks);
            $stmt = $this->bdd->prepare($sql);
            $stmt->execute($datas);
            echo $table . " inserted\n";
        } catch (Exception $e) {
            echo "Error inserting or probably no more : " . $table . "\n";
        }
    }

    public function fillDeputes()
    {
        echo "fillDeputes starting \n";
        $deputeFields = array('mpId', 'civ', 'nameFirst', 'nameLast', 'nameUrl', 'birthDate', 'birthCity', 'birthCountry', 'job', 'catSocPro', 'dateMaj');
        $mandatFields = array('mandatId', 'mpId', 'legislature', 'typeOrgane', 'dateDebut', 'dateFin', 'preseance', 'nominPrincipale', 'codeQualite', 'libQualiteSex', 'organe', 'electionRegion', 'electionRegionType', 'electionDepartement', 'electionDepartementNumero', 'electionCirco', 'datePriseFonction', 'causeFin', 'premiereElection', 'placeHemicyle', 'dateMaj');
        $mandatGroupeFields = array('mandatId', 'mpId', 'legislature', 'typeOrgane', 'dateDebut', 'dateFin', 'preseance', 'nominPrincipale', 'codeQualite', 'libQualiteSex', 'organeRef', 'dateMaj');
        $organeFields = array('uid', 'coteType', 'libelle', 'libelleEdition', 'libelleAbrev', 'libelleAbrege', 'dateDebut', 'dateFin', 'legislature', 'positionPolitique', 'preseance', 'couleurAssociee', 'dateMaj');
        $this->bdd->query("TRUNCATE TABLE deputes");
        $this->bdd->query("TRUNCATE TABLE mandat_principal");
        $this->bdd->query("TRUNCATE TABLE mandat_groupe");
        $this->bdd->query("TRUNCATE TABLE mandat_secondaire");
        $this->bdd->query("TRUNCATE TABLE organes");
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
                                    'dateMaj' => $dateMaj
                                );
                                $question_marks_mandat[] = '('  . $this->placeholders('?', sizeof($mandatPrincipal)) . ')';
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
                                    'dateMaj' => $dateMaj
                                );
                                $question_marks_mandat_groupe[] = '('  . $this->placeholders('?', sizeof($mandatGroupe)) . ')';
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
                                    'dateMaj' => $dateMaj
                                );
                                $question_marks_mandat_secondaire[] = '('  . $this->placeholders('?', sizeof($mandatSecondaire)) . ')';
                                $mandatsSecondaire = array_merge($mandatsSecondaire, array_values($mandatSecondaire));
                            }
                        }
                    }
                    try {
                        $depute = array('mpId' => $mpId, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'nameUrl' => $nameUrl, 'birthDate' => $birthDate, 'birthCity' => $birthCity, 'birthCountry' => $birthCountry, 'job' => $job, 'catSocPro' => $catSocPro, 'dateMaj' => $dateMaj);
                        $question_marks[] = '('  . $this->placeholders('?', sizeof($depute)) . ')';
                        $deputes = array_merge($deputes, array_values($depute));
                    } catch (Exception $e) {
                        echo '<pre>', var_dump($e->getMessage()), '</pre>';
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

                        $organe = array('uid' => $uid, 'coteType' => $codeType, 'libelle' => $libelle, 'libelleEdition' => $libelleEdition, 'libelleAbrev' => $libelleAbrev, 'libelleAbrege' => $libelleAbrege, 'dateDebut' => $dateDebut, 'dateFin' => $dateFin, 'legislature' => $legislature, 'positionPolitique' => $positionPolitique, 'preseance' => $preseance, 'couleurAssociee' => $couleurAssociee, 'dateMaj' => $dateMaj);
                        $question_marks_organe[] = '('  . $this->placeholders('?', sizeof($organe)) . ')';
                        $organes = array_merge($organes, array_values($organe));
                    }
                }
            }

            // insert deputes
            $this->insertAll('deputes', $deputeFields, $question_marks, $deputes);
            // insert mandat
            $this->insertAll('mandat_principal', $mandatFields, $question_marks_mandat, $mandats);
            // insert mandat grpupe
            $this->insertAll('mandat_groupe', $mandatGroupeFields, $question_marks_mandat_groupe, $mandatsGroupe);
            // insert mandat secondaire
            $this->insertAll('mandat_secondaire', $mandatGroupeFields, $question_marks_mandat_secondaire, $mandatsSecondaire);
            // insert organes
            $this->insertAll('organes', $organeFields, $question_marks_organe, $organes);

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

    function groupeEffectif()
    {
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
        $dateMaj = date('Y-m-d');
        $this->bdd->exec('TRUNCATE TABLE deputes_all');

        $query = $this->bdd->query('
            SELECT mp.mpId, mp.legislature, d.nameUrl, d.nameFirst, d.nameLast, d.civ,
            YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age
            FROM mandat_principal mp
            LEFT JOIN deputes d ON d.mpId = mp.mpId
            GROUP BY mp.mpId, mp.legislature
        ');

        $i = 1;

        while ($data = $query->fetch()) {
            $mpId = $data['mpId'];
            $legislature = $data['legislature'];
            $nameUrl = $data['nameUrl'];
            $nameFirst = $data['nameFirst'];
            $nameLast = $data['nameLast'];
            $civ = $data['civ'];
            $age = $data['age'];
            $img = file_exists("../assets/imgs/deputes_nobg_webp/depute_" . substr($mpId, 2) . "_webp.webp") ? 1 : 0;
            $imgOgp = file_exists("../assets/imgs/deputes_ogp/ogp_deputes_" . $mpId . ".png") ? 1 : 0;


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


            // SQL //
            $sql = $this->bdd->prepare("INSERT INTO deputes_all (mpId, legislature, nameUrl, civ, nameFirst, nameLast, age, dptSlug, departementNom, departementCode, circo, mandatId, libelle, libelleAbrev, groupeId, groupeMandat, couleurAssociee, datePriseFonction, dateFin, causeFin, img, imgOgp, dateMaj) VALUES (:mpId, :legislature, :nameUrl, :civ, :nameFirst, :nameLast, :age, :dptSlug, :departementNom, :departementCode, :circo, :mandatId, :libelle, :libelleAbrev, :groupeId, :groupeMandat, :couleurAssociee, :datePriseFonction, :dateFin, :causeFin, :img, :imgOgp, :dateMaj)");
            $sql->execute(array('mpId' => $mpId, 'legislature' => $legislature, 'nameUrl' => $nameUrl, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'age' => $age, 'dptSlug' => $dptSlug, 'departementNom' => $departementNom, 'departementCode' => $departementCode, 'circo' => $circo, 'mandatId' => $mandatId, 'libelle' => $libelle, 'libelleAbrev' => $libelleAbrev, 'groupeId' => $groupeId, 'groupeMandat' => $groupeMandat, 'couleurAssociee' => $couleurAssociee, 'datePriseFonction' => $datePriseFonction, 'dateFin' => $dateFin, 'causeFin' => $causeFin, 'img' => $img, 'imgOgp' => $imgOgp, 'dateMaj' => $dateMaj));
            $i++;
        }
    }

    public function deputeLast()
    {
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

        $this->bdd->exec(
            '
            CREATE INDEX idx_mp ON deputes_last (nameUrl);'
        );
        $this->bdd->exec(
            '
            CREATE INDEX idx_dptSlug ON deputes_last (dptSlug);'
        );
        $this->bdd->exec('CREATE INDEX idx_mpId ON deputes_last(mpId)');
        $this->bdd->exec('CREATE INDEX idx_legislature ON deputes_last(legislature);');
    }

    public function deputeJson()
    {
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
        $dir = __DIR__;
        $dir = str_replace(array("/", "scripts", ".php"), "", $dir);
        $dir = $dir . "assets/data/";
        $dir = "../assets/data/";
        $fp = fopen($dir . "deputes_json.json", 'w');
        if (fwrite($fp, $json)) {
            echo "JSON created \n";
        }
        fclose($fp);
    }

    public function groupeStats()
    {
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
        $dateMaj = date('Y-m-d');
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

        while ($data = $response->fetch()) {
            $organeRef = $data['uid'];
            $libelle = $data['libelle'];
            $libelleAbrev = $data['libelleAbrev'];
            $number = $data['legislature'];
            $dateDebut = $data['dateDebut'];
            $dateFin = $data['dateFin'];

            $name = $number . "ème législature";

            // INSERT INTO DATABSSE //
            $sql = $this->bdd->prepare('INSERT INTO legislature (organeRef, libelle, libelleAbrev, name, legislatureNumber, dateDebut, dateFin, dateMaj) VALUES (:organeRef, :libelle, :libelleAbrev, :name, :legislatureNumber, :dateDebut, :dateFin, :dateMaj)');
            $sql->execute(array('organeRef' => $organeRef, 'libelle' => $libelle, 'libelleAbrev' => $libelleAbrev, 'name' => $name, 'legislatureNumber' => $number, 'dateDebut' => $dateDebut, 'dateFin' => $dateFin, 'dateMaj' => $dateMaj));
        }
    }

    public function vote($legislature_to_get = 15)
    {
        echo "starting vote\n";
        $reponse_vote = $this->bdd->query('
            SELECT voteNumero
            FROM votes
            WHERE legislature = "' . $legislature_to_get . '"
            ORDER BY voteNumero DESC
            LIMIT 1
        ');

        $dernier_vote = $reponse_vote->fetch();
        $last_vote = $dernier_vote['voteNumero'];
        echo "From " . $last_vote ."\n";

        // Last vote
        if (!isset($last_vote)) {
            $number_to_import = 1;
        } else {
            $number_to_import = $last_vote + 1;
        }

        // SCRAPPING DEPENDING ON LEGISLATURE
        if ($legislature_to_get == 15) {

            $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/loi/scrutins/Scrutins_XV.xml.zip';
            $file = trim($file);
            $newfile = 'tmp_Scrutins_XV.xml.zip';
            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
            }
            $zip = new ZipArchive();
            if ($zip->open($newfile) !== TRUE) {
                exit("cannot open <$newfile>\n");
            } else {
                $voteMainFields = array('mpId', 'vote', 'voteNumero', 'voteId', 'legislature', 'mandatId', 'parDelegation', 'causePosition', 'voteType');
                $i = 1;
                $votesMain = [];
                $votesInfos = [];

                while(1) {
                    $file_to_import = 'VTANR5L15V' . $number_to_import++;
                    $xml_string = $zip->getFromName('xml/' . $file_to_import . '.xml');
                    if ($xml_string != false) {
                        $xml = simplexml_load_string($xml_string);

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
                            $question_marks_vote[] = '('  . $this->placeholders('?', sizeof($voteMain)) . ')';
                            $votesMain = array_merge($votesMain, array_values($voteMain));

                            $i++;
                        }

                    }
                    else {
                        break;
                    }
                }
                // insert votes
                $this->insertAll('votes', $voteMainFields, $question_marks_vote, $votesMain);
            }
        } elseif ($legislature_to_get == 14) {

            $file = 'http://data.assemblee-nationale.fr/static/openData/repository/14/loi/scrutins/Scrutins_XIV.xml.zip';
            $file = trim($file);
            $newfile = 'tmp_Scrutins_XIV.xml.zip';
            if (!copy($file, $newfile)) {
                echo "failed to copy $file...\n";
            }
            $zip = new ZipArchive();

            if ($zip->open($newfile) !== TRUE) {
                exit("cannot open <$newfile>\n");
            } else {
                $voteMainFields = array('mpId', 'vote', 'voteNumero', 'voteId', 'legislature', 'mandatId', 'parDelegation', 'causePosition', 'voteType');
                $i = 1;
                $votesMain = [];
                $votesInfos = [];
                $question_marks_vote = [];

                $xml_string = $zip->getFromName('Scrutins_XIV.xml');
                if ($xml_string != false) {
                    $xml = simplexml_load_string($xml_string);
                    foreach ($xml->xpath('//acteurRef/ancestor::scrutin[(numero>=' . $number_to_import . ')]') as $xml2) {

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

                            $voteMain = array('mpId' => $item['mpId'], 'vote' => $vote, 'voteNumero' => $item['voteNumero'], 'voteId' => $item['voteId'], 'legislature' => $legislature_to_get, 'mandatId' => $item['mandatId'], 'parDelegation' => null, 'causePosition' => null, 'voteType' => $item['voteType']);
                            $question_marks_vote[] = '('  . $this->placeholders('?', sizeof($voteMain)) . ')';
                            $votesMain = array_merge($votesMain, array_values($voteMain));
                            $i++;
                        }
                    }
                }
                $this->insertAll('votes', $voteMainFields, $question_marks_vote, $votesMain);
            }
        }
    }
}

$script = new Script();
// $script->fillDeputes();
// $script->downloadPictures();
// $script->webpPictures();
// $script->resmushPictures();
// $script->groupeEffectif();
// $script->deputeAll();
// $script->deputeLast();
// $script->deputeJson();
// $script->groupeStats();
// $script->parties();
// $script->legislature();
$script->vote(15);
