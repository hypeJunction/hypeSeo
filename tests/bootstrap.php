<?php
// Pre-migration unit test bootstrap.
// Loads plugin class files via a minimal PSR-4-ish autoloader so tests can
// run without a full Elgg installation. Integration tests that require
// Elgg core live under tests/phpunit/integration and run inside the Elgg
// Docker container, not from this bootstrap.

require_once __DIR__ . '/phpunit/stubs/elgg_functions.php';

spl_autoload_register(function ($class) {
    $prefix = 'hypeJunction\\Seo\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/../classes/hypeJunction/Seo/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
