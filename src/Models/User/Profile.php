<?php

namespace CarlBennett\Tools\Models\User;

use \DateTimeInterface;

class Profile extends \CarlBennett\Tools\Models\User\ContextUser implements \JsonSerializable
{
    public const ERROR_BIOGRAPHY_LENGTH = 'BIOGRAPHY_LENGTH';
    public const ERROR_DISPLAY_NAME_LENGTH = 'DISPLAY_NAME_LENGTH';
    public const ERROR_EMAIL_INVALID = 'EMAIL_INVALID';
    public const ERROR_EMAIL_LENGTH = 'EMAIL_LENGTH';
    public const ERROR_INTERNAL = 'INTERNAL';
    public const ERROR_INTERNAL_NOTES_LENGTH = 'INTERNAL_NOTES_LENGTH';
    public const ERROR_NONE = 'NONE';
    public const ERROR_TIMEZONE_INVALID = 'TIMEZONE_INVALID';
    public const ERROR_TIMEZONE_LENGTH = 'TIMEZONE_LENGTH';

    public ?bool $acl_invite_users = null;
    public ?bool $acl_manage_users = null;
    public ?bool $acl_pastebin_admin = null;
    public ?bool $acl_phpinfo = null;
    public ?bool $acl_plex_users = null;
    public ?bool $acl_whois_service = null;
    public ?string $avatar = null;
    public ?string $biography = null;
    public DateTimeInterface|string|null $date_added = null;
    public DateTimeInterface|string|null $date_banned = null;
    public DateTimeInterface|string|null $date_disabled = null;
    public ?string $display_name = null;
    public ?string $email = null;
    public mixed $feedback = null;
    public int|string|null $id = null;
    public ?string $internal_notes = null;
    public mixed $invited_by = null;
    public ?int $invites_available = null;
    public ?int $invites_used = null;
    public ?bool $is_banned = null;
    public ?bool $is_disabled = null;
    public mixed $manage = null;
    public mixed $password_confirm = null;
    public mixed $password_desired = null;
    public DateTimeInterface|string|null $record_updated = null;
    public ?string $return = null;
    public mixed $self_manage = null;
    public mixed $timezone = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'acl_invite_users' => $this->acl_invite_users,
            'acl_manage_users' => $this->acl_manage_users,
            'acl_pastebin_admin' => $this->acl_pastebin_admin,
            'acl_phpinfo' => $this->acl_phpinfo,
            'acl_plex_users' => $this->acl_plex_users,
            'acl_whois_service' => $this->acl_whois_service,
            'avatar' => $this->avatar,
            'biography' => $this->biography,
            'date_added' => $this->date_added,
            'date_banned' => $this->date_banned,
            'date_disabled' => $this->date_disabled,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'feedback' => $this->feedback,
            'id' => $this->id,
            'internal_notes' => $this->internal_notes,
            'invited_by' => $this->invited_by,
            'invites_available' => $this->invites_available,
            'invites_used' => $this->invites_used,
            'is_banned' => $this->is_banned,
            'is_disabled' => $this->is_disabled,
            'manage' => $this->manage,
            'password_confirm' => $this->password_confirm,
            'password_desired' => $this->password_desired,
            'record_updated' => $this->record_updated,
            'return' => $this->return,
            'self_manage' => $this->self_manage,
            'timezone' => $this->timezone,
        ]);
    }
}
