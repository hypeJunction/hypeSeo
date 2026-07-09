<?php

declare(strict_types=1);

namespace hypeJunction\Seo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Auto-generated migration regression guard for plugin "hypeseo" (target Elgg 7.x).
 *
 * Instantiated from
 * skills/elgg-test-writer/templates/MigrationRegressionTest.php.template and
 * driven by skills/elgg-migrate/references/migration-failure-catalog.md. Asserts
 * that EVERY statically-detectable failure class from the catalog is ABSENT for
 * the target major. Static (no Elgg boot) because most catalog classes fatal at
 * class-load / page-render on the target version.
 *
 * TWO PLUGIN-SPECIFIC ADAPTATIONS vs. the stock template:
 *   - testNoRemovedSymbols skips comment lines: Bootstrap.php documents the 5.x
 *     removal of elgg_get_registered_entity_types() in a // comment (not a call).
 *   - testNoRouteRewriteAtInit is rewritten: hypeSeo intentionally keeps a
 *     declarative route:rewrite entry in elgg-plugin.php for the sitemap.xml
 *     rewrite, so the stock "route:rewrite absent from manifest" check does not
 *     apply. The real FC-ALL-05 remediation is the imperative route:rewrite,'all'
 *     registration in Bootstrap::boot() at priority 1, which this asserts.
 */
final class MigrationRegressionTest extends TestCase
{
    /** Target Elgg major this suite guards. */
    private const TARGET_MAJOR = 7;

    /**
     * Global functions REMOVED at each major, keyed by the major that removed
     * them. Treated CUMULATIVELY. Mirror of removed-functions.json (call forms).
     *
     * @var array<int, list<string>>
     */
    private const REMOVED_FUNCTIONS = [
        3 => [
            'elgg_get_metastring_id',
            '_elgg_entities_get_metastrings_options',
        ],
        4 => [
            'elgg_register_css',
            'elgg_load_css',
            'elgg_format_attributes',
            'access_get_show_hidden_status',
            'create_metadata',
            'update_metadata',
            'elgg_flush_simplecache',
        ],
        5 => [
            'add_translation',
            'get_default_access',
            'get_current_language',
            'check_entity_relationship',
        ],
        6 => [
            'elgg_trigger_plugin_hook',
            'elgg_register_plugin_hook_handler',
            'elgg_unregister_plugin_hook_handler',
            'elgg_clear_plugin_hook_handlers',
            'register_error',
            'system_message',
            'forward',
            'elgg_redirect',
            'current_page_url',
            '_elgg_rmdir',
            'elgg_require_js',
            'elgg_load_js',
            'elgg_define_js',
            'elgg_unregister_css',
            'elgg_flush_caches',
            'elgg_get_version',
            'elgg_instanceof',
            'elgg_set_ignore_access',
            'elgg_view_menu_item',
            'elgg_pop_breadcrumb',
            'elgg_set_plugin_setting',
            'elgg_unset_plugin_setting',
            'elgg_set_plugin_user_setting',
            'elgg_unset_plugin_user_setting',
            'elgg_delete_metadata_by_id',
            'elgg_add_subscription',
            'elgg_group_gatekeeper',
            'elgg_get_group_tool_options',
            'elgg_get_file_simple_type',
            'elgg_get_registered_tag_metadata_names',
            'elgg_list_views',
            'elgg_register_admin_menu_item',
            'elgg_strrchr',
            'elgg_strripos',
            'elgg_set_view_location',
            'elgg_disable_annotations',
            'elgg_enable_annotations',
            '_elgg_html_decode',
            '_elgg_get_display_query',
            '_elgg_get_access_where_sql',
            '_elgg_admin_add_plugin_settings_menu',
        ],
        7 => [
            'elgg_is_admin_user',
            'elgg_get_logged_in_user',
            'elgg_new_entity',
            'elgg_get_entities_from_relationship',
            'elgg_is_registered_viewtype',
            'elgg_get_registered_entity_types',
            'elgg_geocode_location',
            'elgg_reset_system_cache',
        ],
    ];

    /**
     * Bare global constants removed at a major (no `(` → the call-shaped scan
     * misses them). Keyed cumulatively.
     *
     * @var array<int, list<string>>
     */
    private const REMOVED_CONSTANTS = [
        7 => ['ELGG_CACHE_PERSISTENT'],
    ];

