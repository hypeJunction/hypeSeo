<?php

$count = elgg_get_entities(['count' => true]);

elgg_import_esm('admin/upgrades');

$warning_string = elgg_echo('seo:autogen:intro');
$status_string = elgg_echo('seo:autogen:item_count', [$count]);

$success_count_string = elgg_echo('seo:autogen:success_count');
$error_count_string = elgg_echo('seo:autogen:error_count');

$action_link = elgg_view('output/url', [
	'text' => elgg_echo('seo:autogen'),
	'href' => 'action/seo/autogen',
	'class' => 'elgg-button elgg-button-action mtl',
	'is_action' => true,
	'id' => 'upgrade-run',
]);
?>
<div class="elgg-content">
	<p class="elgg-text-help"><?php echo $warning_string ?> <?php echo $status_string ?></p>
	<span id="upgrade-total" class="hidden"><?php echo $count ?></span>
	<span id="upgrade-count" class="hidden">0</span>
	<span id="upgrade-action" class="hidden"><?php echo $action ?></span>
	<div class="elgg-progressbar mvl"><span class="elgg-progressbar-counter" id="upgrade-counter">0%</span></div>
	<ul class="mvl">
		<li><?php echo $success_count_string ?><span id="upgrade-success-count">0</span></li>
		<li><?php echo $error_count_string ?><span id="upgrade-error-count">0</span></li>
	</ul>
	<div id="upgrade-spinner" class="elgg-ajax-loader hidden"></div>
	<ul class="mvl" id="upgrade-messages"></ul>
	<?php echo $action_link ?>
</div>

