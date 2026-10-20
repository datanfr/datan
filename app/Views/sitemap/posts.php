<?php

	header("Content-Type: application/xml; charset=utf-8");

	echo '<?xml version="1.0" encoding="UTF-8"?>';

	echo '<urlset nbUrl="'.$nbUrl.'" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';


	foreach ($posts as $post) {
		echo '<url>';
		echo '<loc>'.$post['url'].'</loc>';
		echo '<lastmod>'.$post['lastmod'].'</lastmod>';
		echo '</url>';
	}

	echo '</urlset>';

?>
