<?php

$uri = get_input('uri');

$svc = \hypeJunction\Seo\RewriteService::getInstance();
if ($svc->deleteData($uri)) {
	system_message(elgg_echo('seo:delete:success'));
} else {
	register_error(elgg_echo('seo:delete:error'));
}