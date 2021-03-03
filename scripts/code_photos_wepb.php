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

		// LIMIT 5

		$i = 1;

		$dir = "../assets/imgs/deputes_original/";
		$newdir = "../assets/imgs/deputes_webp/";
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

	?>

</body>
</html>
