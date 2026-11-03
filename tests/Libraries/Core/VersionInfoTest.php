<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\VersionInfo;
use \PHPUnit\Framework\TestCase;

class VersionInfoTest extends TestCase
{
    public function testConstructorThrows(): void
    {
        $this->expectException(\Error::class);
        new VersionInfo();
    }

    // Regression: the path pointed at src/etc/ instead of the repository's etc/ directory.
    public function testVersionInfoFileIsInEtc(): void
    {
        $this->assertSame(
            \realpath(__DIR__ . '/../../../etc'),
            \realpath(\dirname(VersionInfo::VERSION_INFO_FILE))
        );
    }
}
