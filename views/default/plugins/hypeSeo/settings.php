<?php

$entity = elgg_extract('entity', $vars);

if (!isset($entity->inline_rewrites)) {
	$entity->inline_rewrites = true;
}

echo elgg_view_input('select', [
	'name' => 'params[inline_rewrites]',
	'value' => $entity->inline_rewrites,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('seo:settings:inline_rewrites'),
	'help' => elgg_echo('seo:settings:inline_rewrites:help'),
]);

$svc = \hypeJunction\Seo\RewriteService::getInstance();

$dbprefix = elgg_get_config('dbprefix');
$sql = "
	SELECT *
	FROM {$dbprefix}entity_subtypes
	ORDER BY subtype
";

$rows = get_data($sql);

$options = [
	'user:' => elgg_echo('item:user'),
	'group:' => elgg_echo('item:group'),
];

foreach ($rows as $row) {
	$type = $row->type;
	$subtype = $row->subtype;
	$options["$type:$subtype"] = elgg_echo("item:$type:$subtype");
}

asort($options);

echo elgg_format_element('p', [
	'class' => 'elgg-text-help',
], elgg_autop(elgg_echo('seo:settings:patterns:help')));

foreach ($options as $key => $label) {
	list($type, $subtype) = explode(':', $key);
	echo elgg_view_input('text', [
		'name' => "params[$key]",
		'value' => $svc->getTargetUrlPattern($type, $subtype),
		'label' => $label,
	]);
}