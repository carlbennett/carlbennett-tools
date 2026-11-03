<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\HttpCode;
use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;

class HttpCodeTest extends TestCase
{
    public static function stringProvider(): array
    {
        return [
            ['OK', 200],
            ['ok', 200],
            ['HTTP_OK', 200],
            ['Not Found', 404],
            ['not found', 404],
            // Regression: underscores were stripped, so names like NOT_FOUND never matched.
            ['NOT_FOUND', 404],
            ['HTTP_NOT_FOUND', 404],
            ['INTERNAL_SERVER_ERROR', 500],
            ['PAYLOAD_TOO_LARGE', 413],
            ['Payload Too Large', 413],
            ['im a teapot', 418],
            ["I'm a teapot", 418],
            ['see other', 303],
        ];
    }

    #[DataProvider('stringProvider')]
    public function testCodeFromString(string $value, int $expected): void
    {
        $this->assertSame($expected, HttpCode::codeFromString($value));
    }

    public function testCodeFromStringRejectsUnknown(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        HttpCode::codeFromString('not a status');
    }

    public static function intProvider(): array
    {
        return [
            [200, 'Ok'],
            [404, 'Not Found'],
            [413, 'Payload Too Large'],
            [500, 'Internal Server Error'],
            [505, 'Http Version Not Supported'],
        ];
    }

    #[DataProvider('intProvider')]
    public function testCodeFromInt(int $value, string $expected): void
    {
        $this->assertSame($expected, HttpCode::codeFromInt($value));
    }

    public function testCodeFromIntRejectsUnknown(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        HttpCode::codeFromInt(299);
    }

    // Every named constant must round-trip through codeFromInt() and codeFromString().
    public function testEveryConstantRoundTrips(): void
    {
        foreach ((new \ReflectionClass(HttpCode::class))->getConstants() as $name => $code)
        {
            if (\substr($name, 0, 5) !== 'HTTP_') continue;
            $this->assertSame($code, HttpCode::codeFromString(HttpCode::codeFromInt($code)), $name);
        }
    }

    public function testConstructAndSerialize(): void
    {
        $code = new HttpCode('not found');
        $this->assertSame(404, $code->getCode());
        $this->assertSame('Not Found', (string) $code);
        $this->assertSame(['code' => 404, 'name' => 'Not Found'], $code->jsonSerialize());
    }

    public function testSetCodeRejectsOutOfRange(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        new HttpCode(600);
    }

    public function testIsRedirect(): void
    {
        $this->assertTrue(HttpCode::isRedirectFromCode(301));
        $this->assertTrue(HttpCode::isRedirectFromCode(399));
        $this->assertFalse(HttpCode::isRedirectFromCode(399, false));
        $this->assertFalse(HttpCode::isRedirectFromCode(200));
        $this->assertTrue((new HttpCode(303))->isRedirect());
    }
}
