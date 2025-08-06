<?php

namespace CarlBennett\Tools\Models\Plex\Users;

class UserForm extends \CarlBennett\Tools\Models\User\ActiveUser implements \JsonSerializable
{
    public const ERROR_EMPTY_TITLE_USERNAME_AND_EMAIL = 'EMPTY_TITLE_USERNAME_AND_EMAIL';
    public const ERROR_EMPTY_USERNAME_AND_EMAIL = 'EMPTY_USERNAME_AND_EMAIL';
    public const ERROR_INTERNAL_ERROR = 'INTERNAL';
    public const ERROR_INVALID_RISK = 'INVALID_RISK';
    public const ERROR_LINKED_USER_ALREADY_ASSIGNED = 'LINKED_USER_ALREADY_ASSIGNED';
    public const ERROR_NULL_PLEX_USER = 'NULL_PLEX_USER';
    public const ERROR_SUCCESS = 'SUCCESS';

    public mixed $disabled = null;
    public mixed $expired = null;
    public mixed $hidden = null;
    public mixed $homeuser = null;
    public mixed $id = null;
    public mixed $notes = null;
    public mixed $plex_email = null;
    public mixed $plex_thumb = null;
    public mixed $plex_title = null;
    public mixed $plex_user = null;
    public mixed $plex_username = null;
    public mixed $risk = null;
    public mixed $user_id = null;

    /**
     * Implements the JSON serialization function from the JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'disabled' => $this->disabled,
            'expired' => $this->expired,
            'hidden' => $this->hidden,
            'homeuser' => $this->homeuser,
            'id' => $this->id,
            'notes' => $this->notes,
            'plex_email' => $this->plex_email,
            'plex_thumb' => $this->plex_thumb,
            'plex_title' => $this->plex_title,
            'plex_user' => $this->plex_user,
            'plex_username' => $this->plex_username,
            'risk' => $this->risk,
            'user_id' => $this->user_id,
        ]);
    }
}
