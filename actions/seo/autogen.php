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

return elgg_ok_response(
	['numSuccess' => $s, 'numErrors' => $e],
	elgg_echo('seo:autogen:count', [$s, $i])
);