    /**
     * Core types whose KIND changed so a plugin's use/new/implements fatals at
     * or beyond the boundary. Each row: [needle regex, forbidden keyword regex, message].
     *
     * @var array<int, list<array{0:string,1:string,2:string}>>
     */
    private const CHANGED_CONTRACTS = [
        4 => [
            ['Elgg\\\\Di\\\\ServiceFacade', 'use', 'Elgg\\Di\\ServiceFacade trait was removed in 4.x — register via DI\\create() in elgg-services.php'],
            ['Elgg\\\\Notifications\\\\NotificationEvent', 'new', 'Elgg\\Notifications\\NotificationEvent is an interface in 4.x — instantiate SubscriptionNotificationEvent / InstantNotificationEvent'],
        ],
        6 => [
            ['Elgg\\\\Upgrade\\\\Batch', 'implements', 'Elgg\\Upgrade\\Batch became an abstract class in 6.x — extends \\Elgg\\Upgrade\\AsynchronousUpgrade instead of implements Batch'],
            ['Elgg\\\\Hook', 'use', 'Elgg\\Hook was removed in 6.x — use Elgg\\Event; and type-hint \\Elgg\\Event $event'],
        ],
    ];

    /** Canonical Elgg core method signatures (param "type $name" list, defaults stripped). */
    private const CORE_SIG = [
        'canComment'          => 'int $user_guid',
        'canWriteToContainer' => 'int $user_guid, string $type, string $subtype',
        'canEdit'             => 'int $user_guid',
        'canDelete'           => 'int $user_guid',
        'canAnnotate'         => 'int $user_guid, string $annotation_name',
    ];

    /* ---------------------------------------------------------------- helpers */

    private static function pluginRoot(): string
    {
        $dir = __DIR__;
        for ($i = 0; $i < 6; $i++) {
            if (is_file($dir . '/elgg-plugin.php') || is_file($dir . '/manifest.xml') || is_file($dir . '/composer.json')) {
                return $dir;
            }
            $dir = \dirname($dir);
        }
        return \dirname(__DIR__);
    }

