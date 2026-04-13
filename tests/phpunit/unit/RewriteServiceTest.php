<?php

namespace hypeJunction\Seo\Tests;

use hypeJunction\Seo\RewriteService;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the pure row-normalisation helper on RewriteService.
 * Does not touch the database (rowToSefData is a pure method). Full CRUD
 * coverage lives in integration tests that run inside an Elgg container
 * after migration.
 */
class RewriteServiceTest extends TestCase {

    public function testRowToSefDataCastsColumnsAndParsesAliases(): void {
        $svc = $this->makeService();
        $row = (object) [
            'id' => '42',
            'path' => '/blog/owner/alice',
            'sef_path' => '/@alice/posts',
            'title' => 'Alice posts',
            'description' => 'All of alice posts',
            'keywords' => 'alice,blog',
            'aliases' => '/blog/owner/alice,/alice/blog',
            'metatags' => null,
            'entity_guid' => '7',
        ];

        $data = $svc->rowToSefData($row);

        $this->assertSame(42, $data['id']);
        $this->assertSame('/blog/owner/alice', $data['path']);
        $this->assertSame('/@alice/posts', $data['sef_path']);
        $this->assertSame('Alice posts', $data['title']);
        $this->assertSame(7, $data['guid']);
        $this->assertSame(['/blog/owner/alice', '/alice/blog'], $data['aliases']);
        $this->assertSame([], $data['metatags']);
    }

    public function testRowToSefDataUnserializesMetatags(): void {
        $svc = $this->makeService();
        $metatags = ['og:title' => 'Hello', 'og:type' => 'article'];
        $row = (object) [
            'id' => '1',
            'path' => '/p',
            'sef_path' => '/p',
            'title' => '',
            'description' => '',
            'keywords' => '',
            'aliases' => '/p',
            'metatags' => serialize($metatags),
            'entity_guid' => '0',
        ];

        $data = $svc->rowToSefData($row);
        $this->assertSame($metatags, $data['metatags']);
    }

    private function makeService(): RewriteService {
        $pool = new class implements \hypeJunction\Seo\Cache {
            public function get($key, callable $callback = null, $default = null) { return $default; }
            public function invalidate($key) {}
            public function put($key, $value) {}
        };
        return new RewriteService($pool);
    }
}
