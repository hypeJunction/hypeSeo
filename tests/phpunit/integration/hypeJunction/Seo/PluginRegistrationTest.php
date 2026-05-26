<?php

namespace hypeJunction\Seo\Tests;

use Elgg\IntegrationTestCase;

class PluginRegistrationTest extends IntegrationTestCase {

	public function up() {}

	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testPluginIsActive(): void {
		$this->assertTrue(\elgg_is_active_plugin('hypeseo'));
	}

	public function testCustomTablesExist(): void {
		$prefix = elgg()->db->prefix;
		foreach (['sef_routes', 'sef_aliases', 'sef_data'] as $tbl) {
			$rows = elgg()->db->getData("SHOW TABLES LIKE '{$prefix}{$tbl}'");
			$this->assertNotEmpty($rows, "Table {$prefix}{$tbl} does not exist");
		}
	}

	public function testActionsAreRegistered(): void {
		$this->assertTrue(\elgg_action_exists('seo/edit'));
		$this->assertTrue(\elgg_action_exists('seo/autogen'));
		$this->assertTrue(\elgg_action_exists('seo/delete'));
		$this->assertTrue(\elgg_action_exists('seo/sitemap'));
	}

	public function testAdminActionsRequireAdminAccess(): void {
		$actions = \_elgg_services()->actions->getAllActions();
		foreach (['seo/edit', 'seo/autogen', 'seo/delete', 'seo/sitemap'] as $action) {
			$this->assertArrayHasKey($action, $actions);
			$this->assertSame('admin', $actions[$action]['access'], "Action {$action} should require admin access");
		}
	}
}