    /** @return list<string> files with $ext under $sub (relative dir); vendor dirs skipped */
    private static function files(string $sub, string $ext): array
    {
        $base = self::pluginRoot() . '/' . ltrim($sub, '/');
        if (!is_dir($base)) {
            return [];
        }
        $out = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $path = $f->getPathname();
            if (preg_match('#/(vendor|vendors|bower_components|node_modules)/#', $path)) {
                continue;
            }
            if (str_ends_with($path, '.' . $ext)) {
                $out[] = $path;
            }
        }
        return $out;
    }

    /** @return list<string> cumulative removed names for the target major */
    private static function cumulative(array $byMajor): array
    {
        $out = [];
        foreach ($byMajor as $major => $names) {
            if ((int) $major <= self::TARGET_MAJOR) {
                $out = array_merge($out, $names);
            }
        }
        return $out;
    }

    private static function normParams(string $raw): string
    {
        $tokens = [];
        foreach (explode(',', $raw) as $p) {
            $p = preg_replace('/=.*$/', '', $p);
            $p = trim(preg_replace('/\s+/', ' ', $p));
            if ($p !== '') {
                $tokens[] = $p;
            }
        }
        return implode(', ', $tokens);
    }

    private static function manifestSource(): string
    {
        $manifest = self::pluginRoot() . '/elgg-plugin.php';
        return is_file($manifest) ? (string) file_get_contents($manifest) : '';
    }

    /** True for a source line that is pure comment / docblock (no executable code). */
    private static function isCommentLine(string $line): bool
    {
        $t = ltrim($line);
        return $t === '' || $t[0] === '*' || str_starts_with($t, '//') || str_starts_with($t, '/*') || str_starts_with($t, '#');
    }

    /* ---------------------------------------------------------------- removed symbols */

    public function testNoRemovedSymbols(): void
    {
        $removed = self::cumulative(self::REMOVED_FUNCTIONS);
        if ($removed === []) {
            $this->assertTrue(true);
            return;
        }
        $alt = implode('|', array_map(static fn (string $n): string => preg_quote($n, '#'), $removed));
        $re = '#(?<![\w>$:\\\\])(' . $alt . ')\s*\(#';

        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
                // Skip comment-only lines and strip trailing // comments so a
                // documented removal (e.g. "// foo() was removed in 5.x") is not
                // mistaken for an actual call.
                if (self::isCommentLine($line)) {
                    continue;
                }
                $code = (string) preg_replace('#//.*$#', '', $line);
                if (preg_match_all($re, $code, $m)) {
                    foreach ($m[1] as $sym) {
                        $violations[] = sprintf('%s:%d %s() — removed at/before Elgg %d.x', basename($file), $n + 1, $sym, self::TARGET_MAJOR);
                    }
                }
            }
        }
        $this->assertSame([], $violations, "Calls to removed core functions fatal on Elgg " . self::TARGET_MAJOR . ".x:\n" . implode("\n", $violations));
    }

    public function testNoRemovedConstants(): void
    {
        $removed = self::cumulative(self::REMOVED_CONSTANTS);
        if ($removed === []) {
            $this->assertTrue(true);
            return;
        }
        $alt = implode('|', array_map(static fn (string $n): string => preg_quote($n, '#'), $removed));
        $re = '#(?<![\w\\\\])(' . $alt . ')\b#';

        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            if (preg_match_all($re, (string) file_get_contents($file), $m)) {
                foreach (array_unique($m[1]) as $c) {
                    $violations[] = basename($file) . ": {$c} removed on Elgg " . self::TARGET_MAJOR . '.x';
                }
            }
        }
        $this->assertSame([], $violations, "Use of removed core constants:\n" . implode("\n", $violations));
    }

    public function testNoRemovedStaticMimeType(): void
    {
        if (self::TARGET_MAJOR < 4) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            if (preg_match('/(ElggFile::|->)detectMimeType\s*\(/', (string) file_get_contents($file))) {
                $violations[] = basename($file) . ': detectMimeType() removed in 4.x — use mime_content_type($path) guarded by is_file()';
            }
        }
        $this->assertSame([], $violations, "Calls to removed ElggFile::detectMimeType:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- changed class contracts */

    public function testNoRemovedClassContracts(): void
    {
        $rows = [];
        foreach (self::CHANGED_CONTRACTS as $major => $entries) {
            if ((int) $major <= self::TARGET_MAJOR) {
                $rows = array_merge($rows, $entries);
            }
        }
        if ($rows === []) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
                foreach ($rows as [$needle, $keyword, $msg]) {
                    if (preg_match('/' . $needle . '\b/', $line) && preg_match('/\b' . $keyword . '\b/', $line)) {
                        $violations[] = sprintf('%s:%d — %s', basename($file), $n + 1, $msg);
                    }
                }
            }
        }
        $this->assertSame([], $violations, "Changed core-type contracts fatal on boot:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- forbidden / required files */

    public function testNoForbiddenBootstrapFiles(): void
    {
        if (self::TARGET_MAJOR < 4) {
            $this->assertTrue(true);
            return;
        }
        $root = self::pluginRoot();
        $violations = [];
        foreach (['start.php', 'activate.php', 'deactivate.php'] as $forbidden) {
            if (is_file($root . '/' . $forbidden)) {
                $violations[] = "{$forbidden} must be deleted for Elgg 4.x+ (plugin is rejected on activation)";
            }
        }
        $this->assertSame([], $violations, "Forbidden bootstrap files present:\n" . implode("\n", $violations));
    }

    public function testStartPhpPresentOn3x(): void
    {
        if (self::TARGET_MAJOR !== 3) {
            $this->assertTrue(true);
            return;
        }
        $root = self::pluginRoot();
        if (is_file($root . '/elgg-plugin.php')) {
            $this->assertFileExists($root . '/start.php', 'Elgg 3.x plugins keep start.php — do not delete it until the 3.x→4.x step');
        } else {
            $this->assertTrue(true);
        }
    }

    /* ---------------------------------------------------------------- lowercase plugin id */

    public function testNoCamelCasePluginIdCallsites(): void
    {
        if (self::TARGET_MAJOR < 4) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            $src = (string) file_get_contents($file);
            if (preg_match_all('/elgg_get_plugin_(?:from_id|setting|user_setting)\s*\([^;]*?[\'"]([A-Za-z0-9_]*[A-Z][A-Za-z0-9_]*)[\'"]/', $src, $m)) {
                foreach (array_unique($m[1]) as $id) {
                    $violations[] = basename($file) . ": plugin id '{$id}' has uppercase — 4.x lowercases ids, callsite silently returns false";
                }
            }
        }
        $this->assertSame([], $violations, "camelCase plugin-id callsites resolve to false on Elgg 4.x+:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- hook/event confusion */

    public function testNoHookEventConfusion(): void
    {
        if (self::TARGET_MAJOR !== 4) {
            $this->assertTrue(true);
            return;
        }
        $src = self::manifestSource();
        if ($src === '' || !preg_match("/['\"]events['\"]\s*=>\s*\[(.*?)\n\s*\],/s", $src, $block)) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (['view', 'register', 'route', 'prepare'] as $hookName) {
            if (preg_match('/[\'"]' . $hookName . '[\'"]\s*=>/', $block[1])) {
                $violations[] = "'{$hookName}' is a HOOK in 4.x but is registered under the 'events' key of elgg-plugin.php";
            }
        }
        $this->assertSame([], $violations, "Hook/event confusion in elgg-plugin.php:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- Seed / Batch shape */

    public function testSeedSubclassShape(): void
    {
        if (self::TARGET_MAJOR < 6) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('classes', 'php') as $file) {
            $src = (string) file_get_contents($file);
            if (!preg_match('/\bextends\s+\\\\?(?:Elgg\\\\Database\\\\Seeds\\\\)?Seed\b/', $src)) {
                continue;
            }
            $hasType = (bool) preg_match('/function\s+getType\s*\(/', $src);
            $hasCount = (bool) preg_match('/function\s+getCountOptions\s*\(/', $src);
            if (!$hasType || !$hasCount) {
                $missing = [];
                if (!$hasType) {
                    $missing[] = 'getType()';
                }
                if (!$hasCount) {
                    $missing[] = 'getCountOptions()';
                }
                $violations[] = basename($file) . ': Seed subclass missing ' . implode(' + ', $missing) . ' (abstract since 6.1 — fatals on every page load)';
            }
        }
        $this->assertSame([], $violations, "Incomplete Seed subclass shape on Elgg 6.x+:\n" . implode("\n", $violations));
    }

    public function testUpgradeBatchShape(): void
    {
        if (self::TARGET_MAJOR < 6) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('classes', 'php') as $file) {
            $src = (string) file_get_contents($file);
            if (preg_match('/\bimplements\b[^;{]*\bBatch\b/', $src)) {
                $violations[] = basename($file) . ': `implements Batch` fatals in 6.x — extends \\Elgg\\Upgrade\\AsynchronousUpgrade';
            }
        }
        $this->assertSame([], $violations, "Upgrade Batch shape wrong on Elgg 6.x+:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- language files */

    public function testNoLegacyLanguageFiles(): void
    {
        if (self::TARGET_MAJOR < 5) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('languages', 'php') as $file) {
            if (preg_match('/(^|[^A-Za-z_])add_translation\s*\(/m', (string) file_get_contents($file))) {
                $violations[] = basename($file) . ': add_translation() removed in 5.0 — return the array directly';
            }
        }
        $this->assertSame([], $violations, "Legacy language files fatal at boot on Elgg 5.x+:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- security (all versions) */

    public function testNoUnsafeUnserialize(): void
    {
        $violations = [];
        foreach (self::files('', 'php') as $file) {
            if (str_contains($file, '/tests/')) {
                continue;
            }
            foreach (explode("\n", (string) file_get_contents($file)) as $n => $line) {
                if (preg_match('/(?<![\w>$:\\\\])unserialize\s*\(/', $line) && !str_contains($line, 'allowed_classes')) {
                    $violations[] = basename($file) . ':' . ($n + 1) . ' unserialize() without allowed_classes => false (PHP object-injection RCE)';
                }
            }
        }
        $this->assertSame([], $violations, "Unsafe unserialize():\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- boot-time timing / manifest */

    public function testNoRouteRewriteAtInit(): void
    {
        // ADAPTED FROM STOCK TEMPLATE (see class docblock): hypeSeo keeps a
        // declarative route:rewrite entry in elgg-plugin.php for the sitemap.xml
        // rewrite, so the stock "route:rewrite absent from manifest" assertion
        // would false-positive. The FC-ALL-05 remediation for the pretty-URL
        // 'all' handler is its imperative registration in Bootstrap::boot() at
        // priority 1 (BEFORE Application::allowPathRewrite() dispatches the
        // event at init) — assert exactly that.
        $bootstrap = self::pluginRoot() . '/classes/hypeJunction/Seo/Bootstrap.php';
        $this->assertFileExists($bootstrap);
        $src = (string) file_get_contents($bootstrap);

        $this->assertSame(1, preg_match('/function\s+boot\s*\([^)]*\)\s*\{(.*?)\n\t\}/s', $src, $m),
            'Bootstrap::boot() not found');
        $this->assertMatchesRegularExpression(
            "/elgg_register_event_handler\(\s*'route:rewrite'\s*,\s*'all'\s*,[^;]*,\s*1\s*\)/",
            $m[1],
            'route:rewrite,all must be registered in Bootstrap::boot() at priority 1 (elgg-plugin.php events run at init, too late — site-wide SEF 404 on Elgg 7)',
        );
    }

    public function testManifestUsesStringLiterals(): void
    {
        $src = self::manifestSource();
        if ($src === '' || !preg_match("/['\"]entities['\"]\s*=>\s*\[(.*)\n\s*\],/sU", $src, $block)) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        if (preg_match('/[\'"](?:class|subtype)[\'"]\s*=>\s*[\\\\\w]+::(?:class|[A-Z_]+)\b/', $block[1])) {
            $violations[] = 'entities block uses ClassName::class / ::CONST — use string literals (autoloader not wired at manifest parse time)';
        }
        $this->assertSame([], $violations, "Non-literal class/subtype in elgg-plugin.php entities block:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- entity-subclass signatures */

    public function testNoIncompatibleCoreOverrides(): void
    {
        if (self::TARGET_MAJOR < 4) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('classes', 'php') as $file) {
            $src = (string) file_get_contents($file);
            if (!preg_match('/extends\s+\\\\?(ElggEntity|ElggObject|ElggComment|ElggGroup|ElggUser|ElggSite)\b/', $src)) {
                continue;
            }
            foreach (self::CORE_SIG as $method => $want) {
                if (!preg_match('/function\s+' . $method . '\s*\(([^)]*)\)/', $src, $m)) {
                    continue;
                }
                $got = self::normParams($m[1]);
                if ($got !== $want) {
                    $violations[] = sprintf('%s: %s(%s) — core wants %s(%s)', basename($file), $method, $got, $method, $want);
                }
            }
        }
        $this->assertSame([], $violations, "Incompatible core-method override(s) fatal at class load:\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- menu register value (7.x) */

    public function testMenuRegisterReturnsArray(): void
    {
        if (self::TARGET_MAJOR < 7) {
            $this->assertTrue(true);
            return;
        }
        $violations = [];
        foreach (self::files('classes', 'php') as $file) {
            $src = (string) file_get_contents($file);
            if (!preg_match('/register[\'"]?\s*,\s*[\'"]menu:|MenuItems|ElggMenuItem/', $src)) {
                continue;
            }
            if (preg_match('/\$(?:return|menu|result)\s*->\s*add\s*\(/', $src)) {
                $violations[] = basename($file) . ': $return->add() in a menu handler fatals on 7.x — use $return[] = \\ElggMenuItem::factory([...]);';
            }
        }
        $this->assertSame([], $violations, "Menu register handler uses ->add() (array in 7.x):\n" . implode("\n", $violations));
    }

    /* ---------------------------------------------------------------- css relocation (7.x) */

    public function testNoOrphanedCssElementViews(): void
    {
        if (self::TARGET_MAJOR < 7) {
            $this->assertTrue(true);
            return;
        }
        $root = self::pluginRoot();
        $dir = $root . '/views/default/css/elements';
        if (!is_dir($dir)) {
            $this->assertTrue(true);
            return;
        }
        $allPhp = '';
        foreach (self::files('', 'php') as $f) {
            $allPhp .= (string) file_get_contents($f);
        }
        $violations = [];
        foreach (glob($dir . '/*.css') ?: [] as $css) {
            $base = basename($css);
            $stem = substr($base, 0, -4);
            if (is_file($root . '/views/default/elements/' . $base)) {
                continue;
            }
            if (str_contains($allPhp, 'css/elements/' . $stem)) {
                continue;
            }
            $violations[] = "css/elements/{$base} has no relocated twin and is not loaded explicitly → never loads on Elgg 7.x";
        }
        $this->assertSame([], $violations, "Orphaned css/elements overrides:\n" . implode("\n", $violations));
    }
}
