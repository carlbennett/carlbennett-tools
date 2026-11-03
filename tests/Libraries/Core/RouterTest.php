<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\Router;

class RouterTest extends EnvironmentTestCase
{
    protected function setUp(): void
    {
        self::resetQuery();
    }

    protected function tearDown(): void
    {
        self::resetQuery();
        parent::tearDown();
    }

    private static function resetQuery(): void
    {
        (new \ReflectionProperty(Router::class, 'args'))->setValue(null, null);
    }

    public function testConstructorThrows(): void
    {
        $this->expectException(\Error::class);
        new Router();
    }

    // Regression: getenv() returns false, not null, so the ?? '' fallback never applied.
    public function testRequestMethodIsEmptyStringWhenUnset(): void
    {
        $this->setEnv('REQUEST_METHOD', null);
        $this->assertSame('', Router::requestMethod());
    }

    public function testRequestMethod(): void
    {
        $this->setEnv('REQUEST_METHOD', 'POST');
        $this->assertSame(Router::METHOD_POST, Router::requestMethod());
    }

    public function testServerNamePrefersHttpHost(): void
    {
        $this->setEnv('HTTP_HOST', 'example.com');
        $this->setEnv('SERVER_NAME', 'server.example.com');
        $this->assertSame('example.com', Router::serverName());
    }

    public function testServerNameFallsBack(): void
    {
        $this->setEnv('HTTP_HOST', null);
        $this->setEnv('SERVER_NAME', 'server.example.com');
        $this->assertSame('server.example.com', Router::serverName());
    }

    public function testServerNameIsEmptyStringWhenUnset(): void
    {
        $this->setEnv('HTTP_HOST', null);
        $this->setEnv('SERVER_NAME', null);
        $this->setEnv('HOST', null);
        $this->assertSame('', Router::serverName());
    }

    public function testQueryParsesQueryString(): void
    {
        $this->setEnv('REQUEST_METHOD', 'GET');
        $this->setEnv('QUERY_STRING', 'a=1&b[]=x&b[]=y');
        $this->assertSame(['a' => '1', 'b' => ['x', 'y']], Router::query());
    }

    // Regression: parse_str() was given false when QUERY_STRING was unset.
    public function testQueryIsEmptyWhenQueryStringUnset(): void
    {
        $this->setEnv('REQUEST_METHOD', 'GET');
        $this->setEnv('QUERY_STRING', null);
        $this->assertSame([], Router::query());
    }

    public function testQueryIsCached(): void
    {
        $this->setEnv('REQUEST_METHOD', 'GET');
        $this->setEnv('QUERY_STRING', 'a=1');
        $this->assertSame(['a' => '1'], Router::query());
        $this->setEnv('QUERY_STRING', 'a=2');
        $this->assertSame(['a' => '1'], Router::query());
    }
}
