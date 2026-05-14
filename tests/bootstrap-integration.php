<?php
/**
 * PHPUnit bootstrap for hypeSeo integration tests.
 * Plugin is mounted at {elgg_root}/mod/hypeseo/ inside the Docker container.
 * Run via: docker compose exec elgg vendor/bin/phpunit -c mod/hypeseo/tests/phpunit-integration.xml
 */

// tests/ -> plugin root -> mod/ -> elgg root
$elggRoot = dirname(__DIR__, 3);

require_once $elggRoot . '/vendor/autoload.php';

$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

\Elgg\Application::loadCore();
