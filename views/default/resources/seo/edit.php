<?php

admin_gatekeeper();

$uri = get_input('page_uri');
if (!$uri) {
	return;
}

$content = elgg_view_form('seo/edit', [], [
	'page_uri' => $uri,
]);

if (elgg_is_xhr()) {
	echo $content;
	return;
}

$title = elgg_echo('seo:edit');

$layout = elgg_view_layout('one_sidebar', [
	'title' => $title,
	'content' => $content,
]);

echo elgg_view_page($title, $layout);