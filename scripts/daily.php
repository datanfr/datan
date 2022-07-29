<?php
include "lib/simplehtmldom_1_9/simple_html_dom.php";
include "include/json_minify.php";
class Script
{
    private $bdd;
    private $legislature_to_get;
    private $dateMaj;
    private $time_pre;
    private $legislature_current;

    // export the variables in environment
    public function __construct($legislature = 16)
    {
        date_default_timezone_set('Europe/Paris');
        ini_set('memory_limit', '2048M');
        $this->dateMaj = date('Y-m-d');
        $this->legislature_current = 16;
        $this->legislature_to_get = $legislature;
        $this->intro = "[" . date('Y-m-d h:i:s') . "] ";
        echo $this->intro . "Launching the daily script for legislature " . $this->legislature_to_get . "\n";
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
        echo "Script is over ! It took: " . round($exec_time, 2) . " seconds.\n";
    }

    private function opendata($query, $csv_filename, $dataset, $resource)
    {
      echo "createCsvFile starting = ". $csv_filename ." \n";

      // query to get data from database
      $query = $this->bdd->query($query);

      // Fetch the result
      $results = $query->fetchAll(PDO::FETCH_ASSOC);

      // Create line with field names
      $fields = [];
      foreach ($results[0] as $key => $value) {
          $fields[] = $key;
      }

      // Export the data
      $dir = __DIR__ . "/../assets/opendata/";
      $fp = fopen($dir . $csv_filename, "w");

      // Print the header
      fputcsv($fp, $fields);

      // Create new line with results
      foreach ($results as $key => $result) {
          fputcsv($fp, $result);
      }

      // CLose the file
      fclose($fp);
      $api = 'https://www.data.gouv.fr/api/1';
      $headers = [
          'X-API-KEY: '. getenv('API_GOUV')
      ];
      $url = $api.'/datasets/'.$dataset. '/resources/'.$resource.'/upload/';
      $cFile = curl_file_create($dir . $csv_filename, 'text/csv', $csv_filename);
      $post = array('file' => $cFile);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      if(curl_exec($ch)){
          echo $csv_filename . " uploaded to gouv.fr\n";
      }
      else {
          echo $csv_filename . " not uploaded, something went wrong !\n";
      }
      curl_close($ch);
    }

    private function insertAll($table, $fields, $datas, $print = TRUE)
    {
        if (count($datas) > 0) {
            try {
                $update = "";
                $values = substr(str_repeat("(" . substr(str_repeat('?,', count($fields)), 0, -1) . "),", count($datas) / count($fields)), 0, -1);
                foreach ($fields as $field) {
                    $update .= $field . "=VALUES(" . $field . "),";
                }
                $update = substr($update, 0, -1);
                // SQL //
                $sql = "INSERT INTO " . $table . " (" . implode(",", $fields) . ") VALUES " . $values
                    . " ON DUPLICATE KEY UPDATE " . $update;
                $stmt = $this->bdd->prepare($sql);
                $stmt->execute($datas);
                $print ? $table . " inserted\n" : NULL;
            } catch (Exception $e) {
                echo "Error inserting : " . $table . "\n" . $e->getMessage() . "\n";
                die;
            }
        } else {
            echo "Nothing to insert in " . $table . "\n";
        }
    }

