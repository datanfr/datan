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
			SELECT d.mpId AS uid, d.legislature
			FROM deputes_last d
			WHERE legislature IN (14, 15)
		');

		// LIMIT 5



		$i = 1;




		while ($d = $donnees->fetch()) {
			$uid = substr($d['uid'], 2);
			$legislature = $d['legislature'];
			$url = 'http://www2.assemblee-nationale.fr/static/tribun/'.$legislature.'/photos/'.$uid.'.jpg';
			//$url = 'http://www2.assemblee-nationale.fr/static/tribun/15/photos/1008.jpg';

			$content = file_get_contents($url);
			$img = imagecreatefromstring($content);
			$width = imagesx($img);
			$height = imagesy($img);
			$newwidth = $width;
			$newheight = $height;
			$quality = 0;
			$thumb = imagecreatetruecolor($newwidth, $newheight);
			imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			imagepng($thumb, '../assets/imgs/deputes_original/depute_'.$uid.'.png', $quality);


			echo '<p>'.$uid.' = <img src="../assets/imgs/deputes_original/depute_'.$uid.'.png"></p>';
			echo '<br>';



			echo "<br>";
			$i++;
		}

	?>

</body>
</html>
