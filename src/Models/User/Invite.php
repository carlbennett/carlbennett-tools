<?php

namespace CarlBennett\Tools\Models\User;

class Invite extends \CarlBennett\Tools\Models\ActiveUser
{
  public const ERROR_EMAIL_ALREADY_INVITED = 'EMAIL_ALREADY_INVITED';
  public const ERROR_EMAIL_INVALID = 'EMAIL_INVALID';
  public const ERROR_ID_MALFORMED = 'ID_MALFORMED';
  public const ERROR_ID_NOT_FOUND = 'ID_NOT_FOUND';
  public const ERROR_INTERNAL_ERROR = 'INTERNAL_ERROR';
  public const ERROR_INVITES_AVAILABLE_ZERO = 'INVITES_AVAILABLE_ZERO';
  public const ERROR_SUCCESS = 'SUCCESS';

  public $date_accepted;
  public $date_invited;
  public $date_revoked;
  public $email;
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
