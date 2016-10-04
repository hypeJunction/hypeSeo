<?php

$entity = elgg_extract('entity', $params);
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