<?php

namespace CarlBennett\Tools\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class Invite extends Model {

  public $feedback;
  public $invite_code;
  public $password_desired;
  public $password_confirm;
  public $return;

}
