<?php

namespace hypeJunction\Seo;

use ElggEntity;
use ElggUser;
use stdClass;

/**
 * @access private
 */
class RewriteService {

	/**
	 * @var self|null
	 */
	public static $_instance;

	/**
	 * @var string
	 */
	private $table;

	/**
	 * @var string
	 */
	private $aliases_table;

	/**
	 * @var string
	 */
	private $data_table;

	/**
	 * @var Cache
	 */
	private $routes_cache;

	/**
	 * Constructor
	 *
	 * @param Cache $routes_cache Cache
	 */
	public function __construct(Cache $routes_cache) {
		$dbprefix = elgg_get_config('dbprefix');
		$this->table = "{$dbprefix}sef_routes";
		$this->aliases_table = "{$dbprefix}sef_aliases";
		$this->data_table = "{$dbprefix}sef_data";
		$this->routes_cache = $routes_cache;
	}

	/**
	 * Strip leading colons from DBAL param keys (Elgg 4.x used ':param', DBAL 3.x needs 'param').
	 *
	 * @param array $params Bound parameters keyed by DBAL placeholder
	 * @return array
	 */
	private function p(array $params): array {
		$out = [];
		foreach ($params as $k => $v) {
			$out[ltrim((string) $k, ':')] = $v;
		}

		return $out;
	}

	/**
	 * Returns a singleton
	 * @return self
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self(new FileCache());
		}

		return self::$_instance;
	}

	/**
	 * Get SEF equivalent for a given URL
	 *
	 * @param string $url URL
	 * @return string|false
	 */
	public function getTargetUrl($url = '') {

		$data = $this->getRewriteRulesFromUri($url);
		
		$target = false;
		if (!empty($data['sef_path'])) {
			$target = $data['sef_path'];
		}

		return $target ? elgg_normalize_url($target) : false;
	}

	/**
	 * Extracts request path relative to site installation
	 *
	 * @param string $url URL
	 * @return string|false
	 */
	public function normalizeUri($url = '') {
		// elgg_normalize_url() requires string in 7.x (was lenient in 3.x).
		if (!is_string($url) || $url === '') {
			return false;
		}

		$url = elgg_normalize_url($url);
		$site_url = elgg_get_site_url();
		if (strpos($url, $site_url) !== 0) {
			return false;
		}

		$path = '/' . substr($url, strlen($site_url));

		// strip query elements
		return parse_url($path, PHP_URL_PATH);
	}

	/**
	 * Get SEF data for a given URL
	 * Searches through aliases and returns SEF table row
	 *
	 * @param string $url URL
	 * @return array|false
	 */
	public function getRewriteRulesFromUri($url) {

		$path = $this->normalizeUri($url);
		if (!$path) {
			return false;
		}

		$hash = sha1($path);
		$data = $this->routes_cache->get($hash);
		if ($data) {
			return $data;
		}

		$query = "
			SELECT rt.*,
				   ri.*,
				   GROUP_CONCAT(at.path) as aliases
			FROM {$this->table} AS rt
			JOIN {$this->data_table} AS ri ON ri.route_id = rt.id
			JOIN {$this->aliases_table} at ON at.route_id = rt.id 
			WHERE rt.path = :path OR rt.sef_path = :path OR at.path = :path
			GROUP BY at.route_id
			LIMIT 1
		";

		$callback = [$this, 'rowToSefData'];
		$rows = elgg()->db->getConnection('read')->executeQuery($query, $this->p([':path' => $path]))->fetchAllAssociative();
		$data = array_map(fn($r) => $callback((object) $r), $rows);

		if (!$data) {
			return false;
		}

		foreach ($data[0]['aliases'] as $alias) {
			$hash = sha1($alias);
			$this->routes_cache->put($hash, $data[0]);
		}

		return $data[0];
	}

	/**
	 * Get SEF data for an entity
	 *
	 * @param string $guid URL
	 * @return array|false
	 */
	public function getRewriteRulesFromGUID($guid) {

		$guid = (int) $guid;

		$query = "
			SELECT rt.*,
				   ri.*,
				   GROUP_CONCAT(at.path) as aliases
			FROM {$this->table} AS rt
			JOIN {$this->data_table} AS ri ON ri.route_id = rt.id
			JOIN {$this->aliases_table} at ON at.route_id = rt.id
			WHERE rt.entity_guid = :guid
			GROUP BY at.route_id
			LIMIT 1
		";

		$callback = [$this, 'rowToSefData'];
		$rows = elgg()->db->getConnection('read')->executeQuery($query, $this->p([':guid' => $guid]))->fetchAllAssociative();
		$data = array_map(fn($r) => $callback((object) $r), $rows);

		if (!$data) {
			return false;
		}

		return $data[0];
	}

