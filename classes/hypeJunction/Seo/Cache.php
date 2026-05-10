<?php

namespace hypeJunction\Seo;

/**
 * Minimal cache contract used by RewriteService to store SEF lookup
 * data. Prior to Elgg 3.x this extended \Elgg\Cache\Pool, which was
 * removed from core; the plugin now owns its own abstraction so the
 * dependency surface is limited to what we actually use.
 */
interface Cache {

	/**
	 * Fetch a cached value, computing it via $callback on miss.
	 *
	 * @param string        $key      Cache key
	 * @param callable|null $callback Producer to populate on miss
	 * @param mixed         $default  Value returned when neither cache nor callback yields one
	 * @return mixed
	 */
	public function get($key, callable $callback = null, $default = null);

	/**
	 * Drop a cached value.
	 *
	 * @param string $key Cache key
	 * @return void
	 */
	public function invalidate($key);

	/**
	 * Store a value in the cache.
	 *
	 * @param string $key   Cache key
	 * @param mixed  $value Value to store
	 * @return void
	 */
	public function put($key, $value);
}
