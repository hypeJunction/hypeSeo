<?php
// Minimal stubs so pure-unit tests can load classes that reference global
// Elgg functions without a full Elgg bootstrap.

if (!function_exists('elgg_get_config')) {
    /**
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    function elgg_get_config($name, $default = null) {
        if ($name === 'dbprefix') {
            return 'elgg_';
        }
        return $default;
    }
}
