<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script upate the table cities_mayors

  -->
  <body>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>Update the table cities_mayors</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <?php
            include 'bdd-connexion.php';

            $bdd->query('
              CREATE TABLE IF NOT EXISTS `cities_mayors` (
                `dpt` varchar(5) DEFAULT NULL,
                `libelle_dpt` text,
                `insee` smallint(6) DEFAULT NULL,
                `libelle_commune` text NOT NULL,
                `nameLast` text,
                `nameFirst` text,
                `gender` varchar(2) DEFAULT NULL,
                `birthDate` date DEFAULT NULL,
                `profession` smallint(6) DEFAULT NULL,
                `libelle_profession` text,
                `dateMaj` date NOT NULL,
                KEY `idx_dpt` (`dpt`),
                KEY `idx_insee` (`insee`)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            ');

            $bdd->query('TRUNCATE TABLE cities_mayors');

            $url = "https://www.data.gouv.fr/fr/datasets/r/2876a346-d50c-4911-934e-19ee07b0e503";
            $lines = file($url, FILE_IGNORE_NEW_LINES);

            //remove two first lines

            $i = 1;
            foreach ($lines as $key => $value) {
              $value = utf8_encode($value);
              $array = preg_split("/[\t]/", $value);
              if ($array[0] != "Titre du rapport" && $array[0] != "Code du département (Maire)") {

                $dpt = $array[0];
                $libelle_dpt = $array[1];
                $insee = $array[2];
                $libelle_commune = $array[3];
                $nameLast = $array[4];
                $nameFirst = $array[5];
                $gender = $array[6];
                $birthDate = $array[7];
                $profession = $array[8];
                $libelle_profession = $array[9];

                $birthDate = str_replace("/", "-", $birthDate);
                $birthDate = date("Y-m-d", strtotime($birthDate));

                if ($profession == "") $profession = null;

                if ($libelle_profession == "") $libelle_profession = null;

                switch ($dpt) {
                  case '2A':
                    $dpt = '2a';
                    break;
                  case '2B':
                    $dpt = '2b';
                    break;
                  case 'ZA':
                    $dpt = 971;
                    break;
                  case 'ZB':
                    $dpt = 972;
                    break;
                  case 'ZC':
                    $dpt = 973;
                    break;
                  case 'ZD':
                    $dpt = 974;
                    break;
                  case 'ZM':
                    $dpt = 976;
                    break;
                  case 'ZN':
                    $dpt = 988;
                    break;
                  case 'ZP':
                    $dpt = 987;
                    break;
                  case 'ZS':
                    $dpt = 975;
                    break;
                  case 'ZW':
                    $dpt = 986;
                    break;
                  case 'ZX':
                    $dpt = 977;
                    break;
                  case 'ZZ':
                    $dpt = "099";
                    break;
                  default:
                    $dpt;
                    break;
                }


                echo $i." - ".$dpt." - ".$libelle_dpt." - ".$insee." - ".$libelle_commune." - ".$nameLast." - ".$nameFirst." - ".$gender." - ".$birthDate." - ".$profession." - ".$libelle_profession."<br>";

                $update1 = $bdd->prepare("INSERT INTO cities_mayors (dpt, libelle_dpt, insee, libelle_commune, nameLast, nameFirst, gender, birthDate, profession, libelle_profession, dateMaj) VALUES (:dpt, :libelle_dpt, :insee, :libelle_commune, :nameLast, :nameFirst, :gender, :birthDate, :profession, :libelle_profession, CURDATE() )");
                $update1array = array(
                  'dpt' => $dpt,
                  'libelle_dpt' => $libelle_dpt,
                  'insee' => $insee,
                  'libelle_commune' => $libelle_commune,
                  'nameLast' => $nameLast,
                  'nameFirst' => $nameFirst,
                  'gender' => $gender,
                  'birthDate' => $birthDate,
                  'profession' => $profession,
                  'libelle_profession' => $libelle_profession
                );
                $update1->execute($update1array);

                $i++;
                //if($i == 35) break;
              }
            }

          ?>
        </div>
			</div>
		</div>
	</body>
</html>
