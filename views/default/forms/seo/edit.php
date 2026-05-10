<?php
$uri = elgg_extract('page_uri', $vars);
if (!$uri) {
	return;
}

$svc = \hypeJunction\Seo\RewriteService::getInstance();
$data = $svc->getRewriteRulesFromUri($uri);
if (!$data) {
	$data = array();
}

echo elgg_format_element('p', [
	'class' => 'elgg-text-help',
], elgg_autop(elgg_echo('seo:settings:edit:help')));

echo elgg_view_input('hidden', [
	'name' => 'seo[admin_defined]',
	'value' => true,
]);

$path = elgg_extract('path', $data, $svc->normalizeUri($uri));
echo elgg_view_input('hidden', [
	'name' => 'seo[path]',
	'value' => $path,
]);
echo elgg_view_input('text', [
	'value' => $path,
	'disabled' => true,
	'label' => elgg_echo('seo:path'),
]);

echo elgg_view_input('text', [
	'name' => 'seo[sef_path]',
	'value' => elgg_extract('sef_path', $data, $path),
	'required' => true,
	'label' => elgg_echo('seo:sef_path'),
	'help' => elgg_echo('seo:sef_path:help'),
]);

echo elgg_view_input('text', [
	'name' => 'seo[title]',
	'value' => elgg_extract('title', $data),
	'label' => elgg_echo('seo:title'),
]);

echo elgg_view_input('text', [
	'name' => 'seo[description]',
	'value' => elgg_extract('description', $data),
	'label' => elgg_echo('seo:description'),
]);

echo elgg_view_input('tags', [
	'name' => 'seo[keywords]',
	'value' => elgg_extract('keywords', $data),
	'label' => elgg_echo('seo:keywords'),
]);

$tags = array(
	'og:type',
	'og:title',
	'og:site_name',
	'og:image',
	'og:url',
	'og:description',
	'og:image',
	'og:image:width',
	'og:image:height',
	'article:published_time',
	'profile:username',
	'article:author',
	'article:tags',
	'twitter:creator',
	'fb:app_id',
	'twitter:card',
	'twitter:site',
	'twitter:creator',
);

$metatags = (array) elgg_extract('metatags', $data, array());
foreach ($tags as $tag) {
	if (!array_key_exists($tag, $metatags)) {
		$metatags[$tag] = '';
	}
}

foreach ($metatags as $tag => $value) {
	echo elgg_view_input('text', [
		'name' => "seo[metatags][$tag]",
		'value' => $value,
		'label' => $tag,
	]);
}

echo elgg_view_input('submit', [
	'wrapper_class' => 'elgg-foot',
	'value' => elgg_echo('save'),
]);
?>
<script>
	require(['forms/seo/edit']);
</script>