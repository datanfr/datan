<?php
	header("Content-Type: application/xml; charset=utf-8");

	echo '<?xml version="1.0" encoding="UTF-8"?>';

	echo '<urlset nbUrl="'.$nbUrl.'" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

	foreach ($urls as $url) {
		echo '<url>';
		echo '<loc>'.$url['url'].'</loc>';
		echo '</url>';
	}

	echo '</urlset>';

?>
