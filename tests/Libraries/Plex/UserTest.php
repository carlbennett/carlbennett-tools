<?php

namespace CarlBennett\Tools\Tests\Libraries\Plex;

use \CarlBennett\Tools\Libraries\Plex\User;
use \PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testNewUserDefaults(): void
    {
        $user = new User(null);
        $this->assertNull($user->getId());
        $this->assertNull($user->getPlexId());
        $this->assertNull($user->getPlexEmail());
        $this->assertSame(User::OPTION_DEFAULT, $user->getOptions());
    }

    // Regression: $plex_id was declared ?string, so ids round-tripped through a string.
    public function testPlexIdIsAnInteger(): void
    {
        $user = new User(null);
        $user->setPlexId(12345);
        $this->assertSame(12345, $user->getPlexId());
        $user->setPlexId(\PHP_INT_MAX);
        $this->assertSame(\PHP_INT_MAX, $user->getPlexId());
        $user->setPlexId(null);
        $this->assertNull($user->getPlexId());
    }

    public function testSetPlexIdRejectsNegative(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        (new User(null))->setPlexId(-1);
    }

    public function testOptions(): void
    {
        $user = new User(null);
        $user->setOption(User::OPTION_HIDDEN, true);
        $this->assertTrue($user->getOption(User::OPTION_HIDDEN));
        $this->assertFalse($user->getOption(User::OPTION_DISABLED));
        $user->setOption(User::OPTION_HIDDEN, false);
        $this->assertFalse($user->getOption(User::OPTION_HIDDEN));
    }

    public function testEmptyStringsBecomeNull(): void
    {
        $user = new User(null);
        $user->setPlexEmail('');
        $user->setPlexTitle('');
        $user->setPlexUsername('');
        $this->assertNull($user->getPlexEmail());
        $this->assertNull($user->getPlexTitle());
        $this->assertNull($user->getPlexUsername());
    }
}
