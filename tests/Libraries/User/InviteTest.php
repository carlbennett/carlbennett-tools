<?php

namespace CarlBennett\Tools\Tests\Libraries\User;

use \CarlBennett\Tools\Libraries\User\Invite;
use \PHPUnit\Framework\TestCase;

class InviteTest extends TestCase
{
    private const UUID = '31952e1c-d05a-44a0-b749-6d892cc96d3a';

    public function testNewInviteHasNoSenderOrRecipient(): void
    {
        $invite = new Invite(null);
        $this->assertNull($invite->getId());
        $this->assertNull($invite->getInvitedBy());
        $this->assertNull($invite->getInvitedById());
        $this->assertNull($invite->getInvitedUser());
        $this->assertNull($invite->getInvitedUserId());
        $this->assertNull($invite->getDateAccepted());
        $this->assertNull($invite->getDateRevoked());
        $this->assertSame('', $invite->getEmail());
    }

    // Regression: the UUID string was returned from methods declared to return ?User, which is a
    // TypeError under strict_types. The UUIDs are now available from their own getters.
    public function testInvitedIdsAreStoredAsUuids(): void
    {
        $invite = new Invite(null);
        $invite->setInvitedBy(self::UUID);
        $invite->setInvitedUser(self::UUID);
        $this->assertSame(self::UUID, $invite->getInvitedById());
        $this->assertSame(self::UUID, $invite->getInvitedUserId());
    }

    public function testSetInvitedByRejectsNonUuid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        (new Invite(null))->setInvitedBy('not-a-uuid');
    }

    public function testSetEmail(): void
    {
        $invite = new Invite(null);
        $invite->setEmail('user@example.com');
        $this->assertSame('user@example.com', $invite->getEmail());
    }

    public function testSetEmailRejectsInvalidEmail(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        (new Invite(null))->setEmail('not an email');
    }

    public function testSetEmailRejectsLongEmail(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        (new Invite(null))->setEmail(\str_repeat('a', Invite::MAX_EMAIL) . '@example.com');
    }

    public function testSetIdRejectsNonUuid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        (new Invite(null))->setId('nope');
    }
}
