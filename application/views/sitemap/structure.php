<?php header('Content-type: application/xml'); ?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" nbUrl="6">

	<url>
		<loc><?= base_url() ?>groupes</loc>
	</url>
	<url>
		<loc><?= base_url() ?>votes</loc>
	</url>
	<url>
		<loc><?= base_url() ?>votes/decryptes</loc>
	</url>
	<url>
		<loc><?= base_url() ?>mentions-legales</loc>
	</url>
	<url>
		<loc><?= base_url() ?>a-propos</loc>
	</url>
	<url>
		<loc><?= base_url() ?>blog</loc>
	</url>
</urlset>