    public function fillDeputes()
    {
        echo "fillDeputes starting \n";
        $file = __DIR__ . '/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
        $zip = new ZipArchive();
        if ($zip->open($file) !== TRUE) {
            exit("cannot open <$file>\n");
        } else {
            $deputeFields = array('mpId', 'civ', 'nameFirst', 'nameLast', 'nameUrl', 'birthDate', 'birthCity', 'birthCountry', 'job', 'catSocPro', 'famSocPro', 'hatvp', 'dateMaj');
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
                        $famSocPro = $xml->profession->socProcINSEE->famSocPro;
                        $hatvp = $xml->uri_hatvp;
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
                        $depute = array(
                          'mpId' => $mpId,
                          'civ' => $civ,
                          'nameFirst' => $nameFirst,
                          'nameLast' => $nameLast,
                          'nameUrl' => $nameUrl,
                          'birthDate' => $birthDate,
                          'birthCity' => $birthCity,
                          'birthCountry' => $birthCountry,
                          'job' => $job,
                          'catSocPro' => $catSocPro,
                          'famSocPro' => $famSocPro,
                          'hatvp' => $hatvp,
                          'dateMaj' => $this->dateMaj);
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
            WHERE legislature IN (14, 15, 16)
        ');

        $originalFolder = __DIR__ . "/../assets/imgs/deputes_original/";
        if (!file_exists($originalFolder)) mkdir($originalFolder);

        while ($d = $donnees->fetch()) {

            $uid = substr($d['uid'], 2);
            $filename = __DIR__ . "/../assets/imgs/deputes_original/depute_" . $uid . ".png";
            $legislature = $d['legislature'];
            $url = 'https://www2.assemblee-nationale.fr/static/tribun/' . $legislature . '/photos/' . $uid . '.jpg';

            // 1. Download original photo in deputes_original folder

            if (!file_exists($filename)) {
                echo "Download MP " . $uid."\n";
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
                        imagepng($thumb, __DIR__ . '/../assets/imgs/deputes_original/depute_' . $uid . '.png', $quality);
                        echo "one image was just downloaded \n";
                    }
                }
            }

            // 2. Remove background of the image in deputes_original folder

            $nobgFolder = __DIR__ . "/../assets/imgs/deputes_nobg_import/";
            if (!file_exists($nobgFolder)) mkdir($nobgFolder);
            $liveUrl = 'https://datan.fr/assets/imgs/deputes_nobg_import/depute_' . $uid . '.png';
            $nobgfilename = __DIR__ . '/../assets/imgs/deputes_nobg_import/depute_' . $uid . '.png';
            if (!file_exists($nobgfilename)) {
                $nobgLive = file_get_contents($liveUrl);
                if ($nobgLive) {
                    file_put_contents($nobgfilename, $nobgLive);
                    echo "one nobg image was just downloaded from datan.fr \n";
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
                    //$version = curl_getinfo($ch, CURLINFO_HTTP_VERSION);
                    if ($nobg && $httpCode == 200) {
                        file_put_contents($nobgfilename, $nobg);
                        echo "one nobg image was just downloaded from remove.bg \n";
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
        unset($files[2]);
        echo "Number of photos in the deputes_original ==> " . count($files) . " \n";

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
        unset($files[2]);
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
            WHERE legislature IN (14, 15, 16)
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
            YEAR(current_timestamp()) - YEAR(d.birthDate) - CASE WHEN MONTH(current_timestamp()) < MONTH(d.birthDate) OR (MONTH(current_timestamp()) = MONTH(d.birthDate) AND DAY(current_timestamp()) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age,
            d.job, d.catSocPro, d.famSocPro
            FROM mandat_principal mp
            LEFT JOIN deputes d ON d.mpId = mp.mpId
            GROUP BY mp.mpId, mp.legislature
        ');

        $i = 1;
        $deputes = [];
        $depute = [];
        $deputeFields = array('mpId', 'legislature', 'nameUrl', 'civ', 'nameFirst', 'nameLast', 'age', 'job', 'catSocPro', 'famSocPro',  'dptSlug', 'departementNom', 'departementCode', 'circo', 'mandatId', 'libelle', 'libelleAbrev', 'groupeId', 'groupeMandat', 'couleurAssociee', 'datePriseFonction', 'dateFin', 'causeFin', 'img', 'imgOgp', 'dateMaj');
        while ($data = $query->fetch()) {
            $mpId = $data['mpId'];
            $legislature = $data['legislature'];
            $nameUrl = $data['nameUrl'];
            $nameFirst = $data['nameFirst'];
            $nameLast = $data['nameLast'];
            $civ = $data['civ'];
            $age = $data['age'];
            $job = $data['job'];
            $catSocPro = $data['catSocPro'];
            $famSocPro = $data['famSocPro'];
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
                echo "ERROR (no mandat principal)";
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
                echo "No group for " . $mpId . " (" . $nameFirst . " " . $nameLast . ") - Legislature: " . $legislature . "\n";
                $libelle = NULL;
                $libelleAbrev = NULL;
                $groupeId = NULL;
                $couleurAssociee = NULL;
            }

            $depute = array(
              'mpId' => $mpId,
              'legislature' => $legislature,
              'nameUrl' => $nameUrl,
              'civ' => $civ,
              'nameFirst' => $nameFirst,
              'nameLast' => $nameLast,
              'age' => $age,
              'job' => $job,
              'catSocPro' => $catSocPro,
              'famSocPro' => $famSocPro,
              'dptSlug' => $dptSlug,
              'departementNom' => $departementNom,
              'departementCode' => $departementCode,
              'circo' => $circo,
              'mandatId' => $mandatId,
              'libelle' => $libelle,
              'libelleAbrev' => $libelleAbrev,
              'groupeId' => $groupeId,
              'groupeMandat' => $groupeMandat,
              'couleurAssociee' => $couleurAssociee,
              'datePriseFonction' => $datePriseFonction,
              'dateFin' => $dateFin,
              'causeFin' => $causeFin,
              'img' => $img,
              'imgOgp' => $imgOgp,
              'dateMaj' => $this->dateMaj);
            $deputes = array_merge($deputes, array_values($depute));
            if ($i % 1000 === 0) {
                echo "Let's import until n " . $i . "\n";
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
            CASE WHEN (legislature = "' . $this->legislature_current . '" AND dateFin IS NULL) THEN 1 ELSE 0 END AS active
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
        FROM deputes_last da
        WHERE da.legislature >= 14
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
        $json = json_minify($json);

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
        $this->bdd->query('CREATE TABLE groupes_stats ( organeRef VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , womenPct DECIMAL(4,2) NULL , womenN INT(3) NULL  , age DECIMAL(4,2) NULL, rose_index DECIMAL(4,3) ) ENGINE = MyISAM;');

        $reponse = $this->bdd->query('
            SELECT *
            FROM organes
            WHERE legislature >= 14 AND coteType = "GP"
        ');

        while ($data = $reponse->fetch()) {
            $groupeId = $data['uid'];

            // 1. AGE
            if ($data['legislature'] == $this->legislature_current && $data['dateFin'] == NULL) {
              $age_response = $this->bdd->query('
                  SELECT da.groupeId AS organeRef, ROUND(AVG(age), 2) AS age, COUNT(age) as n
                  FROM deputes_all da
                  WHERE da.groupeId = "' . $groupeId . '" AND da.legislature = "' . $this->legislature_current . '" AND da.dateFin IS NULL
              ');
            } elseif ($data['legislature'] == $this->legislature_current && $data['dateFin'] != NULL) {
              $age_response = $this->bdd->query('
                SELECT A.organeRef, ROUND(AVG(A.age), 2) AS age, COUNT(A.age) as n
                  FROM
                  (
                    SELECT mg.organeRef, mg.mpId, da.age, mg.dateFin, mg.legislature
                    FROM mandat_groupe mg
                    LEFT JOIN organes o ON mg.organeRef = o.uid
                    LEFT JOIN deputes_all da ON da.mpId = mg.mpId AND da.legislature = mg.legislature
                    WHERE mg.organeRef = "' . $groupeId . '" AND mg.dateFin = o.dateFin
                    GROUP BY mg.mpId
                ) A
              ');
            } else {
              $age_response = $this->bdd->query('
                SELECT A.organeRef, ROUND(AVG(A.age), 2) AS age, COUNT(A.age) as n
                FROM
                (
                  SELECT mg.organeRef, mg.mpId, d.birthDate, l.dateFin,
                  YEAR(l.dateFin) - YEAR(d.birthDate) - CASE WHEN MONTH(l.dateFin) < MONTH(d.birthDate) OR (MONTH(l.dateFin) = MONTH(d.birthDate) AND DAY(l.dateFin) < DAY(d.birthDate)) THEN 1 ELSE 0 END AS age
                  FROM mandat_groupe mg
                  LEFT JOIN organes o ON mg.organeRef = o.uid
                  LEFT JOIN deputes d ON d.mpId = mg.mpId
                  LEFT JOIN legislature l ON legislatureNumber = o.legislature
                  WHERE mg.organeRef = "' . $groupeId . '" AND mg.dateFin = o.dateFin
                  GROUP BY mg.mpId
                ) A
              ');
            }

            while ($age_data = $age_response->fetch()) {
                $age = $age_data['age'];
            }

            // 2. WOMEN
            if ($data['legislature'] == $this->legislature_current && $data['dateFin'] == NULL) {
              $women_response = $this->bdd->query('
                  SELECT A.*,
                  ROUND(female / n * 100, 2) AS pct
                  FROM
                  (
                  SELECT groupeId, COUNT(mpId) AS n,
                  SUM(if(civ = "Mme", 1, 0)) AS female
                  FROM deputes_all
                  WHERE groupeId = "' . $groupeId . '" AND legislature = "' . $this->legislature_current . '" AND dateFin IS NULL
                  GROUP BY groupeId
                  ) A
              ');
            } else {
              $women_response = $this->bdd->query('
                  SELECT A.*, ROUND(female / n * 100, 2) AS pct
                  FROM
                  (
                  SELECT o.uid, SUM(if(dl.civ = "Mme", 1, 0)) AS female, COUNT(dl.civ) AS n
                  FROM organes o
                  LEFT JOIN mandat_groupe mg ON o.uid = mg.organeRef AND o.dateFin = mg.dateFin
                  LEFT JOIN deputes_last dl ON mg.mpId = dl.mpId
                  WHERE o.uid = "' . $groupeId . '" AND mg.preseance != 1
                  GROUP BY o.uid
                  ) A
              ');
            }

              while ($women_data = $women_response->fetch()) {
                  $womenPct = $women_data['pct'];
                  $womenN = $women_data['female'];
              }

            $representation_response = $this->bdd->query('
              SELECT 1 - (0.5 * sum(y)) AS rose_index
              FROM
              (
              SELECT B.*, abs(population - x) AS y
              FROM
              (
                SELECT famille, round(population / 100, 3) AS population, round(count(mpId) / total, 3) AS x
                FROM famsocpro fam
                LEFT JOIN deputes_last dl ON dl.famSocPro = fam.famille AND dl.groupeId = "' . $groupeId . '" AND dl.active AND dl.legislature = 15
                LEFT JOIN (
                  SELECT groupeId, count(*) AS total
                  FROM famsocpro fam
                  LEFT JOIN deputes_last dl ON dl.famSocPro = fam.famille
                  WHERE dl.legislature = 15 AND dl.active AND groupeId = "' . $groupeId . '"
                ) A ON A.groupeId = dl.groupeId
                GROUP BY famille
              ) B
              ) C
            ');

            while ($representation = $representation_response->fetch()) {
                $rose_index = $representation['rose_index'];
            }

            // INSERT INTO DATABSSE //
            $sql = $this->bdd->prepare('INSERT INTO groupes_stats (organeRef, age, womenN, womenPct, rose_index) VALUES (:organeRef, :age, :womenN, :womenPct, :rose_index)');
            $sql->execute(array('organeRef' => $groupeId, 'age' => $age, 'womenN' => $womenN, 'womenPct' => $womenPct, 'rose_index' => $rose_index));
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
        // THIS FUNCTION UPDATE THE FOLLOWING TABLES --> votes ; votes_info ; votes_groupes
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
        echo "From vote n° " . $number_to_import . "\n";

        // SCRAPPING DEPENDING ON LEGISLATURE
        if ($this->legislature_to_get >= 15) {
            if ($this->legislature_to_get == 15) {
              $file = __DIR__ . '/Scrutins_XV.xml.zip';
            } elseif ($this->legislature_to_get == 16) {
              $file = __DIR__ . '/Scrutins.xml.zip';
            }
            $zip = new ZipArchive();
            if ($zip->open($file) !== TRUE) {
                exit("cannot open <$file>\n");
            } else {
                $voteMainFields = array('mpId', 'vote', 'voteNumero', 'voteId', 'legislature', 'mandatId', 'parDelegation', 'causePosition', 'voteType');
                $voteInfoFields =  array('voteId', 'voteNumero', 'organeRef', 'legislature', 'sessionREF', 'seanceRef', 'dateScrutin', 'quantiemeJourSeance', 'codeTypeVote', 'libelleTypeVote', 'typeMajorite', 'sortCode', 'titre', 'demandeur', 'modePublicationDesVotes', 'nombreVotants', 'suffragesExprimes', 'nbrSuffragesRequis', 'decomptePour', 'decompteContre', 'decompteAbs', 'decompteNv');
                $voteGroupeFields = array('voteId', 'voteNumero', 'legislature', 'organeRef', 'nombreMembresGroupe', 'positionMajoritaire', 'nombrePours', 'nombreContres', 'nombreAbstentions', 'nonVotants', 'nonVotantsVolontaires');
                $votesMain = [];
                $votesInfo = [];
                $votesGroupe = [];

                while (1) {
                    $file_to_import = 'VTANR5L' . $this->legislature_to_get . 'V' . $number_to_import++;
                    $xml_string = $zip->getFromName('xml/' . $file_to_import . '.xml');
                    if ($xml_string == false) { // Check if the AN file forgot to include one vote
                      $file_to_import = 'VTANR5L' .  $this->legislature_to_get . 'V' . ($number_to_import + 1);
                      $xml_string = $zip->getFromName('xml/' . $file_to_import . '.xml');
                    }
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
                    if ($number_to_import % 5 === 0) {
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

            $file = 'https://data.assemblee-nationale.fr/static/openData/repository/14/loi/scrutins/Scrutins_XIV.xml.zip';
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

                    while (1) {
                      if (isset($until_number)) {
                        $number_to_import = $until_number;
                      }
                      $until_number = $number_to_import + 50;

                      $result = $xml->xpath('//acteurRef/ancestor::scrutin[(numero>='.$number_to_import.') and (numero<'.$until_number.')]');

                      if ($result) {

                        foreach ($xml->xpath('//acteurRef/ancestor::scrutin[(numero>=' . $number_to_import . ') and (numero < ' . $until_number . ')]') as $xml2) {

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

                        echo "Let's insert scrutins from " . $number_to_import . " to " . $until_number . "\n";
                        // insert votes
                        $this->insertAll('votes', $voteMainFields, $votesMain);
                        // insert votes infos
                        $this->insertAll('votes_info', $voteInfoFields, $votesInfo);
                        // insert votes groupes
                        $this->insertAll('votes_groupes', $voteGroupeFields, $votesGroupe);
                        $votesMain = [];
                        $votesInfo = [];
                        $votesGroupe = [];

                      } else {
                        break;
                      }
                    }
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

            // Change titre if n? instead of n°
            if (strpos($titre, 'n?')) {
              $titre = str_replace('n?', 'n°', $titre);
            }

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
            if ($type_vote == "amendement" || $type_vote == "sous-amendement") {
                $amdt_n = strstr($titre, 'n°');
                $amdt_n = str_replace("2e rect", "", $amdt_n);
                $amdt_n = str_replace("2ème rect", "", $amdt_n);
                $amdt_n = str_replace("2éme rect", "", $amdt_n);
                $amdt_n = substr($amdt_n, 0, 15);
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
                    posArticle = '$pos_article',
                    titre = '" . addslashes($titre) . "'
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

        $reponseVote = $this->bdd->query('SELECT B.voteNumero, B.legislature, B.mpId, B.vote, B.mandatId, B.sortCode, B.positionGroup, B.gvtPosition AS positionGvt,
          CASE
          	 WHEN B.vote = "nv" THEN NULL
             WHEN B.vote = B.positionGroup THEN 1
            ELSE 0
          END AS scoreLoyaute,
          CASE
            WHEN B.vote = "nv" THEN NULL
            WHEN B.vote = B.sortCode THEN 1
            ELSE 0
          END AS scoreGagnant,
          CASE
            WHEN B.vote = "nv" THEN NULL
            WHEN B.vote = B.gvtPosition THEN 1
          	ELSE 0
          END AS scoreGvt,
          CASE
            WHEN B.vote = "nv" THEN NULL
            ELSE 1
          END AS scoreParticipation
          FROM
          (
            SELECT A.*,
            CASE
              WHEN vg.positionMajoritaire = "pour" THEN 1
              WHEN vg.positionMajoritaire = "contre" THEN -1
              WHEN vg.positionMajoritaire = "abstention" THEN 0
              ELSE "error"
            END AS positionGroup,
            CASE
              WHEN gvt.positionMajoritaire = "pour" THEN 1
              WHEN gvt.positionMajoritaire = "contre" THEN -1
              WHEN gvt.positionMajoritaire = "abstention" THEN 0
              ELSE "error"
            END AS gvtPosition
            FROM
            (
              SELECT v.voteNumero, v.mpId, v.vote,
              CASE
                WHEN sortCode = "adopté" THEN 1
                WHEN sortCode = "rejeté" THEN -1
                ELSE 0
              END AS sortCode,
              v.legislature, mg.mandatId, mg.organeRef
              FROM votes v
              JOIN votes_info vi ON vi.voteNumero = v.voteNumero AND vi.legislature = v.legislature
              LEFT JOIN mandat_groupe mg ON mg.mpId = v.mpId
                AND ((vi.dateScrutin BETWEEN mg.dateDebut AND mg.dateFin ) OR (vi.dateScrutin >= mg.dateDebut AND mg.dateFin IS NULL))
                AND mg.codeQualite IN ("Membre", "Député non-inscrit", "Membre apparenté")
              LEFT JOIN organes o ON o.uid = vi.organeRef
              WHERE v.voteType = "decompteNominatif" AND v.voteNumero >= "' . $lastVote . '" AND v.legislature = "' . $this->legislature_to_get . '"
              ) A
            LEFT JOIN votes_groupes vg ON vg.organeRef = A.organeRef AND vg.voteNumero = A.voteNumero AND vg.legislature = A.legislature
            LEFT JOIN votes_groupes gvt ON gvt.organeRef IN ("PO730964", "PO713077", "PO656002", "PO800538") AND gvt.voteNumero = A.voteNumero AND gvt.legislature = A.legislature
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
        } elseif ($this->legislature_to_get >= 15) {
            $votesLeft = $this->bdd->query('
                SELECT voteNumero
                FROM votes_info
                WHERE legislature = "' . $this->legislature_to_get . '" AND voteNumero NOT IN (
                    SELECT DISTINCT(voteNumero)
                    FROM votes_participation
                    WHERE legislature = "' . $this->legislature_to_get . '" AND voteNumero
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
        if ($this->legislature_to_get >= 15) {
            $result = $this->bdd->query('
            SELECT voteNumero
            FROM votes_participation_commission
            WHERE legislature = "' . $this->legislature_to_get . '"
            ORDER BY voteNumero DESC
            LIMIT 1
            ');

            $last = $result->fetch();
            $last_vote = isset($last['voteNumero']) ? $last['voteNumero'] + 1 : 1;
            echo 'Vote participation commission from : ' . $last_vote . "\n";

            $votes = $this->bdd->query('
                SELECT vi.voteNumero, vi.legislature, vi.dateScrutin, d.*, o.libelleAbrev
                FROM votes_info vi
                LEFT JOIN votes_dossiers vd ON vi.voteNumero = vd.voteNumero AND vi.legislature = vd.legislature
                LEFT JOIN dossiers d ON vd.dossier = d.titreChemin AND d.legislature = vi.legislature
                LEFT JOIN organes o ON d.commissionFond = o.uid
                WHERE vi.voteNumero > "' . $last_vote . '" AND vi.legislature = "' . $this->legislature_to_get . '"
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
                      WHERE vp.voteNumero = "' . $voteNumero . '" AND vp.legislature = "'.$this->legislature_to_get.'" AND ms.typeOrgane = "COMPER" AND ms.codeQualite = "Membre" AND ms.organeRef = "' . $commissionFond . '" AND ms.legislature = "' . $this->legislature_to_get . '" AND ((ms.dateDebut <= "' . $voteDate . '" AND ms.dateFin >= "' . $voteDate . '") OR (ms.dateDebut <= "' . $voteDate . '" AND ms.dateFin IS NULL)) AND vp.participation IS NOT NULL
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
        if ($this->legislature_to_get >= 15) {
            $this->bdd->query('
                DROP TABLE IF EXISTS class_participation_commission;
                CREATE TABLE class_participation_commission
                SELECT A.*,
                CASE WHEN da.dateFin IS NULL THEN 1 ELSE 0 END AS active,
                curdate() AS dateMaj
                FROM
                (
                SELECT v.mpId, v.legislature, ROUND(AVG(v.participation),2) AS score, COUNT(v.participation) AS votesN, ROUND(COUNT(v.participation)/100) AS "index"
                FROM votes_participation_commission v
                WHERE v.participation IS NOT NULL
                GROUP BY v.mpId, v.legislature
                ORDER BY ROUND(COUNT(v.participation)/100) DESC, AVG(v.participation) DESC
                ) A
                LEFT JOIN deputes_all da ON da.mpId = A.mpId AND da.legislature = A.legislature;
                ALTER TABLE class_participation_commission ADD INDEX idx_mpId (mpId);
                ALTER TABLE class_participation_commission ADD INDEX idx_active (active);
            ');
        }
    }

    public function classParticipationSolennels()
    {
        echo "classParticipationSolennels starting \n";
        $this->bdd->query('
            DROP TABLE IF EXISTS class_participation_solennels;
            CREATE TABLE class_participation_solennels
            SELECT A.*,
            	CASE WHEN da.dateFin IS NULL THEN 1 ELSE 0 END AS active,
            	curdate() AS dateMaj
            	FROM
                (
            		SELECT v.mpId, v.legislature, ROUND(AVG(v.participation),2) AS score, COUNT(v.participation) AS votesN, ROUND(COUNT(v.participation)/100) AS "index"
            		FROM votes_participation v
            		LEFT JOIN votes_info vi ON v.voteNumero = vi.voteNumero AND v.legislature = vi.legislature
            		WHERE v.participation IS NOT NULL AND vi.codeTypeVote = "SPS"
            		GROUP BY v.mpId, v.legislature
            	) A
            LEFT JOIN deputes_all da ON da.mpId = A.mpId AND da.legislature = A.legislature;
            ALTER TABLE class_participation_solennels ADD INDEX idx_mpId (mpId);
            ALTER TABLE class_participation_solennels ADD INDEX idx_active (active);
        ');
    }

    public function deputeLoyaute()
    {
        echo "deputeLoyaute starting \n";
        $this->bdd->query('DROP TABLE IF EXISTS deputes_loyaute;
          CREATE TABLE deputes_loyaute
          SELECT v.mpId, v.mandatId, ROUND(AVG(v.scoreLoyaute),3) AS score, COUNT(v.scoreLoyaute) AS votesN, v.legislature, curdate() AS dateMaj
          FROM votes_scores v
          LEFT JOIN mandat_groupe mg ON mg.mandatId = v.mandatId
          WHERE v.scoreLoyaute IS NOT NULL AND mg.mandatId IS NOT NULL AND v.vote != "nv"
          GROUP BY v.mandatId
          ORDER BY v.mpId;
          ALTER TABLE deputes_loyaute ADD PRIMARY KEY (id);
          ALTER TABLE deputes_loyaute ADD INDEX idx_mpId (mpId);
          ALTER TABLE deputes_loyaute ADD INDEX idx_mandatId (mandatId);
          ALTER TABLE deputes_loyaute ADD INDEX idx_legislature (legislature);
        ');
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

        $this->bdd->query('DROP TABLE IF EXISTS `class_groups`');
        $this->bdd->query('CREATE TABLE IF NOT EXISTS `class_groups` (
            `organeRef` varchar(15) NOT NULL,
            `legislature` int(5) NOT NULL,
            `active` int(1) NOT NULL DEFAULT "0",
            `stat` varchar(25) NOT NULL,
            `value` decimal(6,3) NULL DEFAULT NULL,
            `votes` bigint(21) NULL DEFAULT NULL,
            `dateMaj` date NOT NULL,
            KEY `idx_organeRef` (`organeRef`),
            KEY `idx_active` (`active`),
            KEY `idx_legislature` (`legislature`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ');

        $fields = array('organeRef', 'legislature', 'active', 'stat', 'value', 'votes', 'dateMaj');

        $groups = $this->bdd->query('SELECT * FROM organes WHERE coteType = "GP" AND legislature >= 14 ORDER BY legislature ASC');

        while ($group = $groups->fetch()) {
          $uid = $group['uid'];

          echo "data for " . $group['libelle'] . " (" . $group['libelleAbrev'] . ") - legislature : " . $group['legislature'] . " \n";

          $active = $group['dateFin'] ? 0 : 1;

          /// --- COHESION --- ///
          $cohesionQuery = $this->bdd->query('SELECT organeRef, round(avg(cohesion), 3) AS mean, count(cohesion) AS n
            FROM groupes_cohesion
            WHERE organeRef = "' . $uid . '"
            LIMIT 1
          ');
          $cohesion = $cohesionQuery->fetch();

          /// --- MAJORITE --- ///
          $majorityQuery = $this->bdd->query('SELECT organeRef, ROUND(AVG(accord), 3) AS mean,  COUNT(accord) AS n
            FROM groupes_accord
            WHERE organeRefAccord IN ("PO730964", "PO713077", "PO656002", "PO800538") AND organeRef = "' . $uid . '"
            LIMIT 1
          ');
          $majority = $majorityQuery->fetch();

          /// --- PARTICIPATION ALL --- ///
          $participationQuery = $this->bdd->query('SELECT organeRef, ROUND(avg(B.participation_rate), 3) AS mean, COUNT(B.participation_rate) AS n
            FROM
            (
              SELECT A.*, round(A.total / (A.n - A.nv), 3) AS participation_rate
              FROM
              (
              	SELECT vg.voteNumero, vg.organeRef, vg.nombreMembresGroupe as n, vg.nombrePours as pour, vg.nombreContres as contre, vg.nombreAbstentions as abstention, CASE WHEN vg.nonVotants IS NULL THEN 0 ELSE vg.nonVotants END AS nv, vg.nonVotantsVolontaires as nvv, vg.nombrePours+vg.nombreContres+vg.nombreAbstentions as total
              	FROM votes_groupes vg
              	WHERE vg.organeRef = "' . $uid . '"
              ) A
            ) B
          ');
          $participation = $participationQuery->fetch();

          /// --- PARTICIPATION SOLENNELS --- ///
          $participationSPSQuery = $this->bdd->query('SELECT organeRef, ROUND(avg(B.participation_rate), 3) AS mean, COUNT(B.participation_rate) AS n
            FROM
            (
              SELECT A.*, round(A.total / (A.n - A.nv), 3) AS participation_rate
              FROM
              (
              	SELECT vg.voteNumero, vg.organeRef, vg.nombreMembresGroupe as n, vg.nombrePours as pour, vg.nombreContres as contre, vg.nombreAbstentions as abstention, CASE WHEN vg.nonVotants IS NULL THEN 0 ELSE vg.nonVotants END AS nv, vg.nonVotantsVolontaires as nvv, vg.nombrePours+vg.nombreContres+vg.nombreAbstentions as total
              	FROM votes_groupes vg
                LEFT JOIN votes_info vi ON vg.voteNumero = vi.voteNumero AND vg.legislature = vi.legislature
              	WHERE vg.organeRef = "' . $uid . '" AND codeTypeVote = "SPS"
              ) A
            ) B
          ');
          $participationSPS = $participationSPSQuery->fetch();

          /// --- PARTICIPATION COMMISSION --- ///
          $participationCommissionQuery = $this->bdd->query('SELECT round(avg(score), 3) AS mean, round(avg(votesN)) as n
            FROM class_participation_commission c
            LEFT JOIN deputes_all d ON d.mpId = c.mpId AND d.legislature = c.legislature
            WHERE d.groupeId = "' . $uid . '"
          ');
          $participationCommission = $participationCommissionQuery->fetch();

          /// --- INSERT THE DATA --- ///
          // Cohesion
          $insertCohesion = array($uid, $group['legislature'], $active, 'cohesion', $cohesion['mean'], $cohesion['n'], $this->dateMaj);
          $this->insertAll('class_groups', $fields, $insertCohesion);
          $insertMajority = array($uid, $group['legislature'], $active, 'majority', $majority['mean'], $majority['n'], $this->dateMaj);
          $this->insertAll('class_groups', $fields, $insertMajority);
          $insertParticipation = array($uid, $group['legislature'], $active, 'participation', $participation['mean'], $participation['n'], $this->dateMaj);
          $this->insertAll('class_groups', $fields, $insertParticipation);
          $insertParticipationSPS = array($uid, $group['legislature'], $active, 'participationSPS', $participationSPS['mean'], $participationSPS['n'], $this->dateMaj);
          $this->insertAll('class_groups', $fields, $insertParticipationSPS);
          $insertParticipationCommission = array($uid, $group['legislature'], $active, 'participationCommission', $participationCommission['mean'], $participationCommission['n'], $this->dateMaj);
          $this->insertAll('class_groups', $fields, $insertParticipationCommission);

        }
        
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
        $until_html = file_get_html("https://www2.assemblee-nationale.fr/scrutins/liste/(legislature)/'.$this->legislature_to_get.'/(type)/TOUS/(idDossier)/TOUS");
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
            $url = "https://www2.assemblee-nationale.fr/scrutins/liste/(offset)/" . $offset . "/(legislature)/" . $this->legislature_to_get . "/(type)/TOUS/(idDossier)/TOUS";

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
                                $dossier1 = str_replace('https://www.assemblee-nationale.fr/'.$this->legislature_to_get.'/dossiers/', '', $href);
                                $dossier = str_replace('.asp', '', $dossier1);
                            } else {
                                //echo "4";
                                $dossier = str_replace('https://www.assemblee-nationale.fr/dyn/'.$this->legislature_to_get.'/dossiers/', '', $href);
                            }
                        }
                    }
                }

                $dossier = !empty($dossier) ? "$dossier" : NULL;
                $href = !empty($href) ? "$href" : NULL;

                $voteDossier = array('offset_num' => $offset, 'legislature' => $this->legislature_to_get, 'voteNumero' => $voteNumero, 'href' => $href, 'dossier' => $dossier);
                $voteDossiers = array_merge($voteDossiers, array_values($voteDossier));
                if ($i % 100 === 0) {
                    echo "Let's insert 100 rows\n";
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
        if ($this->legislature_to_get >= 15) {
            if ($this->legislature_to_get == 15) {
              $file = __DIR__ . '/Dossiers_Legislatifs_XV.xml.zip';
            } elseif ($this->legislature_to_get == 16) {
              $file = __DIR__ . '/Dossiers_Legislatifs.xml.zip';
            }

            $zip = new ZipArchive();

            if ($zip->open($file) !== TRUE) {
                exit("cannot open <$file>\n");
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

    public function dossiersActeurs()
    {
        echo "dossiersActeurs starting \n";
        $this->bdd->query('
            DELETE FROM dossiers_acteurs WHERE legislature = "' . $this->legislature_to_get . '"
        ');

        $dossierActeursFields = array('id', 'legislature', 'etape', 'value', 'type', 'ref', 'mandate');
        $dossierActeur = [];
        $dossiersActeurs = [];
        $n = 1;

        if ($this->legislature_to_get >= 15) {
            if ($this->legislature_to_get == 15) {
              $file = __DIR__ . '/Dossiers_Legislatifs_XV.xml.zip';
            } elseif ($this->legislature_to_get == 16) {
              $file = __DIR__ . '/Dossiers_Legislatifs.xml.zip';
            }

            $zip = new ZipArchive();
            if ($zip->open($file) !== TRUE) {
                exit("cannot open <$file>\n");
            } else {

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    //echo 'Filename: ' . $filename . '<br />';
                    $sub = substr($filename, 0, 13);

                    if ($sub == 'xml/dossierPa') {

                        $xml_string = $zip->getFromName($filename);

                        if ($xml_string != false) {

                            $xml = simplexml_load_string($xml_string);

                            $id = (string) $xml->uid;
                            $legislature = (string) $xml->legislature;

                            // 1 - Get initiateurs : acteurs and organes

                            if (!empty($xml->initiateur)) {

                              if (!empty($xml->initiateur->acteurs)) {
                                $type = 'acteur';
                                foreach ($xml->initiateur->acteurs->acteur as $x) {
                                  $ref = (string) $x->acteurRef;
                                  $mandate = (string) $x->mandatRef;

                                  $dossierActeur = array('id' => $id, 'legislature' => $legislature, 'etape' => NULL, 'value' => 'initiateur', 'type' => $type, 'ref' => $ref, 'mandate' => $mandate);
                                  $dossiersActeurs = array_merge($dossiersActeurs, array_values($dossierActeur));
                                  if ($n % 1000 === 0) {
                                    $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
                                    $dossierActeur = [];
                                    $dossiersActeurs = [];
                                  }
                                  $n++;
                                }
                              }

                              if (!empty($xml->initiateur->organes)) {
                                $type = 'organe';
                                foreach ($xml->initiateur->organes->organe as $y) {
                                  $ref = (string) $y->organeRef->uid;
                                  $mandate = NULL;

                                  $dossierActeur = array('id' => $id, 'legislature' => $legislature, 'etape' => NULL, 'value' => 'initiateur', 'type' => $type, 'ref' => $ref, 'mandate' => $mandate);
                                  $dossiersActeurs = array_merge($dossiersActeurs, array_values($dossierActeur));
                                  if ($n % 1000 === 0) {
                                    $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
                                    $dossierActeur = [];
                                    $dossiersActeurs = [];
                                  }
                                  $n++;
                                }
                              }
                            }

                            // 2 - Get rapporteurs

                            if (!empty($xml->actesLegislatifs)) {
                              foreach ($xml->actesLegislatifs->acteLegislatif as $acte) {
                                $etape = (string) $acte->codeActe;

                                foreach ($acte->xpath(".//*[local-name()='rapporteur']") as $rapporteur) {
                                  $ref = (string) $rapporteur->acteurRef;
                                  $type = (string) $rapporteur->typeRapporteur;
                                  $mandate = NULL;

                                  $dossierActeur = array('id' => $id, 'legislature' => $legislature, 'etape' => $etape, 'value' => 'rapporteur', 'type' => $type, 'ref' => $ref, 'mandate' => $mandate);
                                  $dossiersActeurs = array_merge($dossiersActeurs, array_values($dossierActeur));
                                  if ($n % 1000 === 0) {
                                    $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
                                    $dossierActeur = [];
                                    $dossiersActeurs = [];
                                  }
                                  $n++;
                                }
                              }
                            }
                        }
                    }
                }
                // Insert the rest
                $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
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
                        $id = (string) $dossier->uid;
                        $legislature = (string) $dossier->legislature;

                        // 1 - Get initiateurs : acteurs and organes

                        if (!empty($dossier->initiateur)) {

                          // Acteurs
                          if (!empty($dossier->initiateur->acteurs)) {
                            $type = 'acteur';
                            foreach ($dossier->initiateur->acteurs->acteur as $x) {
                              $ref = (string) $x->acteurRef;
                              $mandate = (string) $x->mandatRef;

                              $dossierActeur = array('id' => $id, 'legislature' => $legislature, 'etape' => NULL, 'value' => 'initiateur', 'type' => $type, 'ref' => $ref, 'mandate' => $mandate);
                              $dossiersActeurs = array_merge($dossiersActeurs, array_values($dossierActeur));
                              if ($n % 1000 === 0) {
                                $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
                                $dossierActeur = [];
                                $dossiersActeurs = [];
                              }
                              $n++;
                            }
                          }

                          // Organes
                          if (!empty($dossier->initiateur->organes)) {
                            $type = 'organe';
                            foreach ($dossier->initiateur->organes->organe as $y) {
                              $ref = (string) $y->organeRef->uid;
                              $mandate = NULL;

                              $dossierActeur = array('id' => $id, 'legislature' => $legislature, 'etape' => NULL, 'value' => 'initiateur', 'type' => $type, 'ref' => $ref, 'mandate' => $mandate);
                              $dossiersActeurs = array_merge($dossiersActeurs, array_values($dossierActeur));
                              if ($n % 1000 === 0) {
                                $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
                                $dossierActeur = [];
                                $dossiersActeurs = [];
                              }
                              $n++;
                            }
                          }
                        }

                        // 2 - Get rapporteurs

                        if (!empty($dossier->actesLegislatifs)) {
                          foreach ($dossier->actesLegislatifs->acteLegislatif as $acte) {
                            $etape = (string) $acte->codeActe;

                            foreach ($acte->xpath(".//*[local-name()='rapporteur']") as $rapporteur) {
                              $ref = (string) $rapporteur->acteurRef;
                              $type = (string) $rapporteur->typeRapporteur;
                              $mandate = NULL;

                              $dossierActeur = array('id' => $id, 'legislature' => $legislature, 'etape' => $etape, 'value' => 'rapporteur', 'type' => $type, 'ref' => $ref, 'mandate' => $mandate);
                              $dossiersActeurs = array_merge($dossiersActeurs, array_values($dossierActeur));
                              if ($n % 1000 === 0) {
                                $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
                                $dossierActeur = [];
                                $dossiersActeurs = [];
                              }
                              $n++;
                            }
                          }
                        }
                    }
                    // Insert the rest
                    $this->insertAll('dossiers_acteurs', $dossierActeursFields, $dossiersActeurs);
                }
            }
        }
    }

    public function documentsLegislatifs()
    {
      echo "documentsLegislatifs starting \n";

      $fields = array('id', 'dossierId', 'numNotice', 'titre', 'titreCourt');
      $insert = [];

      if ($this->legislature_to_get >= 15) {
        if ($this->legislature_to_get == 15) {
          $file = __DIR__ . '/Dossiers_Legislatifs_XV.xml.zip';
        } elseif ($this->legislature_to_get == 16) {
          $file = __DIR__ . '/Dossiers_Legislatifs.xml.zip';
        }

        $zip = new ZipArchive();
        if ($zip->open($file) !== TRUE) {
            exit("cannot open <$file>\n");
        } else {
          for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            //echo 'Filename: ' . $filename . '<br />';

            $split = preg_split("#/#", $filename);
            $type = $split[1];
            $file = $split[2];
            if ($type == "document" && preg_match("/(PRJL|PION|PNRE)/", $file)) {
              $xml_string = $zip->getFromName($filename);

              if ($xml_string != false) {
                $xml = simplexml_load_string($xml_string);

                $id = $xml->uid;
                $dossierId = $xml->dossierRef;
                $numNotice = $xml->notice->numNotice;
                $titre = $xml->titres->titrePrincipal;
                $titreCourt = $xml->titres->titrePrincipalCourt;

                //echo $id. ' - ' . $dossierId . ' - ' . $numNotice . ' - ' . $titre . ' - ' . $titreCourt . '<br>';
                $doc = array('id' => $id, 'dossierId' => $dossierId,  'numNotice' => $numNotice, 'titre' => $titre, 'titreCourt' => $titreCourt);
                $insert = array_merge($insert, array_values($doc));
                if (($i + 1) % 500 === 0) {
                    echo "Let's insert until " . $i . "\n";
                    $this->insertAll('documents_legislatifs', $fields, $insert);
                    $insert = [];
                }
              }
            }
          }
          echo "Let's insert until the end : " . $i . "\n";
          $this->insertAll('documents_legislatifs', $fields, $insert);
        }
      } elseif ($this->legislature_to_get == 14) {

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

            foreach ($xml->xpath("//*[local-name()='document']") as $document) {
              $id = $document->uid;
              $dossierId = $document->dossierRef;
              $numNotice = $document->notice->numNotice;
              if ($numNotice == "") {
                $numNotice = NULL;
              }
              $titre = $document->titres->titrePrincipal;
              $titreCourt = $document->titres->titrePrincipalCourt;

              //echo $id. ' - ' . $dossierId . ' - ' . $numNotice . ' - ' . $titre . ' - ' . $titreCourt . '<br>';
              $doc = array('id' => $id, 'dossierId' => $dossierId,  'numNotice' => $numNotice, 'titre' => $titre, 'titreCourt' => $titreCourt);
              $insert = array_merge($insert, array_values($doc));
              $this->insertAll('documents_legislatifs', $fields, $insert);
              $insert = [];
            }
          }
        }
      }
    }

    public function amendements()
    {
        echo "amendements starting \n";

        $fields = array('id', 'dossier', 'legislature', 'texteLegislatifRef', 'num', 'numordre', 'seanceRef', 'expose');
        $file = __DIR__ . '/Amendements_XV.xml.zip';
        $zip = new ZipArchive();
        $insert = [];

        if ($zip->open($file) !== TRUE) {
            exit("cannot open <$file>\n");
        } else {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                $xml_string = $zip->getFromName($filename);

                if ($xml_string != false) {
                  //echo 'Filename: ' . $filename . '<br>';
                  $xml = simplexml_load_string($xml_string);

                  $split = preg_split("#/#", $filename);
                  $dossier = $split[1];

                  $id = $xml->uid;
                  $legislature = $xml->legislature;
                  $texteLegislatifRef = $xml->texteLegislatifRef;
                  $num = $xml->identification->numeroLong;
                  $numOrdre = $xml->identification->numeroOrdreDepot;
                  $seanceRef = $xml->seanceDiscussionRef;
                  $expose = $xml->corps->contenuAuteur->exposeSommaire;

                  // Insert NULL values
                  $seanceRef = $seanceRef == "" ? NULL : $seanceRef;
                  $expose = $expose == "" ? NULL : $expose;

                  //echo $id . ' - ' . $dossier . ' - ' . $legislature . ' - ' . $texteLegislatifRef . ' - ' . $num . ' - ' . $numOrdre . ' - ' . $seanceRef;
                  //echo ' - ' . $expose;
                  //echo '<br><br>';

                  $amdt = array('id' => $id, 'dossier' => $dossier,  'legislature' => $legislature, 'texteLegislatifRef' => $texteLegislatifRef, 'num' => $num, 'numOrdre' => $numOrdre, 'seanceRef' => $seanceRef, 'expose' => $expose);
                  $insert = array_merge($insert, array_values($amdt));
                  if (($i + 1) % 1000 === 0) {
                      echo "Insert until : " . $i . " | ";
                      $this->insertAll('amendements', $fields, $insert, FALSE);
                      $insert = [];
                  }
                }
            }
        }
        echo "Let's insert until the end : " . $i . "\n";
        $this->insertAll('amendements', $fields, $insert);
    }

    public function amendementsAuteurs()
    {
        echo "amendementsAuteurs starting \n";

        $fields = array('id', 'type', 'acteurRef', 'groupeId', 'auteurOrgane');
        $file = __DIR__ . '/Amendements_XV.xml.zip';
        $zip = new ZipArchive();
        $insertAll = [];

        if ($zip->open($file) !== TRUE) {
            exit("cannot open <$file>\n");
        } else {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                $xml_string = $zip->getFromName($filename);

                if ($xml_string != false) {
                  //echo 'Filename: ' . $filename . '<br>';
                  $xml = simplexml_load_string($xml_string);

                  $id = $xml->uid;
                  $type = $xml->signataires->auteur->typeAuteur;
                  if (in_array($type, array("Député", "Rapporteur"))) {
                    $acteurRef = $xml->signataires->auteur->acteurRef;
                  } elseif ($type == "Gouvernement") {
                    $acteurRef = $xml->signataires->auteur->gouvernementRef;
                  }
                  $groupeId = $xml->signataires->auteur->groupePolitiqueRef;
                  $auteurOrgane = $xml->signataires->auteur->auteurRapporteurOrganeRef;

                  // Insert NULL values
                  $groupeId = $groupeId == "" ? NULL : $groupeId;
                  $auteurOrgane = $auteurOrgane == "" ? NULL : $auteurOrgane;

                  //echo $id . ' - ' . $type . ' - ' . $acteurRef . ' - ' . $groupeId . ' - ' . $auteurOrgane;
                  //echo '<br><br>';

                  $insertAuteur = array('id' => $id, 'type' => $type,  'acteurRef' => $acteurRef, 'groupeId' => $groupeId, 'auteurOrgane' => $auteurOrgane);
                  $insertAll = array_merge($insertAll, array_values($insertAuteur));
                  if (($i + 1) % 1000 === 0) {
                      echo "Insert until : " . $i . " | ";
                      $this->insertAll('amendements_auteurs', $fields, $insertAll, FALSE);
                      $insertAll = [];
                  }
                }
            }
        }
        echo "Let's insert until the end : " . $i . "\n";
        $this->insertAll('amendements_auteurs', $fields, $insertAll);
    }

    public function classParticipationSix()
    {
        echo "classParticipationSix starting \n";
        if ($this->legislature_to_get == 15) {

          $this->bdd->query('
            DROP TABLE IF EXISTS class_participation_six;
            CREATE TABLE class_participation_six
            SELECT @s:=@s+1 AS "classement", C.*, curdate() AS dateMaj
            FROM
            (
                SELECT B.*
                FROM
                (
                    SELECT A.mpId, ROUND(AVG(A.participation),2) AS score, COUNT(A.participation) AS votesN, ROUND(COUNT(A.participation)/10) AS "index"
                    FROM
                    (
                        SELECT v.mpId, v.participation, vi.dateScrutin
                        FROM votes_participation v
                        LEFT JOIN votes_info vi ON v.voteNumero = vi.voteNumero
                        WHERE vi.dateScrutin >= CURDATE() - INTERVAL 12 MONTH AND vi.codeTypeVote = "SPS"
                    ) A
                    WHERE A.participation IS NOT NULL
                    GROUP BY A.mpId
                    ORDER BY ROUND(COUNT(A.participation)/10) DESC, AVG(A.participation) DESC
                ) B
                LEFT JOIN deputes_last dl ON B.mpId = dl.mpId
                WHERE dl.active
            ) C,
            (SELECT @s:= 0) AS s
            ORDER BY C.score DESC, C.votesN DESC;
            ALTER TABLE class_participation_six ADD PRIMARY KEY (id);
            ALTER TABLE class_participation_six ADD INDEX idx_mpId (mpId);
          ');
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
        $terms = array(14, 15, 16);
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

    public function parrainages(){
      // 1. Create table if not exists
      $this->bdd->query("CREATE TABLE IF NOT EXISTS `parrainages` (
          `id` INT NOT NULL AUTO_INCREMENT ,
          `civ` VARCHAR(5) NOT NULL ,
          `nameLast` VARCHAR(75) NOT NULL ,
          `nameFirst` VARCHAR(75) NOT NULL ,
          `mandat` VARCHAR(100) NOT NULL ,
          `circo` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
          `dpt` VARCHAR(80) NOT NULL ,
          `candidat` VARCHAR(100) NOT NULL ,
          `datePublication` DATE NOT NULL ,
          `year` INT NOT NULL ,
          `mpId` VARCHAR(35) NULL DEFAULT NULL ,
          `dateMaj` DATE  ,
          PRIMARY KEY (`id`) ,
          UNIQUE INDEX (`nameLast`, `nameFirst`, `dpt`, `datePublication`, `mandat`) ,
          INDEX `mpId_idx` (`mpId`)) ENGINE = MyISAM;
      ");

      // 2. Get and insert open data
      //$json = file_get_contents("https://www.data.gouv.fr/fr/datasets/r/4b261e7d-b901-4f2a-af3a-195862de49fd"); //Old one using Data Gouv
      function remove_utf8_bom($text) {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
      }
      $context = stream_context_create(
          array(
              "http" => array(
                  "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
              )
          )
      );
      $json = file_get_contents("https://presidentielle2022.conseil-constitutionnel.fr/telechargement/parrainagestotal.json", false, $context); // New one from the Conseil constitutionnel website
      $json = remove_utf8_bom($json);
      $obj = json_decode($json);

      $i = 1;
      $parrainagesFields = array('civ', 'nameLast', 'nameFirst', 'mandat', 'circo', 'dpt', 'candidat', 'datePublication', 'year', 'dateMaj');
      $parrainages = [];
      $parrainage = [];

      foreach ($obj as $key => $value) {
        $year = 2022;
        $civ = $value->Civilite;
        $nameFirst = $value->Prenom;
        $nameLast = ucfirst(mb_strtolower($value->Nom));
        $mandat =  mb_strtolower($value->Mandat);
        $circo = !empty($value->Circonscription) ? $value->Circonscription : NULL;
        $dpt = $value->Departement;
        $candidat = $value->Candidat;
        $datePublication = substr($value->DatePublication, 0, 10);

        $parrainage = array(
          'civ' => $civ,
          'nameLast' => $nameLast,
          'nameFirst' => $nameFirst,
          'mandat' => $mandat,
          'circo' => $circo,
          'dpt' => $dpt,
          'candidat' => $candidat,
          'datePublication' => $datePublication,
          'year' => $year,
          'dateMaj' => $this->dateMaj
        );
        $parrainages = array_merge($parrainages, array_values($parrainage));

        if ($i % 10 === 0) {
            echo "Let's import until parrainage n " . $i . "\n";
            $this->insertAll('parrainages', $parrainagesFields, $parrainages);
            $parrainages = [];
        }
        $i++;

      }

      $this->insertAll('parrainages', $parrainagesFields, $parrainages);

      // 3. Add mpId when we find it

      $query = $this->bdd->query('SELECT * FROM parrainages');

      while ($parrainage = $query->fetch()) {
        if (($parrainage['mandat'] == 'député' || $parrainage['mandat'] == 'députée') && is_null($parrainage['mpId'])) {

          $queryMatch = $this->bdd->query('SELECT *
            FROM deputes_last
            WHERE nameFirst = "' . $parrainage['nameFirst'] . '" AND nameLast = "' . $parrainage['nameLast'] . '" AND legislature = 15
          ');

          if ($queryMatch->rowCount() == 1) {
            while ($match = $queryMatch->fetch()) {
              $mpId = $match['mpId'];

              $this->bdd->query('UPDATE parrainages SET mpId = "'.$mpId.'" WHERE id = "'.$parrainage['id'].'"');

            }
          }
        }

      }

    }

    public function opendata_activeMPs()
    {
      $query = "SELECT
      	da.mpId AS id,
          da.legislature,
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
          da.job,
          dc.mailAn AS mail,
          dc.twitter,
          dc.facebook,
          dc.website,
          h.mandatesN AS nombreMandats,
          h.lengthEdited AS experienceDepute,
          cp.score AS scoreParticipation,
          cpc.score AS scoreParticipationSpecialite,
          cl.score AS scoreLoyaute,
          cm.score AS scoreMajorite,
          curdate() AS dateMaj
        FROM deputes_last da
        LEFT JOIN deputes d ON d.mpId = da.mpId
        LEFT JOIN deputes_contacts dc ON dc.mpId = da.mpId
        LEFT JOIN history_per_mps_average h ON da.mpId = h.mpId
        LEFT JOIN class_participation cp ON da.mpId = cp.mpId AND da.legislature = cp.legislature
        LEFT JOIN class_participation_commission cpc ON da.mpId = cpc.mpId AND da.legislature = cpc.legislature
        LEFT JOIN class_loyaute cl ON da.mpId = cl.mpId AND da.legislature = cl.legislature
        LEFT JOIN class_majorite cm ON da.mpId = cm.mpId AND da.legislature = cm.legislature
        WHERE da.active
      ";

      $this->opendata($query, "deputes_active.csv", "5fc8b732d30fbf1ed6648aab", "092bd7bb-1543-405b-b53c-932ebb49bb8e");
    }

    public function opendata_activeGroupes()
    {
      $query = "SELECT
      	o.uid AS id,
      	o.legislature,
          o.libelle,
          o.libelleAbrev,
          o.libelleAbrege,
          o.dateDebut,
          o.positionPolitique,
          o.couleurAssociee,
          ge.effectif,
          gs.womenPct as women,
          gs.age AS age,
          gs.rose_index AS scoreRose,
          class.cohesion AS socreCohesion,
          ROUND(class.participation, 3) AS scoreParticipation,
          class.majoriteAccord AS scoreMajorite,
          curdate() as dateMaj
      FROM organes o
      LEFT JOIN groupes_stats gs ON gs.organeRef = o.uid
      LEFT JOIN groupes_effectif ge ON ge.organeRef = o.uid
      LEFT JOIN class_groups class ON class.organeRef = o.uid
      WHERE o.coteType = 'GP' AND o.dateFin IS NULL
      ";

      $this->opendata($query, "groupes_active.csv", "60ed57a9f0c7c3a1eb29733f", "4612d596-9a78-4ec6-b60c-ccc1ee11f8c0");
    }

    public function opendata_historyMPs()
    {
      $query = "SELECT
      	da.mpId AS id,
          da.legislature AS legislatureLast,
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
          da.job,
          dc.mailAn AS mail,
          dc.twitter,
          dc.facebook,
          dc.website,
          h.mandatesN AS nombreMandats,
          h.lengthEdited AS experienceDepute,
          cp.score AS scoreParticipation,
          cpc.score AS scoreParticipationSpecialite,
          cl.score AS scoreLoyaute,
          cm.score AS scoreMajorite,
          da.active,
          curdate() AS dateMaj
        FROM deputes_last da
        LEFT JOIN deputes d ON d.mpId = da.mpId
        LEFT JOIN deputes_contacts dc ON dc.mpId = da.mpId
        LEFT JOIN history_per_mps_average h ON da.mpId = h.mpId
        LEFT JOIN class_participation cp ON da.mpId = cp.mpId AND da.legislature = cp.legislature
        LEFT JOIN class_participation_commission cpc ON da.mpId = cpc.mpId AND da.legislature = cpc.legislature
        LEFT JOIN class_loyaute cl ON da.mpId = cl.mpId AND da.legislature = cl.legislature
        LEFT JOIN class_majorite cm ON da.mpId = cm.mpId AND da.legislature = cm.legislature
      ";

      $this->opendata($query, "deputes-historique.csv", "60f2ffc8284ff5e8c1ed0655", "817fda38-d616-43e9-852f-790510f4d157");
    }

    public function opendata_historyGroupes()
    {
      $query = "SELECT
      	o.uid AS id,
      	o.legislature,
          o.libelle,
          o.libelleAbrev,
          o.libelleAbrege,
          o.dateDebut,
          o.positionPolitique,
          o.couleurAssociee,
          ge.effectif,
          gs.womenPct as women,
          gs.age AS age,
          gs.rose_index AS scoreRose,
          class.cohesion AS socreCohesion,
          ROUND(class.participation, 3) AS scoreParticipation,
          class.majoriteAccord AS scoreMajorite,
          CASE WHEN o.dateFin IS NULL THEN 1 ELSE 0 END AS active,
          curdate() as dateMaj
      FROM organes o
      LEFT JOIN groupes_stats gs ON gs.organeRef = o.uid
      LEFT JOIN groupes_effectif ge ON ge.organeRef = o.uid
      LEFT JOIN class_groups class ON class.organeRef = o.uid
      WHERE o.coteType = 'GP' AND o.legislature >= 14
      ";

      $this->opendata($query, "groupes-historique.csv", "60f30419135bec6a5e480086", "530940ab-45f3-41e3-8de3-759568c728b8");
    }
}

// Specify the legislature
if (isset($argv[1])) {
    $script = new Script($argv[1]);
} else {
    $script = new Script();
}
/*
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
$script->vote(); // Depend on the legislature
$script->updateVoteInfo(); // Depend on the legislature
$script->voteScore(); // Depend on the legislature
$script->groupeCohesion(); // Depend on the legislature
$script->groupeAccord(); // Depend on the legislature
$script->deputeAccord(); // Depend on the legislature
$script->voteParticipation(); // Depend on the legislature
$script->votesDossiers(); // Depend on the legislature
$script->dossier(); // Depend on the legislature
$script->dossiersActeurs(); // Depend on the legislature
$script->documentsLegislatifs(); // Depend on the legislature
//$script->amendements(); // Need to be checked for leg 16
//$script->amendementsAuteurs(); // Need to be checked for leg 16
$script->voteParticipationCommission(); // Depend on the legislature
$script->classParticipation();
$script->classParticipationCommission(); // Will need to be changed w/ leg 16
$script->classParticipationSolennels();
$script->deputeLoyaute();
$script->classLoyaute();
$script->classMajorite();
*/
$script->classGroups();
/*
$script->classGroupsProximite();
//$script->classParticipationSix(); // Will need to be changed w/ leg 16
//$script->classLoyauteSix(); // Will need to be changed w/ leg 16
$script->deputeAccordCleaned();
$script->historyMpsAverage();
$script->historyPerMpsAverage();
//$script->parrainages(); // No long used
$script->opendata_activeMPs();
$script->opendata_activeGroupes();
$script->opendata_historyMPs();
$script->opendata_historyGroupes();
