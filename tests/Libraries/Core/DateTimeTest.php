<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\DateTime;
use \CarlBennett\Tools\Libraries\Core\DateTimeImmutable;
use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    public static function classProvider(): array
    {
        return [
            'mutable' => [DateTime::class],
            'immutable' => [DateTimeImmutable::class],
        ];
    }

    #[DataProvider('classProvider')]
    public function testJsonSerialize(string $class): void
    {
        $dt = new $class('2020-02-03 04:05:06', new \DateTimeZone('Etc/UTC'));
        $this->assertSame([
            'iso' => 'Mon, 03 Feb 2020 04:05:06 +0000',
            'tz' => 'Etc/UTC',
            'unix' => 1580702706,
        ], $dt->jsonSerialize());
        $this->assertSame(
            '{"iso":"Mon, 03 Feb 2020 04:05:06 +0000","tz":"Etc\/UTC","unix":1580702706}',
            \json_encode($dt)
        );
    }

    #[DataProvider('classProvider')]
    public function testToString(string $class): void
    {
        $dt = new $class('@1580702706');
        $this->assertSame('1580702706 Mon, 03 Feb 2020 04:05:06 +0000', (string) $dt);
    }

    #[DataProvider('classProvider')]
    public function testIsDateTimeInterface(string $class): void
    {
        $this->assertInstanceOf(\DateTimeInterface::class, new $class('now'));
    }
}
