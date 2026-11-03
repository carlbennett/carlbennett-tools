<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\StringProcessor;
use \PHPUnit\Framework\Attributes\DataProvider;

class StringProcessorTest extends EnvironmentTestCase
{
    public static function fuzzyMatchProvider(): array
    {
        return [
            ['api.*', 'api.enabled', true],
            ['api.*', 'API.Enabled', true],
            ['api.*', 'api.', true],
            ['api.*', 'apixenabled', false],
            ['*.enabled', 'api.enabled', true],
            ['a*c', 'abbbc', true],
            ['a*c', 'abbbd', false],
            ['exact', 'exact', true],
            ['exact', 'exactly', false],
            ['a/b', 'a/b', true],
            ['(x)', '(x)', true],
            ['(x)', 'x', false],
        ];
    }

    // Regression: preg_quote was passed directly as the callback and received the match array.
    #[DataProvider('fuzzyMatchProvider')]
    public function testFuzzyMatch(string $pattern, string $subject, bool $expected): void
    {
        $this->assertSame($expected, StringProcessor::fuzzyMatch($pattern, $subject));
    }

    public function testIsBrowser(): void
    {
        $this->assertTrue(StringProcessor::isBrowser('Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0'));
        $this->assertFalse(StringProcessor::isBrowser('curl/8.0.0'));
    }

    public function testIsBrowserReadsEnvironment(): void
    {
        $this->setEnv('HTTP_USER_AGENT', 'Mozilla/5.0');
        $this->assertTrue(StringProcessor::isBrowser());
        $this->setEnv('HTTP_USER_AGENT', null);
        $this->assertFalse(StringProcessor::isBrowser());
    }

    public function testSanitizeForUrlString(): void
    {
        $this->assertSame('hello-world', StringProcessor::sanitizeForUrl('  Hello, World!  '));
        $this->assertSame('Hello-World', StringProcessor::sanitizeForUrl('Hello, World!', false));
    }

    // Regression: arrays passed an extra argument to trim() through array_walk(), and were then
    // passed to strtolower().
    public function testSanitizeForUrlArray(): void
    {
        $this->assertSame(['hello-world', 'foo-bar'], StringProcessor::sanitizeForUrl(['-Hello World-', 'Foo  Bar']));
        $this->assertSame(['Hello-World'], StringProcessor::sanitizeForUrl(['Hello World'], false));
    }

    public function testStripExcessLines(): void
    {
        $this->assertSame("a\n\nb", StringProcessor::stripExcessLines("a\n\n\n\nb"));
    }

    public function testStripLeftPattern(): void
    {
        $this->assertSame('bar', StringProcessor::stripLeftPattern('foobar', 'foo'));
        $this->assertSame('foobar', StringProcessor::stripLeftPattern('foobar', 'bar'));
    }

    public function testStripToSnippet(): void
    {
        $this->assertSame('short', StringProcessor::stripToSnippet('short', 10));
        $this->assertSame('the quick...', StringProcessor::stripToSnippet('the quick brown fox', 12));
    }

    public function testStripUpTo(): void
    {
        $this->assertSame('no marker', StringProcessor::stripUpTo('no marker', '|'));
        $this->assertSame('first', StringProcessor::stripUpTo('first|second', '|', 20));
    }
}
