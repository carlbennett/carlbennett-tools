<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\UrlFormatter;
use \PHPUnit\Framework\Attributes\DataProvider;

class UrlFormatterTest extends EnvironmentTestCase
{
    protected function setUp(): void
    {
        $this->setEnv('HTTP_HOST', 'tools.example.com');
        $this->setEnv('HTTPS', 'on');
        $this->setEnv('SERVER_PORT', '443');
        $this->setEnv('DOCUMENT_URI', '/user/login');
        $this->setEnv('QUERY_STRING', null);
    }

    public static function formatProvider(): array
    {
        return [
            'absolute path' => ['/tools', 'https://tools.example.com/tools'],
            'path with query' => ['/tools?a=1', 'https://tools.example.com/tools?a=1'],
            'relative path' => ['logout', 'https://tools.example.com/user/logout'],
            'protocol-relative' => ['//cdn.example.com/x.js', 'https://cdn.example.com/x.js'],
            'full url' => ['http://other.example.com/y', 'http://other.example.com/y'],
            'empty uses current path' => ['', 'https://tools.example.com/user/login'],
        ];
    }

    #[DataProvider('formatProvider')]
    public function testFormat(string $value, string $expected): void
    {
        $this->assertSame($expected, UrlFormatter::format($value));
    }

    public function testFormatUsesHttpWhenNotSecure(): void
    {
        $this->setEnv('HTTPS', null);
        $this->setEnv('SERVER_PORT', '80');
        $this->assertSame('http://tools.example.com/tools', UrlFormatter::format('/tools'));
    }

    // Regression: getenv() returns false when unset, so the ?? '' fallback never applied.
    public function testFormatWithoutDocumentUri(): void
    {
        $this->setEnv('DOCUMENT_URI', null);
        $this->assertSame('https://tools.example.com/tools', UrlFormatter::format('tools'));
    }
}
