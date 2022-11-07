<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="5">
    <title><?= $_SERVER['REQUEST_URI'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  </head>
  <!--

  This script scrappe the population data (2007-2012-2017) from the INSEE website
  https://www.insee.fr/fr/statistiques/2011101?geo=COM-35238

  This script only takes population data from 2007 to 2017,
  calculates the 10 years evolution, and takes the sare of male and female.


  -->
  <body>
		<div class="container" style="background-color: #e9ecef;">
			<div class="row">
				<h1>Scrappe the population data</h1>
			</div>
			<div class="row">
				<div class="col-4">
					<a class="btn btn-outline-primary" href="./" role="button">Back</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-secondary" href="http://<?php echo $_SERVER['SERVER_NAME']. ''.$_SERVER['REQUEST_URI'] ?>" role="button">Refresh</a>
				</div>
				<div class="col-4">
					<a class="btn btn-outline-success" href="./cities_2.php" role="button">NEXT</a>
				</div>
			</div>
			<div class="row mt-3">
        <div class="col">
          <?php
            include 'bdd-connexion.php';
            include_once "lib/simplehtmldom_1_9_1/simple_html_dom.php";

            // Context for 403 security on the website
            $context = stream_context_create(
                array(
                    "http" => array(
                        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                    )
                )
            );

            // GET THE CITIES INSEE CODE
            $response = $bdd->query('
              SELECT insee
              FROM cities_infos
              WHERE pop2007 IS NULL
              GROUP BY insee
              ORDER BY RAND()
              LIMIT 10
            ');

            // LOOP AND SCRAPPE THE DATA
            while ($city = $response->fetch()) {
              $url = "https://www.insee.fr/fr/statistiques/2011101?geo=COM-".$city["insee"];
              $content = file_get_contents($url, false, $context);
              $html = str_get_html($content);


              $pop2007 = $html->find('table[id=produit-tableau-POP_T0]', 0)->find('tr', 1)->find('td', 0)->plaintext;
              $pop2007 = str_replace("&nbsp;", '', $pop2007);
              $pop2012 = $html->find('table[id=produit-tableau-POP_T0]', 0)->find('tr', 1)->find('td', 2)->plaintext;
              $pop2012 = str_replace("&nbsp;", '', $pop2012);
              $pop2017 = $html->find('table[id=produit-tableau-POP_T0]', 0)->find('tr', 1)->find('td', 4)->plaintext;
              //pop1999?
              //pop1990?
              //pop1982?
              $pop2017 = str_replace("&nbsp;", '', $pop2017);
              $evol10 = round((($pop2017 - $pop2007) / $pop2007) * 100, 2);
              $popMale = $html->find('table[id=produit-tableau-POP_T3]', 0)->find('tr', 1)->find('td', 0)->plaintext;
              $popMale = str_replace("&nbsp;", '', $popMale);
              $popFemale = $html->find('table[id=produit-tableau-POP_T3]', 0)->find('tr', 1)->find('td', 2)->plaintext;
              $popFemale = str_replace("&nbsp;", '', $popFemale);


              echo "city => ".$city['insee']."<br>";
              echo "url => <a href='".$url."' target='_blank'>".$url."</a><br>";
              echo "pop2007 => ".$pop2007." <br>";
              echo "pop2012 => ".$pop2012." <br>";
              echo "pop2017 => ".$pop2017." <br>";
              echo "evol10 => ".$evol10." <br>";
              echo "popMale => ".$popMale." <br>";
              echo "popFemale => ".$popFemale." <br>";



              echo "<br><br>";

              // INSERT INTO SQL //
              $sql = $bdd->prepare('UPDATE cities_infos SET pop2007 = :pop2007, pop2012 = :pop2012, pop2017 = :pop2017, evol10 = :evol10, popMale = :popMale, popFemale = :popFemale WHERE insee = "'.$city["insee"].'"');
              $sql -> execute(array('pop2007' => $pop2007, 'pop2012' => $pop2012, 'pop2017' => $pop2017, 'evol10' => $evol10, 'popMale' => $popMale, 'popFemale' => $popFemale));

              // Clear the html dom variable
              $html->clear();
            }


          ?>
        </div>
			</div>
		</div>
	</body>
</html>
