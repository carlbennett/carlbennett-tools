<?php

namespace CarlBennett\Tools\Tests\Libraries\User;

use \CarlBennett\Tools\Libraries\User\Gravatar;
use \PHPUnit\Framework\TestCase;

class GravatarTest extends TestCase
{
    public function testHashIsTrimmedLowercaseMd5(): void
    {
        $this->assertSame(\md5('user@example.com'), (new Gravatar('User@Example.com'))->getHash());
    }

    public function testUrl(): void
    {
        $g = new Gravatar('user@example.com');
        $hash = \md5('user@example.com');
        $this->assertSame('//www.gravatar.com/avatar/' . $hash, $g->getUrl());
        $this->assertSame('//www.gravatar.com/avatar/' . $hash . '?s=96&d=mp', $g->getUrl(96, 'mp'));
    }

    public function testJsonSerialize(): void
    {
        $g = new Gravatar('user@example.com');
        $this->assertSame(['email' => 'user@example.com', 'hash' => $g->getHash(), 'url' => $g->getUrl()], $g->jsonSerialize());
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        new Gravatar('not an email');
    }
}
