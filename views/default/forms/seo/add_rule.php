<?php

echo elgg_view_input('hidden', [
	'name' => 'seo[admin_defined]',
	'value' => true,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('seo:path'),
	'name' => 'seo[path]',
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('seo:sef_path'),
	'name' => 'seo[sef_path]',
]);

$submit = elgg_view_field([
	'#type' => 'submit',
]);

elgg_set_form_footer($submit);
