<?php
echo elgg_view_layout('admin', [
	'title' => elgg_echo('admin:seo:add_rule'),
	'content' => elgg_view('admin/seo/add_rule', $vars),
	'filter' => false,
]);
