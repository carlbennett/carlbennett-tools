<?php

namespace CarlBennett\Tools\Models\User;

use \CarlBennett\Tools\Models\ActiveUser as ActiveUserModel;

class Profile extends ActiveUserModel {

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
  public $invites_sent;
  public $invites_used;
  public $password_confirm;
  public $password_desired;
  public $record_updated;
  public $return;

}
