<?php

namespace CarlBennett\Tools\Models\Plex\Users;

class UserForm extends \CarlBennett\Tools\Models\ActiveUser
{
  public const ERROR_EMPTY_TITLE_USERNAME_AND_EMAIL = 'EMPTY_TITLE_USERNAME_AND_EMAIL';
  public const ERROR_EMPTY_USERNAME_AND_EMAIL = 'EMPTY_USERNAME_AND_EMAIL';
  public const ERROR_INTERNAL_ERROR = 'INTERNAL';
  public const ERROR_INVALID_RISK = 'INVALID_RISK';
  public const ERROR_LINKED_USER_ALREADY_ASSIGNED = 'LINKED_USER_ALREADY_ASSIGNED';
  public const ERROR_NULL_PLEX_USER = 'NULL_PLEX_USER';
  public const ERROR_SUCCESS = 'SUCCESS';

  public $disabled;
  public $expired;
  public $hidden;
  public $homeuser;
  public $id;
  public $notes;
  public $plex_email;
  public $plex_thumb;
  public $plex_title;
  public $plex_user;
  public $plex_username;
  public $risk;
  public $user_id;
}
