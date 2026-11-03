<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\Config;
use \PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = \tempnam(\sys_get_temp_dir(), 'tools-config-');
    }

    protected function tearDown(): void
    {
        @\unlink($this->file);
    }

    public function testDefaultFilePathIsEtcConfigJson(): void
    {
        $this->assertSame(
            \realpath(__DIR__ . '/../../../etc') . '/config.json',
            \realpath(\dirname(Config::defaultFilePath())) . '/' . \basename(Config::defaultFilePath())
        );
    }

    public function testSampleConfigIsValidJson(): void
    {
        $config = new Config(__DIR__ . '/../../../etc/config.sample.json');
        $this->assertArrayHasKey('crypt_cost', $config->root['pastes']);
        $this->assertArrayHasKey('crypt_cost', $config->root['users']);
    }

    public function testLoadAndSave(): void
    {
        \file_put_contents($this->file, '{"a":{"b":1}}');
        $config = new Config($this->file);
        $this->assertSame($this->file, $config->getFilePath());
        $this->assertSame(['a' => ['b' => 1]], $config->root);

        $config->root['html'] = '<b>';
        $config->save();

        // Regression: JSON_OBJECT_AS_ARRAY was passed to json_encode(), where it means JSON_HEX_TAG.
        $this->assertStringContainsString('"<b>"', \file_get_contents($this->file));
        $this->assertSame(['a' => ['b' => 1], 'html' => '<b>'], (new Config($this->file))->root);
    }

    public function testLoadThrowsOnMalformedJson(): void
    {
        \file_put_contents($this->file, '{not json');
        $this->expectException(\JsonException::class);
        new Config($this->file);
    }
}
