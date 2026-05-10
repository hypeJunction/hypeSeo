<?php

use hypeJunction\Seo\RewriteService;

$uri = get_input('uri');

$svc = RewriteService::getInstance();
$data = $svc->getRewriteRulesFromUri($uri);
if ($data && $svc->deleteData($data['id'])) {
	return elgg_ok_response('', elgg_echo('seo:delete:success'));
}

return elgg_error_response(elgg_echo('seo:delete:error'));
