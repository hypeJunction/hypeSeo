<?php

$count = elgg_get_entities(['count' => true]);

elgg_require_js('elgg/upgrades');

$warning_string = elgg_echo('seo:autogen:intro');
$status_string = elgg_echo('seo:autogen:item_count', array($count));

$success_count_string = elgg_echo('seo:autogen:success_count');
$error_count_string = elgg_echo('seo:autogen:error_count');

$action_link = elgg_view('output/url', array(
	'text' => elgg_echo('seo:autogen'),
	'href' => 'action/seo/autogen',
	'class' => 'elgg-button elgg-button-action mtl',
	'is_action' => true,
	'id' => 'upgrade-run',
));
?>
<div class="elgg-content">
	<p class="elgg-text-help"><?= $warning_string ?> <?= $status_string ?></p>
	<span id="upgrade-total" class="hidden"><?= $count ?></span>
	<span id="upgrade-count" class="hidden">0</span>
	<span id="upgrade-action" class="hidden"><?= $action ?></span>
	<div class="elgg-progressbar mvl"><span class="elgg-progressbar-counter" id="upgrade-counter">0%</span></div>
	<ul class="mvl">
		<li><?= $success_count_string ?><span id="upgrade-success-count">0</span></li>
		<li><?= $error_count_string ?><span id="upgrade-error-count">0</span></li>
	</ul>
	<div id="upgrade-spinner" class="elgg-ajax-loader hidden"></div>
	<ul class="mvl" id="upgrade-messages"></ul>
	<?= $action_link ?>
</div>

