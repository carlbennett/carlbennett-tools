<?php

namespace CarlBennett\Tools\Models\Plex\Users;

use \CarlBennett\MVC\Libraries\Model;

class UserForm extends Model {

  const ERROR_SUCCESS = 0;
  const ERROR_INTERNAL_ERROR = 1;
  const ERROR_NULL_PLEX_USER = 2;
  const ERROR_EMPTY_USERNAME_AND_EMAIL = 3;
  const ERROR_INVALID_RISK = 4;

  public $action;
  public $email;
  public $error;
  public $id;
  public $notes;
  public $plex_user;
  public $risk;
  public $user;
  public $username;

}