	/**
	 * Counts rewrite rules
	 *
	 * @param array $options Query filters (e.g. owner_guid)
	 * @return array|false
	 */
	public function countRewriteRules(array $options = []) {

		$query = "
			SELECT COUNT(rt.id) as total
			FROM {$this->table} AS rt
		";

		$row = elgg()->db->getConnection('read')->executeQuery($query)->fetchAssociative();

		if (!$row) {
			return 0;
		}

		return (int) $row['total'];
	}

	/**
	 * Returns rewrite rules
	 *
	 * @param array $options Options
	 * @return array|false
	 */
	public function getRewriteRules(array $options = []) {

		$limit = (int) elgg_extract('limit', $options, 25);
		$offset = (int) elgg_extract('offset', $options, 0);

		$where = '(1 = 1)';
		$uri = elgg_extract('uri', $options);
		if ($uri) {
			$where = '(rt.path LIKE :path OR rt.sef_path LIKE :path OR at.path LIKE :path)';
		}

		$query = "
			SELECT rt.*,
				   ri.*,
				   GROUP_CONCAT(at.path) as aliases
			FROM {$this->table} AS rt
			JOIN {$this->data_table} AS ri ON ri.route_id = rt.id
			JOIN {$this->aliases_table} at ON at.route_id = rt.id
			WHERE $where
			GROUP BY at.route_id
			ORDER BY rt.path
			LIMIT $offset,$limit
		";

		$callback = [$this, 'rowToSefData'];
		$rows = elgg()->db->getConnection('read')->executeQuery($query, $this->p([':path' => "%{$uri}%"]))->fetchAllAssociative();
		$data = array_map(fn($r) => $callback((object) $r), $rows);

		if (!$data) {
			return [];
		}

		return $data;
	}

	/**
	 * Normalize stored SEF URL data
	 *
	 * @param stdClass $row Database row
	 * @return array
	 */
	public function rowToSefData(stdClass $row) {
		$data = [
			'id' => (int) $row->id,
			'path' => (string) $row->path,
			'sef_path' => (string) $row->sef_path,
			'title' => (string) $row->title,
			'description' => (string) $row->description,
			'keywords' => (string) $row->keywords,
			'aliases' => explode(',', (string) $row->aliases),
			'metatags' => [],
			'guid' => (int) $row->entity_guid,
		];
		if ($row->metatags) {
			$data['metatags'] = self::decodeMetatags($row->metatags);
		}


		return $data;
	}

	/**
	 * Decode stored metatags, accepting both the JSON format written by
	 * this class on 3.x and the legacy PHP-serialized format written by
	 * pre-migration 2.x installations. The JSON branch is tried first;
	 * unserialize() is scoped to scalars only via allowed_classes=false
	 * to prevent object injection on legacy rows.
	 *
	 * @param string $raw Raw column value
	 * @return array
	 */
	private static function decodeMetatags($raw) {
		if (!is_string($raw) || $raw === '') {
			return [];
		}

		$decoded = json_decode($raw, true);
		if (is_array($decoded)) {
			return $decoded;
		}

		$decoded = @unserialize($raw, ['allowed_classes' => false]);
		return is_array($decoded) ? $decoded : [];
	}

