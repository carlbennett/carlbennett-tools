<?php

namespace CarlBennett\Tools\Tests\Libraries;

use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;

class MaxConstantsTest extends TestCase
{
    public static function classProvider(): array
    {
        return [
            [\CarlBennett\Tools\Libraries\PasteObject::class],
            [\CarlBennett\Tools\Libraries\Plex\User::class],
            [\CarlBennett\Tools\Libraries\User\Invite::class],
            [\CarlBennett\Tools\Libraries\User\User::class],
        ];
    }

    // Regression: 0xFFFFFFFFFFFFFFFF exceeds PHP's integer range, so those constants were floats.
    #[DataProvider('classProvider')]
    public function testMaxConstantsAreIntegers(string $class): void
    {
        $found = 0;
        foreach ((new \ReflectionClass($class))->getConstants() as $name => $value)
        {
            if (\substr($name, 0, 4) !== 'MAX_') continue;
            $this->assertIsInt($value, $class . '::' . $name);
            $found++;
        }
        $this->assertGreaterThan(0, $found);
    }
}
