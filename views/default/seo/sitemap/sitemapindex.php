<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

	<?php
	$sitemaps = elgg_extract('sitemaps', $vars, []);
	foreach ($sitemaps as $sitemap) {
		$tags = '';
		foreach ($sitemap as $tag => $value) {
			if (empty($value)) {
				continue;
			}
			$tags = elgg_format_element($tag, [], htmlentities($value, ENT_QUOTES, 'UTF-8')) . PHP_EOL;
		}
		if (empty($tags)) {
			continue;
		}
		echo elgg_format_element('sitemap', [], $tags);
	}
	?>
</sitemapindex>