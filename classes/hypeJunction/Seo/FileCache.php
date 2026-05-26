<?php

namespace hypeJunction\Seo;

use Flintstone\Flintstone;

/**
 * Flintstone-backed implementation of the Seo Cache contract.
 */
class FileCache implements Cache {

	/**
	 * @var Flintstone
	 */
	private $cache;

	/**
	 * Constructor — opens the on-disk Flintstone store under dataroot.
	 */
	public function __construct() {
		$this->cache = new Flintstone('sef_data_cache', [
			'dir' => \elgg_get_config('dataroot'),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, callable $callback = null, $default = null) {
		$value = $this->cache->get($key);
		if (!isset($value)) {
			$value = $default;
		}

		if (is_callable($callback)) {
			return call_user_func($callback, $value);
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function invalidate($key) {
		$this->cache->delete($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function put($key, $value) {
		$this->cache->set($key, $value);
	}
}
