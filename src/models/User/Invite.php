<?php

namespace CarlBennett\Tools\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class Invite extends Model {

  const ERROR_SUCCESS = 0;
  const ERROR_INTERNAL_ERROR = 1;
  const ERROR_ID_MALFORMED = 2;
  const ERROR_ID_NOT_FOUND = 3;

  public $auth_user;
  public $date_accepted;
  public $date_invited;
  public $date_revoked;
  public $email;
  public $error;
  public $feedback;
  public $id;
  public $invited_by;
  public $invited_user;
  public $invites_available;
  public $invites_used;
  public $password_confirm;
  public $password_desired;
  public $record_updated;
  public $return;

}
