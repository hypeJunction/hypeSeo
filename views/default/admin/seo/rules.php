<?php

echo elgg_view('admin/seo/autogen');

$limit = get_input('limit', 25);
$offset = get_input('offset', 0);

$svc = \hypeJunction\Seo\RewriteService::getInstance();
$rules = new \ElggBatch([$svc, 'getRewriteRules'], [
	'limit' => $limit,
	'offset' => $offset,
	'uri' => get_input('uri'),
]);
echo elgg_view_form('seo/search', [
	'disable_security' => true,
	'method' => 'GET',
	'action' => current_page_url(),
]);
?>
<table class="elgg-table">
	<thead>
		<tr>
			<th></th>
			<th><?= elgg_echo('seo:paths') ?></th>
			<th><?= elgg_echo('seo:sef_path') ?></th>
			<th><?= elgg_echo('seo:metatags') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($rules as $data) {
			$data = $svc->normalizeData($data);
			$attrs['title'] = $data['title'];
			$attrs['description'] = $data['description'];
			$attrs['keywords'] = $data['keywords'];
			$data['metatags'] = array_merge(array_filter($attrs), $data['metatags']);
			?>
			<tr>
				<td>
					<?php
					echo elgg_view('output/url', [
						'text' => elgg_view_icon('pencil'),
						'href' => elgg_http_add_url_query_elements('seo/edit', array(
							'page_uri' => $data['path'],
						)),
						'class' => 'elgg-lightbox',
						'data-colorbox-opts' => json_encode([
							'maxWidth' => '600px',
						]),
					]);
					echo elgg_view('output/url', [
						'text' => elgg_view_icon('delete'),
						'href' => elgg_http_add_url_query_elements('action/seo/delete', [
							'uri' => $data['sef_path'],
						]),
					]);
					?>
				</td>
				<td>
					<?php
					$aliases = [];
					foreach ($data['aliases'] as $alias) {
						$alias = elgg_view('output/url', [
							'text' => $alias,
							'href' => $alias,
							'no_rewrite' => true,
							'target' => '_blank',
							'class' => 'seo-path',
						]);
						$aliases[] = elgg_format_element('li', [], $alias);
					}
					echo elgg_format_element('ul', [
						'class' => 'seo-list',
					], implode('', $aliases));
					?>
				</td>
				<td>
					<?php
					echo elgg_view('output/url', [
						'text' => $data['sef_path'],
						'href' => $data['sef_path'],
						'no_rewrite' => true,
						'target' => '_blank',
						'class' => 'seo-path',
					]);
					?>
				</td>
				<td>
					<?php
					$meta = [];
					foreach ($data['metatags'] as $key => $value) {
						$meta[] = "<b>$key</b>: $value";
					}
					echo elgg_format_element('div', [
						'class' => 'seo-metatags',
					], implode('<br />', $meta));
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
<?php
echo elgg_view('navigation/pagination', [
	'limit' => $limit,
	'offset' => $offset,
	'count' => $svc->countRewriteRules(),
]);