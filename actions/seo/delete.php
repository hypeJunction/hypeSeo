<?php

use hypeJunction\Seo\RewriteService;

$uri = get_input('uri');

$svc = RewriteService::getInstance();
$data = $svc->getRewriteRulesFromUri($uri);
if ($data && $svc->deleteData($data['id'])) {
	system_message(elgg_echo('seo:delete:success'));
} else {
	register_error(elgg_echo('seo:delete:error'));
}