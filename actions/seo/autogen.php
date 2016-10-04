<?php

set_time_limit(0);

$svc = \hypeJunction\Seo\RewriteService::getInstance();

$entities = new ElggBatch('elgg_get_entities', [
	'limit' => 100,
	'order_by' => 'e.guid ASC',
	'offset' => (int) get_input('offset', 0),
		]);

$i = $s = $e = 0;
foreach ($entities as $entity) {
	$i++;
	$data = $svc->prepareEntityData($entity);
	if (!$data) {
		$s++;
		continue;
	}
	if ($svc->saveData($data)) {
		$s++;
	} else {
		$e++;
	}
}

if (elgg_is_xhr()) {
	echo json_encode([
		'numSuccess' => $s,
		'numErrors' => $e,
	]);
}

system_message(elgg_echo('seo:autogen:count', [$s, $i]));
forward(REFERRER);
