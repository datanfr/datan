<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>1_deputes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" >
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"></script>
  </head>
  <!--

  This script gets the data from the AMO30_tous_acteurs_tous_mandats_tous_organes_historique field
  and stores the information in a "deputes" table. Each time it truncate the "deputes" table.

  -->
  <body>
    <?php
      $url = $_SERVER['REQUEST_URI'];
      $url = str_replace(array("/", "datan", "scripts", ".php"), "", $url);
      $url_current = substr($url, 0, 1);
      $url_next = $url_current + 1;
      $deputeFields = array('mpId', 'civ', 'nameFirst', 'nameLast', 'nameUrl', 'birthDate', 'birthCity', 'birthCountry', 'job', 'catSocPro', 'dateMaj');
    ?>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>1. Mise à jour base 'deputes'</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-success" href="./<?= $url_next ?>_deputes.php" role="button">Next</a>
				</div>
			</div>
			<div class="row mt-3">
        <table class="table">
          <thead>
              <tr>
                <th scope="col">#</th>
                <?php foreach($deputeFields as $deputeField) : ?>
                <th scope="col"><?= $deputeField ?></th>
                <?php endforeach ?>
              </tr>
            </thead>
            <tbody>
              <?php
                include('bdd-connexion.php');

                function placeholders($text, $count=0, $separator=","){
                  $result = array();
                  if($count > 0){
                      for($x=0; $x<$count; $x++){
                          $result[] = $text;
                      }
                  }
              
                  return implode($separator, $result);
              }
                $bdd->query("TRUNCATE TABLE deputes");
                $dateMaj = date('Y-m-d');
                //Online file
                $file = 'http://data.assemblee-nationale.fr/static/openData/repository/15/amo/tous_acteurs_mandats_organes_xi_legislature/AMO30_tous_acteurs_tous_mandats_tous_organes_historique.xml.zip';
                $file = trim($file);
                $newfile = 'tmp_acteurs_organes.zip';
                if (!copy($file, $newfile)) {
                  echo "failed to copy $file...\n";
                }
                $zip = new ZipArchive();
                if ($zip->open($newfile)!==TRUE) {
                  exit("cannot open <$filename>\n");
                } else {
                  $insert_values = [];
                  for ($i=0; $i < $zip->numFiles ; $i++) {
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
                        $famSocPro = $xml->profession->socProcINSEE->famSocPro;
                        $lastname = \Transliterator::createFromRules(
                          ':: Any-Latin;'
                          . ':: NFD;'
                          . ':: [:Nonspacing Mark:] Remove;'
                          . ':: NFC;'
                          . ':: [:Space:] Remove;'
                          . ':: [:Punctuation:] Remove;'
                          . ':: Lower();'
                          . '[:Separator:] > \'-\';'
                        )->transliterate($nameLast);
                        $firstname = \Transliterator::createFromRules(
                          ':: Any-Latin;'
                          . ':: NFD;'
                          . ':: [:Nonspacing Mark:] Remove;'
                          . ':: NFC;'
                          . ':: [:Punctuation:] Remove;'
                          . ':: Lower();'
                          . '[:Separator:] > \'-\';'
                        )->transliterate($nameFirst);
                        $nameUrl = "{$firstname}-{$lastname}";
                        ?>

                        <tr>
                          <td><?= $i ?></td>
                          <td><?= $file ?></td>
                          <td><?= $mpId ?></td>
                          <td><?= $civ ?></td>
                          <td><?= $nameFirst ?></td>
                          <td><?= $nameLast ?></td>
                          <td><?= $nameUrl ?></td>
                          <td><?= $birthDate ?></td>
                          <td><?= $birthCity ?></td>
                          <td><?= $birthCountry ?></td>
                          <td><?= $job ?></td>
                          <td><?= $catSocPro ?></td>
                          <td><?= $famSocPro ?></td>
                          <td><?= $dateMaj ?></td>
                        </tr>

                        <?php
                      try{
                        $depute = array('mpId' => $mpId, 'civ' => $civ, 'nameFirst' => $nameFirst, 'nameLast' => $nameLast, 'nameUrl' => $nameUrl, 'birthDate' => $birthDate, 'birthCity' => $birthCity, 'birthCountry' => $birthCountry, 'job' => $job, 'catSocPro' => $catSocPro, 'dateMaj' => $dateMaj);
                        $question_marks[] = '('  . placeholders('?', sizeof($depute)) . ')';
                        $insert_values = array_merge($insert_values, array_values($depute));
  
                      }catch(Exception $e){
                        echo '<pre>', var_dump($e->getMessage()), '</pre>';
                      }

                      }
                    }
                  }

                  try{
                    // SQL //
                    $sql = "INSERT INTO deputes (" . implode(",", $deputeFields ) . ") VALUES ". implode(',', $question_marks);  
                    $stmt = $bdd->prepare ($sql);
                    $stmt->execute($insert_values);
                    }
                    catch(Exception $e){
                      echo '<pre>', var_dump($e->getMessage()), '</pre>';die('');
                    }
                }
              ?>
            </tbody>
        </table>
			</div>
		</div>
	</body>
</html>
