<?php

namespace CarlBennett\Tools\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class Invite extends Model {

  public $date_accepted;
  public $date_invited;
  public $date_revoked;
  public $email;
  public $feedback;
  public $id;
  public $invited_by;
  public $invited_user;
  public $password_confirm;
  public $password_desired;
  public $record_updated;
  public $return;

}
