<?php

namespace hypeJunction\Seo\Tests;

use Elgg\IntegrationTestCase;
use hypeJunction\Seo\Cache;
use hypeJunction\Seo\RewriteService;

class RewriteServiceCRUDTest extends IntegrationTestCase {

	private RewriteService $svc;

	/** @var int[] Route IDs created during a test, cleaned up in down() */
	private array $created_ids = [];

	public function up() {
		$this->svc = new RewriteService(new class implements Cache {
			public function get($key, callable $callback = null, $default = null) { return $default; }
			public function invalidate($key) {}
			public function put($key, $value) {}
		});
	}

	public function down() {
		foreach ($this->created_ids as $id) {
			$this->svc->deleteData($id);
		}
		$this->created_ids = [];
	}

	public function getPluginID(): string {
		return '';
	}

	private function save(array $overrides = []): int {
		$uid = uniqid('', true);
		$data = array_merge([
			'path' => '/blog/test-post-' . $uid,
			'sef_path' => '/posts/test-post-' . $uid,
			'title' => 'Test Post',
			'description' => 'A test SEF route',
			'keywords' => 'test,seo',
		], $overrides);

		$id = $this->svc->saveData($data);
		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
		$this->created_ids[] = $id;
		return $id;
	}

	public function testSaveDataCreatesRoute(): void {
		$uid = uniqid('', true);
		$path = '/blog/test-save-' . $uid;
		$sef = '/posts/test-save-' . $uid;
		$id = $this->save(['path' => $path, 'sef_path' => $sef]);

		$rules = $this->svc->getRewriteRulesFromUri($path);
		$this->assertIsArray($rules);
		$this->assertSame($path, $rules['path']);
		$this->assertSame($sef, $rules['sef_path']);
	}

	public function testSaveDataPersistsMetaFields(): void {
		$uid = uniqid('', true);
		$path = '/blog/meta-test-' . $uid;
		$sef = '/posts/meta-test-' . $uid;
		$id = $this->save([
			'path' => $path,
			'sef_path' => $sef,
			'title' => 'SEO Title',
			'description' => 'SEO Description',
			'keywords' => 'key1,key2',
		]);

		$rules = $this->svc->getRewriteRulesFromUri($path);
		$this->assertSame('SEO Title', $rules['title']);
		$this->assertSame('SEO Description', $rules['description']);
		$this->assertSame('key1,key2', $rules['keywords']);
	}

	public function testGetRewriteRulesFromUriFindsBySefPath(): void {
		$uid = uniqid('', true);
		$path = '/blog/sef-lookup-' . $uid;
		$sef = '/posts/sef-lookup-' . $uid;
		$this->save(['path' => $path, 'sef_path' => $sef]);

		$rules = $this->svc->getRewriteRulesFromUri($sef);
		$this->assertIsArray($rules);
		$this->assertSame($path, $rules['path']);
		$this->assertSame($sef, $rules['sef_path']);
	}

	public function testGetRewriteRulesFromUriReturnsFalseForUnknownPath(): void {
		$result = $this->svc->getRewriteRulesFromUri('/no-such-path-' . uniqid('', true));
		$this->assertFalse($result);
	}

	public function testCountRewriteRulesReflectsSavedRoutes(): void {
		$before = (int) $this->svc->countRewriteRules();
		$this->save();
		$this->save();
		$after = (int) $this->svc->countRewriteRules();
		$this->assertSame($before + 2, $after);
	}

	public function testDeleteDataRemovesRoute(): void {
		$uid = uniqid('', true);
		$path = '/blog/del-test-' . $uid;
		$sef = '/posts/del-test-' . $uid;
		$id = $this->save(['path' => $path, 'sef_path' => $sef]);

		$this->svc->deleteData($id);
		// Remove from cleanup list since already deleted
		$this->created_ids = array_diff($this->created_ids, [$id]);

		$this->assertFalse($this->svc->getRewriteRulesFromUri($path));
	}

	public function testDeleteDataFromGUIDRemovesRoutesForEntity(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		elgg_get_session()->setLoggedInUser($admin);

		$object = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $admin->guid,
		]);

		$uid = uniqid('', true);
		$path = '/blog/entity-del-' . $uid;
		$sef = '/posts/entity-del-' . $uid;
		$id = $this->save(['path' => $path, 'sef_path' => $sef, 'guid' => $object->guid]);

		$this->svc->deleteDataFromGUID($object->guid);
		$this->created_ids = array_diff($this->created_ids, [$id]);

		$this->assertFalse($this->svc->getRewriteRulesFromUri($path));

		elgg_get_session()->removeLoggedInUser();
	}

	public function testSaveDataMetatagsStoredAsJson(): void {
		$uid = uniqid('', true);
		$path = '/blog/meta-json-' . $uid;
		$sef = '/posts/meta-json-' . $uid;
		$metatags = ['og:title' => 'Hello', 'og:type' => 'article'];
		$id = $this->save(['path' => $path, 'sef_path' => $sef, 'metatags' => $metatags]);

		$prefix = elgg()->db->prefix;
		$row = elgg()->db->getDataRow("SELECT metatags FROM {$prefix}sef_data WHERE route_id = ?", null, [$id]);
		$this->assertNotNull($row);
		$this->assertSame($metatags, json_decode($row->metatags, true));
	}
}
