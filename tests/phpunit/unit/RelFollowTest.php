<?php

namespace hypeJunction\Seo\Tests;

use hypeJunction\Seo\RelFollow;
use PHPUnit\Framework\TestCase;

class RelFollowTest extends TestCase {

    /**
     * @return void
     */
    public function testStripRelRemovesNofollowAttribute(): void {
        $html = '<a href="https://example.com" rel="nofollow">link</a>';
        $result = RelFollow::stripRel($html);
        $this->assertStringNotContainsString('rel="nofollow"', $result);
        $this->assertStringContainsString('href="https://example.com"', $result);
    }

    /**
     * @return void
     */
    public function testStripRelLeavesOtherRelValuesAlone(): void {
        $html = '<a href="https://example.com" rel="noopener">link</a>';
        $result = RelFollow::stripRel($html);
        $this->assertStringContainsString('rel="noopener"', $result);
    }

    /**
     * @return void
     */
    public function testStripRelHandlesMultipleLinks(): void {
        $html = '<p><a href="/a" rel="nofollow">a</a> and <a href="/b" rel="nofollow">b</a></p>';
        $result = RelFollow::stripRel($html);
        $this->assertStringNotContainsString('nofollow', $result);
        $this->assertStringContainsString('href="/a"', $result);
        $this->assertStringContainsString('href="/b"', $result);
    }

    /**
     * @return void
     */
    public function testStripRelIsCaseInsensitive(): void {
        $html = '<A HREF="/x" REL="nofollow">x</A>';
        $result = RelFollow::stripRel($html);
        $this->assertStringNotContainsString('nofollow', $result);
    }

    /**
     * @return void
     */
    public function testStripRelNoOpOnPlainText(): void {
        $html = 'no links here';
        $this->assertSame($html, RelFollow::stripRel($html));
    }
}
