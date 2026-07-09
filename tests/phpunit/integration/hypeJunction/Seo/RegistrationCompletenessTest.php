<?php

namespace hypeJunction\Seo\Tests;

use Elgg\IntegrationTestCase;
use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Batch;
use Elgg\Upgrade\Result;
use hypeJunction\Seo\Upgrades\MigratePluginId;

/**
 * elgg-plugin.php registration completeness on a booted Elgg 7: the resource
 * route + the four AdminGatekeeper admin routes actually resolve, and the
 * declared upgrade is an asynchronous Batch with the 6.x run() signature.
 */
class RegistrationCompletenessTest extends IntegrationTestCase {

	public function up() {}

	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testResourceAndAdminRoutesAreRegistered(): void {
		$routes = _elgg_services()->routes;

		$seo = $routes->get('seo');
		$this->assertNotNull($seo, "route 'seo' is not registered");
		$this->assertSame('/seo/{segments}', $seo->getPath());

		foreach ([
			'admin:seo:generator',
			'admin:seo:rules',
			'admin:seo:sitemap',
			'admin:seo:add_rule',
		] as $name) {
			$this->assertNotNull($routes->get($name), "admin route '{$name}' is not registered");
		}
	}

	public function testMigratePluginIdUpgradeIsAsynchronousUpgrade(): void {
		// Batch became an abstract class in 6.x; the upgrade must extend
		// AsynchronousUpgrade and expose run(Result, $offset): Result.
		$this->assertTrue(
			is_subclass_of(MigratePluginId::class, AsynchronousUpgrade::class),
			'MigratePluginId must extend AsynchronousUpgrade'
		);
		$this->assertTrue(
			is_subclass_of(MigratePluginId::class, Batch::class),
			'AsynchronousUpgrade must satisfy the Batch contract'
		);

		$run = new \ReflectionMethod(MigratePluginId::class, 'run');
		$this->assertSame(Result::class, (string) $run->getReturnType(),
			'run() must return an Elgg\\Upgrade\\Result');
	}
}
