<?php

namespace CarlBennett\Tools\Libraries\Core;

class GlobalErrorHandler
{
    public static function createOverrides(): void
    {
        \set_error_handler('\\CarlBennett\\Tools\\Libraries\\Core\\GlobalErrorHandler::errorHandler', E_ALL);
        \set_exception_handler('\\CarlBennett\\Tools\\Libraries\\Core\\GlobalErrorHandler::exceptionHandler');
    }

    public static function exceptionHandler(\Throwable $ex): void
    {
        self::handleProblem([
            'exception' => [
                'class' => \get_class($ex),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'message' => $ex->getMessage(),
                'previous' => $ex->getPrevious(),
                'trace' => $ex->getTrace(),
            ],
        ]);
    }

    public static function errorHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext = []): bool
    {
        if (\error_reporting() & $errno !== $errno)
        {
            return false;
        }

        return self::handleProblem([
            'error' => [
                'code' => $errno,
                'file' => $errfile,
                'line' => $errline,
                'message' => $errstr,
                'context' => $errcontext,
                'trace' => \debug_backtrace(),
            ],
        ]);
    }

    private static function handleProblem(array $args): bool
    {
        while (\ob_get_level()) \ob_end_clean();

        \http_response_code(500);
        $display_errors = self::ToBool(\ini_get('display_errors') ?? 'Off');
        if ($display_errors)
        {
            \header('Content-Type: application/json;charset=utf-8');
            echo \json_encode($args, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
            return true;
        }
        else
        {
            echo 'Internal Server Error' . PHP_EOL;
            return true;
        }
    }

    private static function ToBool(string $value): bool
    {
        $lvalue = \strtolower($value);
        if ($lvalue == 'false') return false;
        if ($lvalue == 'off') return false;
        if ($lvalue == 'on') return true;
        if ($lvalue == 'no') return false;
        if ($lvalue == 'n') return false;
        if ($lvalue == 'true') return true;
        if ($lvalue == 'y') return true;
        if ($lvalue == 'yes') return true;
        return ($value == true);
    }
}