	/**
	 * Save SEF data
	 *
	 * @param array $data Data
	 * @return int|false
	 */
	public function saveData($data) {

		if (empty($data['path']) || empty($data['sef_path'])) {
			return false;
		}

		$defaults = [
			'title' => null,
			'description' => null,
			'keywords' => null,
			'metatags' => null,
			'custom' => 'no',
			'aliases' => [],
			'guid' => 0,
		];

		$data = array_merge($defaults, $data);

		$data['path'] = $this->normalizeUri($data['path']);
		$data['sef_path'] = $this->normalizeUri($data['sef_path']);

		$data['aliases'][] = $data['path'];
		$data['aliases'][] = $data['sef_path'];
		$data['aliases'] = array_unique($data['aliases']);
		
		if ($data['metatags']) {
			$data['metatags'] = json_encode($data['metatags']);
		}

		foreach ($data['aliases'] as $alias) {
			$this->routes_cache->invalidate(sha1($alias));
		}
		
		$params = [
			':path' => (string) $data['path'],
			':sef_path' => (string) $data['sef_path'],
			':custom' => $data['custom'] == 'yes' ? 'yes' : 'no',
			':entity_guid' => (int) $data['guid'],
		];

		$id = false;
		$wconn = elgg()->db->getConnection('write');
		if (empty($data['id'])) {
			$query = "
				INSERT INTO {$this->table}
				SET path = :path,
					sef_path = :sef_path,
					entity_guid = :entity_guid,
					custom = :custom
				ON DUPLICATE KEY UPDATE
					path = :path,
					entity_guid = :entity_guid,
					custom = :custom
			";
			$wconn->executeStatement($query, $this->p($params));
			$id = (int) $wconn->lastInsertId();
		} else {
			$params[':id'] = $data['id'];
			$query = "
				UPDATE {$this->table}
				SET path = :path,
					sef_path = :sef_path,
					entity_guid = :entity_guid,
					custom = :custom
				WHERE id = :id
			";
			if ($wconn->executeStatement($query, $this->p($params))) {
				$id = $data['id'];
			}
		}

		if (!$id) {
			return false;
		}

		$query = "
			INSERT INTO {$this->data_table}
			SET route_id = :route_id,
				title = :title,
				description = :description,
				keywords = :keywords,
				metatags = :metatags
			ON DUPLICATE KEY UPDATE
				title = :title,
				description = :description,
				keywords = :keywords,
				metatags = :metatags
		";

		$params = [
			':route_id' => (int) $id,
			':title' => $data['title'],
			':description' => $data['description'],
			':keywords' => $data['keywords'],
			':metatags' => $data['metatags'],
		];

		$wconn->executeStatement($query, $this->p($params));

		$aliases = array_filter(array_unique($data['aliases']));
		if (!empty($aliases)) {
			foreach ($aliases as $alias) {
				if (empty($alias)) {
					continue;
				}

				$query = "
					INSERT INTO {$this->aliases_table}
					SET route_id = :route_id,
						path = :path
					ON DUPLICATE KEY UPDATE
						route_id = :route_id
				";

				$params = [
					':route_id' => (int) $id,
					':path' => $alias,
				];

				$wconn->executeStatement($query, $this->p($params));

				$hash = sha1($alias);
				$this->routes_cache->put($hash, $data);
			}
		}

		return $id;
	}

	/**
	 * Delete data
	 *
	 * @param int $id Rule id
	 * @return bool
	 */
	public function deleteData($id = 0) {

		$params = [':id' => (int) $id];
		$rconn = elgg()->db->getConnection('read');
		$wconn = elgg()->db->getConnection('write');

		$aliases = $rconn->executeQuery(
			"SELECT path FROM {$this->aliases_table} WHERE route_id = :id",
			$this->p($params)
		)->fetchAllAssociative();

		if ($aliases) {
			foreach ($aliases as $alias) {
				$this->routes_cache->invalidate(sha1($alias['path']));
			}
		}

		$wconn->executeStatement(
			"DELETE FROM {$this->aliases_table} WHERE route_id = :id",
			$this->p($params)
		);

		$wconn->executeStatement(
			"DELETE FROM {$this->data_table} WHERE route_id = :id",
			$this->p($params)
		);

		return (bool) $wconn->executeStatement(
			"DELETE FROM {$this->table} WHERE id = :id",
			$this->p($params)
		);
	}

	/**
	 * Delete data by entity guid
	 *
	 * @param int $guid Entity guid
	 * @return bool
	 */
	public function deleteDataFromGUID($guid = 0) {

		$params = [':entity_guid' => (int) $guid];
		$rows = elgg()->db->getConnection('read')->executeQuery(
			"SELECT id FROM {$this->table} WHERE entity_guid = :entity_guid",
			$this->p($params)
		)->fetchAllAssociative();

		if ($rows) {
			foreach ($rows as $row) {
				$this->deleteData((int) $row['id']);
			}
		}
	}

