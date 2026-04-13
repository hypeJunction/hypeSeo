<?php
echo elgg_view_layout('admin', [
	'title' => elgg_echo('admin:seo:sitemap'),
	'content' => elgg_view('admin/seo/sitemap', $vars),
	'filter' => false,
]);
