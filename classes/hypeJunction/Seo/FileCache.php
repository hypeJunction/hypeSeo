<?php

namespace hypeJunction\Seo;

use Flintstone\Flintstone;

class FileCache implements Cache {

	/**
	 * @var Flintstone
	 */
	private $cache;

	public function __construct() {
$this->cache = new Flintstone('sef_data_cache', [
			'dir' => \elgg_get_config('dataroot'),
		]);
	}

	/**
     * @param mixed $key
     * @param callable $callback
     * @param mixed $default
     * @return mixed
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
     * @param mixed $key
     */
    public function invalidate($key) {
		$this->cache->delete($key);
	}

	/**
     * @param mixed $key
     * @param mixed $value
     */
    public function put($key, $value) {
		$this->cache->set($key, $value);
	}

}
