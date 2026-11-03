<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\ArrayFlattener;
use \CarlBennett\Tools\Libraries\Core\DateTimeImmutable;
use \PHPUnit\Framework\TestCase;

class ArrayFlattenerTest extends TestCase
{
    public function testConstructorThrows(): void
    {
        $this->expectException(\Error::class);
        new ArrayFlattener();
    }

    public function testFlattensScalars(): void
    {
        $this->assertSame(
            "a 1\nb true\nc false\nd null\ne text\n",
            \str_replace(\PHP_EOL, "\n", ArrayFlattener::flatten(['a' => 1, 'b' => true, 'c' => false, 'd' => null, 'e' => 'text']))
        );
    }

    public function testFlattensNestedArrays(): void
    {
        $this->assertSame(
            "a_b_c 1\na_d 2\n",
            \str_replace(\PHP_EOL, "\n", ArrayFlattener::flatten(['a' => ['b' => ['c' => 1], 'd' => 2]]))
        );
    }

    public function testFlattensEmptyArrayAsKey(): void
    {
        $this->assertSame("a\n", \str_replace(\PHP_EOL, "\n", ArrayFlattener::flatten(['a' => []])));
    }

    public function testFlattensObjectProperties(): void
    {
        $o = new \stdClass();
        $o->x = 'y';
        $this->assertSame("x y\n", \str_replace(\PHP_EOL, "\n", ArrayFlattener::flatten($o)));
    }

    public function testFlattensDateTime(): void
    {
        $dt = new DateTimeImmutable('@0');
        $out = ArrayFlattener::flatten(['t' => $dt]);
        $this->assertStringContainsString('t_iso Thu, 01 Jan 1970 00:00:00 +0000', $out);
        $this->assertStringContainsString('t_tz +00:00', $out);
        $this->assertStringContainsString('t_unix 0', $out);
    }

    // Regression: the recursive call passed $depth++, so siblings got deeper instead of children,
    // and a long list of siblings was cut off at MAX_DEPTH.
    public function testManySiblingsAreNotTruncated(): void
    {
        $siblings = \array_fill(0, ArrayFlattener::MAX_DEPTH + 5, 'v');
        $out = ArrayFlattener::flatten(['list' => $siblings]);
        $last = ArrayFlattener::MAX_DEPTH + 4;
        $this->assertStringContainsString("list_{$last} v", $out);
    }

    public function testDeepNestingStopsAtMaxDepth(): void
    {
        $value = 'leaf';
        for ($i = 0; $i < ArrayFlattener::MAX_DEPTH + 5; $i++) $value = ['k' => $value];
        $out = ArrayFlattener::flatten(['root' => $value]);
        $this->assertStringNotContainsString('leaf', $out);
        $this->assertSame(1, \substr_count($out, \PHP_EOL));
    }
}
