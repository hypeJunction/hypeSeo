<?php

$data = get_input('seo');

$svc = \hypeJunction\Seo\RewriteService::getInstance();

$path = $data['path'];
if (!$path) {
	register_error(elgg_echo('seo:edit:error'));
	forward(REFERRER);
}

$sef_data = $svc->getRewriteRulesFromUri($data['path']);
if (!$sef_data) {
	$sef_data = [];
}

// Validate SEF uniqueness
$sef_alt_data = $svc->getRewriteRulesFromUri($data['sef_path']);
if ($sef_alt_data && !in_array($data['path'], $sef_alt_data['aliases'])) {
	register_error(elgg_echo('seo:edit:not_unique'));
	forward(REFERRER);
}

$data['path'] = $svc->normalizeUri($data['path']);
$data['sef_path'] = $svc->normalizeUri($data['sef_path']);

$sef_data['aliases'][] = $data['path'];
$sef_data['aliases'][] = $data['sef_path'];

foreach (['path', 'sef_path', 'title', 'description', 'keywords'] as $key) {
	if (isset($data[$key])) {
		$sef_data[$key] = $data[$key];
	}
}

foreach ($data['metatags'] as $key => $value) {
	$sef_data['metatags'][$key] = $value;
}

if ($svc->saveData($sef_data)) {
	system_message(elgg_echo('seo:edit:success'));
} else {
	register_error(elgg_echo('seo:edit:error'));
}

forward(REFERRER);
