<?php

namespace CarlBennett\Tools\Models\User;

use \CarlBennett\Tools\Models\ActiveUser as ActiveUserModel;

class Profile extends ActiveUserModel {

  const ERROR_NONE = 0;
  const ERROR_INTERNAL = 1;
  const ERROR_EMAIL_INVALID = 2;
  const ERROR_EMAIL_LENGTH = 3;
  const ERROR_DISPLAY_NAME_LENGTH = 4;
  const ERROR_INTERNAL_NOTES_LENGTH = 5;
  const ERROR_TIMEZONE_INVALID = 6;
  const ERROR_TIMEZONE_LENGTH = 7;
  const ERROR_BIOGRAPHY_LENGTH = 8;

  public $acl_invite_users;
  public $acl_manage_users;
  public $acl_pastebin_admin;
  public $acl_phpinfo;
  public $acl_plex_users;
  public $acl_whois_service;
  public $avatar;
  public $date_added;
  public $date_banned;
  public $date_disabled;
  public $display_name;
  public $email;
  public $error;
  public $feedback;
  public $id;
  public $internal_notes;
  public $invited_by;
  public $invites_available;
  public $invites_used;
  public $is_banned;
  public $is_disabled;
  public $manage;
  public $password_confirm;
  public $password_desired;
  public $record_updated;
  public $return;
  public $self_manage;
  public $user;

}
