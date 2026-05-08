<?php

$entity = elgg_extract('entity', $vars);

if (!isset($entity->inline_rewrites)) {
	$entity->inline_rewrites = true;
}

echo elgg_view('input/select', [
	'name' => 'params[inline_rewrites]',
	'value' => $entity->inline_rewrites,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('seo:settings:inline_rewrites'),
	'help' => elgg_echo('seo:settings:inline_rewrites:help'),
]);

echo elgg_view('input/select', [
	'name' => 'params[redirect_to_canonical]',
	'value' => $entity->redirect_to_canonical,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('seo:settings:redirect_to_canonical'),
	'help' => elgg_echo('seo:settings:redirect_to_canonical:help'),
]);

echo elgg_view('input/select', [
	'name' => 'params[rel_follow]',
	'value' => $entity->rel_follow,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('seo:settings:rel_follow'),
	'help' => elgg_echo('seo:settings:rel_follow:help'),
]);

$svc = \hypeJunction\Seo\RewriteService::getInstance();

// Elgg 3.x dropped the entity_subtypes table; iterate the registered
// entities map instead.
$options = [
	'user:' => elgg_echo('item:user'),
	'group:' => elgg_echo('item:group'),
];

foreach ((array) [] as $type => $subtypes) {
	foreach ((array) $subtypes as $subtype) {
		$options["$type:$subtype"] = elgg_echo("item:$type:$subtype");
	}
}

asort($options);

echo elgg_format_element('p', [
	'class' => 'elgg-text-help',
], elgg_autop(elgg_echo('seo:settings:patterns:help')));

foreach ($options as $key => $label) {
	list($type, $subtype) = explode(':', $key);
	echo elgg_view('input/text', [
		'name' => "params[$key]",
		'value' => $svc->getTargetUrlPattern($type, $subtype),
		'label' => $label,
	]);
}
