<?php

namespace CarlBennett\Tools\Models\User;

class Invite extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public const ERROR_EMAIL_ALREADY_INVITED = 'EMAIL_ALREADY_INVITED';
    public const ERROR_EMAIL_INVALID = 'EMAIL_INVALID';
    public const ERROR_ID_MALFORMED = 'ID_MALFORMED';
    public const ERROR_ID_NOT_FOUND = 'ID_NOT_FOUND';
    public const ERROR_INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const ERROR_INVITES_AVAILABLE_ZERO = 'INVITES_AVAILABLE_ZERO';
    public const ERROR_SUCCESS = 'SUCCESS';

    public mixed $date_accepted = null;
    public mixed $date_invited = null;
    public mixed $date_revoked = null;
    public mixed $email = null;
    public mixed $feedback = null;
    public mixed $id = null;
    public mixed $invited_by = null;
    public mixed $invited_user = null;
    public mixed $invites_available = null;
    public mixed $invites_sent = null;
    public mixed $invites_used = null;
    public mixed $password_confirm = null;
    public mixed $password_desired = null;
    public mixed $record_updated = null;
    public ?string $return = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'date_accepted' => $this->date_accepted,
            'date_invited' => $this->date_invited,
            'date_revoked' => $this->date_revoked,
            'email' => $this->email,
            'feedback' => $this->feedback,
            'id' => $this->id,
            'invited_by' => $this->invited_by,
            'invited_user' => $this->invited_user,
            'invites_available' => $this->invites_available,
            'invites_sent' => $this->invites_sent,
            'invites_used' => $this->invites_used,
            'password_confirm' => $this->password_confirm,
            'password_desired' => $this->password_desired,
            'record_updated' => $this->record_updated,
            'return' => $this->return,
        ]);
    }
}
