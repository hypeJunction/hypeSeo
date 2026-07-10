<?php

namespace hypeJunction\Seo\Tests;

use hypeJunction\Seo\RewriteService;
use PHPUnit\Framework\TestCase;

/**
 * REGRESSION: getRewriteRulesFromUri() resolved a path with
 *
 *     WHERE rt.path = :path OR rt.sef_path = :path OR at.path = :path
 *
 * An OR spanning three tables cannot use an index, so MySQL full-scanned
 * elgg_sef_aliases (11.7k rows) on EVERY lookup. Elgg renders many URLs that have
 * no SEF route, and a miss was never cached, so each one re-ran the scan.
 *
 * On bodyology's homepage: ~39 lookups x 126ms = 4.9s of a 5.9s render. Fixing
 * both took the render to 3.0s with byte-identical output.
 */
class RewriteServiceQueryTest extends TestCase {

	protected function source(): string {
		return file_get_contents(dirname(__DIR__, 3) . '/classes/hypeJunction/Seo/RewriteService.php');
	}

	public function testPathLookupDoesNotOrAcrossTables(): void {
		$this->assertStringNotContainsString(
			'WHERE rt.path = :path OR rt.sef_path = :path OR at.path = :path',
			$this->source(),
			'a cross-table OR forces a full scan of elgg_sef_aliases on every lookup'
		);
	}

	public function testPathLookupResolvesTheRouteIdViaIndexedUnion(): void {
		$src = $this->source();

		$this->assertStringContainsString('UNION ALL', $src);
		$this->assertStringContainsString('WHERE path = :path', $src);
		$this->assertStringContainsString('WHERE sef_path = :path', $src);
	}

	public function testAMissIsCachedSoItIsNotRepeated(): void {
		$this->assertTrue(defined(RewriteService::class . '::MISS'));
		$this->assertStringContainsString(
			'$this->routes_cache->put($hash, self::MISS);',
			$this->source(),
			'an unresolvable path must not re-run the lookup on every request'
		);
	}
}
