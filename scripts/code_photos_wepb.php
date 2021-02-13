<!DOCTYPE html>
<html>
<head>
	<title>code_photos_webp</title>

	<style type="text/css">

table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}

	</style>
</head>
<body>



	<?php

		include 'bdd-connexion.php';

		$donnees = $bdd->query('
			SELECT d.mpId
			FROM deputes d
			LEFT JOIN mandat_principal mp ON d.mpId = mp.mpId
			WHERE mp.legislature = 15
			GROUP BY d.mpId
		');

		// LIMIT 5



		$i = 1;

		$dir = "C:/wamp64/www/datan/assets/imgs/deputes_original/";
		$newdir = "C:/wamp64/www/datan/assets/imgs/deputes_webp/";
		$files = scandir($dir);
		unset($files[0]);
		unset($files[1]);
		echo "dir ==> ".$dir."<br>";
		echo "new dir ==> ".$newdir."<br>";
		echo "Number of photos ==> ".count($files)."<br>";

		echo "<br>";

		//$files = array_slice($files, 0, 3);

		foreach ($files as $file) {
			//Variables
			$newfile = str_replace(".png", "", $file);
			$newfile = $newfile."_webp.webp";

			//Create and Save
			$img = imagecreatefrompng($dir . $file);
			imagepalettetotruecolor($img);
			imagealphablending($img, true);
			imagesavealpha($img, true);
			imagewebp($img, $newdir . $newfile, 80);
			imagedestroy($img);

			//Display results
			echo "photo n° ".$i."<br>";
			echo "name: ".$file."<br>";
			echo "new name: ".$newfile."<br>";

			echo "<br>";
			$i++;
		}

		/*

		while ($d = $donnees->fetch()) {
			$uid = substr($d['uid'], 2);
			$url = 'http://www2.assemblee-nationale.fr/static/tribun/15/photos/'.$uid.'.jpg';

			$content = file_get_contents($url);
			$img = imagecreatefromstring($content);
			$width = imagesx($img);
			$height = imagesy($img);
			$newwidth = '130';
			$newheight = '166.4';
			$quality = 0;
			$thumb = imagecreatetruecolor($newwidth, $newheight);
			imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			imagepng($thumb, '../assets/imgs/deputes/depute_'.$uid.'.png', $quality);

			// Start RESMUSH CODE
			// 1.
			$file = "C:/wamp64/www/datan/assets/imgs/deputes/depute_".$uid.".png";
			$mime = mime_content_type($file);
			$info = pathinfo($file);
			$name = $info['basename'];
			$output = new CURLFile($file, $mime, $name);
			$data = array(
				"files" => $output,
			);
			// 2.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/?qlty=80');
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
				$result = curl_error($ch);
			}
			curl_close($ch);

			$arr_result = json_decode($result);

			print_r($arr_result);

			// store the optimized version of the image
			$ch = curl_init($arr_result->dest);
			$dest = "C:/wamp64/www/datan/assets/imgs/deputes/".$name;
			$fp = fopen($dest, "wb");
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			// END RESMUSH

			echo '<p>'.$uid.' = <img src="../assets/imgs/deputes/depute_'.$uid.'.png"></p>';
			echo '<br>';



			echo "<br>";
			$i++;
		}

		*/

	?>

</body>
</html>
