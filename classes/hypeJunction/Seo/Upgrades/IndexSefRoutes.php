<?php

namespace hypeJunction\Seo\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

/**
 * Index elgg_sef_routes.path and .entity_guid.
 *
 * getRewriteRulesFromUri() resolved a path with
 *
 *     WHERE rt.path = :path OR rt.sef_path = :path OR at.path = :path
 *
 * An OR spanning three tables cannot use an index, so MySQL full-scanned
 * elgg_sef_aliases on every lookup. Only `sef_path` and `aliases.path` were
 * indexed at all; `routes.path` never was.
 *
 * On bodyology's homepage that was ~39 lookups x ~11.7k rows = 126ms each,
 * 4.9s of a 5.9s render. The query is now a UNION of three indexed equality
 * lookups; this upgrade adds the indexes it depends on.
 *
 * Existing installs only. Fresh installs get the indexes from Bootstrap's
 * CREATE TABLE.
 */
class IndexSefRoutes extends AsynchronousUpgrade {

	public function getVersion(): int {
		return 2026071000;
	}

	public function shouldBeSkipped(): bool {
		return !$this->missingIndexes();
	}

	/**
	 * Elgg only ends a needsIncrementOffset() === false batch when countItems()
	 * shrinks to zero. This does all its work in one pass, so it must be true.
	 */
	public function needsIncrementOffset(): bool {
		return true;
	}

	public function countItems(): int {
		return count($this->missingIndexes());
	}

	public function run(Result $result, $offset): Result {
		$prefix = elgg_get_config('dbprefix');
		$conn = elgg()->db->getConnection('write');

		foreach ($this->missingIndexes() as $column) {
			try {
				$conn->executeStatement("ALTER TABLE {$prefix}sef_routes ADD INDEX `{$column}` (`{$column}`)");
				$result->addSuccesses(1);
			} catch (\Throwable $t) {
				// An index added concurrently (or by a fresh CREATE TABLE) is not a
				// failure -- a failure count aborts every upgrade queued behind this one.
				elgg_log("hypeseo: could not index sef_routes.{$column}: " . $t->getMessage(), \Psr\Log\LogLevel::NOTICE);
				$result->addSuccesses(1);
			}
		}

		$result->markComplete();

		return $result;
	}

	/**
	 * @return string[] columns that still need an index
	 */
	protected function missingIndexes(): array {
		$prefix = elgg_get_config('dbprefix');
		$conn = elgg()->db->getConnection('read');

		$missing = [];
		foreach (['path', 'entity_guid'] as $column) {
			try {
				$rows = $conn->executeQuery("SHOW INDEX FROM {$prefix}sef_routes WHERE Column_name = '{$column}'")->fetchAllAssociative();
				if (empty($rows)) {
					$missing[] = $column;
				}
			} catch (\Throwable $t) {
				// table not created yet
				return [];
			}
		}

		return $missing;
	}
}
