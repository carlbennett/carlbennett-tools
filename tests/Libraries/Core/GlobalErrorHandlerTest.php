<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\GlobalErrorHandler;
use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;

class GlobalErrorHandlerTest extends TestCase
{
    private string $log_file;
    private string|false $previous_log;

    protected function setUp(): void
    {
        $this->log_file = \tempnam(\sys_get_temp_dir(), 'tools-error-log-');
        $this->previous_log = \ini_set('error_log', $this->log_file);
    }

    protected function tearDown(): void
    {
        \ini_set('error_log', $this->previous_log === false ? '' : $this->previous_log);
        @\unlink($this->log_file);
    }

    public static function deprecationProvider(): array
    {
        return [
            'E_DEPRECATED' => [\E_DEPRECATED],
            'E_USER_DEPRECATED' => [\E_USER_DEPRECATED],
        ];
    }

    // Deprecations, e.g. from vendored libraries on a newer PHP, must be logged and the request
    // must continue, instead of being escalated to an HTTP 500.
    #[DataProvider('deprecationProvider')]
    public function testDeprecationsAreLoggedAndHandled(int $errno): void
    {
        $previous = \error_reporting(\E_ALL);
        try
        {
            $handled = GlobalErrorHandler::errorHandler($errno, 'old thing is deprecated', '/x/y.php', 42);
        }
        finally
        {
            \error_reporting($previous);
        }

        $this->assertTrue($handled);
        $this->assertStringContainsString(
            'PHP Deprecated: old thing is deprecated in /x/y.php on line 42',
            \file_get_contents($this->log_file)
        );
    }

    // Errors excluded from error_reporting() fall through to PHP's handler instead of failing the request.
    public function testIgnoredLevelsAreNotHandled(): void
    {
        $previous = \error_reporting(\E_ALL & ~\E_WARNING);
        try
        {
            $handled = GlobalErrorHandler::errorHandler(\E_WARNING, 'ignored', '/x/y.php', 1);
        }
        finally
        {
            \error_reporting($previous);
        }

        $this->assertFalse($handled);
        $this->assertSame('', \file_get_contents($this->log_file));
    }
}
