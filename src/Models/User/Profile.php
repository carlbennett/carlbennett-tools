<?php

namespace CarlBennett\Tools\Models\User;

class Profile extends \CarlBennett\Tools\Models\ContextUser
{
  public const ERROR_BIOGRAPHY_LENGTH = 'BIOGRAPHY_LENGTH';
  public const ERROR_DISPLAY_NAME_LENGTH = 'DISPLAY_NAME_LENGTH';
  public const ERROR_EMAIL_INVALID = 'EMAIL_INVALID';
  public const ERROR_EMAIL_LENGTH = 'EMAIL_LENGTH';
  public const ERROR_INTERNAL = 'INTERNAL';
  public const ERROR_INTERNAL_NOTES_LENGTH = 'INTERNAL_NOTES_LENGTH';
  public const ERROR_NONE = 'NONE';
  public const ERROR_TIMEZONE_INVALID = 'TIMEZONE_INVALID';
  public const ERROR_TIMEZONE_LENGTH = 'TIMEZONE_LENGTH';

  public $acl_invite_users;
  public $acl_manage_users;
  public $acl_pastebin_admin;
  public $acl_phpinfo;
  public $acl_plex_users;
  public $acl_whois_service;
  public $avatar;
  public $biography;
  public $date_added;
  public $date_banned;
  public $date_disabled;
  public $display_name;
  public $email;
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
  public $timezone;
}
