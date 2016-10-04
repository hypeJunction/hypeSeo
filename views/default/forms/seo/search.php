<?php

echo elgg_view_input('text', [
	'name' => 'uri',
	'value' => get_input('uri'),
	'label' => elgg_echo('seo:search:path'),
]);

echo elgg_view_input('submit', [
	'value' => elgg_echo('search'),
]);