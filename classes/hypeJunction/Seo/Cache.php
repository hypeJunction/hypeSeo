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
     * @param mixed $key
     * @param callable $callback
     * @param mixed $default
     */
    public function get($key, callable $callback = null, $default = null);

    /**
     * @param mixed $key
     */
    public function invalidate($key);

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function put($key, $value);
}
