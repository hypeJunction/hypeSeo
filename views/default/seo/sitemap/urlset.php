<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php
	$urls = elgg_extract('urls', $vars, []);
	foreach ($urls as $url) {
		?>
		<url>
			<?php
			foreach ($url as $tag => $value) {
				if (empty($value)) {
					continue;
				}
				echo elgg_format_element($tag, [], htmlentities($value, ENT_QUOTES, 'UTF-8')) . PHP_EOL;
			}
			?>
		</url>
		<?php
	}
	?>
</urlset>