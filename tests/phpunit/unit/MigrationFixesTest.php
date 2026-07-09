<?php

namespace hypeJunction\Seo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Per-fix regression guards for the 6.x -> 7.x migration of hypeSeo.
 *
 * Each test pins the FIXED shape of exactly one migration commit so a later
 * refactor that reverts the fix fails here (RED) instead of surfacing as a
 * runtime fatal / silent 404 on Elgg 7. These are source-level assertions on
 * purpose — the failures they guard against fatal at class-load, boot, or
 * page-render on 7.x, so a booted test would crash before it could assert.
 */
class MigrationFixesTest extends TestCase {

	private static function root(): string {
		// tests/phpunit/unit -> tests/phpunit -> tests -> plugin root
		return \dirname(__DIR__, 3);
	}

	private static function read(string $relative): string {
		$path = self::root() . '/' . ltrim($relative, '/');
		self::assertFileExists($path, "expected plugin file missing: {$relative}");
		return (string) file_get_contents($path);
	}

	/** @return list<string> non-test PHP files under the given relative dirs */
	private static function phpFiles(array $dirs): array {
		$out = [];
		foreach ($dirs as $dir) {
			$base = self::root() . '/' . trim($dir, '/');
			if (!is_dir($base)) {
				continue;
			}
			$it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
			foreach ($it as $f) {
				$p = $f->getPathname();
				if (str_ends_with($p, '.php') && !str_contains($p, '/vendor/') && !str_contains($p, '/tests/')) {
					$out[] = $p;
				}
			}
		}
		return $out;
	}

	/**
	 * ec89d54 / FC-ALL-05 — the route:rewrite,all handler must be registered
	 * imperatively in Bootstrap::boot() at priority 1. The declarative
	 * elgg-plugin.php event fires at init, AFTER Application::allowPathRewrite()
	 * dispatches route:rewrite, so every SEF pretty URL 404'd on Elgg 7.
	 */
	public function testRouteRewriteRegisteredInBootAtPriorityOne(): void {
		$src = self::read('classes/hypeJunction/Seo/Bootstrap.php');

		// isolate the boot() body so the assertion can't be satisfied by init()
		$this->assertSame(1, preg_match('/function\s+boot\s*\([^)]*\)\s*\{(.*?)\n\t\}/s', $src, $m),
			'Bootstrap::boot() not found');
		$boot = $m[1];

		$this->assertMatchesRegularExpression(
			"/elgg_register_event_handler\(\s*'route:rewrite'\s*,\s*'all'\s*,[^;]*enforceRewriteRules[^;]*,\s*1\s*\)/",
			$boot,
			'route:rewrite,all must be registered in Bootstrap::boot() at priority 1 (elgg-plugin.php events run too late -> site-wide SEF 404 on Elgg 7)'
		);
	}

	/**
	 * a92c6cf — admin/seo/autogen.php must import the core 'admin/upgrades' ES
	 * module; the elgg/upgrades AMD module was removed in 7.x.
	 */
	public function testAutogenViewImportsAdminUpgradesEsm(): void {
		$src = self::read('views/default/admin/seo/autogen.php');

		$this->assertStringContainsString("elgg_import_esm('admin/upgrades')", $src,
			'autogen view must load the 7.x core admin/upgrades ES module');
		$this->assertStringNotContainsString('elgg/upgrades', $src,
			"the elgg/upgrades AMD module was removed in 7.x and must not be referenced");
		$this->assertDoesNotMatchRegularExpression("/require\(\s*\[/", $src,
			'inline AMD require([...]) is not resolvable in the 7.x importmap');
	}

	/**
	 * 6aea599 — forms/seo/edit.js renamed to edit.mjs and loaded via
	 * elgg_import_esm(); a bare .js is never registered in the 7.x importmap.
	 */
	public function testSeoEditFormUsesEsmModule(): void {
		$this->assertFileExists(self::root() . '/views/default/forms/seo/edit.mjs',
			'the edit form JS must be an ESM (.mjs) module on 7.x');
		$this->assertFileDoesNotExist(self::root() . '/views/default/forms/seo/edit.js',
			'the legacy AMD edit.js must be gone (never registered in the 7.x importmap)');

		$php = self::read('views/default/forms/seo/edit.php');
		$this->assertStringContainsString("elgg_import_esm('forms/seo/edit')", $php,
			'edit.php must load the ESM module via elgg_import_esm()');
		$this->assertDoesNotMatchRegularExpression("/require\(\s*\[\s*'forms\/seo\/edit'/", $php,
			'inline AMD require([forms/seo/edit]) must be gone');
	}

