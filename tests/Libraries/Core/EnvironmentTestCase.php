<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \PHPUnit\Framework\TestCase;

/**
 * Sets request environment variables for a test and restores them afterwards.
 */
abstract class EnvironmentTestCase extends TestCase
{
    private array $saved_env = [];

    protected function setEnv(string $name, ?string $value): void
    {
        if (!\array_key_exists($name, $this->saved_env)) $this->saved_env[$name] = \getenv($name);
        \putenv(\is_null($value) ? $name : $name . '=' . $value);
    }

    protected function tearDown(): void
    {
        foreach ($this->saved_env as $name => $value)
        {
            \putenv($value === false ? $name : $name . '=' . $value);
        }
        $this->saved_env = [];
    }
}
