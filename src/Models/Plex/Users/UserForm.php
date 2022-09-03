<?php

namespace CarlBennett\Tools\Models\Plex\Users;

use \CarlBennett\Tools\Models\ActiveUser as ActiveUserModel;

class UserForm extends ActiveUserModel {

  const ERROR_SUCCESS = 0;
  const ERROR_INTERNAL_ERROR = 1;
  const ERROR_NULL_PLEX_USER = 2;
  const ERROR_EMPTY_TITLE_USERNAME_AND_EMAIL = 3;
  const ERROR_INVALID_RISK = 4;
  const ERROR_LINKED_USER_ALREADY_ASSIGNED = 5;

  public $disabled;
  public $error;
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
