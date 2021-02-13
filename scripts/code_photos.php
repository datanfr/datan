<!DOCTYPE html>
<html>
<head>
	<title>code_photos</title>

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
			SELECT d.mpId AS uid
			FROM deputes d
			LEFT JOIN mandat_principal mp ON d.mpId = mp.mpId
			WHERE mp.legislature = 15
			GROUP BY d.mpId
		');

		// LIMIT 5

		$i = 1;


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
			/*
			$ch = curl_init($arr_result->dest);
			$dest = "C:/wamp64/www/datan/assets/imgs/deputes/resmushed_".$name;
			$fp = fopen($dest, "wb");
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			*/
			$url = $arr_result->dest;
			$img = "C:/wamp64/www/datan/assets/imgs/deputes/".$name;
			file_put_contents($img, file_get_contents($url));

			// END RESMUSH

			echo '<p>'.$uid.' = <img src="../assets/imgs/deputes/depute_'.$uid.'.png"></p>';
			echo '<br>';



			echo "<br>";
			$i++;
		}

	?>

</body>
</html>
