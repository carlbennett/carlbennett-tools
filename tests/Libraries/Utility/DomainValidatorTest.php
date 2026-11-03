<?php

namespace CarlBennett\Tools\Tests\Libraries\Utility;

use \CarlBennett\Tools\Libraries\Utility\DomainValidator;
use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;

class DomainValidatorTest extends TestCase
{
    public static function domainProvider(): array
    {
        return [
            ['example.com', true],
            ['sub.example.co.uk', true],
            ['localhost', true],
            ['xn--bcher-kva.example', true],
            ['a-b.example', true],
            ['-ab.example', false],
            ['ab-.example', false],
            ['exa mple.com', false],
            ['example..com', false],
            ['', false],
            [\str_repeat('a', 64) . '.com', false],
            [\str_repeat('a.', 127) . 'aa', false],
        ];
    }

    #[DataProvider('domainProvider')]
    public function testValidate(string $domain, bool $expected): void
    {
        $this->assertSame($expected, DomainValidator::validate($domain));
    }
}
