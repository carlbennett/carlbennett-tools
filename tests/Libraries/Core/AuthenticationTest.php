<?php

namespace CarlBennett\Tools\Tests\Libraries\Core;

use \CarlBennett\Tools\Libraries\Core\Authentication;
use \PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    protected function tearDown(): void
    {
        Authentication::setTimezone();
    }

    public function testSetTimezone(): void
    {
        $this->assertTrue(Authentication::setTimezone('America/Chicago'));
        $this->assertTrue(Authentication::setTimezone());
    }

    // Regression: the finally block read $tz, which was undefined when the timezone was invalid,
    // so the warning replaced the intended exception.
    public function testSetTimezoneRejectsInvalidTimezone(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        Authentication::setTimezone('Not/A_Timezone');
    }
}