	/**
	 * fbb1597 — Upgrade\Batch became abstract in 6.x; MigratePluginId must
	 * EXTEND AsynchronousUpgrade (not implement Batch) and implement
	 * run(Result $result, $offset): Result.
	 */
	public function testMigratePluginIdIsAsynchronousUpgradeShape(): void {
		$src = self::read('classes/hypeJunction/Seo/Upgrades/MigratePluginId.php');

		$this->assertMatchesRegularExpression('/\bextends\s+AsynchronousUpgrade\b/', $src,
			'MigratePluginId must extend AsynchronousUpgrade (Batch is abstract since 6.x)');
		$this->assertDoesNotMatchRegularExpression('/\bimplements\b[^{]*\bBatch\b/', $src,
			'`implements Batch` fatals on 6.x/7.x');
		$this->assertMatchesRegularExpression(
			'/function\s+run\s*\(\s*Result\s+\$result\s*,\s*\$offset\s*\)\s*:\s*Result\b/',
			$src,
			'run() must have the 6.x AsynchronousUpgrade signature run(Result $result, $offset): Result'
		);
	}

	/**
	 * f021410 — the upgrade backfills settings from the orphaned camelCase
	 * 'hypeSeo' 3.x plugin entity to the lowercase 'hypeseo' 4.x entity (Elgg
	 * 4.x derives the id from the dir name, orphaning the old entity).
	 */
	public function testMigratePluginIdMapsCamelCaseToLowercase(): void {
		$src = self::read('classes/hypeJunction/Seo/Upgrades/MigratePluginId.php');

		$this->assertMatchesRegularExpression("/const\s+OLD_ID\s*=\s*'hypeSeo'/", $src,
			'source id must remain the camelCase 3.x entity title');
		$this->assertMatchesRegularExpression("/const\s+NEW_ID\s*=\s*'hypeseo'/", $src,
			'target id must be the lowercase 4.x plugin id');
		// settings must land on the NEW (lowercase) entity, resolved by id
		$this->assertMatchesRegularExpression('/elgg_get_plugin_from_id\(\s*self::NEW_ID\s*\)/', $src,
			'run() must resolve the destination plugin by the lowercase NEW_ID');
	}

	/**
	 * 925feda — get_user_by_username() was removed in 5.x. The bare form must
	 * not reappear anywhere in shipped code (elgg_get_user_by_username() is the
	 * replacement and is allowed).
	 */
	public function testRemovedUserLookupFunctionAbsent(): void {
		$violations = [];
		foreach (self::phpFiles(['classes', 'views', 'actions', 'lib']) as $file) {
			foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
				// bare get_user_by_username( — the '_' before elgg_ prefix is \w, so
				// the negative lookbehind excludes elgg_get_user_by_username(
				if (preg_match('/(?<![\w])get_user_by_username\s*\(/', $line)) {
					$violations[] = basename($file) . ':' . ($n + 1);
				}
			}
		}
		$this->assertSame([], $violations,
			"get_user_by_username() removed in 5.x -> use elgg_get_user_by_username():\n" . implode("\n", $violations));
	}

	/**
	 * b13d08d — the hooks/events merge rewrote every \Elgg\Hook handler
	 * signature to \Elgg\Event; \Elgg\Hook was removed entirely in 6.x.
	 */
	public function testHandlerClassesUseElggEventNotHook(): void {
		$violations = [];
		foreach (self::phpFiles(['classes']) as $file) {
			if (preg_match('/\bElgg\\\\Hook\b/', (string) file_get_contents($file))) {
				$violations[] = basename($file);
			}
		}
		$this->assertSame([], $violations,
			"\\Elgg\\Hook was removed in 6.x -> use \\Elgg\\Event:\n" . implode("\n", $violations));

		// positive: the event-handler classes must actually type-hint \Elgg\Event
		foreach (['Router', 'Page', 'Menus', 'RewriteService'] as $cls) {
			$src = self::read("classes/hypeJunction/Seo/{$cls}.php");
			$this->assertMatchesRegularExpression('/\\\\Elgg\\\\Event\s+\$/', $src,
				"{$cls} handlers must type-hint \\Elgg\\Event on 7.x");
		}
	}
}
