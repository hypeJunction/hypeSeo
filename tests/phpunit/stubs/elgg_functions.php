<?php
// Minimal stubs so pure-unit tests can load classes that reference global
// Elgg functions and the \Elgg\Cache\Pool interface without a full Elgg
// bootstrap.

namespace Elgg\Cache {
    if (!interface_exists(Pool::class, false)) {
        interface Pool {
            public function get($key, callable $callback = null, $default = null);
            public function invalidate($key);
            public function put($key, $value);
        }
    }
}

namespace {
    if (!function_exists('elgg_get_config')) {
        function elgg_get_config($name, $default = null) {
            if ($name === 'dbprefix') {
                return 'elgg_';
            }
            return $default;
        }
    }
}
