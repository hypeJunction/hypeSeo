<?php
echo elgg_view_layout('admin', [
	'title' => elgg_echo('admin:seo:generator'),
	'content' => elgg_view('admin/seo/generator', $vars),
	'filter' => false,
]);
