<?php

echo elgg_view_field([
	'#type' => 'text',
	'name' => 'uri',
	'value' => get_input('uri'),
	'#label' => elgg_echo('seo:search:path'),
]);

$submit = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('search'),
]);

elgg_set_form_footer($submit);