<?php
echo elgg_view_layout('admin', [
	'title' => elgg_echo('admin:seo:rules'),
	'content' => elgg_view('admin/seo/rules', $vars),
	'filter' => false,
]);