	/**
	 * Prepare entity SEF data
	 *
	 * @param ElggEntity $entity Entity
	 * @return array|false
	 */
	public function prepareEntityData(ElggEntity $entity) {

		if (!$entity->guid) {
			return;
		}

		$type = $entity->getType();
		$subtype = $entity->getSubtype();

		$pattern = $this->getTargetUrlPattern($type, $subtype);
		if (!$pattern) {
			return;
		}

		$path = $this->normalizeUri($entity->getURL());
		if (!$path) {
			return;
		}

		$data = $this->getRewriteRulesFromGUID($entity->guid);
		if (!$data) {
			$data = [
				'custom' => 'no',
				'path' => $path,
				'guid' => $entity->guid,
				'aliases' => [],
			];
		}

		if ($data['custom'] != 'yes') {
			$title = $entity->getDisplayName() ?: $entity->description;
			$replacements = [
				'{guid}' => $entity->guid,
				'{title}' => elgg_get_friendly_title(elgg_get_excerpt($title, 50)),
				'{username}' => $entity instanceof ElggUser ? $entity->username : '',
				'{timestamp}' => $entity->time_crated,
				'{date}' => gmdate('Y-m-d', $entity->time_created),
			];
			$sef_path = str_replace(array_keys($replacements), array_values($replacements), $pattern);

			// Make sure SEF URL is unique and suffix if needed
			$i = 1;
			$suffix = '';
			$unique = false;
			while (!$unique) {
				$sef_alt_data = $this->getRewriteRulesFromUri($sef_path . $suffix);
				if ($sef_alt_data && !in_array($data['path'], $sef_alt_data['aliases'])) {
					$suffix = "-$i";
					$i++;
					continue;
				}

				$unique = true;
			}

			$sef_path .= $suffix;
			$data['sef_path'] = $sef_path;
		}

		return $data;
	}

	/**
	 * Normalize SEF data array — backfills title/description/keywords/metatags from the entity.
	 *
	 * @param array $data Raw SEF row keyed by column name
	 * @return array
	 */
	public function normalizeData(array $data = []) {
		$guid = elgg_extract('guid', $data);
		$entity = get_entity($guid);

		if ($entity) {
			if (!$data['title']) {
				$data['title'] = $entity->getDisplayName();
			}

			if (!$data['description']) {
				$data['description'] = elgg_get_excerpt($entity->description);
			}

			if (!$data['keywords']) {
				$data['keywords'] = implode(',', (array) $entity->tags);
			}

			$data['metatags'] = elgg_trigger_event_results('metatags', 'discovery', [
				'entity' => $entity,
				'url' => elgg_normalize_url($data['path']),
			], (array) $data['metatags']);
			$data['metatags'] = array_filter($data['metatags']);
			ksort($data['metatags']);
		}

		return $data;
	}

	/**
	 * Populate SEF data when an entity is created, updated or deleted.
	 *
	 * @param \Elgg\Event $event The event — name is one of create|update|delete
	 * @return void
	 */
	public static function updateEntityRewriteRules(\Elgg\Event $event) {
		$entity = $event->getObject();
		if (!$entity instanceof ElggEntity) {
			return;
		}

		$svc = self::getInstance();

		switch ($event->getName()) {
			case 'update':
			case 'create':
				$data = $svc->prepareEntityData($entity);
				if ($data) {
					$svc->saveData($data);
				}
				break;

			case 'delete':
				$svc->deleteDataFromGUID($entity->guid);
				break;
		}
	}

	/**
	 * Returns SEF rewrite pattern for entity URLs
	 *
	 * @param string $type    Entity type
	 * @param string $subtype Entity subtype
	 * @return string
	 */
	public function getTargetUrlPattern($type, $subtype = '') {
		$setting = elgg_get_plugin_setting("$type:$subtype", 'hypeseo');
		if (!is_null($setting)) {
			return $setting;
		}

		switch ($type) {
			case 'user':
				return '/@{username}';

			case 'object':
				if (in_array($subtype, ['comment', 'discussion_reply'])) {
					return;
				}

				// fall through — share the slug-derivation block with 'group'.
			case 'group':
				$slug = $subtype;
				$keys = [
					"seo:item:$type:$subtype",
					"item:$type:$subtype",
					"seo:$type",
					"item:$type",
				];

				foreach ($keys as $key) {
					if (elgg_language_key_exists($key, 'en')) {
						$slug = elgg_echo($key, [], 'en');
						break;
					}
				}

				$slug = elgg_get_friendly_title(strtolower($slug));
				return "/$slug/{guid}-{title}";
		}
	}

	/**
	 * Substitute URLs with their SEF equivalent.
	 *
	 * @param \Elgg\Event $hook Event with the rendered output/url view vars as value
	 * @return array|null
	 */
	public static function rewriteInlineUrls(\Elgg\Event $hook) {
		$return = $hook->getValue();


		if (!empty($return['no_rewrite'])) {
			return;
		}

		if (!elgg_get_plugin_setting('inline_rewrites', 'hypeseo', true)) {
			return;
		}
		
		$svc = self::getInstance();

		$href = elgg_extract('href', $return);

		// Not using DB here, as it is way too heavy on performance
		$sef = $svc->getTargetUrl($href, false);
		
		if ($sef) {
			$return['href'] = $sef;
			$return['is_trusted'] = true;
		}

		return $return;
	}
}
